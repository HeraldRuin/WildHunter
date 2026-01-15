<?php
namespace Modules\User\Models;

use Modules\Agency\Models\Agency;
use Modules\Agency\Models\AgencyAgent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;

class User extends \App\User
{
    /**
     * @return bool
     */
    public function isMasterHunter()
    {
        if (!$this->hasRole('hunter')) {
            return false;
        }

        return BookingHunter::where('invited_by', $this->id)
            ->where('is_master', true)
            ->exists();
    }

    /**
     * Получает все брони, где пользователь является мастер-охотником
     * (через таблицу bc_booking_hunters)
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getMasterHunterBookings()
    {
        $bookingHunterIds = BookingHunter::where('invited_by', $this->id)
            ->where('is_master', true)
            ->pluck('booking_id');

        return Booking::whereIn('id', $bookingHunterIds);
    }
}
