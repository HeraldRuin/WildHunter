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
        //TODO не понятно как быть с значением оплаты - ведь мы везде считаем кол оплативших - разобраться
        $paidCount = 1;


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
        $balanceBase = $this->bookingCalculator->getBalanceBase($booking, $user, $services, $paidCount);
        $paymentDisplayData = $this->bookingCalculator->getBookingTotal($booking, $services, $paidCount);

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
            'meals' => $meals,
            'preparation' => $preparations,
            'addetionals' => $addetionals,
            'spendings' => $spendingData['items'],
            'all_items' => $allItems,

            //Подсчет в историю бронирования в колонку оплата (админа базы)
            'booking_total' => $paymentDisplayData['booking_total'],
            'base_total' => $paymentDisplayData['base_total'],
        ];
    }
}
