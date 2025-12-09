<?php
namespace Modules\Animals\Models;

use App\BaseModel;

class AnimalDate extends BaseModel
{
    protected $table = 'bc_animal_dates';

    protected $casts = [
        'price'=>'float',
    ];

    public static function getDatesInRanges($start_date,$end_date,$id){
        return static::query()->where([
            ['start_date','>=',$start_date],
            ['end_date','<=',$end_date],
            ['target_id','=',$id],
        ])->take(100)->get();
    }
}
