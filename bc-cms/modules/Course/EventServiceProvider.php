<?php

namespace Modules\Course;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Order\Events\OrderStatusUpdated;    
use Modules\Course\Listeners\AddUserToCourseListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        
        OrderStatusUpdated::class=>[
            AddUserToCourseListener::class,
        ],
    ];
}
