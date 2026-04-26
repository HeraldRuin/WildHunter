<?php

namespace App\Exceptions;

class ForbiddenException extends BaseException
{
    public function __construct(
        string $message = 'Forbidden',
        string $errorCode = 'FORBIDDEN',
        string $domain = 'app',
        array $context = []
    ) {
        parent::__construct($message, 403, $errorCode,  $domain, $context);
    }
}
