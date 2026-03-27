<?php

namespace Modules\Animals\Models;

use App\Traits\HasHotelAnimalPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AnimalFine extends Model
{
    use HasHotelAnimalPrice;

    protected $table = 'bc_animal_fines';

    protected $fillable = [
        'animal_id',
        'type',
        'price',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
    public function hotelPrices($hotelId): MorphMany
    {
        return $this->morphMany(HotelAnimalPrice::class, 'priceable')->where('hotel_id', $hotelId);;
    }
}
