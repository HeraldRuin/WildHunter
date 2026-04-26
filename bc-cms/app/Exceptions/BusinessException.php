<?php

namespace App\Exceptions;

class BusinessException extends BaseException
{
    public function __construct(
        string $message = 'Business logic error',
        string $errorCode = 'BUSINESS_ERROR',
        string $domain = 'app',
        array $context = [],
        int $status = 400
    ) {
        parent::__construct($message, $status, $errorCode, $domain, $context);
    }
}
