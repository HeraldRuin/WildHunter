<?php

namespace Modules\Weapon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Booking\Models\Bookable;

class Caliber extends Bookable
{
    protected $table = 'bc_calibers';

    protected $fillable = ['name', 'type', 'description'];
    protected $translation_class = CaliberTranslation::class;

    public static function isEnable(): bool
    {
        return true;
    }

    public function weaponType(): BelongsTo
    {
        return $this->belongsTo(WeaponType::class);
    }
}
