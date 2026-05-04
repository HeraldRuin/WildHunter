<?php

namespace Modules\Booking\Services;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Auth;
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
        if (is_baseAdmin()) {
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

    /**
     * @throws BusinessException
     */
    public function ensureUserAuthenticated(string $errorCode, string $domain): void
    {
        if (!Auth::user()) {
            throw new BusinessException(
                errorCode: $errorCode,
                domain: $domain
            );
        }
    }
}
