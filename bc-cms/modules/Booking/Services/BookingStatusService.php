<?php

namespace Modules\Booking\Services;

use Modules\Booking\Models\Booking;

class BookingStatusService
{
    public function getAllowedStatuses(string $role): array
    {
        $allStatuses = config('booking.statuses');

        $excludedByRole = [
            'hunter' => [
                Booking::COMPLETED,
                Booking::PROCESSING,
                Booking::CONFIRMED,
                Booking::CANCELLED,
                Booking::UNPAID,
                Booking::PAID,
                Booking::PARTIAL_PAYMENT,
                Booking::START_COLLECTION,
                Booking::PREPAYMENT_COLLECTION,
                Booking::BED_COLLECTION,
                Booking::FINISHED_BED,
            ],
            'baseadmin' => [
                Booking::PROCESSING,
                Booking::CONFIRMED,
                Booking::CANCELLED,
                Booking::UNPAID,
                Booking::PAID,
                Booking::PARTIAL_PAYMENT,
                Booking::START_COLLECTION,
                Booking::PREPAYMENT_COLLECTION,
                Booking::BED_COLLECTION,
                Booking::FINISHED_BED,
                Booking::INVITATION,
            ]
        ];

        return array_values(array_filter(
            $allStatuses,
            fn ($status) => !in_array($status, $excludedByRole[$role] ?? [])
        ));
    }

    public function getDropdownStatuses(): array
    {
        return [
            Booking::CANCELLED,
            Booking::PROCESSING,
            Booking::CONFIRMED,
            Booking::START_COLLECTION,
            Booking::FINISHED_COLLECTION,
            Booking::PREPAYMENT_COLLECTION,
            Booking::FINISHED_PREPAYMENT,
            Booking::BED_COLLECTION,
            Booking::FINISHED_BED,
            Booking::PAID,
        ];
    }
}
