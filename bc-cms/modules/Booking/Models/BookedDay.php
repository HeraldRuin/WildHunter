<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Hotel\Models\HotelRoom;

class BookedDay extends Model
{
    protected $table = 'bc_booked_days';

    protected $fillable = ['booking_id', 'room_id', 'date', 'number'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class);
    }
}
