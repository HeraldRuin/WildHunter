<?php

namespace Modules\Booking\DTO;

use Illuminate\Http\Request;

class StoreFoodData
{
    public function __construct(
        public int $count,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            count: (int) $data['count'],
        );
    }
}
