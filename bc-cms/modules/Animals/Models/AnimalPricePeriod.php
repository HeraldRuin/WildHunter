<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalPricePeriod extends Model
{
    protected $table = 'bc_animal_price_periods';

    protected $fillable = [
        'animal_id',
        'start_date',
        'end_date',
        'price',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
