<?php

namespace Modules\Booking\Services\Calculation;

use App\User;
use Illuminate\Support\Collection;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Hotel\Models\HotelRoomBooking;

class BookingCalculator
{
    public function calculateRooms($booking, User $user): array
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
            $roomPriceAllDay = $roomPrices[$roomId] ?? 0;
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

    public function getMyAccommodationCost(Booking $booking, User $user): float
    {
        $rooms = $this->calculateRooms($booking, $user);

        return array_sum(array_column($rooms, 'my_cost'));
    }

    public function calculateTrophies(Collection $trophies, int $paidCount): array
    {
        $result = [];

        foreach ($trophies as $trophy) {
            $animalName = $trophy->animal?->title ?? '';

            $result[] = [
                'name' => $animalName . ' (' . $trophy->type . ' x ' . $trophy->count . 'шт)',
                'total_cost' => round((float)$trophy->price),
                'my_cost' => $paidCount > 0 ? round($trophy->price / $paidCount): 0,
            ];
        }

        return $result;
    }

    public function calculateMeals(Collection $meals, int $paidCount, Booking $booking): array
    {
        $result = [];

        foreach ($meals as $food) {
            $totalCost = round(
                $food->price * $booking->duration_days
            );

            $result[] = [
                'name' => $food->type,
                'total_cost' => $totalCost,
                'my_cost' => $paidCount > 0 ? round($totalCost / $paidCount): 0,
            ];
        }

        return $result;
    }
    public function calculatePenalties(Collection $penalties, User $user): array
    {
        $result = [];

        $groupedByAnimalType = $penalties->groupBy(fn($item) => $item->animal_id . '|' . $item->type);

        foreach ($groupedByAnimalType as $items) {
            $first = $items->first();

            $totalCost = $items->sum('price');

            $myCost = $items
                ->where('hunter_id', $user->id)
                ->sum('price');

            $result[] = [
                'name' => $first->type,
                'total_cost' => round($totalCost),
                'my_cost' => round($myCost),
            ];
        }

        return $result;
    }

    public function calculatePreparations(Collection $preparations, int $paidCount): array
    {
        $result = [];

        foreach ($preparations as $preparation) {
            $animalName = $preparation->animal?->title ?? '';
            $totalCost = round($preparation->price);

            $result[] = [
                'name' => 'Разделка' . ' (' . $animalName . ' x ' . $preparation->count . 'шт)',
                'total_cost' => $totalCost,
                'my_cost' => $paidCount > 0 ? round($preparation->price / $paidCount): 0,
            ];
        }

        return $result;
    }

    public function calculateAdditional(Collection $additional, int $paidCount): array
    {
        $result = [];

        foreach ($additional as $item) {
            $totalCost = round($item->price);

            $result[] = [
                'name' => $item->type,
                'total_cost' => $totalCost,
                'my_cost' => round($item->price / max(1, $paidCount)),
            ];
        }

        return $result;
    }

    public function calculateSpendings(Collection $spendings, User $user, int $paidCount): array
    {
        $result = [];
        $totalMyDebt = 0;
        $totalSpending = 0;

        foreach ($spendings as $spending) {
            $hunter = $spending->hunter;
            $isMe = $spending->hunter_id === $user->id;

            $myCost = $spending->hunter_id === $user->id ? 0 : round($spending->price / max(1, $paidCount));
            $totalMyDebt += $myCost;
            $totalSpending += $spending->price;

            $name = $isMe ? (($hunter->last_name ?? '—') . ' (это я) ' .' (' . ($spending->comment ?? '') . ')') : (($hunter->last_name ?? '—') . ' (' . ($spending->comment ?? '') . ')');

            $result[] = [
                'name' => $name,
                'total_cost' => round($spending->price),
                'my_cost' => $myCost,
            ];
        }

        return [
            'items' => $result,
            'total_my_debt' => $totalMyDebt,
            'total_spending' => round($totalSpending),
        ];
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
