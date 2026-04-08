<?php

namespace Modules\Booking\DTO;

use Illuminate\Http\Request;

class StoreSpendingData
{
    public function __construct(
        public float $price,
        public string $comment,
        public int $hunter_id,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->validated();

        return new self(
            price: (float) $data['price'],
            comment: $data['comment'],
            hunter_id: (int) $data['hunter_id'],
        );
    }
}
