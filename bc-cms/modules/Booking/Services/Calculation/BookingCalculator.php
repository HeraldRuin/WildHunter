<?php

namespace Modules\Booking\Services\Calculation;

use App\User;
use Illuminate\Support\Collection;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Hotel\Models\HotelRoomBooking;

class BookingCalculator
{
    public function getServiceCount(Booking $booking, $services, array $allowedTypes): array
    {
        $grouped = $services->groupBy('service_type');

        $totals = collect();

        foreach ($allowedTypes as $type) {

            $items = $grouped->get($type, collect());

            $sum = $items->sum(function ($item) use ($booking, $type) {

                if ($type === 'food') {
                    return $item->price * max(1, $booking->duration_days);
                }

                return $item->price;
            });

            $totals->put($type, round($sum));
        }

        return [
            'trophy' => $totals->get('trophy', 0),
            'penalty' => $totals->get('penalty', 0),
            'food' => $totals->get('food', 0),
            'preparation' => $totals->get('preparation', 0),
            'additional' => $totals->get('addetional', 0),

            'total' => $totals->sum(),
        ];
    }
    public function getServiceMyCount(Booking $booking, $user, $huntersCount, $services, array $allowedTypes): array
    {
        $grouped = $services->groupBy('service_type');

        $totals = collect();

        foreach ($allowedTypes as $type) {

            $items = $grouped->get($type, collect());

            $sum = $items->sum(function ($item) use ($booking, $type, $user, $huntersCount) {

                if ($type === 'food') {

                    return $item->price * max(1, $booking->duration_days) / $huntersCount;
                }
                if ($type === 'addetional') {
                    if ($item->hunter_id !== $user->id) {
                        return 0;
                    }
                    return $item->price;
                }
                if ($type === 'penalty') {
                    if ($item->hunter_id !== $user->id) {
                        return 0;
                    }
                    return $item->price;
                }

                return $item->price / $huntersCount;
            });

            $totals->put($type, round($sum));
        }

        return [
            'trophy' => $totals->get('trophy', 0),
            'penalty' => $totals->get('penalty', 0),
            'food' => $totals->get('food', 0),
            'preparation' => $totals->get('preparation', 0),
            'additional' => $totals->get('addetional', 0),

            'total' => $totals->sum(),
        ];
    }
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

    public function getAccommodationCost(Booking $booking): float
    {
        return round($booking->total);
    }
    public function getMyAccommodationCost(Booking $booking, User $user): float
    {
        $rooms = $this->calculateRooms($booking, $user);

        return array_sum(array_column($rooms, 'my_cost'));
    }

    public function calculateTrophies(Collection $trophies, int $huntersCount): array
    {
        $result = [];

        foreach ($trophies as $trophy) {
            $animalName = $trophy->animal?->title ?? '';

            $result[] = [
                'name' => $animalName . ' (' . $trophy->type . ' x ' . $trophy->count . 'шт)',
                'total_cost' => round((float)$trophy->price),
                'my_cost' => $huntersCount > 0 ? round($trophy->price / $huntersCount): 0,
            ];
        }

        return $result;
    }

