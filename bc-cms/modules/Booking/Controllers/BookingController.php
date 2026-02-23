<?php

namespace Modules\Booking\Controllers;

use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mockery\Exception;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalFine;
use Modules\Animals\Models\AnimalPreparation;
use Modules\Animals\Models\AnimalTrophy;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Emails\StatusUpdatedEmail;
use Modules\Booking\Emails\HunterMessageEmail;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Events\BookingFinishEvent;
use Modules\Booking\Events\BookingStartCollectionEvent;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Events\EnquirySendEvent;
use Modules\Booking\Events\SetPaidAmountEvent;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Booking\Models\BookingService;
use Modules\Booking\Services\BookingTimerService;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelAnimal;
use Modules\User\Events\SendMailUserRegistered;
use Modules\Booking\Emails\CollectionTimerFinishedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Enquiry;
use App\Helpers\ReCaptchaEngine;

class BookingController extends \App\Http\Controllers\Controller
{
    use AuthorizesRequests;

    protected $booking;
    protected $enquiryClass;
    protected $bookingInst;
    protected $animalClass;
    protected $bookingTimerService;

    public function __construct(Booking $booking, Enquiry $enquiryClass, Animal $animalClass, BookingTimerService $bookingTimerService)
    {
        $this->booking = $booking;
        $this->enquiryClass = $enquiryClass;
        $this->animalClass = $animalClass;
        $this->bookingTimerService = $bookingTimerService;
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

        //TODO закоментировал так как на время чтобы убрать оплату ставлю статус processing но тут тогда перекидывает на главную
//        if (!in_array($booking->status, ['draft', 'unpaid'])) {
//            return redirect('/');
//        }

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
            'all_total'  => $this->getAllPay($booking->total, $booking->amount_hunting)
        ];
        return view('Booking::frontend/checkout', $data);
    }

    public function getAllPay($booking, $hunter)
    {
        return $booking + $hunter;
    }

    public function checkStatusCheckout($code)
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
        //TODO закоментировал так как на время чтобы убрать оплату ставлю статус processing но тут тогда перекидывает на главную
