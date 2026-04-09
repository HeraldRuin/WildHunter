<?php

namespace Modules\Booking\DTO;

use Illuminate\Http\Request;

class StoreAddetionalData
{
    public function __construct(
        public int $addetional_id,
        public string $addetional,
        public int $count,
        public int $hunter_id,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            addetional_id: (int) $data['addetional_id'],
            addetional: $data['addetional'],
            count: (int) $data['count'],
            hunter_id: (int) $data['hunter_id'],
        );
    }
}
