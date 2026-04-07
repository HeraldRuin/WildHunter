<?php

namespace Modules\Booking\Services\Calculation\Contracts;

interface BookingCalculationStrategy
{
    public function calculate($booking, array $data, $user): array;
}
