<?php

namespace Modules\Booking\Services\Calculation;

use App\Exceptions\BusinessException;
use App\Exceptions\ValidationException;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\Calculation\Strategies\BookingCalculationStrategyResolver;

readonly class BookingCalculatingService
{
    public function __construct(private BookingDataBuilder $builder, private BookingCalculationStrategyResolver $resolver) {}

    /**
     * @throws ValidationException
     * @throws BusinessException
     */
    public function calculate(Booking $booking, $user): array
    {
        $data = $this->builder->build($booking);

        $strategy = $this->resolver->resolve($booking);
        $result = $strategy->calculate($booking, $data, $user);


        if ($result['status'] === false) {
            throw new BusinessException(
                errorCode: $result['message'],
                domain: 'calculate'
            );
        }
        return $result;
    }
}
