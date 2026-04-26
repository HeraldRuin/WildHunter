<?php

namespace App\Exceptions;

class NotFoundException extends BaseException
{
    public function __construct(
        string $message = 'Resource not found',
        string $errorCode = 'NOT_FOUND',
        string $domain = 'app',
        array $context = []
    ) {
        parent::__construct($message, 404, $errorCode, $domain, $context);
    }
}
