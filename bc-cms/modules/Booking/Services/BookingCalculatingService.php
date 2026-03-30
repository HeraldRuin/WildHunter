<?php

namespace Modules\Booking\Services;

use Modules\Animals\Models\Animal;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\BookingRoomPlace;
use Modules\Booking\Models\BookingService;
use Modules\Hotel\Models\HotelRoomBooking;
use Modules\User\Models\User;

class BookingCalculatingService
{
    public function calculate($booking, $user): array
    {
        $isBaseAdmin = $user->hasRole('baseadmin');
        $masterBookingHunter = $booking->masterHunter();

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

        $totalMyCost = array_sum(array_column($result, 'my_cost'));

        // Обновляем prepayment_paid текущего пользователя
        BookingHunterInvitation::where('booking_hunter_id', $masterBookingHunter->id)
            ->where('hunter_id', $user->id)
            ->update(['prepayment_paid' => true]);

        $paidCount = $booking->countAcceptedAndPaidHunters();

        // === Получаем услуги ===
        $services = BookingService::where('booking_id', $booking->id)->get();
        $grouped = $services->groupBy('service_type');

        $trophies = [];
        $penalties = [];
        $meals = [];
        $preparations = [];
        $spendings = [];
        $addetionals = [];

        // === Трофеи ===
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

        // === Штрафы ===
        if ($grouped->has('penalty')) {
            $groupedByAnimalType = $grouped['penalty']->groupBy(fn($item) => $item->animal_id . '|' . $item->type);
            foreach ($groupedByAnimalType as $items) {
                $first = $items->first();
                $totalCost = $items->sum('price');
                $myCost = $items->where('hunter_id', $user->id)->sum('price');
                $penalties[] = [
                    'name' => $first->type,
                    'total_cost' => round($totalCost),
                    'my_cost' => round($myCost),
                ];
            }
        }

        // === Питание ===
        if ($grouped->has('food')) {
            foreach ($grouped['food'] as $foods) {
                $totalCost = round($foods->price * $paidCount * $booking->duration_days);
                $meals[] = [
                    'name' => $foods->type,
                    'total_cost' => $totalCost,
                    'my_cost' => round($totalCost / $paidCount),
                ];
            }
        }

        // === Разделка ===
        if ($grouped->has('preparation')) {
            foreach ($grouped['preparation'] as $preparation) {
                $preparations[] = [
                    'name' => 'Разделка',
                    'total_cost' => round($preparation->price),
                    'my_cost' => round($preparation->price / $paidCount),
                ];
            }
        }

        // === Дополнительные услуги ===
        if ($grouped->has('addetional')) {
            foreach ($grouped['addetional'] as $addetional) {
                $addetionals[] = [
                    'name' => $addetional->type,
                    'total_cost' => round($addetional->price),
                    'my_cost' => round($addetional->price / max(1, $paidCount)),
                ];
            }
        }

        // === Расходы охотников ===
        $totalMyDebt = 0;
        $totalSpending = 0;
        if ($grouped->has('spending')) {
            foreach ($grouped['spending'] as $spending) {
                $hunter = User::find($spending->hunter_id);
                $myCost = $spending->hunter_id === $user->id ? 0 : round($spending->price / $paidCount);

                $totalMyDebt += $myCost;
                $totalSpending += $spending->price;

                $spendings[] = [
                    'name' => ($hunter->last_name ?? '—') . ' (' . ($spending->comment ?? '') . ')',
                    'total_cost' => round($spending->price),
                    'my_cost' => $myCost,
                ];
            }
        }

        // === Подсчёты итогов ===
        $trophiesTotal = array_sum(array_column($trophies, 'total_cost'));
        $penaltiesTotal = array_sum(array_column($penalties, 'total_cost'));
        $addetionalsTotal = array_sum(array_column($addetionals, 'total_cost'));
        $preparationTotal = array_sum(array_column($preparations, 'total_cost'));
        $mealsTotal = array_sum(array_column($meals, 'total_cost'));

        $trophiesMyTotal = array_sum(array_column($trophies, 'my_cost'));
        $penaltiesMyTotal = array_sum(array_column($penalties, 'my_cost'));
        $addetionalsMyTotal = array_sum(array_column($addetionals, 'my_cost'));
        $preparationMyTotal = array_sum(array_column($preparations, 'my_cost'));
        $mealsMyTotal = array_sum(array_column($meals, 'my_cost'));

        $huntingAmountPaid = ($booking->amount_hunting / $booking->total_hunting) * $paidCount;

        $baseAmount = $booking->type === Booking::BookingTypeAnimal
            ? round(
                round($huntingAmountPaid) + $trophiesTotal + $penaltiesTotal + $addetionalsTotal + $preparationTotal + $mealsTotal
            )
            : round(
                $booking->total + round($huntingAmountPaid) + $trophiesTotal + $penaltiesTotal + $addetionalsTotal + $preparationTotal + $mealsTotal
            );

        $baseTotal = $baseAmount - $booking->total;
        $huntingAmountMyPaid = round($huntingAmountPaid / $paidCount);
        $baseMyAmount = round($trophiesMyTotal + $penaltiesMyTotal + $addetionalsMyTotal + $preparationMyTotal + $mealsMyTotal);
        $myPrepayment = round($booking->total / $paidCount);
        $baseMyTotalCost = round(($totalMyCost + $huntingAmountMyPaid + $baseMyAmount) - $myPrepayment);

        // === Формируем итоговые массивы ===
        $allItems = [
            [
                'name' => 'Внесена предоплата:',
                'total_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : round($booking->total),
                'my_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : $myPrepayment,
            ],
            [
                'name' => 'Итог базе',
                'total_cost' => $baseTotal,
                'my_cost' => $baseMyTotalCost,
            ],
        ];

        if (!$isBaseAdmin) {
            $allItems[] = [
                'name' => 'Итог охотникам',
                'total_cost' => round($totalSpending),
                'my_cost' => round($totalMyDebt),
            ];
        }

        return [
            'status' => true,
            'is_baseAdmin' => $isBaseAdmin,
            'items' => [
                [
                    'name' => $booking->type === Booking::BookingTypeAnimal
                        ? 'Проживание, ' . plural_sutki(0)
                        : 'Проживание, ' . plural_sutki($booking->duration_days),
                    'total_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : round($booking->total),
                    'my_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : $totalMyCost,
                ],
                [
                    'name' => 'Организация охоты',
                    'total_cost' => round($huntingAmountPaid),
                    'my_cost' => $huntingAmountMyPaid,
                    'has_tooltip' => true,
                ],
            ],
            'trophies' => $trophies,
            'penalties' => $penalties,
            'meals' => $meals,
            'preparation' => $preparations,
            'addetionals' => $addetionals,
            'spendings' => $spendings,
            'all_items' => $allItems,
        ];
    }
}
