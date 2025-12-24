<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Weapon\Models\Caliber;
use Modules\Weapon\Models\WeaponType;

class UserWeapon extends Model
{
    protected $table = 'bc_user_weapons';

    protected $fillable = [
        'user_id',
        'hunter_billet_number',
        'hunter_license_number',
        'hunter_license_date',
        'weapon_type_id',
        'caliber',
    ];

    protected $casts = [
        'license_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
