<?php

namespace Modules\Booking\Services\Calculation;

use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;

class BookingDataBuilder
{
    public function build(Booking $booking): array
    {
        $services = BookingService::with(['hunter', 'animal'])->where('booking_id', $booking->id)->get();

        $paidCount = $booking->countAcceptedAndPaidHunters();
        $totalHunting = $booking->total_hunting;

        return [
            'booking' => $booking,
            'services' => $services,
            'paidCount' => $paidCount,
            'totalHunting' => $totalHunting,
        ];
    }
}
