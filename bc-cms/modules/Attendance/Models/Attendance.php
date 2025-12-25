<?php

namespace Modules\Attendance\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Models\Booking;
use Modules\Hotel\Models\Hotel;
use Illuminate\Http\Request;

class Attendance
{
    use SoftDeletes;

    protected $bookingClass;
    public    $checkout_booking_detail_file       = 'Animal::frontend/booking/detail';
    public    $checkout_booking_detail_modal_file = 'Animal::frontend/booking/detail-modal';

    public    $email_new_booking_file             = 'Animal::emails.new_booking_detail';

    protected $table = 'bc_animals';

    protected $fillable = [
        'title',
        'content',
        'status',
        'faqs',
        'hotel_id',
    ];
    public function getCheckoutUrl()
    {
        return route('animal.booking.checkout', ['booking_code' => $this->id]);
    }

    public static function isEnable(): bool
    {
        return  true;
    }
    public static function getServiceIconFeatured()
    {
        return "icofont-read-book";
    }
}

