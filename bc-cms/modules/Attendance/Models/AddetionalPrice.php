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
        'type',
        'calculation_type',
        'count',
    ];

    const INDIVIDUAL = 'individual';
    const PERSON = 'per_person';

    public const CALCULATION_TYPES = [
        self::PERSON => 'Кол-во людей',
        self::INDIVIDUAL => 'Индивидуальный',
    ];

    const ADDETIONAL = 'addetional';
    const FOOD = 'food';
    const SPENDING = 'spending';
    const PREPARATION = 'preparation';
    const PENALTY = 'penalty';
    const TROPHY = 'trophy';
}
