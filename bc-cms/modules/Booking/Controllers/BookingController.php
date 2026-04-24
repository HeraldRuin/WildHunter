<?php

namespace Modules\Booking\Controllers;

use App\Helpers\ReCaptchaEngine;
use App\Service\MailService;
use App\User;
use DomainException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalTrophy;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\DTO\ReplaceHunterData;
use Modules\Booking\DTO\StoreAddetionalData;
use Modules\Booking\DTO\StoreFoodData;
use Modules\Booking\DTO\StorePenaltyData;
use Modules\Booking\DTO\StorePreparationData;
use Modules\Booking\DTO\StoreSpendingData;
use Modules\Booking\DTO\StoreTrophyData;
use Modules\Booking\Emails\HunterMessageEmail;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Events\BookingStartCollectionEvent;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Events\EnquirySendEvent;
use Modules\Booking\Events\SetPaidAmountEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Booking\Models\Enquiry;
use Modules\Booking\Models\Payment;
use Modules\Booking\Requests\EmailHunterRequest;
use Modules\Booking\Requests\InviteHunterByEmailRequest;
use Modules\Booking\Requests\InviteHunterRequest;
use Modules\Booking\Requests\StoreAddetionalRequest;
use Modules\Booking\Requests\StoreFoodRequest;
use Modules\Booking\Requests\StorePenaltyRequest;
use Modules\Booking\Requests\StorePreparationRequest;
use Modules\Booking\Requests\StoreSpendingRequest;
use Modules\Booking\Requests\StoreTrophyRequest;
use Modules\Booking\Services\BookingCollectionService;
use Modules\Booking\Services\BookingInvitationService;
use Modules\Booking\Services\BookingNotificationService;
use Modules\Booking\Services\BookingNumberService;
use Modules\Booking\Services\BookingServiceManager;
use Modules\Booking\Services\BookingTimerService;
use Modules\Booking\Services\BookingUserService;
use Modules\Booking\Services\Calculation\BookingCalculatingService;
use Modules\Booking\Services\Payments\PaymentService;
use Modules\User\Events\SendMailUserRegistered;

class BookingController extends \App\Http\Controllers\Controller
{
    use AuthorizesRequests;

    protected $booking;
    protected $enquiryClass;
    protected $bookingInst;
    protected $animalClass;

    public function __construct(
        Booking $booking,
        Enquiry $enquiryClass,
        Animal $animalClass,
        protected BookingTimerService $bookingTimerService,
        protected BookingCalculatingService $bookingCalculatingService,
        protected BookingCollectionService $bookingCollectionService,
        protected PaymentService $paymentService,
        protected BookingServiceManager $serviceManager,
        protected BookingNumberService $bookingNumberService,
        protected BookingInvitationService $bookingInvitationService,
        protected BookingNotificationService $bookingNotificationService,
        protected MailService $mailService,
        protected BookingUserService $bookingUserService)
    {
        $this->booking = $booking;
        $this->enquiryClass = $enquiryClass;
        $this->animalClass = $animalClass;
    }

    protected function validateCheckout($code)
    {
        if (!is_enable_guest_checkout() and !Auth::check()) {
            $error = __("You have to login in to do this");
            if (\request()->isJson()) {
                return $this->sendError($error)->setStatusCode(401);
            }
            return redirect(route('login', ['redirect' => \request()->fullUrl()]))->with('error', $error);
        }

        $booking = $this->booking::where('code', $code)->first();

        $this->bookingInst = $booking;

        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {

            // Check if booking does not have customer_id
            if (\auth()->user() && empty($booking->customer_id)) {
                // Then assign booking to current user
                $booking->customer_id = Auth::id();
                $booking->save();
            } else {
                // Otherwise abort with 404
                abort(404);
            }
        }
        return true;
    }

    public function checkout($code)
    {
        $res = $this->validateCheckout($code);
        if ($res !== true) return $res;

        $booking = $this->bookingInst;
        $trophyPrice = AnimalTrophy::where('animal_id', $booking->animal_id)
            ->get()
            ->map(fn($t) => $t->priceForHotel($booking->hotel_id))
            ->filter()
            ->sort()
            ->values();

        $trophyPrice = match ($trophyPrice->count()) {
            0 => null,
            1 => round($trophyPrice[0]),
            default => round($trophyPrice->first()) . ' - ' . round($trophyPrice->last()),
        };

        $is_api = request()->segment(1) == 'api';

        $data = [
            'page_title' => __('Checkout'),
            'booking'    => $booking,
            'service'    => $booking->service,
            'animal_service' => Animal::where('id', $booking->animal_id)->first(),
            'gateways' => get_available_gateways(),
            'user'       => auth()->user(),
            'is_api'     => $is_api,
            'booking_type'  => $booking->type,
            'all_total'  => $booking->total + $booking->amount_hunting,
            'trophyPrice' => $trophyPrice
        ];
        return view('Booking::frontend/checkout', $data);
    }

