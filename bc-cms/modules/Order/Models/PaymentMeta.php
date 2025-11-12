<?php


namespace Modules\Order\Models;


use App\BaseModel;
use App\BaseMeta;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMeta extends BaseMeta
{
    protected $table = 'core_payment_meta';
}