    public function calculateMeals(Collection $meals, int $huntersCount, Booking $booking): array
    {
        $result = [];

        foreach ($meals as $food) {
            $totalCost = round(
                $food->price * $booking->duration_days
            );

            $result[] = [
                'name' => $food->type,
                'total_cost' => $totalCost,
                'my_cost' => $huntersCount > 0 ? round($totalCost / $huntersCount): 0,
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
            $animalName = $first->animal?->title ?? '';
            $totalCost = $items->sum('price');

            $myCost = $items
                ->where('hunter_id', $user->id)
                ->sum('price');

            $result[] = [
                'name' => $first->type . ' (' . $animalName . ')',
                'total_cost' => round($totalCost),
                'my_cost' => round($myCost),
            ];
        }

        return $result;
    }

    public function calculatePreparations(Collection $preparations, int $huntersCount): array
    {
        $result = [];

        foreach ($preparations as $preparation) {
            $animalName = $preparation->animal?->title ?? '';
            $totalCost = round($preparation->price);

            $result[] = [
                'name' => 'Разделка' . ' (' . $animalName . ' x ' . $preparation->count . 'шт)',
                'total_cost' => $totalCost,
                'my_cost' => $huntersCount > 0 ? round($preparation->price / $huntersCount): 0,
            ];
        }

        return $result;
    }

    public function calculateAdditional(Collection $additional, User $user): array
    {
        $result = [];

        foreach ($additional as $item) {
            $isMe = $item->hunter_id === $user->id;
            $totalCost = round($item->price);
            $myCost = $isMe ? round($totalCost): 0;

            $result[] = [
                'name' => $item->type,
                'total_cost' => $totalCost,
                'my_cost' => $myCost,
            ];
        }

        return $result;
    }

    public function calculateSpendings(Collection $spendings, User $user, int $huntersCount): array
    {
        $result = [];
        $totalMyDebt = 0;
        $totalSpending = 0;

        foreach ($spendings as $spending) {
            $hunter = $spending->hunter;

            $myCost = $spending->hunter_id === $user->id ? 0 : round($spending->price / max(1, $huntersCount));
            $totalMyDebt += $myCost;
            $totalSpending += $spending->price;

            $name = (($hunter->last_name ?? '—') . ' (' . ($spending->comment ?? '') . ')');

            $result[] = [
                'name' => $name,
                'total_cost' => round($spending->price),
                'my_cost' => $myCost,
            ];
        }

        return [
            'items' => $result,
            'total_spending' => round($totalSpending),
            'total_my_debt' => $totalMyDebt,
        ];
    }

    public function getSpendings(Collection $spendings, User $user, int $huntersCount): array
    {
        $data = $this->calculateSpendings($spendings, $user, $huntersCount);

        return [
            'title_name' => 'Итог охотникам',
            'items' => $data['items'],
            'total_cost' => $data['total_spending'],
            'my_cost' => $data['total_my_debt'],
        ];
    }

    public function calculateBaseTotal(Booking $booking, $services, int $huntersCount): float
    {
        $result = $this->getServiceCount($booking, $services, ['trophy', 'penalty', 'food', 'preparation', 'addetional']);
        $organisationHuntingPaid = $this->calculateOrganisationHunting($booking, $huntersCount);
//TODO тут нужно будет убрать тип животного
        $baseAmount = $booking->type === Booking::BookingTypeAnimal ? ($organisationHuntingPaid + $result['total']) :
            (
                $booking->total + $organisationHuntingPaid + $result['total']
            );

        return $organisationHuntingPaid + $result['total'];
    }
    public function calculateMyBaseTotal(Booking $booking, User $user, $services, int $huntersCount): float
    {
        $myAccommodationCost = $this->getMyAccommodationCost($booking, $user);
        $myOrganizationCost = $this->calculateMyOrganisationHunting($booking, $huntersCount);
        $result = $this->getServiceCount($booking, $services, ['trophy', 'penalty', 'food', 'preparation', 'addetional']);
        $myPrepaymentMade = $this->myPrepaymentMade($booking->total, $huntersCount);

        return ($myAccommodationCost + $myOrganizationCost + $result['total']) - $myPrepaymentMade;
    }
    public function calculateMyBaseTotalOnlyHunting(Booking $booking, User $user, $services, int $huntersCount): float
    {
        $myOrganizationCost = $this->calculateMyOrganisationHunting($booking, $huntersCount);
        $result = $this->getServiceMyCount($booking, $user, $huntersCount, $services, ['trophy', 'penalty', 'food', 'preparation', 'addetional']);

        return ($myOrganizationCost + $result['total']);
    }
    public function getAccommodation(Booking $booking, User $user): array
    {
        $isAnimal = $booking->type === Booking::BookingTypeAnimal;

        if ($isAnimal) {
            return [
                'title_name' => 'Проживание, ' . plural_sutki(0),
                'total_cost' => 0,
                'my_cost' => 0,
            ];
        }

        return [
            'title_name' => 'Проживание, ' . plural_sutki($booking->duration_days),
            'total_cost' => $this->getAccommodationCost($booking),
            'my_cost' => $this->getMyAccommodationCost($booking, $user),
        ];
    }
    public function calculateOrganisationHunting(Booking $booking, int $huntersCount): float
    {
        if (!$booking->total_hunting || $huntersCount <= 0) {
            return 0;
        }

        return round(($booking->amount_hunting / $booking->total_hunting) * $huntersCount);
    }
    public function calculateMyOrganisationHunting(Booking $booking, int $huntersCount): float
    {
        return $this->calculateOrganisationHunting($booking, $huntersCount) / $huntersCount;
    }

    public function getOrganisationHunting(Booking $booking, int $huntersCount): array
    {
        return [
            'title_name' => 'Организация охоты',
            'total_cost' => $this->calculateOrganisationHunting($booking, $huntersCount),
            'my_cost' => $this->calculateMyOrganisationHunting($booking, $huntersCount),
        ];
    }
    public function getPrepaymentMade(Booking $booking, int $huntersCount): array
    {
        if ($booking->type === Booking::BookingTypeAnimal) {
            return [
                'total_cost' => 0,
                'my_cost' => 0,
            ];
        }

        return [
            'title_name' => 'Внесена предоплата:',
            'total_cost' => $this->basePrepaymentMade($booking),
            'my_cost' => $this->myPrepaymentMade($booking->total, $huntersCount),
        ];
    }
    public function basePrepaymentMade(Booking $booking): float
    {
        return round($booking->total);
    }
    public function myPrepaymentMade($bookingTotal, int $huntersCount): float
    {
        return round($bookingTotal / $huntersCount);
    }
    public function getBalanceBase(Booking $booking, User $user, $services, int $huntersCount): array
    {
        return [
            'title_name' =>  'Остаток базе',
            'total_cost' => $this->calculateBaseTotal($booking, $services, $huntersCount),
            'my_cost' => $this->calculateMyBaseTotal($booking, $user, $services, $huntersCount),
        ];
    }
    public function getBalanceBaseHunting(Booking $booking, User $user, $services, int $huntersCount): array
    {
        return [
            'title_name' =>  'Остаток базе',
            'total_cost' => $this->calculateBaseTotal($booking, $services, $huntersCount),
            'my_cost' => $this->calculateMyBaseTotalOnlyHunting($booking, $user, $services, $huntersCount),
        ];
    }

    //Подсчет в историю бронирования в колонку оплата (админа базы)
    public function getBookingTotal(Booking $booking, $services, int $huntersCount): array
    {
        if ($booking->type === Booking::BookingTypeAnimal) {
            return [
                'prepaid_total' => 0,
                'base_total' => 0,
                'total' => 0,
            ];
        }

        if ($booking->type === Booking::BookingTypeHotel) {
            return [
                'prepaid_total' => $this->basePrepaymentMade($booking),
                'base_total' => $this->calculateBaseTotal($booking, $services, $huntersCount),
                'total' => $this->basePrepaymentMade($booking) + $this->calculateBaseTotal($booking, $services, $huntersCount),
            ];
        }

        if ($booking->type === Booking::BookingTypeHotelAnimal) {
            return [
                'prepaid_total' => $this->basePrepaymentMade($booking),
                'base_total' => $this->calculateBaseTotal($booking, $services, $huntersCount),
                'total' => $this->basePrepaymentMade($booking) + $this->calculateBaseTotal($booking, $services, $huntersCount),
            ];
        }

        return [
            'prepaid_total' => 0,
            'base_total' => 0,
            'total' => 0,
        ];
    }
}
