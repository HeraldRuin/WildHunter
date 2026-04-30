<?php

namespace Modules\Booking\Controllers;

use App\Exceptions\BaseException;
use App\Exceptions\BusinessException;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\ReCaptchaEngine;
use App\Http\Responses\SuccessResponse;
use App\Service\MailService;
use App\User;
use Illuminate\Auth\Events\Registered;
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
use Modules\Booking\DTO\ReplaceHunterData;
use Modules\Booking\DTO\SelectPlaceData;
use Modules\Booking\DTO\StoreAddetionalData;
use Modules\Booking\DTO\StoreFoodData;
use Modules\Booking\DTO\StorePenaltyData;
use Modules\Booking\DTO\StorePreparationData;
use Modules\Booking\DTO\StoreSpendingData;
use Modules\Booking\DTO\StoreTrophyData;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Events\EnquirySendEvent;
use Modules\Booking\Events\SetPaidAmountEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\Enquiry;
use Modules\Booking\Requests\ChangeUserBookingRequest;
use Modules\Booking\Requests\InviteHunterByEmailRequest;
use Modules\Booking\Requests\InviteHunterRequest;
use Modules\Booking\Requests\StoreAddetionalRequest;
use Modules\Booking\Requests\StoreFoodRequest;
use Modules\Booking\Requests\StorePenaltyRequest;
use Modules\Booking\Requests\StorePreparationRequest;
use Modules\Booking\Requests\StoreSpendingRequest;
use Modules\Booking\Requests\StoreTrophyRequest;
use Modules\Booking\Services\BookingAccessService;
use Modules\Booking\Services\BookingCollectionService;
use Modules\Booking\Services\BookingInvitationService;
use Modules\Booking\Services\BookingNotificationService;
use Modules\Booking\Services\BookingNumberService;
use Modules\Booking\Services\BookingPlaceService;
use Modules\Booking\Services\BookingServiceManager;
use Modules\Booking\Services\BookingStatusService;
use Modules\Booking\Services\BookingTimerService;
use Modules\Booking\Services\BookingUserService;
use Modules\Booking\Services\Calculation\BookingCalculatingService;
use Modules\Booking\Services\Payments\PaymentManagerService;
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
        protected BookingServiceManager $serviceManager,
        protected BookingNumberService $bookingNumberService,
        protected BookingInvitationService $bookingInvitationService,
        protected BookingNotificationService $bookingNotificationService,
        protected MailService $mailService,
        protected BookingUserService $bookingUserService,
        protected BookingPlaceService $bookingPlaceService,
        protected BookingStatusService $bookingStatusService,
        protected PaymentManagerService $paymentManagerService,
        protected BookingAccessService $bookingAccessService)
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

    /**
     * @throws NotFoundException
     */
    public function changeUserBooking(ChangeUserBookingRequest $request, Booking $booking): JsonResponse
    {
        $result = $this->bookingUserService->changeUser($booking, $request->input('user_id'));

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    /**
     * @throws ConflictException
     */
    public function confirmBooking(Booking $booking): JsonResponse
    {
        $result = $this->bookingCollectionService->confirmBooking($booking);

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    public function startCollection(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingTimerService->startCollectionTimer($booking);

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    public function cancelCollection(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingCollectionService->cancelCollection($booking, $this->currentUser());

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    public function finishCollection(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingCollectionService->finishCollection($booking, $this->currentUser());

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    /**
     * Сохраняет приглашение охотника для брони
     */
    public function inviteHunter(InviteHunterRequest $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingInvitationService->invite($booking, (int) $request->validated('hunter_id'));

        return new SuccessResponse(data: $result['data']);
    }

    /**
     * Отправка приглашения охотнику по email (даже если пользователя нет в системе)
     */
    public function inviteHunterByEmail(InviteHunterByEmailRequest $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingInvitationService->inviteByEmail($booking, trim((string) $request->validated('email')));

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    public function getInvitedHunters(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingInvitationService->getInvitedHunters($booking, Auth::id());

        return new SuccessResponse(data: $result);
    }

    public function acceptInvitation(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingInvitationService->accept($booking, Auth::id());

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    public function declineInvitation(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $result = $this->bookingInvitationService->declineInvitation($booking);

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }


    /**
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws BaseException
     */
    public function cancelBooking(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $this->bookingAccessService->ensureCanAccessBooking($booking, Auth::user());
        $result = $this->bookingCollectionService->cancel($booking);
        $this->bookingNotificationService->sendCancelledEmail($booking);

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    /**
     * @throws ForbiddenException
     * @throws ConflictException
     */
    public function completeBooking(Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $this->bookingAccessService->ensureCanAccessBooking($booking, Auth::user());
        $result = $this->bookingCollectionService->complete($booking);
        $this->bookingNotificationService->sendCompletedEmail($booking);

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }


    public function getBookingServices(Booking $booking): SuccessResponse
    {
        $result = $this->serviceManager->getBookingServices($booking);

        return new SuccessResponse(data: $result['data']);
    }

    //Трофеи
    public function getAnimalTrophyServices(Booking $booking): JsonResponse
    {
        $result = $this->serviceManager->getTrophyData($booking);

        return new SuccessResponse(data: $result['data']);
    }

    public function storeTrophy(StoreTrophyRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreTrophyData::fromRequest($request);
        $result = $this->serviceManager->createTrophy($booking, $data);

        return new SuccessResponse(data: $result['data']);
    }

    public function deleteTrophy(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);

        return new SuccessResponse();
    }

    //Штрафы
    public function getAnimalPenaltyServices(Booking $booking): JsonResponse
    {
        $result = $this->serviceManager->getAnimalHunterData($booking);

        return new SuccessResponse(data: $result['data']);
    }
    public function storePenalty(StorePenaltyRequest $request, Booking $booking): JsonResponse
    {
        $data = StorePenaltyData::fromRequest($request);
        $result = $this->serviceManager->createPenalty($booking, $data);

        return new SuccessResponse(data: $result['data']);
    }
    public function deletePenalty(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);

        return new SuccessResponse();
    }

    // Разделка
    public function getAnimalPreparationServices(Booking $booking): JsonResponse
    {
        $result = $this->serviceManager->getPreparationData($booking);

        return new SuccessResponse(data: $result['data']);
    }
    public function storePreparation(StorePreparationRequest $request, Booking $booking): JsonResponse
    {
        $data = StorePreparationData::fromRequest($request);
        $result = $this->serviceManager->createOrUpdatePreparation($booking, $data);

        return new SuccessResponse(data: $result['data']);
    }
    public function deletePreparation(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);

        return new SuccessResponse();
    }

    // Питание
    public function storeFoods(StoreFoodRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreFoodData::fromRequest($request);
        $result = $this->serviceManager->createFood($booking, $data);

        return new SuccessResponse(data: $result['data']);
    }
    public function deleteFoods(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);

        return new SuccessResponse();
    }

    //Другое
    public function getAddetionalServices(Booking $booking): JsonResponse
    {
        $result = $this->serviceManager->getAddetionalHunterData($booking);

        return new SuccessResponse(data: $result['data']);

    }
    public function storeAddetional(StoreAddetionalRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreAddetionalData::fromRequest($request);
        $result = $this->serviceManager->createAddetional($booking, $data);

        return new SuccessResponse(data: $result['data']);
    }
    public function deleteAddetional(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);

        return new SuccessResponse();
    }

    // Траты охотника
    public function getUserSpendingServices(Booking $booking): JsonResponse
    {
        $result = $this->serviceManager->getAddetionalHunterData($booking);

        return new SuccessResponse(data: $result['data']);
    }
    public function storeSpending(StoreSpendingRequest $request, Booking $booking): JsonResponse
    {
        $data = StoreSpendingData::fromRequest($request);
        $result = $this->serviceManager->createSpending($booking, $data);

        return new SuccessResponse(data: $result['data']);
    }
    public function deleteSpending(Booking $booking, $serviceId): JsonResponse
    {
        $this->serviceManager->deleteService($serviceId, $booking);

        return new SuccessResponse();
    }
    public function checkPrepayment(Booking $booking): JsonResponse
    {
        $this->bookingCollectionService->markAllPendingAsUnpaid($booking);

        return new SuccessResponse();
    }
    public function checkPaymentStatus(Booking $booking): JsonResponse
    {
        $result = $this->paymentManagerService->getStatusFromPayment($booking, Auth::id());

        return new SuccessResponse(data: $result['data']);
    }
    public function storePrepayment(Booking $booking): JsonResponse
    {
        $result = $this->paymentManagerService->store($booking, Auth::id());

        return new SuccessResponse(data: $result['data']);
    }

    public function deleteHunter(Request $request, Booking $booking): JsonResponse
    {
        $result = $this->bookingInvitationService->deleteHunter($booking, (int) $request->input('hunter_id'));

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    /**
     * @throws \Exception
     */
    public function replaceHunter(Request $request, Booking $booking): JsonResponse
    {
        $data = ReplaceHunterData::fromRequest($request);
        $result = $this->bookingUserService->replaceHunter($booking, $data);

        return new SuccessResponse(code: $result['code'], domain: 'booking', data: $result['data']);
    }
    public function places(Booking $booking): SuccessResponse
    {
        $result = $this->bookingPlaceService->getPlaces($booking);

        return new SuccessResponse(data: $result['data']);
    }

    /**
     * @throws ForbiddenException
     * @throws ConflictException
     */
    public function selectPlace(Request $request, Booking $booking): JsonResponse
    {
        $data = SelectPlaceData::fromRequest($request);
        $result = $this->bookingPlaceService->selectPlace($booking, $data);

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    /**
     * @throws ForbiddenException
     */
    public function cancelSelectPlace(Request $request, $bookingId): JsonResponse
    {
        $result = $this->bookingPlaceService->cancelSelectPlace($bookingId, $request->input('place_id'), Auth::id());

        return new SuccessResponse(code: $result['code'], domain: 'booking');
    }

    public function markPaid(Booking $booking): JsonResponse
    {
        $booking->update([
            'status' => Booking::PAID,
            'is_paid' => true,
        ]);

        return new SuccessResponse();
    }
    public function markCompleted(Booking $booking): JsonResponse
    {
        $booking->update([
            'status' => Booking::COMPLETED,
        ]);

        return new SuccessResponse();
    }


    /**
     * Калькуляция
     * @throws ValidationException
     * @throws BusinessException
     */
    public function getCalculating(Booking $booking): JsonResponse
    {
        $result = $this->bookingCalculatingService->calculate($booking, Auth::user());

        if ($result && $result['success'] === false) {
            throw new BusinessException(
                errorCode: $result['message'],
                domain: 'calculate'
            );
        }

        return new SuccessResponse(data: $result);
    }
}
