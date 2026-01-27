<?php

namespace Modules\Hotel\Models;

use Illuminate\Database\Eloquent\Model;

class HotelAnimal extends Model
{
    protected $table = 'bc_hotel_animals';
    protected $fillable = [
        'hotel_id',
        'animal_id',
        'status',
        'hunters_count',
    ];
}
