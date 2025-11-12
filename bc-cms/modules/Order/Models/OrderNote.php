<?php


namespace Modules\Order\Models;


use App\BaseModel;

class OrderNote extends BaseModel
{
    const STATUS_CHANGED = 'status_changed';
    const ITEM_STATUS_CHANGED = 'item_status_changed';
    const ORDER_EXPIRED = 'order_expired';

    protected $table = 'core_order_notes';

    protected $fillable = [
        'name',
        'value',
        'extra'
    ];

    protected $casts = [
        'extra'=>'array'
    ];

}
