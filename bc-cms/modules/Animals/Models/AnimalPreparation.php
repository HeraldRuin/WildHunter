<?php

namespace Modules\Animals\Models;

use App\Traits\HasHotelAnimalPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AnimalPreparation extends Model
{
    use HasHotelAnimalPrice;

    protected $table = 'bc_animal_preparations';

    protected $fillable = [
        'animal_id',
        'type',
        'price',
    ];

    public function scopeForHotel($query, int $hotelId)
    {
        return $query->whereHas('animal', function($q) use ($hotelId) {
            $q->whereHas('hotels', function($q2) use ($hotelId) {
                $q2->where('hotel_id', $hotelId);
            });
        });
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
    public function hotelPrices(): MorphMany
    {
        return $this->morphMany(HotelAnimalPrice::class, 'priceable');
    }
    public function hotelPriceForHotel($hotelId)
    {
        return $this->hotelPrices()->where('hotel_id', $hotelId)->first()?->price ?? null;
    }
}
