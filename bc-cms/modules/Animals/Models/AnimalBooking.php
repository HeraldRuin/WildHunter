<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalBooking extends Model
{
    protected $table = 'bc_animal_bookings';

    protected $fillable = [
        'animal_id',
    ];
}
