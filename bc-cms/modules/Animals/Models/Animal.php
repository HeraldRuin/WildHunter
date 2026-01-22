<?php

namespace Modules\Animals\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Models\Booking;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Hotel\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $booking = new $this->bookingClass();
        $booking->status = 'processing';
        $booking->object_id = $request->input('service_id');
        $booking->object_model = $request->input('service_type');
        $booking->vendor_id = $this->author_id;
        $booking->customer_id = Auth::id();
        $booking->amount_hunting = $request->input('hunting_adults') * $request->input('animal_price');
        $booking->animal_id = $request->input('animal_id') ?? null;
        $booking->type = $request->input('type') ?? null;
        $booking->total_hunting = $request->input('hunting_adults');
        $booking->start_date = Carbon::parse($request->input('start_date_animal'))->startOfDay();
        $booking->start_date_animal = Carbon::parse($request->input('start_date_animal'))->startOfDay();
        $booking->hotel_id = $request->input('hotel_id');
        if ($request->input('userRole') === 'baseadmin') {
            $booking->event = true;
        }

        $check = $booking->save();

        if ($check) {
            // Вызываем событие создания брони
            event(new BookingCreatedEvent($booking));

            return $this->sendSuccess([
                'url' => $booking->getCheckoutUrl(),
                'booking_code' => $booking->code,
            ]);
        }
        return $this->sendError(__("Can not check availability"));
    }

    public static function getServiceIconFeatured()
    {
        return "icofont-paw";
    }

    public function getCheckoutUrl()
    {
        return route('animal.booking.checkout', ['booking_code' => $this->id]);
    }

    public static function isEnable(): bool
    {
        return true;
    }
    public static function isEnableForAdmin(): bool
    {
        return setting_item('admin_animal_disable');
    }

    public function getNumberReviewsInService($status = false)
    {
        return $this->reviewClass::countReviewByServiceID($this->id, false, $status, $this->type) ?? 0;
    }
    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'bc_hotel_animals','animal_id','hotel_id')->withPivot('status', 'hunters_count');
    }
    public function periods(): HasMany
    {
        return $this->hasMany(AnimalPricePeriod::class);
    }
    public function trophies(): HasMany
    {
        return $this->hasMany(AnimalTrophy::class);
    }
}

