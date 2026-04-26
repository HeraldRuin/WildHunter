<?php

namespace App\Exceptions;

class ConflictException extends ApiException
{
    public function __construct(
        string $message = 'Conflict',
        string $errorCode = 'CONFLICT',
        string $domain = 'app',
        array $context = []
    ) {
        parent::__construct($message, 409, $errorCode,  $domain, $context);
    }
}
