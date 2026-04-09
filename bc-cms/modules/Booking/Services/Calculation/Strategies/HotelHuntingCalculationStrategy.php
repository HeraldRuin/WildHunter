<?php

namespace Modules\Booking\Services\Calculation\Strategies;

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

        // === Трофеи ===
        $trophies = $this->bookingCalculator->calculateTrophies(collect($grouped['trophy'] ?? []), $paidCount);

        // === Штрафы ===
        $penalties = $this->bookingCalculator->calculatePenalties(collect($grouped['penalty'] ?? []), $user);

        // === Питание ===
        $meals = $this->bookingCalculator->calculateMeals(collect($grouped['food'] ?? []), $paidCount, $booking);

        // === Разделка ===
        $preparations = $this->bookingCalculator->calculatePreparations(collect($grouped['preparation'] ?? []), $paidCount);

        // === Дополнительные услуги ===
        $addetionals = $this->bookingCalculator->calculateAdditional(collect($grouped['addetional'] ?? []), $user, $paidCount);

        // === Расходы охотников ===
        $spendingData = $this->bookingCalculator->getSpendings(collect($grouped['spending'] ?? []), $user, $paidCount);

        // === Подсчёты итогов ===
        $organisationHunting = $this->bookingCalculator->getOrganisationHunting($booking, $paidCount);
        $accommodation = $this->bookingCalculator->getAccommodation($booking, $user, $paidCount);
        $prepaymentMade = $this->bookingCalculator->getPrepaymentMade($booking, $paidCount);
        $balanceBase = $this->bookingCalculator->getBalanceBase($booking, $user, $services, $paidCount);

        // === Формируем итоговые массивы ===
        $allItems = [
            [
                'name' => $prepaymentMade['title_name'],
                'total_cost' => $prepaymentMade['total_cost'],
                'my_cost' => $prepaymentMade['my_cost'],
            ],
            [
                'name' => $balanceBase['title_name'],
                'total_cost' => $balanceBase['total_cost'],
                'my_cost' => $balanceBase['my_cost'],
            ]
        ];

        if (!is_baseAdmin()) {
            $allItems[] = [
                'name' => $spendingData['title_name'],
                'total_cost' => $spendingData['total_cost'],
                'my_cost' => $spendingData['my_cost'],
            ];
        }

        return [
            'status' => true,
            'is_baseAdmin' => is_baseAdmin(),
            'items' => [
                [
                    'name' => $accommodation['title_name'],
                    'total_cost' => $accommodation['total_cost'],
                    'my_cost' => $accommodation['my_cost'],
                ],
                [
                    'name' => $organisationHunting['title_name'],
                    'total_cost' => $organisationHunting['total_cost'],
                    'my_cost' => $organisationHunting['my_cost'],
                    'has_tooltip' => true,
                ],
            ],
            'trophies' => $trophies,
            'penalties' => $penalties,
            'meals' => $meals,
            'preparation' => $preparations,
            'addetionals' => $addetionals,
            'spendings' => $spendingData['items'],
            'all_items' => $allItems,
            //TODO тут нужно думать
            'base_total' => $this->bookingCalculator->calculateBaseTotal($booking, $services, $paidCount),
        ];
    }
}
