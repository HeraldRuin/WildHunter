<?php

namespace Modules\Booking\Services;

use Modules\Booking\Models\Booking;
use App\Exceptions\ForbiddenException;
use Illuminate\Contracts\Auth\Authenticatable;

class BookingAccessService
{
    /**
     * @throws ForbiddenException
     */
    public function ensureCanAccessBooking(Booking $booking, Authenticatable $user): void
    {
        $isBaseAdmin = is_baseAdmin();

        if ($isBaseAdmin || $user->hasPermission('dashboard_vendor_access')) {
            return;
        }

        if ($booking->customer_id == $user->id || $booking->create_user == $user->id) {
            return;
        }

        throw new ForbiddenException(
            errorCode: 'booking_access_denied',
            domain: 'booking'
        );
    }
}
