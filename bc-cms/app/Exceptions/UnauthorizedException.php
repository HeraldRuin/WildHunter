<?php

namespace App\Exceptions;

class UnauthorizedException extends BaseException
{
    public function __construct(
        string $message = 'Unauthorized',
        string $errorCode = 'UNAUTHORIZED',
        string $domain = 'app',
        array $context = []
    ) {
        parent::__construct($message, 401, $errorCode, $domain, $context);
    }
}
