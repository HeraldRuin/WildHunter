<?php

namespace Modules\Booking\Services\Calculation;

use Modules\Animals\Models\Animal;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Hotel\Models\HotelRoomBooking;

class BookingCalculator
{
    public function calculateRooms($booking, $user): array
    {
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
        return $result;
    }

    public function calculateTrophies($grouped, $paidCount)
    {
        if ($grouped->has('trophy')) {
            foreach ($grouped['trophy'] as $trophy) {
                $animalName = optional(Animal::find($trophy->animal))->title ?? '';
                $trophies[] = [
                    'name' => $animalName . '  (' . $trophy->type . '  x ' . $trophy->count . 'шт)',
                    'total_cost' => round((float)$trophy->price),
                    'my_cost' => round($trophy->price / $paidCount),
                ];
            }
        }
    }
    public function calculateBaseTotal(Booking $booking, $services, int $paidCount): float
    {
        $grouped = $services->groupBy('service_type');

        $trophiesTotal = $grouped->get('trophy', collect())->sum('price');
        $penaltiesTotal = $grouped->get('penalty', collect())->sum('price');
        $addetionalsTotal = $grouped->get('addetional', collect())->sum('price');
        $preparationTotal = $grouped->get('preparation', collect())->sum('price');
        $mealsTotal = $grouped->get('food', collect())->sum('price');

        $huntingAmountPaid = $this->calculateHuntingAmountPaid($booking, $paidCount);

        $baseAmount = $booking->type === Booking::BookingTypeAnimal
            ? (
                $huntingAmountPaid +
                $trophiesTotal +
                $penaltiesTotal +
                $addetionalsTotal +
                $preparationTotal +
                $mealsTotal
            )
            : (
                $booking->total +
                $huntingAmountPaid +
                $trophiesTotal +
                $penaltiesTotal +
                $addetionalsTotal +
                $preparationTotal +
                $mealsTotal
            );

        return $baseAmount - $booking->total;
    }

    public function calculateHuntingAmountPaid(Booking $booking, int $paidCount): float
    {
        if (!$booking->total_hunting || $paidCount <= 0) {
            return 0;
        }

        return round(($booking->amount_hunting / $booking->total_hunting) * $paidCount);
    }

}
