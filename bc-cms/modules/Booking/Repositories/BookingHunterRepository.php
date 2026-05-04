<?php

namespace Modules\Booking\Repositories;

use Modules\Booking\Models\BookingHunter;

class BookingHunterRepository
{
    public function save(BookingHunter $hunter): void
    {
        $hunter->save();
    }
}
