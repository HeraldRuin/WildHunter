<?php


namespace Modules\Order\Listeners\OrderItem;



use Modules\Order\Events\OrderItemStatusUpdated;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Models\Order;
use Modules\Product\Models\Product;

class OrderItemProductStockListener
{
    public function handle(OrderItemStatusUpdated $event)
    {
        $item = $event->_order_item;
        switch ($item->status) {
            case Order::PROCESSING:
            case Order::COMPLETED:
                $item->reduceStock();
                break;
            case Order::REFUNDED:
            case Order::CANCELLED:
            case Order::DRAFT:
            case Order::FAILED:
                $item->returnStock();
                break;
        }
    }
}
