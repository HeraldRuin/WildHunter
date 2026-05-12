<?php

namespace Modules\Attendance\DTO;

use Illuminate\Http\Request;

class StoreAdditionalData
{
    public function __construct(
        public string $name,
        public int $price,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: (string) $data['name'],
            price: (int) $data['price'],
        );
    }
}
