<?php
namespace Modules\Order\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\Coupon\Models\Coupon;

class CouponOrder extends BaseModel
{
    protected $table = 'core_coupon_order';
    protected $fillable = [
        'order_id',
        'order_status',
        'object_id',
        'object_model',
        'coupon_code',
        'coupon_discount_type',
        'coupon_amount',
        'coupon_data',
    ];
    protected $casts = [
        'coupon_data' => 'array',
    ];

    public function clean($coupon_id)
    {
        $query = $this->where("order_id", $coupon_id);
        $query->get();
        if (!empty($query)) {
            $query->delete();
        }
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class,'coupon_code','code');
    }

    public function name() : Attribute{
        return Attribute::make(
            get:function(){
                    return $this->coupon->name ?? '';
            }
        );
    }

}
