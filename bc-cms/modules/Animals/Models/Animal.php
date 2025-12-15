<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Models\Hotel;
use Illuminate\Http\Request;

class Animal extends Bookable
{
    use SoftDeletes;

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
        'hotel_id',
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

        $booking = new $this->bookingClass();
        $booking->status = 'processing';
        $booking->object_id = $request->input('service_id');
        $booking->object_model = $request->input('service_type');
        $booking->vendor_id = $this->author_id;
        $booking->customer_id = Auth::id();
//        $booking->total = $total;
        $booking->total_guests = $adults;
        $booking->start_date = $start_date->format('Y-m-d H:i:s');
        $booking->hotel_id = $hotelId;

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
        return setting_item('animal_disable');
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

