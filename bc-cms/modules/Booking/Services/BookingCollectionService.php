<?php

namespace Modules\Booking\Services;

use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\BookingRoomPlace;

class BookingCollectionService
{
    protected $bookingTimerService;

    public function __construct(BookingTimerService $bookingTimerService)
    {
        $this->bookingTimerService = $bookingTimerService;
    }
    public function checkPrepaymentAllPaid(Booking $booking, ?BookingHunterInvitation $invitation = null): void
    {
        $unpaidHunters = $booking->unpaidInvitationsOfHunters();

        if ($unpaidHunters->isEmpty()) {
            $this->bookingTimerService->startBedTimer($booking);
        }else {
            $invitation->prepayment_paid_status = BookingHunterInvitation::PREPAYMENT_PENDING;
            $invitation->save();
            $this->bookingTimerService->startPaidTimer($booking);
        }
    }

    public function updateStatusIfAllPlacesSelected(Booking $booking): void
    {
        $paidCount = $booking->countAcceptedAndPaidHunters();
        $alreadyHasPlace = BookingRoomPlace::where('booking_id', $booking->id)->count() === $paidCount;

        if ($paidCount > 0 && $paidCount === $alreadyHasPlace) {
            $booking->status = Booking::FINISHED_BED;
            $booking->save();
        }
    }

    public function markAllPendingAsUnpaid(Booking $booking): void
    {
        $unpaidHunters = $booking->pendingInvitationsOfHunters();

        if ($unpaidHunters->isNotEmpty()) {
            $unpaidHunters->each(function (BookingHunterInvitation $invitation) {
                $invitation->prepayment_paid_status = BookingHunterInvitation::PREPAYMENT_UNPAID;
                $invitation->save();
            });
        }
    }
}
