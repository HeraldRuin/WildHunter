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

        return [
            'booking' => $booking,
            'services' => $services,
            'paidCount' => $paidCount,
        ];
    }
}
