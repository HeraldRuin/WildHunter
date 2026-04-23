<?php

namespace Modules\Booking\Services;

use App\User;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;

class BookingUserService
{
    public function changeUser(Booking $booking, int $userId): void
    {
        $booking->create_user = $userId;
        $booking->save();

        if (!$userId) {
            return;
        }

        $user = User::find($userId);

        if (!$user) {
            return;
        }

        $bookingHunter = BookingHunter::where('booking_id', $booking->id)->first();

        if (!$bookingHunter) {
            return;
        }

        $bookingHunter->invited_by = $userId;
        $bookingHunter->is_master = $user->hasRole('hunter');
        $bookingHunter->creator_role = $user->role->code ?? null;
        $bookingHunter->save();
    }
}
