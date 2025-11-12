<?php


namespace Modules\Order\Listeners;


use Illuminate\Support\Carbon;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Models\Order;
use Modules\Product\Models\ProductOnHold;

class ProductOnHoldListener
{
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->_order;
        $items = $order->items;
        switch ($order->status) {
            case Order::ON_HOLD:
                if(!empty($items)){
                    $setting_expired_at = setting_item('product_hold_stock',60);
                    $expired_at  = Carbon::now()->addMinutes($setting_expired_at??60)->format('Y-m-d H:i:s');

                    foreach ($items as $item) {
                        $checkOnHoldExist = ProductOnHold::where([
                            'order_id'  =>$item->order_id,
                            'product_id'  =>$item->object_id,
                            'variant_id'  =>$item->variation_id,
                        ])->where('expired_at','>=',now())->count();

                        if(empty($checkOnHoldExist)){
                            ProductOnHold::create(
                                ['order_id'=>$item->order_id,'product_id'=>$item->object_id,'variant_id'=>$item->variation_id,'qty'=>$item->qty,'expired_at'=>$expired_at]
                            );
                        }
                    }
                }
                break;
            case Order::PROCESSING:
            case Order::COMPLETED:
            case Order::FAILED:
            case Order::CANCELLED:
//              remove product on-hold in order item
                ProductOnHold::where('order_id',$order->id)->delete();
                break;
            case Order::DRAFT:
                break;
        }
    }
}
