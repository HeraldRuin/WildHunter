<?php

namespace Modules\Course\Listeners;

use Modules\Order\Events\OrderStatusUpdated;
use Modules\Course\Models\Course;
use Modules\Order\Models\Order;

class AddUserToCourseListener
{
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->_order;
        switch ($event->_new_status) {

            // Listen for completed new status, then add user to course if user is not already in the course
            case Order::COMPLETED:
                if($order->items && $order->items->count() > 0 && $order->customer_id){
                    $order->items->each(function ($item) use ($order) {
                        $product = $item->product;
                        if($product instanceof Course){
                            // NOTE: Student will be able to study course immediately if they are not in the course
                            // Upsert used here
                            return $product->addStudentById($order->customer_id);
                        }
                    });
                }
                break;
        }
    }
}
