<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingHunterInvitation extends Model
{
    const STATUS_ACCEPTED = 'accepted';
    use SoftDeletes;

    protected $table = 'bc_booking_hunter_invitations';

    protected $fillable = [
        'booking_hunter_id',
        'hunter_id',
        'email',
        'invited',
        'status',
        'invited_at',
        'accepted_at',
        'declined_at',
        'invitation_token',
        'note',
    ];

    protected $casts = [
        'invited' => 'boolean',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public function bookingHunter()
    {
        return $this->belongsTo(BookingHunter::class, 'booking_hunter_id');
    }
    public function hunter()
    {
        return $this->belongsTo(\App\User::class, 'hunter_id');
    }
}
