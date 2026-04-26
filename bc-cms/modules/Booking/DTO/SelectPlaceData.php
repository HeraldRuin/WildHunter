<?php

namespace Modules\Booking\DTO;

use Illuminate\Http\Request;

class SelectPlaceData
{
    public function __construct(
        public int $roomId,
        public int $placeNumber,
        public int $roomIndex,
        public int $userId,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            roomId: (int) $request->input('room_id'),
            placeNumber: (int) $request->input('place_number'),
            roomIndex: (int) $request->input('room_index'),
            userId: auth()->id(),
        );
    }
}
