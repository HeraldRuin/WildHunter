<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class ErrorResponse
 *
 */
final class SuccessResponse extends JsonResponse
{
    /**
     * @var string
     *
     */
    protected string $message;

    public function __construct(string $message = '', int $status = 200)
    {
        parent::__construct([
                'success' => true,
                'error' => null,
                'message' => $message,
            ], $status);
    }
}
