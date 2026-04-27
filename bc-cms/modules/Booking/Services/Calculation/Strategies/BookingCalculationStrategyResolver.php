<?php

namespace Modules\Booking\Services\Calculation\Strategies;

use App\Exceptions\ValidationException;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\Calculation\Contracts\BookingCalculationStrategy;

class BookingCalculationStrategyResolver
{
    protected array $map = [
        Booking::BookingTypeHotel => HotelCalculationStrategy::class,
        Booking::BookingTypeHotelAnimal => HotelHuntingCalculationStrategy::class,
        Booking::BookingTypeAnimal => HuntingCalculationStrategy::class,
    ];

    /**
     * @throws ValidationException
     */
    public function resolve(Booking $booking): BookingCalculationStrategy
    {
        $class = $this->map[$booking->type] ?? null;

        if (!$class) {
            throw new ValidationException(
                errorCode: 'unknown_booking_type',
                domain: 'booking',
                context: ['type' => $booking->type]
            );
        }

        return app($class);
    }
}
