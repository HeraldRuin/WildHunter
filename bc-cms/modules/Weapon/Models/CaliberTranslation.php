<?php
namespace Modules\Weapon\Models;

use App\BaseModel;

class CaliberTranslation extends BaseModel
{
    protected $table = 'bc_caliber_translations';
    protected $fillable = ['title', 'content','trip_ideas'];
    protected $seo_type = 'caliber_translation';
    protected $cleanFields = [
        'content'
    ];
    protected $casts = [
        'trip_ideas'  => 'array',
    ];
}
