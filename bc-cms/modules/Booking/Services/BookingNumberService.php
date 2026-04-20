<?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\BookingCounter;

class BookingNumberService
{
    public function generate(int $hotelId ): int
    {
        return DB::transaction(function () use ($hotelId) {

            $counter = BookingCounter::where('hotel_id', $hotelId)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                throw new \Exception("Counter for hotel {$hotelId} not found");
            }

            $counter->increment('last_number');

            return $counter->last_number;

        });
    }
}
