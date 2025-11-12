<?php


namespace Modules\Order;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Order\Events\OrderItemStatusUpdated;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Listeners\CouponOrderStatusListener;
use Modules\Order\Listeners\OrderItem\OrderItemProductStockListener;
use Modules\Order\Listeners\OrderItem\OrderItemStatusUpdatedNotification;
use Modules\Order\Listeners\OrderStatusUpdatedNotification;
use Modules\Order\Listeners\ProductStockListener;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        OrderStatusUpdated::class=>[
            ProductStockListener::class,
            OrderStatusUpdatedNotification::class,


            // TODO: enable this when we enable coupon system later
            // CouponOrderStatusListener::class,
        ],
        OrderItemStatusUpdated::class=>[
            OrderItemProductStockListener::class,
            OrderItemStatusUpdatedNotification::class,
        ],
    ];
}
