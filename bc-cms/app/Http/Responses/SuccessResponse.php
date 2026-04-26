<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class SuccessResponse extends JsonResponse
{
    public function __construct(
        private readonly string  $message = '',
        private readonly ?string $code = null,
        private readonly ?string $domain = null,
        int                      $status = 200,
        array                    $data = []
    ) {
        parent::__construct(
            $this->buildPayload($data),
            $status
        );
    }

    private function buildPayload(array $data): array
    {
        return array_merge([
            'success' => true,
            'message' => $this->resolveMessage(),
        ], $data);
    }

    private function resolveMessage(): string
    {
        if ($this->code && $this->domain) {
            return __($this->domain . '.successes.' . $this->code);
        }

        return $this->message;
    }
}
