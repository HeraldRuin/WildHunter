<?php
namespace Modules\Weapon\Models;

use App\BaseModel;

class WeaponTranslation extends BaseModel
{
    protected $table = 'bc_weapon_translations';
    protected $fillable = ['title', 'content','trip_ideas'];
    protected $seo_type = 'weapon_translation';
    protected $cleanFields = [
        'content'
    ];
    protected $casts = [
        'trip_ideas'  => 'array',
    ];
}
