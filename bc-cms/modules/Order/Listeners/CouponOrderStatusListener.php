<?php


namespace Modules\Order\Listeners;


use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Models\Order;

class CouponOrderStatusListener
{
    /**
     *
     * @param OrderStatusUpdated $event
     * @return mixed
     */
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->_order;
        $order->coupons()->update([
            'status'=>$order->status
        ]);
    }
}
