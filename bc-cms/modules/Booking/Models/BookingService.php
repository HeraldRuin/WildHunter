<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    protected $table = 'bc_booking_services';

    protected $fillable = [
        'booking_id',
        'service_type',
        'service_id',
        'hunter_id',
        'animal',
        'type',
    ];

    /**
     * Бронь
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
