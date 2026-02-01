<?php

namespace Modules\Booking;

use Modules\Booking\Events\BookingFinishEvent;
use Modules\Booking\Events\BookingStartCollectionEvent;
use Modules\Booking\Events\EnquiryReplyCreated;
use Modules\Booking\Listeners\BookingFinishListen;
use Modules\Booking\Listeners\BookingStartCollectionListen;
use Modules\Booking\Listeners\SendEnquiryReplyNotification;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        EnquiryReplyCreated::class => [
            SendEnquiryReplyNotification::class
        ],
        BookingFinishEvent::class => [
            BookingFinishListen::class,
        ],
        BookingStartCollectionEvent::class => [
            BookingStartCollectionListen::class,
        ],

    ];

}
