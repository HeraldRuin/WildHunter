<?php

namespace Modules\Animals\DTO;

use Illuminate\Http\Request;

readonly class AnimalPricePeriodUpdateDTO
{
    public function __construct(
        public string $startDate,
        public string $endDate,
        public float  $price,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            price: (float) $request->input('amount'),
        );
    }
}
