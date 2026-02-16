<?php
return [
    'booking_route_prefix'=>env("BOOKING_ROUTER_PREFIX",'booking'),
    'statuses'=>[
        'completed',
        'processing',
        'confirmed',
        'cancelled',
        'paid',
        'unpaid',
        'partial_payment',
        'collection',
//        'finished_collection',
        'invitation',
        'prepayment_collection',
    ]
];
