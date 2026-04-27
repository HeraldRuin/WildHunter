<?php

namespace App\Exceptions;

class ValidationException extends BaseException
{
    public function __construct(
        string $message = 'Validation error',
        string $errorCode = 'VALIDATION_ERROR',
        string $domain = 'app',
        array $context = []
    ) {
        parent::__construct($message, 422, $errorCode, $domain, $context);
    }
}
