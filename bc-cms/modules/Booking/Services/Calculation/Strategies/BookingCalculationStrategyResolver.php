<?php

namespace Modules\Booking\Services\Calculation\Strategies;

use Modules\Booking\Models\Booking;
use Modules\Booking\Services\Calculation\Contracts\BookingCalculationStrategy;

class BookingCalculationStrategyResolver
{
    protected array $map = [
        Booking::BookingTypeHotel => HotelCalculationStrategy::class,
        Booking::BookingTypeHotelAnimal => HotelHuntingCalculationStrategy::class,
        Booking::BookingTypeAnimal => HuntingCalculationStrategy::class,
    ];

    public function resolve(Booking $booking): BookingCalculationStrategy
    {
        $class = $this->map[$booking->type] ?? null;

        if (!$class) {
            throw new \InvalidArgumentException( booking_error('unknown_type', ['type' => $booking->type]) );
        }

        return app($class);
    }
}