//        if (!in_array($booking->status, ['draft', 'unpaid'])) {
//            $data = [
//                'error'    => true,
//                'redirect' => url('/')
//            ];
//        }
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
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|string|email|max:255',
            'phone'           => 'required|string|max:255',
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
            $messages['payment_gateway.required'] = __('Payment gateway is required field');
            $messages['last_name.required'] = __('Last Name field is required');
            $messages['first_name.required'] = __('First Name field is required');
            $messages['phone.required'] = __('Phone field is required');
            $messages['email.required'] = __('Email field is required');

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
        $booking->first_name = $request->input('first_name');
        $booking->last_name = $request->input('last_name');
        $booking->email = $request->input('email');
        $booking->phone = $request->input('phone');
        $booking->address = $request->input('address_line_1');
        $booking->address2 = $request->input('address_line_2');
        $booking->city = $request->input('city');
        $booking->state = $request->input('state');
        $booking->zip_code = $request->input('zip_code');
        $booking->country = $request->input('country');
        $booking->customer_notes = $request->input('customer_notes');
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

       $gateways = get_payment_gateways();
        if ($booking->pay_now) {
            $gatewayObj = $gateways[$payment_gateway] ?? null;

//            if (!empty($rules['payment_gateway'])) {
//                if (empty($gatewayObj)) {
//                    return $this->sendError(__("Payment gateway not found"));
//                }
//                if (!$gatewayObj->isAvailable()) {
//                    return $this->sendError(__("Payment gateway is not available"));
//                }
//            }
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
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->phone = $request->input('phone');
            $user->address = $request->input('address_line_1');
            $user->address2 = $request->input('address_line_2');
            $user->city = $request->input('city');
            $user->state = $request->input('state');
            $user->zip_code = $request->input('zip_code');
            $user->country = $request->input('country');
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

        // Save Passenger
        $this->savePassengers($booking, $request);

        if ($res = $service->afterCheckout($request, $booking)) {
            return $res;
        }

//        if ($booking->pay_now > 0) {
//            try {
//                //$gatewayObj->process($request, $booking, $service);
//            } catch (Exception $exception) {
//                return $this->sendError($exception->getMessage());
//            }
//        } else {
//            if ($booking->paid < $booking->total) {
//                $booking->status = $booking::PARTIAL_PAYMENT;
//            } else {
//                $booking->status = $booking::PAID;
//            }
//
//            if (!empty($booking->coupon_amount) and $booking->coupon_amount > 0 and $booking->total == 0) {
//                $booking->status = $booking::PAID;
//            }
//
//            $booking->save();
//            event(new BookingCreatedEvent($booking));
//            return $this->sendSuccess([
//                'url' => $booking->getDetailUrl()
//            ], __("You payment has been processed successfully"));
//        }
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

//        if ($booking->status == 'draft') {
//            return redirect($booking->getCheckoutUrl());
//        }

        if (!$ifAdminBase && !is_enable_guest_checkout() && $booking->customer_id != Auth::id())
        {
            abort(404);
        }

        $data = [
            'page_title' => __('Booking Details'),
            'booking'    => $booking,
            'service'    => $booking->service,
            'animal_service' => Animal::where('id', $booking->animal_id)->first(),
            'user'       => auth()->user(),
            'ifAdminBase' => $ifAdminBase,
            'booking_type'  => $booking->type,
            'all_total'  => $this->getAllPay($booking->total, $booking->amount_hunting)
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
        $userId = $request->input('user_id');

        $booking->create_user = $userId;
        $booking->save();

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $bookingHunter = BookingHunter::where('booking_id', $booking->id)->first();
                if ($bookingHunter) {
                    $bookingHunter->invited_by = $userId;
                    $bookingHunter->is_master = $user->hasRole('hunter');
                    $bookingHunter->creator_role = $user->role->code ?? null;
                    $bookingHunter->save();
                }
            }
        }

        return $this->sendSuccess([
            'message' => __('Customer changed')
        ]);
    }

    public function confirmBooking( Booking $booking): JsonResponse
    {
        if ($booking->status !== 'processing') {
            return response()->json([
                'status' => false,
                'message' => 'Эта бронь уже подтверждена или недоступна для подтверждения.'
            ]);
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

        $oldStatus = $booking->status;
        $wasAlreadyCollection = ($booking->status === Booking::START_COLLECTION);
        $timerHour = 24;

        if ($booking->hotel_id) {
            $hotelData = Hotel::where('id', $booking->hotel_id)->first();

            if ($hotelData) {
                // Получаем таймер сбора
                $timerHourCollect = $hotelData->collection_timer_hours ?? null;

                if ($timerHourCollect !== null && $timerHourCollect > 0) {
                    $timerHour = (int) $timerHourCollect;
                }
            }
        }

//        // ВАЖНО: ВСЕГДА удаляем ВСЕ старые записи таймера перед установкой нового
//        // Используем прямой SQL для гарантированного удаления всех записей
//        \Illuminate\Support\Facades\DB::table('bc_booking_meta')
//            ->where('booking_id', $booking->id)
//            ->whereIn('name', ['collection_end_at', 'collection_start_at', 'collection_timer_hours'])
//            ->delete();
//
//        $booking->status = Booking::START_COLLECTION;
//
//        // Сохраняем время начала сбора и количество часов таймера
//        // Это позволит делать обратный отсчет от установленного значения
//        $now = \Carbon\Carbon::now();
//        $collectionStartAtString = $now->toIso8601String();
//
//        // Сохраняем время начала сбора
//        \Illuminate\Support\Facades\DB::table('bc_booking_meta')->insert([
//            'booking_id' => $booking->id,
//            'name' => 'collection_start_at',
//            'val' => $collectionStartAtString,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
//
//        // Сохраняем количество часов таймера
//        \Illuminate\Support\Facades\DB::table('bc_booking_meta')->insert([
//            'booking_id' => $booking->id,
//            'name' => 'collection_timer_hours',
//            'val' => (string)$timerHours,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
//
//        // Также сохраняем время окончания для обратной совместимости
//        $collectionEndAt = $now->copy()->addHours($timerHours);
//        $collectionEndAtString = $collectionEndAt->toIso8601String();
//
//        \Illuminate\Support\Facades\DB::table('bc_booking_meta')->insert([
//            'booking_id' => $booking->id,
//            'name' => 'collection_end_at',
//            'val' => $collectionEndAtString,
//            'created_at' => now(),
//            'updated_at' => now(),
//        ]);
//
//        $booking->save();
//
//        // Финальная проверка: убеждаемся, что запись создана с правильным значением
//        $finalCheck = \Illuminate\Support\Facades\DB::table('bc_booking_meta')
//            ->where('booking_id', $booking->id)
//            ->where('name', 'collection_end_at')
//            ->first();
//
//        if (!$finalCheck || $finalCheck->val !== $collectionEndAtString) {
//            \Log::error('startCollection: ОШИБКА - запись не создана или значение неправильное', [
//                'booking_id' => $booking->id,
//                'expected_value' => $collectionEndAtString,
//                'actual_value' => $finalCheck ? $finalCheck->val : null,
//            ]);
//
//            // Пытаемся создать запись еще раз
//            \Illuminate\Support\Facades\DB::table('bc_booking_meta')
//                ->where('booking_id', $booking->id)
//                ->where('name', 'collection_end_at')
//                ->delete();
//
//            \Illuminate\Support\Facades\DB::table('bc_booking_meta')->insert([
//                'booking_id' => $booking->id,
//                'name' => 'collection_end_at',
//                'val' => $collectionEndAtString,
//                'created_at' => now(),
//                'updated_at' => now(),
//            ]);
//        }

        $timerData = $this->bookingTimerService->startTimer($booking->id, $timerHour, 'collection');

        // ВСЕГДА не отправляем письмо инициатору при запуске сбора
        // Инициатор (create_user) уже автоматически является участником сбора
        // и не должен получать письмо о смене статуса
        $booking->skip_status_email = true;

        event(new BookingStartCollectionEvent($booking));

        return $this->sendSuccess([
            'message' => __('The gathering of hunters has begun'),
            'collection_end_at' => $timerData['end_at'],
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

        $user = Auth::user();
        $isBaseAdmin = $user->hasRole('baseadmin') || $user->hasPermission('baseAdmin_dashboard_access');

        // Только владелец брони, base-admin могут отменить сбор
        if (!$isBaseAdmin && $booking->create_user !== $user->id
        ) {
            return $this->sendError(__("You don't have access."))->setStatusCode(403);
        }

        // Заблокируем отмену сбора только для уже окончательно отменённых/завершённых броней
        if (in_array($booking->status, [Booking::CANCELLED, Booking::COMPLETED], true)) {
            return $this->sendError(__('This booking cannot be modified'))->setStatusCode(422);
        }

        if ($booking->status === Booking::START_COLLECTION || $booking->status === Booking::FINISHED_COLLECTION) {
            $booking->status = Booking::CONFIRMED;

            $minHuntersRequired = HotelAnimal::where('hotel_id', $booking->hotel_id)
                ->where('animal_id', $booking->animal_id)
                ->value('hunters_count');

            $acceptedHuntersCount = BookingHunterInvitation::whereHas('bookingHunter', function ($q) use ($booking) {
                $q->where('booking_id', $booking->id);
            })
                ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
                ->count();

            if ($acceptedHuntersCount < $minHuntersRequired) {
                return $this->sendError(__('Нельзя отменить сбор: не собрано минимальное количество охотников.'))->setStatusCode(422);
            }

            // Полностью удаляем ВСЕ мета-данные таймера (сброс таймера)
            // Используем прямой SQL для гарантированного удаления
            $deletedCount = \Illuminate\Support\Facades\DB::table('bc_booking_meta')
                ->where('booking_id', $booking->id)
                ->whereIn('name', ['collection_end_at', 'collection_start_at', 'collection_timer_hours', 'collection_timer_started_at'])
                ->delete();

            $booking->save();

            event(new BookingUpdatedEvent($booking));
        }

        $invitations = $booking->getAllInvitations();

        foreach ($invitations as $invitation) {
            $hunter = $invitation->hunter;

            // Уведомляем охотника о том, что сбор отменён
            if ($hunter && !empty($hunter->email)) {
                try {
                    $message = __('Сбор охотников для этой брони отменён.');
                    Mail::to($hunter->email)->send(new HunterMessageEmail($booking, $hunter, $message, false));
                } catch (\Exception $e) {
                    Log::warning('cancelCollection: failed to send email to hunter', [
                        'booking_id' => $booking->id,
                        'hunter_id'  => $hunter->id ?? null,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        }

        // Удаляем все приглашения охотников, кроме мастера охотника (того, кто приглашал)
        try {
            $masterBookingHunterId = BookingHunter::where('booking_id', $booking->id)
                ->where('is_master', true)
                ->first();

            if ($masterBookingHunterId) {
                BookingHunterInvitation::where('booking_hunter_id', $masterBookingHunterId->id)
                    ->where('hunter_id', '!=', $masterBookingHunterId->invited_by)
                    ->forceDelete();
            }
        } catch (\Exception $e) {
            Log::warning('cancelCollection: failed to force delete invitations', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Уведомляем создателя брони о смене статуса
        try {
            $old = app()->getLocale();
            $bookingLocale = $booking->getMeta('locale');
            if ($bookingLocale) {
                app()->setLocale($bookingLocale);
            }

            if ($booking->create_user) {
                $creator = User::find($booking->create_user);
                if ($creator && !empty($creator->email)) {
                    $customMessage = __('Сбор охотников для этой брони отменён.');
                    Mail::to($creator->email)->send(new StatusUpdatedEmail($booking, 'customer', $customMessage));
                }
            }

            app()->setLocale($old);
        } catch (\Exception | \Swift_TransportException $e) {
            Log::warning('cancelCollection: failed to send status email to creator: ' . $e->getMessage());
        }

        return $this->sendSuccess([
            'message' => __('Сбор охотников для этой брони отменён.')
        ]);
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

        $user = Auth::user();
        $isBaseAdmin = $user->hasRole('baseadmin') || $user->hasPermission('baseAdmin_dashboard_access');

        // Только владелец брони, вендор или base-admin могут завершить сбор
        if (
            !$isBaseAdmin
            && $booking->vendor_id !== $user->id
            && $booking->create_user !== $user->id
        ) {
            return $this->sendError(__("You don't have access."))->setStatusCode(403);
        }

        // Проверяем, что бронь в режиме сбора
        if ($booking->status !== Booking::START_COLLECTION) {
            return $this->sendError(__('Сбор охотников не начат или уже завершён'))->setStatusCode(422);
        }


        // Получаем все приглашения
        $allInvitations = $booking->getAllInvitations();

        // Сначала проверяем, что НЕТ приглашений, которые ещё не подтверждены
        // (например, со статусом pending или любым другим, кроме accepted / declined / removed)
        $notConfirmedInvitations = $allInvitations->filter(function ($invitation) {
            return !in_array($invitation->status, ['accepted', 'declined', 'removed']);
        });

        if ($notConfirmedInvitations->count() > 0) {
            return $this->sendError(
                __('Не все приглашённые участники подтвердили приглашение. Дождитесь ответа всех участников или удалите неподтвердившихся.')
            )->setStatusCode(422);
        }

        // Фильтруем приглашения: учитываем все статусы, кроме 'declined' и 'removed'.
        // Это соответствует логике метода isInvited() - приглашенные охотники считаются участниками
        // до тех пор, пока они не отклонят приглашение
        $acceptedInvitations = $allInvitations->filter(function ($invitation) {
            return !in_array($invitation->status, ['declined', 'removed']);
        });

        // Считаем только приглашенных охотников (без создателя брони)
        // Минимальное количество охотников относится только к приглашенным, а не к создателю
        $invitedHuntersCount = $acceptedInvitations->count();

        // Загружаем модель заново из базы данных, чтобы получить актуальные данные
        $booking = Booking::find($booking->id);

        $animalName = '';
        $requiredHunters = 1;

        // Если есть животное и отель, получаем минимальное количество охотников из pivot таблицы
        if ($booking->animal_id && $booking->hotel_id) {
            $animal = Animal::find($booking->animal_id);
            if ($animal) {
                $animalName = $animal->title ?? '';

                $hotelAnimal = Animal::where('id', $booking->animal_id)
                    ->where('hotel_id', $booking->hotel_id)
                    ->first();

                if ($hotelAnimal && isset($hotelAnimal->hunters_count) && $hotelAnimal->hunters_count !== null) {
                    $requiredHunters = (int) $hotelAnimal->hunters_count;
                }

                // Если значение не найдено или равно 0, используем минимальное значение 1
                if ($requiredHunters <= 0) {
                    $requiredHunters = 1;
                }
            }
        } else {
            // Если нет животного или отеля, используем старую логику
            if ($booking->type === 'hotel') {
                $requiredHunters = (int) ($booking->total_guests ?? 0);
            } elseif ($booking->type === 'animal' || $booking->type === 'hotel_animal') {
                $requiredHunters = (int) ($booking->total_hunting ?? 0);
            }

            if ($requiredHunters <= 0) {
                $requiredHunters = 1;
            }

            // Получаем название животного, если есть
            if ($booking->animal_id) {
                $animal = Animal::find($booking->animal_id);
                if ($animal) {
                    $animalName = $animal->title ?? '';
                }
            }
        }

        // Проверяем, что собрано достаточное количество приглашенных охотников
        // Минимальное количество относится только к приглашенным охотникам, создатель не учитывается
        if ($invitedHuntersCount < $requiredHunters) {
            $message = __('Минимальное кол-во охотников для :animal :count', [
                'animal' => $animalName ?: __('животного'),
                'count' => $requiredHunters
            ]);
            return $this->sendError($message)->setStatusCode(422);
        }

        $booking->status = Booking::PREPAYMENT_COLLECTION;

        // Полностью удаляем ВСЕ мета-данные таймера (сброс таймера)
        // Используем прямой SQL для гарантированного удаления
        $deletedCount = \Illuminate\Support\Facades\DB::table('bc_booking_meta')
            ->where('booking_id', $booking->id)
            ->whereIn('name', ['collection_end_at', 'collection_start_at', 'collection_timer_hours', 'collection_timer_started_at'])
            ->delete();

        $booking->save();

        event(new BookingFinishEvent($booking));

        // Находим "собирающего" охотника и отправляем ему письмо о завершении таймера
//        try {
//            $master = BookingHunter::where('booking_id', $booking->id)
//                ->where('is_master', true)
//                ->first();
//
//            if ($master) {
//                $hunterUser = User::find($master->invited_by);
//                if ($hunterUser && !empty($hunterUser->email)) {
//                    Mail::to($hunterUser->email)->send(new CollectionTimerFinishedEmail($booking, $hunterUser));
//                }
//            }
//        } catch (\Exception | \Swift_TransportException $e) {
//            Log::warning('finishCollection: failed to send collection timer finished email: ' . $e->getMessage());
//        }

        // Уведомляем создателя брони о смене статуса
//        try {
//            $old = app()->getLocale();
//            $bookingLocale = $booking->getMeta('locale');
//            if ($bookingLocale) {
//                app()->setLocale($bookingLocale);
//            }
//
//            if ($booking->create_user) {
//                $creator = User::find($booking->create_user);
//                if ($creator && !empty($creator->email)) {
//                    Mail::to($creator->email)->send(new StatusUpdatedEmail($booking, 'customer'));
//                }
//            }
//
//            app()->setLocale($old);
//        } catch (\Exception | \Swift_TransportException $e) {
//            Log::warning('finishCollection: failed to send status email to creator: ' . $e->getMessage());
//        }

        return $this->sendSuccess([
            'message' => __('Сбор охотников завершён.')
        ]);
    }

    /**
     * Сохраняет приглашение охотника для брони
     *
     * @param \Illuminate\Http\Request $request
     * @param \Modules\Booking\Models\Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function inviteHunter(Request $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $hunterId = (int) $request->input('hunter_id');
        if (!$hunterId) {
            return $this->sendError('Не передан hunter_id')->setStatusCode(422);
        }

        $hunter = User::find($hunterId);
        if (!$hunter) {
            return $this->sendError('Пользователь не найден')->setStatusCode(404);
        }

        $bookingHunter = BookingHunter::where('booking_id', $booking->id)->first();
        if (!$bookingHunter) {
            return $this->sendError('Запись BookingHunter для этой брони не найдена')->setStatusCode(404);
        }

        $invitation = BookingHunterInvitation::updateOrCreate(
            [
                'booking_hunter_id' => $bookingHunter->id,
                'hunter_id'         => $hunterId,
            ],
            [
                'invited'         => true,
                'status'          => 'pending',
                'invited_at'      => now(),
                'invitation_token'=> $booking->code . '-' . $hunterId,
            ]
        );

        // Пытаемся сразу отправить письмо-приглашение охотнику
        // НЕ отправляем письмо создателю брони - он уже приглашен автоматически
        if (!empty($hunter->email) && $hunterId != $booking->create_user) {
            try {
                $message = __('Вас пригласили в сбор для брони №:id', ['id' => $booking->id]);
                Mail::to($hunter->email)->send(new HunterMessageEmail($booking, $hunter, $message, true));
            } catch (\Exception $e) {
                Log::warning('inviteHunter: failed to send invitation email', [
                    'booking_id' => $booking->id,
                    'hunter_id'  => $hunterId,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        try {
            event(new \Modules\Booking\Events\HunterInvitedEvent($booking, $hunterId));
        } catch (\Exception $e) {
            Log::error('Ошибка отправки HunterInvitedEvent', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'hunter_id' => $hunterId
            ]);
        }

        return $this->sendSuccess([
            'data'    => [
                'invitation_id' => $invitation->id,
            ],
        ]);
    }

    /**
     * Отправка приглашения охотнику по email (даже если пользователя нет в системе)
     */
    public function inviteHunterByEmail(Request $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $email = trim((string)$request->input('email', ''));
        if (!$email) {
            return $this->sendError('Не передан email')->setStatusCode(422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Некорректный email адрес')->setStatusCode(422);
        }
        $hunter = User::where('email', $email)->first();

        if ($hunter && $hunter->id) {
            $request->merge(['hunter_id' => $hunter->id]);
            return $this->inviteHunter($request, $booking);
        }
        $bookingHunter = BookingHunter::where('booking_id', $booking->id)->first();
        if (!$bookingHunter) {
            return $this->sendError('Запись BookingHunter для этой брони не найдена')->setStatusCode(404);
        }
        $invitation = BookingHunterInvitation::where('booking_hunter_id', $bookingHunter->id)
            ->where('email', $email)
            ->whereNull('hunter_id')
            ->first();

        if ($invitation) {
            $invitation->update([
                'invited'         => true,
                'status'          => 'pending',
                'invited_at'      => now(),
                'invitation_token'=> $booking->code . '-' . md5($email),
            ]);
        } else {
            $invitation = BookingHunterInvitation::create([
                'booking_hunter_id' => $bookingHunter->id,
                'email'             => $email,
                'hunter_id'         => null,
                'invited'           => true,
                'status'            => 'pending',
                'invited_at'        => now(),
                'invitation_token'  => $booking->code . '-' . md5($email),
            ]);
        }

        // НЕ отправляем письмо создателю брони - он уже приглашен автоматически
        $creatorEmail = null;
        if($booking->create_user) {
            $creator = User::find($booking->create_user);
            if($creator) {
                $creatorEmail = $creator->email;
            }
        }

        // Отправляем письмо только если email не принадлежит создателю брони
        if($email !== $creatorEmail) {
            try {
                $message = __('Вас пригласили в сбор для брони №:id', ['id' => $booking->id]);
                $tempHunter = new User();
                $tempHunter->setAttribute('id', 0);
                $tempHunter->setAttribute('email', $email);
                $tempHunter->setAttribute('first_name', '');
                $tempHunter->setAttribute('last_name', '');
                $tempHunter->setAttribute('name', $email);
                $tempHunter->syncOriginal();

                Mail::to($email)->send(new HunterMessageEmail($booking, $tempHunter, $message, true));
            } catch (\Exception $e) {
                Log::error('inviteHunterByEmail: failed to send invitation email', [
                    'booking_id' => $booking->id,
                    'email'      => $email,
                    'error'      => $e->getMessage(),
                ]);
                return $this->sendError('Не удалось отправить приглашение: ' . $e->getMessage())->setStatusCode(500);
            }
        }

        return $this->sendSuccess([
            'message' => __('Приглашение отправлено на email :email', ['email' => $email]),
            'data'    => [
                'invitation_id' => $invitation->id,
            ],
        ]);
    }

    /**
     * Отправка письма выбранному охотнику с произвольным сообщением
     */
    public function emailHunter(Request $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $hunterId = (int) $request->input('hunter_id');
        $message  = trim((string)$request->input('message', ''));

        if (!$hunterId) {
            return $this->sendError('Не передан hunter_id')->setStatusCode(422);
        }
        if ($message === '') {
            return $this->sendError('Введите текст сообщения')->setStatusCode(422);
        }

        $hunter = User::find($hunterId);

        if (!$hunter || empty($hunter->email)) {
            return $this->sendError('У выбранного пользователя не указан email')->setStatusCode(404);
        }

        try {
            Mail::to($hunter->email)->send(new HunterMessageEmail($booking, $hunter, $message, false));
        } catch (\Exception $e) {
            Log::warning('emailHunter: ' . $e->getMessage());
            return $this->sendError('Не удалось отправить письмо')->setStatusCode(500);
        }

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

        $allInvitations = $booking->getAllInvitations();
        $invitations = $allInvitations->whereNotIn('status', ['removed']);

        $hunters = $invitations->map(function($invitation) {
            $hunter = $invitation->hunter;

            if ($hunter) {
                return [
                    'id' => $hunter->id,
                    'user_name' => $hunter->user_name,
                    'first_name' => $hunter->first_name,
                    'last_name' => $hunter->last_name,
                    'email' => $hunter->email,
                    'phone' => $hunter->phone,
                    'invited' => true,
                    'invitation_status' => $invitation->status,
                ];
            }

            if (!$hunter && $invitation->email) {
                return [
                    'id' => null,
                    'user_name' => null,
                    'first_name' => '',
                    'last_name' => '',
                    'email' => $invitation->email,
                    'phone' => null,
                    'invited' => true,
                    'invitation_status' => $invitation->status,
                    'is_external' => true,
                ];
            }

            return null;
        })->filter()->values();

        return $this->sendSuccess([
            'hunters' => $hunters,
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

        $userId = Auth::id();
        $invitation = $booking->getCurrentUserInvitation();

        if (!$invitation) {
            return $this->sendError('Приглашение не найдено')->setStatusCode(404);
        }

        $invitation->status = 'accepted';
        $invitation->accepted_at = now();
        $invitation->save();

        // Отправляем событие для обновления счетчика в реальном времени
        try {
            event(new \Modules\Booking\Events\HunterInvitationAcceptedEvent($booking, $userId));
        } catch (\Exception $e) {
            \Log::error('Ошибка отправки HunterInvitationAcceptedEvent', [
                'booking_id' => $booking->id,
                'hunter_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }

        return $this->sendSuccess([
            'message' => __('Invitation accepted'),
        ]);
    }

    /**
     * Отказаться от приглашения на бронь
     */
    public function declineInvitation(Request $request, Booking $booking): JsonResponse
    {
        if (!Auth::check()) {
            return $this->sendError('Необходима авторизация')->setStatusCode(401);
        }

        $userId = Auth::id();
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

        $booking->status = Booking::CANCELLED;
        $booking->save();

        $booking->skip_status_email = true;
        event(new BookingUpdatedEvent($booking));

        // Удаляем все приглашения охотников, кроме мастера охотника (того, кто приглашал)
        try {
            // Получаем все booking_hunter_id для этой брони, где is_master = false (не мастера)
            $nonMasterBookingHunterIds = BookingHunter::where('booking_id', $booking->id)
                ->where('is_master', false)
                ->pluck('id');

            if ($nonMasterBookingHunterIds->isNotEmpty()) {
                // Жёстко удаляем все приглашения, связанные с не-мастерами
                $deletedCount = BookingHunterInvitation::whereIn('booking_hunter_id', $nonMasterBookingHunterIds)->forceDelete();

                Log::info('cancelBooking: force delete приглашений охотников (кроме мастера)', [
                    'booking_id' => $booking->id,
                    'non_master_booking_hunter_ids' => $nonMasterBookingHunterIds->toArray(),
                    'deleted' => $deletedCount,
                ]);
            } else {
                Log::info('cancelBooking: нет не-мастеров для удаления приглашений', [
                    'booking_id' => $booking->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('cancelBooking: failed to force delete invitations', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $old = app()->getLocale();
            $bookingLocale = $booking->getMeta('locale');
            if($bookingLocale){
                app()->setLocale($bookingLocale);
            }

            if($isBaseAdmin) {
                if($booking->create_user) {
                    Mail::to(User::find($booking->create_user))->send(new StatusUpdatedEmail($booking, 'customer'));
                }
            } else {
                if(!$booking->relationLoaded('hotel')) {
                    $booking->load('hotel');
                }

                if($booking->hotel && $booking->hotel->admin_base) {
                    $baseAdmin = User::find($booking->hotel->admin_base);
                    if($baseAdmin && $baseAdmin->email) {
                        Mail::to($baseAdmin->email)->send(new StatusUpdatedEmail($booking, 'admin', null, $baseAdmin));
                    }
                }

                if (!empty($booking->email)) {
                    Mail::to($booking->email)->send(new StatusUpdatedEmail($booking, 'customer'));
                }
            }

            app()->setLocale($old);
        } catch(\Exception | \Swift_TransportException $e){
            Log::warning('sendCompletedStatusEmail: '.$e->getMessage());
        }

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

        if (in_array($booking->status, [Booking::CANCELLED, Booking::COMPLETED])) {
            return $this->sendError(__('This booking cannot be completed'));
        }

        $booking->status = Booking::COMPLETED;
        $booking->save();
        event(new BookingUpdatedEvent($booking));

        try {
            $old = app()->getLocale();
            $bookingLocale = $booking->getMeta('locale');
            if($bookingLocale){
                app()->setLocale($bookingLocale);
            }

            if($booking->create_user) {
                $creator = User::find($booking->create_user);
                if($creator && !empty($creator->email)) {
                    Mail::to($creator->email)->send(new StatusUpdatedEmail($booking, 'customer'));
                }
            }

            app()->setLocale($old);
        } catch(\Exception | \Swift_TransportException $e){
            Log::warning('sendCompletedStatusEmail: '.$e->getMessage());
        }

        return $this->sendSuccess([
            'message' => __('Booking has been completed successfully')
        ]);
    }


    public function getBookingServices(Booking $booking): JsonResponse
    {
        $services = BookingService::where('booking_id', $booking->id)->get();

        $trophies = BookingService::query()
            ->where('bc_booking_services.booking_id', $booking->id)
            ->where('bc_booking_services.service_type', 'trophy')
            ->leftJoin(
                'bc_animals',
                'bc_animals.id',
                '=',
                'bc_booking_services.animal'
            )
            ->select([
                'bc_booking_services.id',
                'bc_booking_services.booking_id',
                'bc_booking_services.service_type',
                'bc_booking_services.animal as animal_id',
                'bc_animals.title as animal_title',
                'bc_booking_services.type',
                'bc_booking_services.count',
                'bc_booking_services.created_at',
                'bc_booking_services.updated_at',
            ])
            ->get();

        $penalties = BookingService::query()
            ->where('bc_booking_services.booking_id', $booking->id)
            ->where('bc_booking_services.service_type', 'penalty')
            ->leftJoin(
                'bc_animals',
                'bc_animals.id',
                '=',
                'bc_booking_services.animal'
            )
            ->leftJoin('users', 'users.id', '=', 'bc_booking_services.hunter_id')
            ->select([
                'bc_booking_services.id',
                'bc_booking_services.booking_id',
                'bc_booking_services.service_type',
                'bc_booking_services.animal as animal_id',
                'bc_animals.title as animal_title',
                'bc_booking_services.type',
                'users.id as hunter_id',
                'users.name as hunter_name',
                'bc_booking_services.created_at',
                'bc_booking_services.updated_at',
            ])
            ->get();

        $preparations = BookingService::query()
            ->where('bc_booking_services.booking_id', $booking->id)
            ->where('bc_booking_services.service_type', 'preparation')
            ->leftJoin(
                'bc_animals',
                'bc_animals.id',
                '=',
                'bc_booking_services.animal'
            )
            ->select([
                'bc_booking_services.id',
                'bc_booking_services.booking_id',
                'bc_booking_services.service_type',
                'bc_booking_services.animal as animal_id',
                'bc_animals.title as animal_title',
                'bc_booking_services.count',
                'bc_booking_services.created_at',
                'bc_booking_services.updated_at',
            ])
            ->get();

        return response()->json([
            'trophies' => $trophies,
            'penalties' => $penalties,
            'preparations' => $preparations,
            'foods' => $services->where('service_type', 'food')->values(),
            'addetionals' => $services->where('service_type', 'addetional')->values(),
        ]);
    }

    public function getAnimalTrophyServices(): JsonResponse
    {
        $userHotelId = get_user_hotel_id();

        $animals = $this->animalClass::query()
            ->join('bc_hotel_animals as bha', function ($join) use ($userHotelId) {
                $join->on('bha.animal_id', '=', 'bc_animals.id')
                    ->where('bha.hotel_id', '=', $userHotelId);
            })
            ->select([
                'bc_animals.id',
                'bc_animals.title as title',
                'bha.status as animal_status'
            ])
            ->with('trophies:id,animal_id,type')
            ->get();

        return response()->json($animals);
    }


    public function storeTrophy(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'animal_id' => 'required|integer|exists:bc_animals,id',
            'type'      => 'required|string',
            'count'     => 'required|integer|min:1',
        ]);

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'trophy',
            'type'         => $request->input('type'),
            'service_id'   => null,
            'animal'       => $request->input('animal_id'),
            'count'        => $request->input('count'),
        ]);

        $animal = Animal::find($request->input('animal_id'));

        return response()->json([
            'id'           => $service->id,
            'animal_title' => $animal->title ?? '—',
            'type'         => $service->type,
            'count'        => $service->count,
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }

    public function deleteTrophy(Booking $booking, $serviceId): JsonResponse
    {
        $service = BookingService::where('id', $serviceId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        $service->delete();

        return response()->json(['status' => 'ok']);
    }

    //Штрафы
    public function getAnimalPenaltyServices(Booking $booking): JsonResponse
    {
        $booking->load('bookingHunter.invitations');
        $userHotelId = get_user_hotel_id();

        $animals = $this->animalClass::query()
            ->join('bc_hotel_animals as bha', function ($join) use ($userHotelId) {
                $join->on('bha.animal_id', '=', 'bc_animals.id')
                    ->where('bha.hotel_id', '=', $userHotelId);
            })
            ->select([
                'bc_animals.id',
                'bc_animals.title as title',
                'bha.status as animal_status'
            ])
            ->with('fines:id,animal_id,type')
            ->get();

        $hunterIds = $booking->bookingHunter?->invitations?->pluck('hunter_id')->unique();

        $hunters = User::query()
            ->whereIn('id', $hunterIds)
            ->select(['id', 'name'])
            ->get();

        return response()->json([
            'animals'  => $animals,
            'hunters'  => $hunters,
        ]);
    }
    public function storePenalty(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'animal_id' => 'required|integer|exists:bc_animals,id',
            'type'      => 'required|string',
            'hunter_id'     => 'required|integer',
        ]);

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'penalty',
            'type'         => $request->input('type'),
            'service_id'   => null,
            'hunter_id'   => $request->input('hunter_id'),
            'animal'       => $request->input('animal_id'),
        ]);

        $animal = Animal::find($request->input('animal_id'));
        $hunter = User::where('id', $service->hunter_id)->first();

        return response()->json([
            'id'           => $service->id,
            'animal_title' => $animal->title ?? '—',
            'type'         => $service->type,
            'count'        => 1,
            'hunter_name'  => $hunter->name ?? '—',
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deletePenalty(Booking $booking, $serviceId): JsonResponse
    {
        $service = BookingService::where('id', $serviceId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        $service->delete();

        return response()->json(['status' => 'ok']);
    }


// Разделка

    public function getAnimalPreparationServices(Booking $booking): JsonResponse
    {
        $animals = $this->animalClass::query()
            ->join('bc_hotel_animals as bha', function ($join) use ($booking) {
                $join->on('bha.animal_id', '=', 'bc_animals.id')
                    ->where('bha.hotel_id', '=', $booking->hotel_id);
            })
            ->select([
                'bc_animals.id',
                'bc_animals.title as title',
                'bha.status as animal_status'
            ])
            ->with('fines:id,animal_id,type')
            ->get();

        return response()->json([
            'animals'  => $animals,
        ]);
    }
    public function storePreparation(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'animal_id' => 'required|integer|exists:bc_animals,id',
            'count'     => 'required|integer|min:1',
        ]);

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'preparation',
            'type'         => $request->input('type'),
            'service_id'   => null,
            'animal'       => $request->input('animal_id'),
            'count'        => $request->input('count'),
        ]);

        $animal = Animal::find($request->input('animal_id'));

        return response()->json([
            'id'           => $service->id,
            'animal_title' => $animal->title ?? '—',
            'count'        => $service->count,
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deletePreparation(Booking $booking, $serviceId): JsonResponse
    {
        $service = BookingService::where('id', $serviceId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        $service->delete();

        return response()->json(['status' => 'ok']);
    }

    // Питание
    public function storeFoods(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'count'     => 'required|integer|min:1',
        ]);

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'food',
            'count'        => $request->input('count'),
        ]);

        return response()->json([
            'id'           => $service->id,
            'count'        => $service->count,
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deleteFoods(Booking $booking, $serviceId): JsonResponse
    {
        $service = BookingService::where('id', $serviceId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        $service->delete();

        return response()->json(['status' => 'ok']);
    }

    //Другое
    public function getAddetionalServices(Booking $booking): JsonResponse
    {
        $addetionals = AddetionalPrice::whereNull('type')->where('hotel_id', $booking->hotel_id)->get()
            ->map(fn ($addetional) => [
                'type'   => $addetional->type,
                'name'   => $addetional->name,
                'count'   => $addetional->count,
            ])
            ->values()
            ->toArray();

        return response()->json([
            'addetionals' => $addetionals,
        ]);
    }
    public function storeAddetional(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'addetional'     => 'required|string|',
        ]);

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'addetional',
            'type'       => $request->input('addetional'),
            'count'       => $request->input('count'),
        ]);

        return response()->json([
            'id'           => $service->id,
            'type'         => $service->type,
            'count'         => $service->count,
            'created_at'   => $service->created_at,
            'updated_at'   => $service->updated_at,
        ]);
    }
    public function deleteAddetional(Booking $booking, $serviceId): JsonResponse
    {
        $service = BookingService::where('id', $serviceId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        $service->delete();

        return response()->json(['status' => 'ok']);
    }
    public function storePrepayment(Booking $booking): JsonResponse
    {
        $masterBookingHunter = BookingHunter::where('booking_id', $booking->id)
            ->where('is_master', true)
            ->first();

        BookingHunterInvitation::where('booking_hunter_id', $masterBookingHunter->id)
            ->where('hunter_id', Auth::id())
            ->update(['prepayment_paid' => true]);

        $acceptedInvitations = $masterBookingHunter->invitations
            ->where('status', 'accepted');

        $paidCount = $acceptedInvitations->where('prepayment_paid', true)->count();

        if ($paidCount === $acceptedInvitations->count()) {
            $booking->status = Booking::FINISHED_PREPAYMENT;

            $timerHour = 24;

        if ($booking->hotel_id) {
            $hotelData = Hotel::where('id', $booking->hotel_id)->first();

            if ($hotelData) {
                // Получаем таймер койко-мест
                $timerHoursPlace = $hotelData->bed_timer_hours ?? null;

                if ($timerHoursPlace !== null && $timerHoursPlace > 0) {
                    $timerHour = (int) $timerHoursPlace;
                }
            }
        }

            $timerData = $this->bookingTimerService->startTimer($booking->id, $timerHour, 'beds');
        }

        $booking->prepayment_paid = true;
        $booking->save();

        return $this->sendSuccess([
            'message' => __('The gathering of hunters has begun'),
            'place_end_at' => $timerData['end_at'],
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

        $places = BookingRoomPlace::with('user:id,name,first_name,last_name')
            ->where('booking_id', $booking->id)
            ->get()
            ->groupBy(['room_id', 'place_number']);

        return $this->sendSuccess([
            'rooms' => $rooms,
            'places' => $places,
        ]);
    }

    public function selectPlace(Request $request, $bookingId): JsonResponse
    {
        $roomId = $request->input('room_id');
        $selectedPlaceNumber = $request->input('place_number');

        try {
            $occupiedPlaceNumbers = BookingRoomPlace::where('booking_id', $bookingId)
                ->where('room_id', $roomId)
                ->pluck('place_number')
                ->toArray();

            $finalPlaceNumber = $selectedPlaceNumber;
            for ($i = 1; $i <= $selectedPlaceNumber; $i++) {
                if (!in_array($i, $occupiedPlaceNumbers)) {
                    $finalPlaceNumber = $i;
                    break;
                }
            }

            $place = BookingRoomPlace::create([
                'booking_id'   => $bookingId,
                'room_id'      => $roomId,
                'place_number' => $finalPlaceNumber,
                'user_id'      => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'current_user_id' => auth()->id(),
                'place' => [
                    'user_id' => $place->user_id,
                    'place_number' => $place->place_number
                ]
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
        BookingRoomPlace::where('booking_id', $bookingId)->where('id', $request->input('place_id'))->where('user_id', auth()->id())->delete();
    }
}
