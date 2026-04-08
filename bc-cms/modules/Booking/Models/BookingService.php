<?php

namespace Modules\Booking\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Animals\Models\Animal;

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
        'count',
        'price',
        'comment',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function hunter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hunter_id');
    }
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
