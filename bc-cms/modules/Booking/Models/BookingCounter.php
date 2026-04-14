<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;

class BookingCounter extends Model
{
    protected $table = 'bc_booking_counters';

    protected $fillable = [
        'type',
        'last_number',
    ];

    protected $casts = [
        'last_number' => 'integer',
    ];

    public $timestamps = false;
}
