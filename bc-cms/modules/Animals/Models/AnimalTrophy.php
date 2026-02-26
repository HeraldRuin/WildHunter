<?php

namespace Modules\Animals\Models;

use App\Traits\HasHotelAnimalPrice;
use Illuminate\Database\Eloquent\Model;

class AnimalTrophy extends Model
{
    use HasHotelAnimalPrice;

    protected $table = 'bc_animal_trophies';

    protected $fillable = [
        'animal_id',
        'type',
        'price',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
