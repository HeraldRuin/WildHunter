<?php
namespace Modules\User\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Matrix\Exception;
use Modules\Boat\Models\Boat;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Enquiry;
use Modules\Booking\Models\Service;
use Modules\Booking\Services\BookingInvitationService;
use Modules\Booking\Services\BookingStatusService;
use Modules\Booking\Services\BookingUserService;
use Modules\Booking\Services\Calculation\BookingCalculatingService;
use Modules\Car\Models\Car;
use Modules\Event\Models\Event;
use Modules\Flight\Models\Flight;
use Modules\FrontendController;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Services\AddDataInView;
use Modules\Space\Models\Space;
use Modules\Tour\Models\Tour;
use Modules\User\Events\NewVendorRegistered;
use Modules\User\Events\UserSubscriberSubmit;
use Modules\User\Models\Subscriber;
use Modules\User\Models\User;
use Modules\User\Models\UserWeapon;
use Modules\Vendor\Models\VendorRequest;
use Modules\Weapon\Models\Caliber;
use Modules\Weapon\Models\WeaponType;
use Validator;

class UserController extends FrontendController
{
    use AuthenticatesUsers;

    protected $enquiryClass;
    private Booking $booking;
    protected AddDataInView $cabinetService;

    public function __construct(Booking $booking, Enquiry $enquiry, AddDataInView $cabinetService,
        protected BookingCalculatingService $bookingCalculatingService,
        protected BookingUserService $bookingUserService,
        protected BookingInvitationService $bookingInvitationService,
        protected BookingStatusService $bookingStatusService)
    {
        $this->enquiryClass = $enquiry;
        parent::__construct();
        $this->booking = $booking;
        $this->cabinetService = $cabinetService;
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('baseadmin') && $user->hasPermission('baseAdmin_dashboard_access')) {
            $view = 'User::frontend.dashboardBaseAdmin';
            $data = $this->getBaseAdminDashboardData($user);
        } elseif ($user->hasRole('hunter') && $user->hasPermission('hunter_dashboard_access')) {
            $view = 'User::frontend.dashboardHunter';
            $data = $this->getVendorDashboardData($user);
        } else {
            abort(403);
        }

        $f = strtotime('monday this week');
        $data = [
            'recent_bookings'    => Booking::getRecentBookings(),
            'top_cards'          => Booking::getTopCardsReport(),
            'earning_chart_data' => Booking::getDashboardChartData($f, time())
        ];

        $data['user'] = $user;
        $data['isAdmin'] = $user->hasRole('administrator');
        $data['viewAdminCabinet'] = $user->hasRole('administrator');

