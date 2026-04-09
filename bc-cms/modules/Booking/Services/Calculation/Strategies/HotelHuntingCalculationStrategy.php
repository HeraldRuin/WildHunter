<?php

namespace Modules\Booking\Services\Calculation\Strategies;

use Modules\User\Models\User;
use Modules\Animals\Models\Animal;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\Calculation\BookingCalculator;
use Modules\Booking\Services\Calculation\Contracts\BookingCalculationStrategy;

class HotelHuntingCalculationStrategy implements BookingCalculationStrategy
{
    public function __construct(protected BookingCalculator $bookingCalculator){}
    public function calculate($booking, array $data, $user): array
    {
        $services = $data['services'];
        $grouped = $services->groupBy('service_type');
        $paidCount = $data['paidCount'];

        if ($data['paidCount'] <= 0) {
            return [
                'status' => false,
                'message' => 'Нет оплативших участников',
            ];
        }

        $myAccommodationCost = $this->bookingCalculator->getMyAccommodationCost($booking, $user);

        // === Трофеи ===
        $trophies = $this->bookingCalculator->calculateTrophies(collect($grouped['trophy'] ?? []), $paidCount);

        // === Штрафы ===
        $penalties = $this->bookingCalculator->calculatePenalties(collect($grouped['penalty'] ?? []), $user);

        // === Питание ===
        $meals = $this->bookingCalculator->calculateMeals(collect($grouped['food'] ?? []), $paidCount, $booking);

        // === Разделка ===
        $preparations = $this->bookingCalculator->calculatePreparations(collect($grouped['preparation'] ?? []), $paidCount);

        // === Дополнительные услуги ===
        $addetionals = $this->bookingCalculator->calculateAdditional(collect($grouped['addetional'] ?? []), $paidCount);

        // === Расходы охотников ===
        $data = $this->bookingCalculator->calculateSpendings(collect($grouped['spending'] ?? []), $user, $paidCount);
        $spendings = $data['items'];
        $totalMyDebt = $data['total_my_debt'];
        $totalSpending = $data['total_spending'];


        // === Подсчёты итогов ===
        $trophiesMyTotal = array_sum(array_column($trophies, 'my_cost'));
        $penaltiesMyTotal = array_sum(array_column($penalties, 'my_cost'));
        $addetionalsMyTotal = array_sum(array_column($addetionals, 'my_cost'));
        $preparationMyTotal = array_sum(array_column($preparations, 'my_cost'));
        $mealsMyTotal = array_sum(array_column($meals, 'my_cost'));

        $baseTotal = $this->bookingCalculator->calculateBaseTotal($booking, $services, $paidCount);

        $huntingAmountMyPaid = $paidCount > 0
            ? round($this->bookingCalculator->calculateHuntingAmountPaid($booking, $paidCount) / $paidCount)
            : 0;

        $baseMyAmount = round($trophiesMyTotal + $penaltiesMyTotal + $addetionalsMyTotal + $preparationMyTotal + $mealsMyTotal);
        $myPrepayment = round($booking->total / $paidCount);
        $baseMyTotalCost = round(($myAccommodationCost + $huntingAmountMyPaid + $baseMyAmount) - $myPrepayment);

        // === Формируем итоговые массивы ===
        $allItems = [
            [
                'name' => 'Внесена предоплата:',
                'total_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : round($booking->total),
                'my_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : $myPrepayment,
            ],
            [
                'name' => 'Остаток базе',
                'total_cost' => $baseTotal,
                'my_cost' => $baseMyTotalCost,
            ],
        ];

        if (!is_baseAdmin()) {
            $allItems[] = [
                'name' => 'Итог охотникам',
                'total_cost' => round($totalSpending),
                'my_cost' => round($totalMyDebt),
            ];
        }

        return [
            'status' => true,
            'is_baseAdmin' => is_baseAdmin(),
            'items' => [
                [
                    'name' => $booking->type === Booking::BookingTypeAnimal
                        ? 'Проживание, ' . plural_sutki(0)
                        : 'Проживание, ' . plural_sutki($booking->duration_days),
                    'total_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : round($booking->total),
                    'my_cost' => $booking->type === Booking::BookingTypeAnimal ? 0 : $myAccommodationCost,
                ],
                [
                    'name' => 'Организация охоты',
                    'total_cost' => $this->bookingCalculator->calculateHuntingAmountPaid($booking, $paidCount),
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
            //TODO тут нужно думать
            'base_total' => $this->bookingCalculator->calculateBaseTotal($booking, $services, $paidCount),
        ];
    }
}
