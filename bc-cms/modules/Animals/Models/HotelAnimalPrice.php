<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HotelAnimalPrice extends Model
{
    protected $table = 'bc_hotel_animal_prices';

    protected $fillable = [
        'hotel_id',
        'priceable_id',
        'priceable_type',
        'price',
    ];

    /**
     * Полиморфная связь к сущности, которой принадлежит цена
     */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }
}
