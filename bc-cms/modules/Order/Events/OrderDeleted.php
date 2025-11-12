<?php


namespace Modules\Order\Events;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Order\Models\Order;

class OrderDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * @var Order
     */
    public $_order;

    public function __construct(Order $order){
        $this->_order = $order;
    }
}
