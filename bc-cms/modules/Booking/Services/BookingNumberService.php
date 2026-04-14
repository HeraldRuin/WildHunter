<?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\BookingCounter;

class BookingNumberService
{
    public function generate(string $type): int
    {
        return DB::transaction(function () use ($type) {

            $counter = BookingCounter::where('type', $type)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                throw new \Exception("Counter for type {$type} not found");
            }

            $counter->increment('last_number');

            return $counter->last_number;

        });
    }
}
