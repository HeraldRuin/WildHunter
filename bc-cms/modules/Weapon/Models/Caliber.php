<?php

namespace Modules\Weapon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Caliber extends Model
{
    protected $table = 'bc_calibers';

    protected $fillable = ['name', 'weapon_type_id', 'description'];

    public function weaponType(): BelongsTo
    {
        return $this->belongsTo(WeaponType::class);
    }
}
