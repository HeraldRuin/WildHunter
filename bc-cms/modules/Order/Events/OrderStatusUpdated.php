<?php


namespace Modules\Order\Events;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Order\Models\Order;

class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * @var Order
     */
    public $_order;

    public $_old_status;

    public $_new_status;

    public function __construct(Order $order,$old_status,$new_status){
        $this->_order = $order;
        $this->_new_status = $new_status;
        $this->_old_status = $old_status;
    }
}