        return view($view, $data);
    }
    protected function getVendorDashboardData($user): array
    {
        return [
            'cards_report'       => $this->booking->getTopCardsReportForVendor($user->id),
            'earning_chart_data' => $this->booking->getEarningChartDataForVendor(strtotime('monday this week'), time(), $user->id),
            'page_title'         => __("Vendor Dashboard"),
            'breadcrumbs'        => [
                ['name' => __('Dashboard'), 'class' => 'active']
            ]
        ];
    }
    protected function getBaseAdminDashboardData($user): array
    {
        return [
            'cards_report'       => $this->booking->getTopCardsReportForBaseAdmin($user->id),
            'earning_chart_data' => $this->booking->getEarningChartDataForVendor(strtotime('monday this week'), time(), $user->id),
            'page_title'         => __("BaseAdmin Dashboard"),
            'breadcrumbs'        => [
                ['name' => __('Dashboard'), 'class' => 'active']
            ]
        ];
    }

    public function reloadChart(Request $request)
    {
        $chart = $request->input('chart');
        $user_id = Auth::id();
        switch ($chart) {
            case "earning":
                $from = $request->input('from');
                $to = $request->input('to');
                return $this->sendSuccess([
                    'data' => $this->booking->getEarningChartDataForVendor(strtotime($from), strtotime($to), $user_id)
                ]);
                break;
        }
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $userWeapons = $user->weapons->map(function ($weapon) {
            return [
                'id'                    => $weapon->id,
                'hunter_license_number' => $weapon->hunter_license_number,
                'hunter_license_date'   => $weapon->hunter_license_date,
                'weapon_type_id'        => $weapon->weapon_type_id,
                'caliber'               => $weapon->caliber,
            ];
        })->values();
        $data = [
            'user'         => $user,
            'page_title'       => __("Profile"),
            'weapons' => WeaponType::all(),
            'calibers' => Caliber::all(),
            'userWeapons'  => $userWeapons,
            'breadcrumbs'      => [
                [
                    'name'  => __('Setting'),
                    'class' => 'active'
                ]
            ],
            'is_vendor_access' => $this->hasPermission('hunter_dashboard_access')
        ];

        if ($user->hasRole('baseadmin')){
          return view('User::frontend.profile.profile_base_admin', $data);
        }else {
           return view('User::frontend.profile.profile_hunter', $data);
        }
    }

    public function profileUpdate(Request $request)
    {
        if(is_demo_mode()){
            return back()->with('error',"Demo mode: disabled");
        }
        $user = Auth::user();
        $messages = [
            'user_name.required'      => __('The User name field is required.'),
        ];
        $request->validate([
            'first_name' => 'required|max:255',
            'last_name'  => 'required|max:255',
            'email'      => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'user_name'=> [
                'required',
                'max:255',
                'min:4',
                'string',
                'alpha_dash',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone'       => [
                'required',
                Rule::unique('users')->ignore($user->id)
            ],
        ],$messages);
        $input = $request->except('bio');
        $user->fill($input);
        $user->bio = clean($request->input('bio'));
        $user->birthday = date("Y-m-d", strtotime($user->birthday));
        $user->user_name = Str::slug( $request->input('user_name') ,"_");
        $user->save();

        if ($request->filled('weapons')) {
            foreach($request->weapons as $weapon) {
                if (
                    empty($weapon['hunter_license_number']) &&
                    empty($weapon['hunter_license_date']) &&
                    empty($weapon['weapon_type_id']) &&
                    empty($weapon['caliber'])
                ) {
                    continue;
                }

                $validatedWeapon = validator($weapon, [
                    'hunter_license_number' => 'required|string|max:255',
                    'hunter_license_date' => 'required|date',
                    'weapon_type_id' => 'required|integer',
                    'caliber' => 'required|integer',
                ])->validate();

                UserWeapon::updateOrCreate(
                    ['id' => $weapon['id'] ?? null],
                    [
                        'user_id' => $user->id,
                        'hunter_billet_number' => $request->hunter_billet_number,
                        'hunter_license_number' => $validatedWeapon['hunter_license_number'],
                        'hunter_license_date' => $validatedWeapon['hunter_license_date'],
                        'weapon_type_id' => $validatedWeapon['weapon_type_id'],
                        'caliber' => $validatedWeapon['caliber'],
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Update successfully'));
    }

    public function bookingHistory(Request $request)
    {
        $cabinetData = $this->cabinetService->getCabinetData();

        $authUser = Auth::user();
        $bookingId = $request->input('booking_id');
        $code = $request->input('code');

        $this->bookingInvitationService->handleCodeAccess($code, $authUser);

        if (is_baseAdmin()){
            $userRole = 'baseadmin';
            $hotelId = $authUser->hotels->first()->id;
            $bookings = $this->booking->getBookingHistoryForAdminBase($hotelId, $request->input('status'), $bookingId);
        }else {
            $userRole = 'hunter';
            $bookings = $this->booking->getBookingHistory($request->input('status'), $authUser->id, false, false, false, $bookingId);
        }

        $statuses = $this->bookingStatusService->getAllowedStatuses($userRole);

        //Вывод калькуляции в главном шаблоне в колонке оплата админа базы
        $service = app(BookingCalculatingService::class);
        $user = Auth::user();

        $bookings->getCollection()->transform(function ($booking) use ($service, $user) {

            $booking->calculation = $service->calculate($booking, $user);
            return $booking;
        });

        $data = array_merge($cabinetData, [
            'userRole' => $userRole,
            'bookings' => $bookings,
            'hotelSlug' => $authUser->hotels?->first()?->slug,
            'statues'     => $statuses,
            'dropdownStatuses'  => $this->bookingStatusService->getDropdownStatuses(),
            'bookingId' => $bookingId,
            'bookingCode' => $code,
            'breadcrumbs' => [
                [
                    'name'  => __('Booking History'),
                    'class' => 'active'
                ]
            ],
            'page_title'  => __("Booking History"),
        ]);

        return view('User::frontend.bookingHistory', $data);
    }

    public function subscribe(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255'
        ]);
        $check = Subscriber::withTrashed()->where('email', $request->input('email'))->first();
        if ($check) {
            if ($check->trashed()) {
                $check->restore();
                return $this->sendSuccess([], __('Thank you for subscribing'));
            }
            return $this->sendError(__('You are already subscribed'));
        } else {
            $a = new Subscriber();
            $a->email = $request->input('email');
            $a->first_name = $request->input('first_name');
            $a->last_name = $request->input('last_name');
            $a->save();

            event(new UserSubscriberSubmit($a));

            return $this->sendSuccess([], __('Thank you for subscribing'));
        }
    }

    public function upgradeVendor(Request $request){
        $user = Auth::user();
        $vendorRequest = VendorRequest::query()->where("user_id",$user->id)->where("status","pending")->first();
        if(!empty($vendorRequest)){
            return redirect()->back()->with('warning', __("You have just done the become vendor request, please wait for the Admin's approved"));
        }
        // check vendor auto approved
        $vendorAutoApproved = setting_item('vendor_auto_approved');
         $dataVendor['role_request'] = setting_item('vendor_role');
        if ($vendorAutoApproved) {
            if ($dataVendor['role_request']) {
                $user->assignRole($dataVendor['role_request']);
            }
            $dataVendor['status'] = 'approved';
            $dataVendor['approved_time'] = now();
        } else {
            $dataVendor['status'] = 'pending';
        }
        $vendorRequestData = $user->vendorRequest()->save(new VendorRequest($dataVendor));
        try {
            event(new NewVendorRegistered($user, $vendorRequestData));
        } catch (Exception $exception) {
            Log::warning("NewVendorRegistered: " . $exception->getMessage());
        }
        return redirect()->back()->with('success', __('Request vendor success!'));
    }



    public function permanentlyDelete(Request $request){
        if(is_demo_mode()){
            return back()->with('error',"Demo mode: disabled");
        }
        if(!empty(setting_item('user_enable_permanently_delete')))
        {
            $user = Auth::user();
            \DB::beginTransaction();
            try {
                Service::where('author_id',$user->id)->delete();
                Tour::where('author_id',$user->id)->delete();
                Car::where('author_id',$user->id)->delete();
                Space::where('author_id',$user->id)->delete();
                Hotel::where('author_id',$user->id)->delete();
                Event::where('author_id',$user->id)->delete();
                Boat::where('author_id',$user->id)->delete();
                Flight::where('author_id',$user->id)->delete();
                $user->sendEmailPermanentlyDelete();
                $user->delete();
                \DB::commit();
                Auth::logout();
                if(is_api()){
                    return $this->sendSuccess([],'Deleted');
                }
                return redirect(route('home'));
            }catch (\Exception $exception){
                \DB::rollBack();
            }
        }
        if(is_api()){
            return $this->sendError('Error. You can\'t permanently delete');
        }
        return back()->with('error',__('Error. You can\'t permanently delete'));

    }

    public function searchUser(Request $request): JsonResponse
    {
        $query = trim($request->get('query'));

        $users = User::where('id', $query)
            ->select(['id', 'user_name', 'first_name'])
            ->get();

        return response()->json($users);
    }

    public function searchHunters(Request $request): JsonResponse
    {
        $query = trim($request->get('query'));
        return response()->json($this->bookingUserService->searchHunters($query, (int) $request->get('booking_id')));
    }
}
