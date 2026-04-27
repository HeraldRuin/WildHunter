<?php

namespace Modules\Booking\Services\Calculation\Strategies;

use Modules\Booking\Services\Calculation\BookingCalculator;
use Modules\Booking\Services\Calculation\Contracts\BookingCalculationStrategy;

class HuntingCalculationStrategy implements BookingCalculationStrategy
{
    public function __construct(protected BookingCalculator $bookingCalculator){}
    public function calculate($booking, array $data, $user): array
    {
        $services = $data['services'];
        $grouped = $services->groupBy('service_type');
        $isBaseAdmin = $data['isBaseAdmin'];

        if ($data['totalHunting'] === null || $data['totalHunting'] <= 0)
        {
            return [
                'status' => false,
                'message' => 'Нет участников охоты',
            ];
        }

        $totalHunting = $data['totalHunting'];

        // === Трофеи ===
        $trophies = $this->bookingCalculator->calculateTrophies(collect($grouped['trophy'] ?? []), $totalHunting);

        // === Штрафы ===
        $penalties = $this->bookingCalculator->calculatePenalties(collect($grouped['penalty'] ?? []), $user);


        // === Дополнительные услуги ===
        $addetionals = $this->bookingCalculator->calculateAdditional(collect($grouped['addetional'] ?? []), $user, $totalHunting);

        // === Питание ===
        $meals = $this->bookingCalculator->calculateMeals(collect($grouped['food'] ?? []), $totalHunting, $booking);

        // === Разделка ===
        $preparations = $this->bookingCalculator->calculatePreparations(collect($grouped['preparation'] ?? []), $totalHunting);

        $additionalServices = array_merge(
            $meals,
            $preparations,
            $addetionals
        );

        // === Расходы охотников ===
        $spendingData = $this->bookingCalculator->getSpendings(collect($grouped['spending'] ?? []), $user, $totalHunting);

        // === Подсчёты итогов ===
        $organisationHunting = $this->bookingCalculator->getOrganisationHunting($booking, $totalHunting);
        $balanceBase = $this->bookingCalculator->getBalanceBaseHunting($booking, $user, $services, $totalHunting, $isBaseAdmin);
        $paymentDisplayData = $this->bookingCalculator->getBookingTotal($booking, $services, $totalHunting);

        // === Формируем итоговые массивы ===
        $allItems = [
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
                    'name' => $organisationHunting['title_name'],
                    'total_cost' => $organisationHunting['total_cost'],
                    'my_cost' => $organisationHunting['my_cost'],
                ],
            ],
            'trophy_show' => true,
            'trophies' => $trophies,
            'penalties_show' => true,
            'penalties' => $penalties,
            'additional_services_show' => !empty($additionalServices),
            'meals' => $meals,
            'preparation' => $preparations,
            'addetionals' => $addetionals,
            'spendings_show' => !empty($spendingData),
            'spendings' => $spendingData['items'],
            'all_items' => $allItems,

            //Подсчет в историю бронирования в колонку оплата (админа базы)
            'base_total' => $paymentDisplayData['base_total'],
        ];
    }
}
