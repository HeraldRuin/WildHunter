<?php

namespace Modules\Hotel\DTO;

use Modules\Hotel\Requests\LoadDatesRequest;

class RoomCalendarData
{
    public function __construct(
        public int|string $id,
        public string $start,
        public string $end,
        public bool $forSingle = false
    ) {}

    public static function fromRequest(LoadDatesRequest $request): self
    {
        return new self(
            id: $request->validated('id'),
            start: $request->validated('start'),
            end: $request->validated('end'),
            forSingle: $request->boolean('for_single')
        );
    }
}