    public function checkStatusCheckout($code): JsonResponse
    {
        $booking = $this->booking::where('code', $code)->first();
        $data = [
            'error'    => false,
            'message'  => '',
            'redirect' => ''
        ];
        if (empty($booking)) {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            $data = [
                'error'    => true,
                'redirect' => url('/')
            ];
        }
        return response()->json($data, 200);
    }

    protected function validateDoCheckout()
    {
        $request = \request();
        if (!is_enable_guest_checkout() and !Auth::check()) {
            return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
        }

        if (auth()->user() && !auth()->user()->hasVerifiedEmail() && setting_item('enable_verify_email_register_user') == 1) {
            return $this->sendError(__("You have to verify email first"), ['url' => url('/email/verify')]);
        }
        /**
         * @param Booking $booking
         */
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
        $code = $request->input('code');

        $booking = $this->booking::where('code', $code)->first();
        $this->bookingInst = $booking;

        if (empty($booking)) {
            abort(404);
        }
        if (!is_enable_guest_checkout() and $booking->customer_id != Auth::id()) {
            abort(404);
        }
        return true;
    }

    public function doCheckout(Request $request)
    {
        /**
         * @var $booking Booking
         * @var $user User
         */
        $res = $this->validateDoCheckout();
        if ($res !== true) {
            return $res;
        }

        $user = auth()->user();

        $booking = $this->bookingInst;

        if (!in_array($booking->status, ['draft', 'unpaid', 'processing'])) {
            return $this->sendError('', [
                'url' => $booking->getDetailUrl()
            ]);
        }
        $service = $booking->service;
        if (empty($service)) {
            return $this->sendError(__("Service not found"));
        }

        $is_api = request()->segment(1) == 'api';

        /**
         * Google ReCapcha
         */
        if (!$is_api and ReCaptchaEngine::isEnable() and setting_item("booking_enable_recaptcha")) {
            $codeCapcha = $request->input('g-recaptcha-response');
            if (!$codeCapcha or !ReCaptchaEngine::verify($codeCapcha)) {
                return $this->sendError(__("Please verify the captcha"));
            }
        }

        $messages = [];
        $rules = [
            'term_conditions' => 'required',
        ];


        $confirmRegister = $request->input('confirmRegister');
        if (!empty($confirmRegister)) {
            $rules['password'] = 'required|string|confirmed|min:6|max:255';
            $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users')];
            $messages['password.confirmed'] = __('The password confirmation does not match');
            $messages['password.min'] = __('The password must be at least 6 characters');
        }

        $how_to_pay = $request->input('how_to_pay', '');
        $credit = $request->input('credit', 0);
        $payment_gateway = $request->input('payment_gateway');

        if (empty($payment_gateway)) {
            $payment_gateway = get_active_payment_gateway_name();
        }

        // require payment gateway except pay full
//        if (empty(floatval($booking->deposit)) || $how_to_pay == 'deposit' || !auth()->check()) {
//            $rules['payment_gateway'] = 'required';
//        }

        if (auth()->check()) {
            if ($credit > $user->balance) {
                return $this->sendError(__("Your credit balance is :amount", ['amount' => $user->balance]));
            }
        } else {
            // force credit to 0 if not login
            $credit = 0;
        }

        $rules = $service->filterCheckoutValidate($request, $rules);
        if (!empty($rules)) {
            $messages['term_conditions.required'] = __('Term conditions is required field');

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return $this->sendError('', ['errors' => $validator->errors()]);
            }
        }

        $wallet_total_used = credit_to_money($credit);
        if ($wallet_total_used > $booking->total) {
            $credit = money_to_credit($booking->total, true);
            $wallet_total_used = $booking->total;
        }

        if ($res = $service->beforeCheckout($request, $booking)) {
            return $res;
        }

        if ($how_to_pay == 'full' and !empty($booking->deposit)) {
            $booking->addMeta('old_deposit', $booking->deposit ?? 0);
        }
        $oldDeposit = $booking->getMeta('old_deposit', 0);
        if (empty(floatval($booking->deposit)) and !empty(floatval($oldDeposit))) {
            $booking->deposit = $oldDeposit;
        }

        // Normal Checkout
        $booking->booking_number = $this->bookingNumberService->generate($booking->hotel_id);
        $booking->first_name = $request->input('first_name', $user->first_name);
        $booking->last_name = $request->input('last_name', $user->last_name);
        $booking->email = $request->input('email', $user->email);
        $booking->phone = $request->input('phone', $user->phone);
        $booking->address = $request->input('address_line_1', $user->address);
        $booking->address2 = $request->input('address_line_2', $user->address2);
        $booking->city = $request->input('city', $user->city);
        $booking->state = $request->input('state', $user->state);
        $booking->zip_code = $request->input('zip_code', $user->zip_code);
        $booking->country = $request->input('country', $user->country);
        $booking->customer_notes = $request->input('customer_notes', $user->customer_notes);
        $booking->gateway = $payment_gateway;
        $booking->wallet_credit_used = floatval($credit);
        $booking->wallet_total_used = floatval($wallet_total_used);
        $booking->pay_now = floatval((int)$booking->deposit == null ? $booking->total : (int)$booking->deposit);

