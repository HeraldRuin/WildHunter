<?php

namespace Modules\Weapon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\User\Models\User;

class WeaponType extends Model
{
    protected $table = 'bc_weapons';

    protected $fillable = ['name', 'type', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'weapon_type_id');
    }
    public function calibers(): HasMany
    {
        return $this->hasMany(Caliber::class);
    }
}
