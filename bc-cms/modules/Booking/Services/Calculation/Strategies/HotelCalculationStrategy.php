<?php

namespace Modules\Booking\Services\Calculation\Strategies;

use Modules\Booking\Services\Calculation\BookingCalculator;
use Modules\Booking\Services\Calculation\Contracts\BookingCalculationStrategy;

class HotelCalculationStrategy implements BookingCalculationStrategy
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

        // === Питание ===
        $meals = $this->bookingCalculator->calculateMeals(collect($grouped['food'] ?? []), $paidCount, $booking);

        // === Дополнительные услуги ===
        $addetionals = $this->bookingCalculator->calculateAdditional(collect($grouped['addetional'] ?? []), $user, $paidCount);

        // === Расходы охотников ===
        $spendingData = $this->bookingCalculator->getSpendings(collect($grouped['spending'] ?? []), $user, $paidCount);

        // === Подсчёты итогов ===
        $accommodation = $this->bookingCalculator->getAccommodation($booking, $user, $paidCount);
        $prepaymentMade = $this->bookingCalculator->getPrepaymentMade($booking, $paidCount);
        $balanceBase = $this->bookingCalculator->getBalanceBase($booking, $user, $services, $paidCount);
        $paymentDisplayData = $this->bookingCalculator->getBookingTotal($booking, $services, $paidCount);

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
            ],
            'trophy_show' => false,
            'penalties_show' => false,
            'meals' => $meals,
            'addetionals' => $addetionals,
            'spendings' => $spendingData['items'],
            'all_items' => $allItems,

            //Подсчет в историю бронирования в колонку оплата (админа базы)
            'booking_total' => $paymentDisplayData['booking_total'],
            'prepaid_total' => $paymentDisplayData['prepaid_total'],
            'base_total' => $paymentDisplayData['base_total'],
            'total' => $paymentDisplayData['total'],
        ];
    }
}
