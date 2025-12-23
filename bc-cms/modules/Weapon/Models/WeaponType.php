<?php

namespace Modules\Weapon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Booking\Models\Bookable;
use Modules\Booking\Models\Booking;
use Modules\User\Models\User;

class WeaponType extends Bookable
{
    protected $table = 'bc_weapons';

    protected $fillable = ['name', 'type', 'description'];
    protected $translation_class = WeaponTranslation::class;

    public static function isEnable(): bool
    {
        return true;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'weapon_type_id');
    }
    public function calibers(): HasMany
    {
        return $this->hasMany(Caliber::class);
    }
}
