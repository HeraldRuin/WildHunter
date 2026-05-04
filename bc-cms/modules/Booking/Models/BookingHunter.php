<?php

namespace Modules\Booking\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function changeCreator(User $user): void
    {
        $this->invited_by = $user->id;
        $this->is_master = $user->hasRole('hunter');
        $this->creator_role = $user->role->code ?? null;
    }

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
    public function isMasterHunter($userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->invited_by === $userId;
    }
    public function hunter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hunter_id');
    }
}
