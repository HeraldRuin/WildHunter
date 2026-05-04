<?php

namespace Modules\Booking\Repositories;

use Modules\Booking\Models\Booking;

class BookingRepository
{
    public function save(Booking $booking): void
    {
        $booking->save();
    }
}
