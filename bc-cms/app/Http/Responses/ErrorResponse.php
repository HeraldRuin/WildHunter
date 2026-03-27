<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class ErrorResponse
 *
 */
final class ErrorResponse extends JsonResponse
{
    /**
     * @var string
     *
     */
    protected string $message;

    public function __construct(string $message, int $status = 400)
    {
        parent::__construct([
                'success' => false,
                'error' => $message,
            ], $status);
    }
}
