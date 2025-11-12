<?php


namespace Modules\Order\Events;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;

class OrderItemStatusUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * @var OrderItem
     */
    public $_order_item;

    public $_old_status;

    public $_new_status;

    public function __construct(OrderItem $order_item,$old_status,$new_status){
        $this->_order_item = $order_item;
        $this->_new_status = $new_status;
        $this->_old_status = $old_status;
    }
}