        // If using credit
        if ($booking->wallet_total_used > 0) {
            if ($how_to_pay == 'full') {
                $booking->deposit = 0;
                $booking->pay_now = $booking->total;
            } elseif ($how_to_pay == 'deposit') {
                // case guest input credit more than "pay deposit" need to pay
                // Ex : pay deposit 10$ but guest input 20$ -> minus credit balance = 10$
                if ($wallet_total_used > $booking->deposit) {
                    $wallet_total_used = $booking->deposit;
                    $booking->wallet_total_used = floatval($wallet_total_used);
                    $booking->wallet_credit_used = money_to_credit($wallet_total_used, true);
                }

            }

            $booking->pay_now = max(0, $booking->pay_now - $wallet_total_used);
            $booking->paid = $booking->wallet_total_used;
        } else {
            if ($how_to_pay == 'full') {
                $booking->deposit = 0;
                $booking->pay_now = $booking->total;
            }
        }

        if ($booking->wallet_credit_used && auth()->check()) {
            try {
                $transaction = $user->withdraw($booking->wallet_credit_used, [
                    'wallet_total_used' => $booking->wallet_total_used
                ], $booking->id);

            } catch (\Exception $exception) {
                return $this->sendError($exception->getMessage());
            }
            $booking->wallet_transaction_id = $transaction->id;
        }
        $booking->save();

//        event(new VendorLogPayment($booking));

        if (Auth::check()) {
            $user = auth()->user();
            $user->first_name = $request->input('first_name', $user->first_name);
            $user->last_name = $request->input('last_name', $user->last_name);
            $user->phone = $request->input('phone', $user->phone);
            $user->address = $request->input('address_line_1', $user->address);
            $user->address2 = $request->input('address_line_2', $user->address2);
            $user->city = $request->input('city', $user->city);
            $user->state = $request->input('state', $user->state);
            $user->zip_code = $request->input('zip_code', $user->zip_code);
            $user->country = $request->input('country', $user->country);
            $user->save();
        } elseif (!empty($confirmRegister)) {
            $user = new User();
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->email = $request->input('email');
            $user->phone = $request->input('phone');
            $user->address = $request->input('address_line_1');
            $user->address2 = $request->input('address_line_2');
            $user->city = $request->input('city');
            $user->state = $request->input('state');
            $user->zip_code = $request->input('zip_code');
            $user->country = $request->input('country');
            $user->password = bcrypt($request->input('password'));
            $user->status = 'publish';
            $user->save();

            event(new Registered($user));
            Auth::loginUsingId($user->id);
            try {
                event(new SendMailUserRegistered($user));
            } catch (\Matrix\Exception $exception) {
                Log::warning("SendMailUserRegistered: " . $exception->getMessage());
            }
            $user->assignRole(setting_item('user_role'));
        }

        $booking->addMeta('locale', app()->getLocale());
        $booking->addMeta('how_to_pay', $how_to_pay);

        $this->savePassengers($booking, $request);

        if ($res = $service->afterCheckout($request, $booking)) {
            return $res;
        }

        $booking->status = $booking::PROCESSING;
        $booking->save();

        event(new BookingCreatedEvent($booking));

        return $this->sendSuccess([
            'url' => $booking->getDetailUrl()
        ], __("You payment has been processed successfully"));
    }

    protected function savePassengers(Booking $booking, Request $request)
    {
        if ($booking->service && method_exists($booking->service, 'savePassengers') ) {
            call_user_func([$booking->service, 'savePassengers'], $booking, $request);
            return;
        }
        if ($totalPassenger = $booking->calTotalPassenger()) {
            $booking->passengers()->delete();
            $input = $request->input('passengers', []);
            for ($i = 1; $i <= $totalPassenger; $i++) {
                $passenger = new BookingPassenger();
                $data = [
                    'booking_id' => $booking->id,
                    'first_name' => $input[$i]['first_name'] ?? '',
                    'last_name'  => $input[$i]['last_name'] ?? '',
                    'email'      => $input[$i]['email'] ?? '',
                    'phone'      => $input[$i]['phone'] ?? '',
                ];
                $data = $booking->service->filterPassengerData($data, $booking, $request, $i);
                $passenger->fillByAttr(array_keys($data), $data);
                $passenger->save();
            }
        }
    }

    public function confirmPayment(Request $request, $gateway)
    {

        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = $gateways[$gateway];
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        return $gatewayObj->confirmPayment($request);
    }

    public function callbackPayment(Request $request, $gateway)
    {
        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = $gateways[$gateway];
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        if (!empty($request->input('is_normal'))) {
            return $gatewayObj->callbackNormalPayment();
        }
        return $gatewayObj->callbackPayment($request);
    }

    public function cancelPayment(Request $request, $gateway)
    {

        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = $gateways[$gateway];
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        return $gatewayObj->cancelPayment($request);
    }

    /**
     * @param Request $request
     * @return string json
     * @todo Handle Add To Cart Validate
     *
     */
    public function addToCart(Request $request)
    {
        // NOTE: Always allow addToCart for guest
        // Will check for logged in user at checkout step

        $validator = Validator::make($request->all(), [
            'service_id'   => 'required|integer',
            'service_type' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();
        if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$service_type];
        $service = $module::find($service_id);
        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            return $this->sendError(__('Service not found'));
        }
        if (!$service->isBookable()) {
            return $this->sendError(__('Service is not bookable'));
        }

//        if (\auth()->user() && Auth::id() == $service->author_id) {
//            return $this->sendError(__('You cannot book your own service'));
//        }

        return $service->addToCart($request);
    }

    public function addToCartAnimal(Request $request)
    {
        // NOTE: Always allow addToCart for guest
        // Will check for logged in user at checkout step

//        $validator = Validator::make($request->all(), [
//            'service_id'   => 'required|integer',
//            'service_type' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return $this->sendError('', ['errors' => $validator->errors()]);
//        }
        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();
        if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$service_type];
        $service = $module::find($service_id);

        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            return $this->sendError(__('Service not found'));
        }
        if (!$service->isBookable()) {
            return $this->sendError(__('Service is not bookable'));
        }

