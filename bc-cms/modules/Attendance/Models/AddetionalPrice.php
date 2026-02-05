<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class AddetionalPrice extends Model
{
    protected $table = 'bc_addetional_prices';

    protected $fillable = [
        'user_id',
        'hotel_id',
        'name',
        'start_date',
        'end_date',
        'price',
    ];
}
