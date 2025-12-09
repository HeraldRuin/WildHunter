<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Models\Hotel;
use Illuminate\Http\Request;

class Animal extends Bookable
{
    protected $bookingClass;
    public    $checkout_booking_detail_file       = 'Animal::frontend/booking/detail';
    public    $checkout_booking_detail_modal_file = 'Animal::frontend/booking/detail-modal';

    public    $email_new_booking_file             = 'Animal::emails.new_booking_detail';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->bookingClass = Booking::class;
    }

    protected $table = 'bc_animals';

    protected $fillable = [
        'title',
        'content',
        'status',
        'faqs',

    ];

    public function addToCart(Request $request)
    {
//        $res = $this->addToCartValidate($request);
//        if ($res !== true) return $res;



        // Add Booking
        $adults = $request->input('adults');

        $start_date = new \DateTime($request->input('start_date'));
        $hotelId = $request->input('hotel_id');
        $animal_id = $request->input('animal_id');

         AnimalBooking::create([
            'animal_id' => $animal_id,
            'user_id'   =>  auth()->id(),
            'hotel_id'  => $hotelId,
            'date'      => $start_date,
            'adults'    => $adults,
            'status'    => 'processing'
        ]);

//        $booking = new Booking();
//        $booking->object_model = 'hunting'; // или имя модели как в get_bookable_services()
//        $booking->object_id = $service->id;
//        $booking->customer_id = $customer->id;
//        $booking->vendor_id = $service->create_user ?? $service->vendor_id; // владелец сервиса
//        $booking->start_date = $startDate; // "2025-12-15"
//        $booking->end_date   = $endDate;   // "2025-12-17"
//        $booking->total = $totalPrice; // сумма брони
//        $booking->first_name = $customer->first_name;
//        $booking->last_name  = $customer->last_name;
//        $booking->email      = $customer->email;
//        $booking->status     = Booking::CONFIRMED; // или DRAFT/UNPAID
//        $booking->total_guests = $guestsCount ?? 1;

        $booking = new $this->bookingClass();
        $booking->status = 'draft';
        $booking->object_id = $request->input('service_id');
        $booking->object_model = $request->input('service_type');
        $booking->vendor_id = $this->author_id;
        $booking->customer_id = Auth::id();
//        $booking->total = $total;
//        $booking->total_guests = $total_guests;
        $booking->start_date = $start_date->format('Y-m-d H:i:s');
//        $booking->end_date = $end_date->format('Y-m-d H:i:s');

        $booking->vendor_service_fee_amount = $total_service_fee ?? '';
        $booking->vendor_service_fee = $list_service_fee ?? '';
        $booking->buyer_fees = $list_buyer_fees ?? '';
//        $booking->total_before_fees = $total_before_fees;
//        $booking->total_before_discount = $total_before_fees;

        $check = $booking->save();

        if ($check) {

            return $this->sendSuccess([
                'url' => $booking->getCheckoutUrl(),
                'booking_code' => $booking->code,
            ]);
        }
        return $this->sendError(__("Can not check availability"));
    }

    public function getCheckoutUrl()
    {
        return route('animal.booking.checkout', ['booking_code' => $this->id]);
    }

    public static function isEnable(): bool
    {
        return setting_item('animal_disable') == false;
    }

    public function getNumberReviewsInService($status = false)
    {
        return $this->reviewClass::countReviewByServiceID($this->id, false, $status, $this->type) ?? 0;
    }
    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'bc_hotel_animals','animal_id','hotel_id')->withPivot('status');
    }
}

