<?php

namespace Modules\Booking\Services\Calculation\Strategies;

use Modules\Booking\Models\BookingRoomPlace;
use Modules\Booking\Services\Calculation\Contracts\BookingCalculationStrategy;
use Modules\Hotel\Models\HotelRoomBooking;

class HuntingCalculationStrategy implements BookingCalculationStrategy
{

    public function calculate($booking, array $data, $user): array
    {
        $services = $data['services'];
        $grouped = $services->groupBy('service_type');

//        if ($data['paidCount'] <= 0) {
//            return [
//                'status' => false,
//                'message' => 'Нет оплативших участников',
//            ];
//        }

        // === Распределение по комнатам ===
        $places = BookingRoomPlace::where('booking_id', $booking->id)->get();
        $rooms = $places->groupBy('room_id');

        $roomPrices = HotelRoomBooking::where('booking_id', $booking->id)
            ->whereIn('room_id', $rooms->keys())
            ->pluck('price', 'room_id');

        $result = [];

        foreach ($rooms as $roomId => $roomPlaces) {
            $totalPlaces = $roomPlaces->count();
            $myPlaces = $roomPlaces->where('user_id', $user->id)->count();

            $roomPrice = $roomPrices[$roomId] ?? 0;
            $roomPriceAllDay = $roomPrice * $booking->duration_days;

            $pricePerPlace = $totalPlaces > 0 ? $roomPriceAllDay / $totalPlaces : 0;
            $myCost = $pricePerPlace * $myPlaces;

            $result[] = [
                'room_id' => $roomId,
                'total_places' => $totalPlaces,
                'my_places' => $myPlaces,
                'price_per_place' => round($pricePerPlace),
                'my_cost' => round($myCost),
            ];
        }

        dd($result);
    }
}
