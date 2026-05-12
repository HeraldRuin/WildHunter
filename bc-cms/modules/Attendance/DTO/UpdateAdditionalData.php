<?php

namespace Modules\Attendance\DTO;

use Illuminate\Http\Request;

class UpdateAdditionalData
{
    public function __construct(
        public string $name,
        public int $price,
        public int $count,
        public string $calculation_type,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            name: (string) $data['name'],
            price: (int) $data['price'],
            count: (int) $data['count'],
            calculation_type: (string) $data['calculation_type'],
        );
    }
}
