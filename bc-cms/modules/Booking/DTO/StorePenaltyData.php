<?php

namespace Modules\Booking\DTO;

use Illuminate\Http\Request;

class StorePenaltyData
{
    public function __construct(
        public int $penalty_id,
        public int $hunter_id,
        public int $animal_id,
        public string $type,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            penalty_id: (int) $data['penalty_id'],
            hunter_id: (int) $data['hunter_id'],
            animal_id: (int) $data['animal_id'],
            type: $data['type'],
        );
    }
}
