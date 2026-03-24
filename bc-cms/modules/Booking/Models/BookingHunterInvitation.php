<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingHunterInvitation extends Model
{
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const PREPAYMENT_PAID    = 'paid';
    const PREPAYMENT_PENDING = 'pending';
    const PREPAYMENT_UNPAID  = 'unpaid';

    protected $table = 'bc_booking_hunter_invitations';

    protected $fillable = [
        'booking_hunter_id',
        'hunter_id',
        'email',
        'invited',
        'status',
        'prepayment_paid_status',
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

    protected $appends = [
        'prepayment_badge'
    ];

    public function isPaid(): bool
    {
        return $this->prepayment_paid_status === self::PREPAYMENT_PAID;
    }

    public function isPending(): bool
    {
        return $this->prepayment_paid_status === self::PREPAYMENT_PENDING;
    }

    public function isUnpaid(): bool
    {
        return $this->prepayment_paid_status === self::PREPAYMENT_UNPAID;
    }

    public function scopePaid($query)
    {
        return $query->where('prepayment_paid_status', self::PREPAYMENT_PAID);
    }

    public function scopePending($query)
    {
        return $query->where('prepayment_paid_status', self::PREPAYMENT_PENDING);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('prepayment_paid_status', self::PREPAYMENT_UNPAID);
    }

    public function getPrepaymentBadgeAttribute(): array
    {
        return match ($this->prepayment_paid_status) {
            self::PREPAYMENT_PAID => [
                'text' => 'Оплачено',
                'class' => 'bg-success',
            ],
            self::PREPAYMENT_UNPAID => [
                'text' => 'Не оплачено',
                'class' => 'bg-danger',
            ],
            default => [
                'text' => 'Ожидается оплата',
                'class' => 'bg-warning',
            ],
        };
    }

    public function bookingHunter(): BelongsTo
    {
        return $this->belongsTo(BookingHunter::class, 'booking_hunter_id');
    }
    public function hunter(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'hunter_id');
    }

    /**
     * Возвращает охотника в этом приглашение для брони
     */
    public static function findInventedHunterForBooking($bookingHunterId, $hunterId)
    {
        return self::where('booking_hunter_id', $bookingHunterId)
            ->where('hunter_id', $hunterId)
            ->first();
    }
}
