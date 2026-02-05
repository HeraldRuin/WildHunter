<?php

namespace Modules\Animals\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalPreparation extends Model
{
    protected $table = 'bc_animal_preparations';

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
