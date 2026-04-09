<?php

namespace Modules\Booking\DTO;

use Illuminate\Http\Request;

class StorePreparationData
{
    public function __construct(
        public int $preparation_id,
        public int $animal_id,
        public int $count,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            preparation_id: (int) $data['preparation_id'],
            animal_id: (int) $data['animal_id'],
            count: (int) $data['count'],
        );
    }
}
