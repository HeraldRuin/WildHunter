<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalSystem extends Model
{
    protected $table = 'bc_additional_systems';

    protected $fillable = [
        'user_id',
        'name',
    ];
}
