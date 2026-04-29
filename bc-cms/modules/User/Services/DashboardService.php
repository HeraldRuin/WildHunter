<?php

namespace Modules\User\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Booking\Models\Booking;

class DashboardService
{
    public function getBaseAdminData($booking, Authenticatable $user): array
    {
        $hotelId = $user->hotels->first()->id;

        return [
            'recent_bookings'    => Booking::getRecentBookings(hotel_id: $hotelId),
            'top_cards'          => Booking::getTopCardsReport(hotel_id: $hotelId),
            'cards_report'       => $booking->getTopCardsReportForBaseAdmin($user->id),
            'earning_chart_data' => $booking->getEarningChartDataForVendor(strtotime('monday this week'), time(), $user->id),
        ];
    }

    public function getBaseHunterData($booking, $user): array
    {
        return [
            'cards_report'       => $booking->getTopCardsReportForVendor($user->id),
            'earning_chart_data' => $booking->getEarningChartDataForVendor(strtotime('monday this week'), time(), $user->id),
        ];
    }
}
