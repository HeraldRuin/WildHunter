<?php

namespace Modules\Booking\DTO;

use Illuminate\Http\Request;

class StoreTrophyData
{
    public function __construct(
        public int $trophy_id,
        public int $animal_id,
        public string $type,
        public int $count,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            trophy_id: (int) $data['trophy_id'],
            animal_id: (int) $data['animal_id'],
            type: $data['type'],
            count: (int) $data['count'],
        );
    }
}
