<?php

namespace Modules\Booking\Services;

use Modules\Animals\Models\AnimalFine;
use Modules\Animals\Models\AnimalTrophy;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;

class BookingServiceManager
{
    public function createTrophy(Booking $booking, array $data): BookingService
    {
        $trophy = AnimalTrophy::find($data['trophy_id']);
        $price = $trophy->hotelPrices()->where('hotel_id', $booking->hotel_id)->first()?->price;
        $count = (int) $data['count'];
        $totalCost = number_format($price * $count, 2, '.', '');

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'trophy',
            'type'         => $data['type'],
            'service_id'   => null,
            'animal_id'    => $data['animal_id'],
            'count'        => $data['count'],
            'price'        => $totalCost,
        ]);
    }
    public function createPenalty(Booking $booking, array $data): BookingService
    {
        $penalty = AnimalFine::find($data['penalty_id']);
        $price = $penalty->hotelPrices()->where('hotel_id', $booking->hotel_id)->first()?->price;

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'penalty',
            'type'         => $data['type'],
            'service_id'   => null,
            'hunter_id'    => $data['hunter_id'],
            'animal_id'    => $data['animal_id'],
            'price'        => $price,
        ]);
    }
    public function createFood(Booking $booking, $price, array $data): BookingService
    {
        $count = (int) $data['count'];
        $totalCost = number_format($price * $count, 2, '.', '');

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'food',
            'type' => 'Питание',
            'price' => $totalCost,
            'count' => $data['count'],
        ]);
    }
    public function createAddetional(Booking $booking, array $data): BookingService
    {
        $price = AddetionalPrice::where('id', $data['addetional_id'])->value('price');
        $count = (int) $data['count'];
        $totalCost = number_format($price * $count, 2, '.', '');

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'addetional',
            'type'       => $data['addetional'],
            'count'       => $data['count'],
            'price'       => $totalCost,
        ]);
    }
    public function createSpending(Booking $booking, array $data): BookingService
    {
        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'spending',
            'price'        => $data['price'],
            'comment'      => $data['comment'],
            'service_id'   => null,
            'hunter_id'    => $data['hunter_id'],
        ]);
    }
    public function deleteService(int $serviceId, Booking $booking): void
    {
        $service = BookingService::where('id', $serviceId)->where('booking_id', $booking->id)->firstOrFail();
        $service->delete();
    }
}
