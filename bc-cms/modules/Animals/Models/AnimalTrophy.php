<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalTrophy extends Model
{
    protected $table = 'bc_animal_trophies';

    protected $fillable = [
        'animal_id',
        'type',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }
}
