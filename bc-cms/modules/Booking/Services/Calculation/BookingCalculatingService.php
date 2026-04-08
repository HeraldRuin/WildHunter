<?php

namespace Modules\Booking\Services\Calculation;

use Modules\Booking\Models\Booking;
use Modules\Booking\Services\Calculation\Strategies\BookingCalculationStrategyResolver;

readonly class BookingCalculatingService
{
    public function __construct(private BookingDataBuilder $builder, private BookingCalculationStrategyResolver $resolver) {}

    public function calculate(Booking $booking, $user): array
    {
        $data = $this->builder->build($booking);

        $strategy = $this->resolver->resolve($booking);

        return $strategy->calculate($booking, $data, $user);
    }
}
