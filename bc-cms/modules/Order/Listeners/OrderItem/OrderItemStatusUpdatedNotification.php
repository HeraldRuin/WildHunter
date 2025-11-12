<?php


namespace Modules\Order\Listeners\OrderItem;


use Modules\Order\Events\OrderItemStatusUpdated;
use Modules\Order\Models\Order;
use Modules\Order\Notifications\OrderNotification;

class OrderItemStatusUpdatedNotification
{

    /**
     *
     * @param OrderItemStatusUpdated $event
     * @return mixed
     */
    public function handle(OrderItemStatusUpdated $event)
    {
        $order = $event->_order_item;

        if(in_array($event->_old_status,[Order::ON_HOLD,Order::DRAFT,Order::PROCESSING]) and in_array($order->status,[Order::CANCELLED])){
            return $order->sendOrderNotifications(OrderNotification::CANCELLED_ORDER);
        }

        if(in_array($event->_old_status,[Order::PROCESSING]) and in_array($order->status,[Order::COMPLETED])){
            return $order->sendOrderNotifications(OrderNotification::COMPLETED_ORDER);
        }

        if(in_array($event->_old_status,[Order::COMPLETED]) and in_array($order->status,[Order::REFUNDED])){
            return $order->sendOrderNotifications(OrderNotification::REFUNDED_ORDER);
        }
    }
}
