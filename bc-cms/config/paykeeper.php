<?php

return [
    'base_url' => env('PAYKEEPER_BASE_URL'),
    'success_url' => env('PAYKEEPER_SUCCESS_URL'),
    'fail_url' => env('PAYKEEPER_FAIL_URL'),

    'send_check_email' => env('SEND_CHECK_EMAIL', false),

    'pay_ttl_days' => env('PAYKEEPER_PAY_TTL_DAYS', 1),
    'token_ttl' => env('PAYKEEPER_TOKEN_TTL', 1440),
    'cache_key' => env('PAYKEEPER_CACHE_KEY', 'paykeeper_token'),
];
