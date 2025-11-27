<?php
namespace Modules\Animals\Models;

use App\BaseModel;

class AnimalTranslation extends BaseModel
{
    protected $table = 'bc_animal_translations';
    protected $fillable = ['title', 'content','trip_ideas'];
    protected $seo_type = 'animal_translation';
    protected $cleanFields = [
        'content'
    ];
    protected $casts = [
        'trip_ideas'  => 'array',
    ];
}
