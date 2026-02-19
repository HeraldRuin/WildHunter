<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class BookingRoomPlace extends Model
{
    protected $table = 'bc_booking_room_places';

    protected $fillable = [
        'booking_id',
        'room_id',
        'place_number',
        'user_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
