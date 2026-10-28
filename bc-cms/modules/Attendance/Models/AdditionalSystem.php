<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalSystem extends Model
{
    public const TYPE_BASE = 'base';

    public const TYPE_HUNT = 'hunt';

    protected $table = 'bc_additional_systems';

    protected $fillable = [
        'user_id',
        'name',
        'type',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_BASE => __('At the base'),
            self::TYPE_HUNT => __('On the hunt'),
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? '';
    }
}
