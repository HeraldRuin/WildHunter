<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingHunter extends Model
{
    use SoftDeletes;

    protected $table = 'bc_booking_hunters';

    protected $fillable = [
        'booking_id',
        'invited_by',
        'is_master',
        'creator_role',
        'note',
    ];

    protected $casts = [
        'is_master' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
    public function invitedBy()
    {
        return $this->belongsTo(\App\User::class, 'invited_by');
    }
    public function invitations()
    {
        return $this->hasMany(BookingHunterInvitation::class, 'booking_hunter_id');
    }
}
