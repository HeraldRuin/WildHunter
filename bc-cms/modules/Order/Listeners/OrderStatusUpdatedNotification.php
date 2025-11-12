<?php


namespace Modules\Order\Listeners;


use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Models\Order;
use Modules\Order\Notifications\OrderNotification;

class OrderStatusUpdatedNotification
{

    /**
     *
     * @param OrderStatusUpdated $event
     * @return mixed
     */
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->_order;

        if(in_array($event->_old_status,[Order::DRAFT,Order::ON_HOLD]) and in_array($order->status,[Order::COMPLETED,Order::PROCESSING])){

            return $order->sendOrderNotifications(OrderNotification::NEW_ORDER);
        }

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
