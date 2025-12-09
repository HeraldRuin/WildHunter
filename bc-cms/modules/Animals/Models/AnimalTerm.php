<?php
namespace Modules\Animals\Models;

use App\BaseModel;

class AnimalTerm extends BaseModel
{
    protected $table = 'bc_animal_term';
    protected $fillable = [
        'term_id',
        'target_id'
    ];
}
