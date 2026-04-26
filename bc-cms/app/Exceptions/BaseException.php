<?php

namespace App\Exceptions;

use Exception;

class BaseException extends Exception
{
    protected int $status;
    protected string $errorCode;
    protected string $domain;
    protected array $context;

    public function __construct(
        string $message = 'Server error',
        int $status = 400,
        string $errorCode = 'API_ERROR',
        string $domain = 'app',
        array $context = []
    ) {
        parent::__construct($message);

        $this->status = $status;
        $this->errorCode = $errorCode;
        $this->domain = $domain;
        $this->context = $context;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
