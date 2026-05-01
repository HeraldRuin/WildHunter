<?php

namespace App\Http\Responses;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

final class SuccessResponse extends JsonResponse
{
    public function __construct(
        private readonly string  $message = '',
        private readonly ?string $code = null,
        private readonly ?string $domain = null,
        int                      $status = 200,
        protected mixed $data = [],
    ) {
        parent::__construct([
            'success' => true,
            'message' => $this->resolveMessage(),
            'data' => $this->normalize($data),
        ], $status);
    }

    private function normalize(mixed $data): mixed
    {
        if ($data instanceof Collection) {
            return $data->values()->toArray();
        }

        if ($data instanceof Model) {
            return $data->toArray();
        }

        return $data;
    }

    private function resolveMessage(): string
    {
        if ($this->code && $this->domain) {
            return __($this->domain . '.successes.' . $this->code);
        }

        return $this->message;
    }
}