//        if (\auth()->user() && Auth::id() == $service->author_id) {
//            return $this->sendError(__('You cannot book your own service'));
//        }

        return $service->addToCart($request);
    }

    public function validateRooms(Request $request)
    {
        // NOTE: Only validation, no cart addition

        $validator = Validator::make($request->all(), [
            'service_id'   => 'required|integer',
            'service_type' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }

        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();

        if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
        }

        $module = $allServices[$service_type];
        $service = $module::find($service_id);

        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            return $this->sendError(__('Service not found'));
        }

        if (!$service->isBookable()) {
            return $this->sendError(__('Service is not bookable'));
        }

        // Вызываем только валидацию, без добавления в корзину
        $validationResult = $service->addToCartValidate($request);

        if ($validationResult === true) {
            return $this->sendSuccess(['message' => __('Validation passed')]);
        }

        return $validationResult;
    }

    public function detail(Request $request, $code)
    {
        $ifAdminBase = $request->boolean('adminBase');
        if (!is_enable_guest_checkout() and !Auth::check()) {
            if (\request()->isJson()) {
                return $this->sendError(__("You have to login in to do this"))->setStatusCode(401);
            }
            return redirect(route('login', ['redirect' => \request()->fullUrl()]))->with('error', $error);
        }

        $booking = $this->booking::where('code', $code)->first();
        if (empty($booking)) {
            abort(404);
        }

        if (!$ifAdminBase && !is_enable_guest_checkout() && $booking->customer_id != Auth::id())
        {
            abort(404);
        }

        $trophyPrice = AnimalTrophy::where('animal_id', $booking->animal_id)
            ->get()
            ->map(fn($t) => $t->priceForHotel($booking->hotel_id))
            ->filter()
            ->sort()
            ->values();

        $trophyPrice = match ($trophyPrice->count()) {
            0 => null,
            1 => round($trophyPrice[0]),
            default => round($trophyPrice->first()) . ' - ' . round($trophyPrice->last()),
        };

        $data = [
            'page_title' => __('Booking Details'),
            'booking'    => $booking,
            'service'    => $booking->service,
            'animal_service' => Animal::where('id', $booking->animal_id)->first(),
            'user'       => auth()->user(),
            'ifAdminBase' => $ifAdminBase,
            'booking_type'  => $booking->type,
            'all_total'  => $booking->total + $booking->amount_hunting,
            'trophyPrice' => $trophyPrice
        ];
        if ($booking->gateway) {
            $data['gateway'] = get_payment_gateway_obj($booking->gateway);
        }
        return view('Booking::frontend/detail', $data);
    }

    public function exportIcal($type, $id = false)
    {
        if (empty($type) or empty($id)) {
            return $this->sendError(__('Service not found'));
        }

        $allServices = get_bookable_services();
        $allServices['room'] = 'Modules\Hotel\Models\HotelRoom';
        if (empty($allServices[$type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$type];

        $path = '/ical/';
        $fileName = 'booking_' . $type . '_' . $id . '.ics';
        $fullPath = $path . $fileName;

        $content = $this->booking::getContentCalendarIcal($type, $id, $module);
        Storage::disk('uploads')->put($fullPath, $content);
        $file = Storage::disk('uploads')->get($fullPath);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        echo $file;
    }

    public function addEnquiry(Request $request)
    {
        $rules = [
            'service_id'    => 'required|integer',
            'service_type'  => 'required',
            'enquiry_name'  => 'required',
            'enquiry_note'  => 'required',
            'enquiry_email' => [
                'required',
                'email',
                'max:255',
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }

        if (setting_item('booking_enquiry_enable_recaptcha')) {
            $codeCapcha = trim($request->input('g-recaptcha-response'));
            if (empty($codeCapcha) or !ReCaptchaEngine::verify($codeCapcha)) {
                return $this->sendError(__("Please verify the captcha"));
            }
        }

        $service_type = $request->input('service_type');
        $service_id = $request->input('service_id');
        $allServices = get_bookable_services();
        if (empty($allServices[$service_type])) {
            return $this->sendError(__('Service type not found'));
        }
        $module = $allServices[$service_type];
        $service = $module::find($service_id);
        if (empty($service) or !is_subclass_of($service, '\\Modules\\Booking\\Models\\Bookable')) {
            return $this->sendError(__('Service not found'));
        }
        $row = new $this->enquiryClass();
        $row->fill([
            'name'  => $request->input('enquiry_name'),
            'email' => $request->input('enquiry_email'),
            'phone' => $request->input('enquiry_phone'),
            'note'  => $request->input('enquiry_note'),
        ]);
        $row->object_id = $request->input("service_id");
        $row->object_model = $request->input("service_type");
        $row->status = "pending";
        $row->vendor_id = $service->author_id;
        $row->save();
        event(new EnquirySendEvent($row));
        return $this->sendSuccess([
            'message' => __("Thank you for contacting us! We will be in contact shortly.")
        ]);
    }

    public function storeNoteBooking(Request $request)
    {
        $rules = [
            'note' => 'required',
            'id'     => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }
        $id = $request->input('id');
        $booking = Booking::where('id', $id)->first();
        if (empty($booking)) {
            return $this->sendError(__('Booking not found'));
        }
        if (!Auth::user()->hasPermission('dashboard_vendor_access')) {
            if ($booking->vendor_id != Auth()->id()) {
                return $this->sendError(__("You don't have access."));
            }
        }
        $booking->addMeta("note_for_vendor",$request->input('note'));
        return $this->sendSuccess([
            'message' => __("Save successfully")
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPaidAmount(Request $request)
    {
        $rules = [
            'remain' => 'required|integer',
            'id'     => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendError('', ['errors' => $validator->errors()]);
        }


        $id = $request->input('id');
        $remain = floatval($request->input('remain'));

        if ($remain < 0) {
            return $this->sendError(__('Remain can not smaller than 0'));
        }

        $booking = Booking::where('id', $id)->first();
        if (empty($booking)) {
            return $this->sendError(__('Booking not found'));
        }

        // Remain should not greater than total
        if ($remain > $booking->total) {
            return $this->sendError(__('Remain can not greater than total'));
        }

        if (!Auth::user()->hasPermission('dashboard_vendor_access')) {
            if ($booking->vendor_id != Auth()->id()) {
                return $this->sendError(__("You don't have access."));
            }
        }

        $booking->pay_now = $remain;
        $booking->paid = floatval($booking->total) - $remain;
        event(new SetPaidAmountEvent($booking));
        if ($remain == 0) {
            $booking->status = $booking::PAID;
//            $booking->sendStatusUpdatedEmails();
            event(new BookingUpdatedEvent($booking));
        }

        $booking->save();

        return $this->sendSuccess([
            'message' => __("You booking has been changed successfully")
        ]);
    }

    public function modal(Booking $booking)
    {
        if (!is_admin() and $booking->vendor_id != auth()->id() and $booking->customer_id != auth()->id()) abort(404);

        return view('Booking::frontend.detail.modal', ['booking' => $booking, 'service' => $booking->service]);
    }

    public function changeUserBooking(Request $request, Booking $booking): JsonResponse
    {
        $this->bookingUserService->changeUser(
            $booking,
            $request->input('user_id')
        );

        return $this->sendSuccess([
            'message' => __('Customer changed')
        ]);
    }

    public function confirmBooking( Booking $booking): JsonResponse
    {
        if ($booking->status !== 'processing') {
            return $this->sendError('Эта бронь уже подтверждена или недоступна для подтверждения.')->setStatusCode(409);
        }
        $booking->status = Booking::CONFIRMED;
        $booking->save();

        event(new BookingUpdatedEvent($booking));

        return $this->sendSuccess([
            'message' => __('Reservation successfully confirmed')
        ]);
    }

    public function startCollection(Booking $booking)
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $this->bookingTimerService->startCollectionTimer($booking);
        event(new BookingStartCollectionEvent($booking));

        return $this->sendSuccess([
            'message' => __('The gathering of hunters has begun'),
        ]);
    }

    /**
     * Отменяет сбор охотников и переводит бронь в статус "подтверждено"
     * Также уведомляет всех приглашенных охотников и скрывает их приглашения
     */
    public function cancelCollection(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        try {
            $this->bookingCollectionService->cancelCollection($booking, $this->currentUser());

            return $this->sendSuccess([
                'message' => __('Сбор охотников для этой брони отменён.')
            ]);
        } catch (DomainException $e) {
            return $this->sendError($e->getMessage())->setStatusCode(422);
        } catch (\Throwable $e) {
            Log::error('cancelCollection failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return $this->sendError('Server error')->setStatusCode(500);
        }
    }

    /**
     * Завершает сбор охотников и переводит бронь в статус "подтверждено"
     * Проверяет минимальное количество принятых охотников
     */
    public function finishCollection(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        try {
            $this->bookingCollectionService->finishCollection($booking, $this->currentUser());

            return $this->sendSuccess([
                'message' => __('Сбор охотников завершён.')
            ]);

        } catch (\DomainException $e) {
            return $this->sendError($e->getMessage())->setStatusCode(422);

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $this->sendError($e->getMessage())->setStatusCode($e->getStatusCode());
        }
    }

    /**
     * Сохраняет приглашение охотника для брони
     */
    public function inviteHunter(InviteHunterRequest $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        try {
            $invitation = $this->bookingInvitationService->invite($booking, (int) $request->input('hunter_id'));

            return $this->sendSuccess([
                'data' => [
                    'invitation_id' => $invitation->id,
                ],
            ]);

        }catch (ModelNotFoundException $e) {
            return $this->sendError($e->getMessage())->setStatusCode(404);
        } catch (\Throwable $e) {
            Log::error('inviteHunter failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return $this->sendError('Server error')->setStatusCode(500);
        }
    }

    /**
     * Отправка приглашения охотнику по email (даже если пользователя нет в системе)
     */
    public function inviteHunterByEmail(InviteHunterByEmailRequest $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        try {
            $invitation = $this->bookingInvitationService->inviteByEmail($booking, trim((string) $request->input('email')));

            return $this->sendSuccess([
                'message' => __('Приглашение отправлено'),
                'data' => [
                    'invitation_id' => $invitation->id,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('inviteHunterByEmail failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return $this->sendError('Server error')->setStatusCode(500);
        }
    }

    /**
     * Отправка письма выбранному охотнику с произвольным сообщением
     */
    public function emailHunter(EmailHunterRequest $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $hunterId = (int) $request->input('hunter_id');
        $message  = trim((string)$request->input('message', ''));
        $hunter = User::find($hunterId);

        if (!$hunter) {
            return $this->sendError('Пользователь не найден')->setStatusCode(404);
        }

        if (empty($hunter->email)) {
            return $this->sendError('У выбранного пользователя не указан email')->setStatusCode(404);
        }

        $this->mailService->send($hunter->email, new HunterMessageEmail($booking, $hunter, $message, false));

        return $this->sendSuccess([
            'message' => __('Message has been sent'),
        ]);
    }

    /**
     * Получить список приглашенных охотников для брони
     * Включая отказавшихся для истории
     */
    public function getInvitedHunters(Request $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $hunters = $this->bookingInvitationService->getInvitedHunters($booking, Auth::id());

        return $this->sendSuccess([
            'hunters' => $hunters,
            'booking' => $booking,
        ]);
    }

    /**
     * Принять приглашение на бронь
     */
    public function acceptInvitation(Request $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }
        try {
            $this->bookingInvitationService->accept($booking, Auth::id());
        } catch (\DomainException $e) {
            return $this->sendError($e->getMessage())->setStatusCode(404);
        }

        return $this->sendSuccess([
            'message' => __('Invitation accepted'),
        ]);
    }

    /**
     * Отказаться от приглашения на бронь
     */
    public function declineInvitation(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $invitation = $booking->getCurrentUserInvitation();

        if (!$invitation) {
            return $this->sendError('Приглашение не найдено')->setStatusCode(404);
        }

        $invitation->status = 'declined';
        $invitation->declined_at = now();
        $invitation->save();

        return $this->sendSuccess([
            'message' => __('Invitation declined'),
        ]);
    }

    public function cancelBooking(Booking $booking)
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $isBaseAdmin = Auth::user()->hasRole('baseadmin') || Auth::user()->hasPermission('baseAdmin_dashboard_access');

        if (!$isBaseAdmin && !Auth::user()->hasPermission('dashboard_vendor_access')) {
            if ($booking->customer_id != Auth::id() && $booking->create_user != Auth::id()) {
                return $this->sendError(__("You don't have access."))->setStatusCode(403);
            }
        }

        if (in_array($booking->status, [Booking::CANCELLED, Booking::COMPLETED])) {
            return $this->sendError(__('This booking cannot be cancelled'));
        }

        $this->bookingCollectionService->cancel($booking);
        $this->bookingNotificationService->sendCancelledEmail($booking, $isBaseAdmin);

        return $this->sendSuccess([
            'message' => __('Booking has been cancelled successfully')
        ]);
    }

    public function completeBooking(Booking $booking)
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $isBaseAdmin = Auth::user()->hasRole('baseadmin') || Auth::user()->hasPermission('baseAdmin_dashboard_access');

        if (!$isBaseAdmin) {
            return $this->sendError(__("You don't have access."))->setStatusCode(403);
        }

        try {
            $this->bookingCollectionService->complete($booking);
            $this->bookingNotificationService->sendCompletedEmail($booking);

        } catch (\DomainException $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }

        return $this->sendSuccess([
            'message' => __('Booking has been completed successfully')
        ]);
    }


    public function getBookingServices(Booking $booking): JsonResponse
    {
        $data = $this->serviceManager->getBookingServices($booking);
        return response()->json($data);
    }

    //Трофеи
    public function getAnimalTrophyServices(Booking $booking): JsonResponse
    {
        $animals = Animal::forHotelWithService($booking->hotel_id, Animal::SERVICE_TROPHIES)->get();
        return response()->json($animals);
    }

    public function storeTrophy(StoreTrophyRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreTrophyData::fromRequest($request);
        $service = $this->serviceManager->createTrophy($booking, $data);

        return response()->json([
            'id'           => $service->id,
            'animal_title' => $service->animal->title ?? '—',
            'type'         => $service->type,
            'count'        => $service->count,
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }

    public function deleteTrophy(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);
        return response()->json(['status' => 'ok']);
    }

    //Штрафы
    public function getAnimalPenaltyServices(Booking $booking): JsonResponse
    {
        $data = $this->serviceManager->getAnimalHunterData($booking);

        return response()->json($data);
    }
    public function storePenalty(StorePenaltyRequest $request, Booking $booking): JsonResponse
    {
        $data = StorePenaltyData::fromRequest($request);
        $service = $this->serviceManager->createPenalty($booking, $data);

        return response()->json([
            'id'           => $service->id,
            'animal_title' => $service->animal->title ?? '—',
            'type'         => $service->type,
            'count'        => 1,
            'hunter_name'  => $service->hunter->name ?? '—',
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deletePenalty(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);
        return response()->json(['status' => 'ok']);
    }


// Разделка
    public function getAnimalPreparationServices(Booking $booking): JsonResponse
    {
        $animals = Animal::forHotelWithService($booking->hotel_id, Animal::SERVICE_PREPARATIONS)->get();

        return response()->json(['animals'  => $animals]);
    }
    public function storePreparation(StorePreparationRequest $request, Booking $booking): JsonResponse
    {
        $data = StorePreparationData::fromRequest($request);
        $service = $this->serviceManager->createOrUpdatePreparation($booking, $data);

        return response()->json([
            'id'           => $service->id,
            'animal_title' => $service->animal->title ?? '—',
            'count'        => $service->count,
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deletePreparation(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);
        return response()->json(['status' => 'ok']);
    }

    // Питание
    public function storeFoods(StoreFoodRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreFoodData::fromRequest($request);
        $price = AddetionalPrice::where('type', 'food')->where('hotel_id', $booking->hotel_id)->value('price');

        if (!$price) {
            return response()->json([
                'status' => false,
                'message' => 'Цена питания не найдена'
            ], 400);
        }

        $service = $this->serviceManager->createFood($booking, $price, $data);

        return response()->json([
            'id'           => $service->id,
            'count'        => $service->count,
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deleteFoods(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);
        return response()->json(['status' => 'ok']);
    }

    //Другое
    public function getAddetionalServices(Booking $booking): JsonResponse
    {
        $data = $this->serviceManager->getHunterData($booking);
        $addetionals = AddetionalPrice::whereNull('type')->where('hotel_id', $booking->hotel_id)->where('price', '>', 0)->get()
            ->map(fn ($addetional) => [
                'id'   => $addetional->id,
                'type'   => $addetional->type,
                'calculation_type'   => $addetional->calculation_type,
                'name'   => $addetional->name,
                'count'   => $addetional->count,
                'price'   => $addetional->price,
            ])
            ->values()
            ->toArray();

        return response()->json([
            'addetionals' => $addetionals,
            'hunters' => $data['hunters'],
        ]);
    }
    public function storeAddetional(StoreAddetionalRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreAddetionalData::fromRequest($request);
        $service = $this->serviceManager->createAddetional($booking, $data);

        return response()->json([
            'id'           => $service->id,
            'type'         => $service->type,
            'calculation_type'   => $service->calculation_type,
            'count'         => $service->count,
            'hunter_name'  => $service->hunter->name ?? '—',
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deleteAddetional(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);
        return response()->json(['status' => 'ok']);
    }

    // Траты охотника
    public function getUserSpendingServices(Booking $booking): JsonResponse
    {
        $data = $this->serviceManager->getHunterData($booking);

        return response()->json($data);
    }
    public function storeSpending(StoreSpendingRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreSpendingData::fromRequest($request);
        $service = $this->serviceManager->createSpending($booking, $data);

        return response()->json([
            'id'           => $service->id,
            'count'        => $service->price,
            'comment'      => $service->comment,
            'hunter_name'  => $service->hunter->name ?? '—',
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deleteSpending(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);
        return response()->json(['status' => 'ok']);
    }
    public function checkPrepayment(Booking $booking): void
    {
        $this->bookingCollectionService->markAllPendingAsUnpaid($booking);
    }
    public function checkPaymentStatus(Booking $booking): JsonResponse
    {
        $payment = $booking->payments()->where('create_user', Auth::id())->first();

        $status = $payment?->status ?? Payment::PROCESSING;

        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }
    public function storePrepayment(Booking $booking): JsonResponse
    {
        $paymentUrl = $this->paymentService->getOrCreatePrepayment($booking, Auth::id());

        return $this->sendSuccess([
            'message' => __('The gathering of hunters has begun'),
            'payment_url' => $paymentUrl,
        ]);
    }

    public function deleteHunter(Request $request, Booking $booking): JsonResponse
    {
        $invitation = $booking->invitationUser($request->input('hunter_id'));

        if (!$invitation) {
            return $this->sendError(__('There is no such hunter among the invitees'));
        }

        if ($booking->master_hunter_id && $invitation === $booking->master_hunter_id) {
            return $this->sendError(__('You cannot remove the master hunter'));
        }

        $invitation->delete();

        return $this->sendSuccess([
            'message' => __('Hunter successfully removed from this hunt'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function replaceHunter(Request $request, Booking $booking): JsonResponse
    {
        $data = ReplaceHunterData::fromRequest($request);

        $result = $this->bookingUserService->replaceHunter($booking, $data);

        if (!($result['success'] ?? false)) {
            return $this->sendError(__($result['error']));
        }

        return response()->json([
            'status' => true,
            'message' => 'Охотник успешно заменён',
            'hunter' => $result['data'],
        ]);
    }
    public function places(Booking $booking)
    {
        $rooms = $booking
            ->roomsBooking()
            ->with('room', 'booking:id,total_guests')
            ->get()
            ->map(function ($roomBooking) {
                $booking = $roomBooking->booking;
                $room = $roomBooking->room;

                return [
                    'booking_total_guests' => $booking->total_guests,
                    'booking_room_id' => $roomBooking->id,
                    'booking_number' => $roomBooking->number,
                    'room_id'         => $room->id,
                    'title'           => $room->title,
                    'number'          => $room->number,
                    'total_guests_in_type'   => $roomBooking->number * $room->number,
                ];
            });

        $places = BookingRoomPlace::with('user:id,first_name,last_name,user_name')
            ->where('booking_id', $booking->id)
            ->get()
            ->groupBy(['room_index', 'room_id', 'place_number']);

        return $this->sendSuccess([
            'rooms' => $rooms,
            'places' => $places,
        ]);
    }

    public function selectPlace(Request $request, Booking $booking): JsonResponse
    {
        $roomId = $request->input('room_id');
        $selectedPlaceNumber = $request->input('place_number');
        $selectedRoomIndex = $request->input('room_index');
        $booking = Booking::findOrFail($booking->id);

        try {
            $alreadyHasPlace = BookingRoomPlace::where('booking_id', $booking->id)
                ->where('user_id', auth()->id())
                ->exists();

            if ($alreadyHasPlace) {
                return $this->sendError(__('You have already selected a place'));
            }

            $occupiedPlaceNumbers = BookingRoomPlace::where('booking_id', $booking->id)
                ->where('room_id', $roomId)
                ->where('room_index', $selectedRoomIndex)
                ->pluck('place_number')
                ->toArray();

            $totalPlaces = $booking->hotelRoom()
                ->find($roomId)
                ->number;

            $finalPlaceNumber = null;
            for ($i = 1; $i <= $totalPlaces; $i++) {
                if (!in_array($i, $occupiedPlaceNumbers)) {
                    $finalPlaceNumber = $i;
                    break;
                }
            }

            if (!$finalPlaceNumber) {
                return response()->json([
                    'success' => false,
                    'message' => __('No free places available in this room')
                ], 409);
            }

            BookingRoomPlace::create([
                'booking_id'   => $booking->id,
                'room_index'   => $selectedRoomIndex,
                'room_id'      => $roomId,
                'place_number' => $finalPlaceNumber,
                'user_id'      => auth()->id(),
            ]);

            $this->bookingCollectionService->updateStatusIfAllPlacesSelected($booking);

            $rooms = $booking
                ->roomsBooking()
                ->with('room', 'booking:id,total_guests')
                ->get()
                ->map(function ($roomBooking) {
                    $booking = $roomBooking->booking;
                    $room = $roomBooking->room;

                    return [
                        'booking_total_guests' => $booking->total_guests,
                        'booking_room_id' => $roomBooking->id,
                        'booking_number' => $roomBooking->number,
                        'room_id'         => $room->id,
                        'title'           => $room->title,
                        'number'          => $room->number,
                        'total_guests_in_type'   => $roomBooking->number * $room->number,
                    ];
                });

            $places = BookingRoomPlace::with('user:id,first_name,last_name,user_name')
                ->where('booking_id', $booking->id)
                ->get()
                ->groupBy(['room_index', 'room_id', 'place_number']);

            return response()->json([
                'success' => true,
                'current_user_id' => auth()->id(),
                'rooms' => $rooms,
                'places' => $places,
            ]);



        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => __('The selected seat is already taken, try choosing a different one')
                ], 409);
            }
            throw $e;
        }
    }

    public function cancelSelectPlace(Request $request, $bookingId)
    {
        $place = BookingRoomPlace::where('booking_id', $bookingId)
            ->where('id', $request->input('place_id'))
            ->where('user_id', Auth::id())->first();

        if (!$place) {
            return $this->sendError(__('Вы можете сделать отмену только на себе'));
        }

        $place->delete();
    }

    public function markPaid(Booking $booking): JsonResponse
    {
        $booking->update([
            'status' => Booking::PAID,
            'is_paid' => true,
        ]);

        return $this->sendSuccess([]);
    }
    public function markCompleted(Booking $booking): JsonResponse
    {
        $booking->update([
            'status' => Booking::COMPLETED,
        ]);

        return $this->sendSuccess([]);
    }

    //Калькуляция
    public function getCalculating(Booking $booking): JsonResponse
    {
        $result = $this->bookingCalculatingService->calculate($booking, Auth::user());

        return response()->json($result);
    }
}
