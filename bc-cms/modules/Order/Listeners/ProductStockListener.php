<?php


namespace Modules\Order\Listeners;



use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Product\Models\Product;

class ProductStockListener
{
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->_order;
        $items = $order->items;
        switch ($order->status) {
            case Order::PROCESSING:
            case Order::COMPLETED:
                $this->reduceStock($items);
                break;
            case Order::REFUNDED:
            case Order::CANCELLED:
            case Order::DRAFT:
            case Order::FAILED:
                $this->returnStock($items);
                break;
        }
    }

    public function reduceStock($items){
        /** @var OrderItem $item */
        if(!empty($items)){
            foreach ($items as $item) {
                $item->reduceStock();
            }
        }
    }
    public function returnStock($items){

        /** @var OrderItem $item */

        if(!empty($items)){
            foreach ($items as $item) {
                $item->returnStock();
            }
        }
    }
}
