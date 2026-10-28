<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class AddetionalPrice extends Model
{
    public const PLACE_BASE = 'base';

    public const PLACE_HUNT = 'hunt';
    protected $table = 'bc_addetional_prices';

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'hotel_id',
        'name',
        'start_date',
        'end_date',
        'price',
        'type',
        'is_visible',
        'calculation_type',
        'count',
        'place',
        'is_system',
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

    public static function placeOptions(): array
    {
        return [
            self::PLACE_BASE => __('At the base'),
            self::PLACE_HUNT => __('On the hunt'),
        ];
    }

    public function placeLabel(): string
    {
        return self::placeOptions()[$this->place] ?? '';
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeAccessible($query, int $hotelId, int $userId)
    {
        return $query
            ->where('hotel_id', $hotelId)
            ->where('user_id', $userId);
    }
}
