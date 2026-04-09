<?php

namespace Modules\Booking\Services;

use App\Models\User;
use Modules\Animals\Models\Animal;
use Modules\Animals\Models\AnimalFine;
use Modules\Animals\Models\AnimalPreparation;
use Modules\Animals\Models\AnimalTrophy;
use Modules\Attendance\Models\AddetionalPrice;
use Modules\Booking\DTO\StoreAddetionalData;
use Modules\Booking\DTO\StoreFoodData;
use Modules\Booking\DTO\StorePenaltyData;
use Modules\Booking\DTO\StorePreparationData;
use Modules\Booking\DTO\StoreSpendingData;
use Modules\Booking\DTO\StoreTrophyData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;

class BookingServiceManager
{
    public function getBookingServices(Booking $booking): array
    {
       $services = BookingService::where('booking_id', $booking->id)->get();

        $trophies = BookingService::query()
            ->where('bc_booking_services.booking_id', $booking->id)
            ->where('bc_booking_services.service_type', 'trophy')
            ->leftJoin(
                'bc_animals',
                'bc_animals.id',
                '=',
                'bc_booking_services.animal_id'
            )
            ->select([
                'bc_booking_services.id',
                'bc_booking_services.booking_id',
                'bc_booking_services.service_type',
                'bc_booking_services.animal_id as animal_id',
                'bc_animals.title as animal_title',
                'bc_booking_services.type',
                'bc_booking_services.count',
                'bc_booking_services.created_at',
                'bc_booking_services.updated_at',
            ])
            ->get();

        $penalties = BookingService::query()
            ->where('bc_booking_services.booking_id', $booking->id)
            ->where('bc_booking_services.service_type', 'penalty')
            ->leftJoin(
                'bc_animals',
                'bc_animals.id',
                '=',
                'bc_booking_services.animal_id'
            )
            ->leftJoin('users', 'users.id', '=', 'bc_booking_services.hunter_id')
            ->select([
                'bc_booking_services.id',
                'bc_booking_services.booking_id',
                'bc_booking_services.service_type',
                'bc_booking_services.animal_id as animal_id',
                'bc_animals.title as animal_title',
                'bc_booking_services.type',
                'users.id as hunter_id',
                'users.name as hunter_name',
                'bc_booking_services.created_at',
                'bc_booking_services.updated_at',
            ])
            ->get();

        $preparations = BookingService::query()
            ->where('bc_booking_services.booking_id', $booking->id)
            ->where('bc_booking_services.service_type', 'preparation')
            ->leftJoin(
                'bc_animals',
                'bc_animals.id',
                '=',
                'bc_booking_services.animal_id'
            )
            ->select([
                'bc_booking_services.id',
                'bc_booking_services.booking_id',
                'bc_booking_services.service_type',
                'bc_booking_services.animal_id as animal_id',
                'bc_animals.title as animal_title',
                'bc_booking_services.count',
                'bc_booking_services.created_at',
                'bc_booking_services.updated_at',
            ])
            ->get();

        $spendings = BookingService::query()
        ->where('bc_booking_services.booking_id', $booking->id)
        ->where('bc_booking_services.service_type', 'spending')
        ->leftJoin('users', 'users.id', '=', 'bc_booking_services.hunter_id')
        ->select([
            'bc_booking_services.id',
            'bc_booking_services.booking_id',
            'bc_booking_services.service_type',
            'bc_booking_services.count',
            'bc_booking_services.comment',
            'bc_booking_services.type',
            'users.id as hunter_id',
            'users.name as hunter_name',
            'bc_booking_services.created_at',
            'bc_booking_services.updated_at',
        ])
        ->get();

        $addetionals = BookingService::query()
        ->where('bc_booking_services.booking_id', $booking->id)
        ->where('bc_booking_services.service_type', 'addetional')
        ->leftJoin('users', 'users.id', '=', 'bc_booking_services.hunter_id')
        ->select([
            'bc_booking_services.id',
            'bc_booking_services.booking_id',
            'bc_booking_services.service_type',
            'bc_booking_services.count',
            'bc_booking_services.type',
            'users.id as hunter_id',
            'users.name as hunter_name',
            'bc_booking_services.created_at',
            'bc_booking_services.updated_at',
        ])
        ->get();

        return [
            'trophies'      => $trophies,
            'penalties'     => $penalties,
            'preparations'  => $preparations,
            'foods'         => $services->where('service_type', 'food')->values(),
            'addetionals'   => $addetionals,
            'spendings'     => $spendings,
        ];
    }
    public function createTrophy(Booking $booking, StoreTrophyData $data): BookingService
    {
        $trophy = AnimalTrophy::find($data->trophy_id);
        $price = $trophy->hotelPrices()->where('hotel_id', $booking->hotel_id)->first()?->price;
        $count = $data->count;
        $totalCost = number_format($price * $count, 2, '.', '');

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'trophy',
            'type'         => $data->type,
            'service_id'   => null,
            'animal_id'    => $data->animal_id,
            'count'        => $data->count,
            'price'        => $totalCost,
        ])->load('animal');
    }
    public function createPenalty(Booking $booking, StorePenaltyData $data): BookingService
    {
        $penalty = AnimalFine::find($data->penalty_id);
        $price = $penalty->hotelPrices()->where('hotel_id', $booking->hotel_id)->first()?->price;

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'penalty',
            'type'         => $data->type,
            'service_id'   => null,
            'hunter_id'    => $data->hunter_id,
            'animal_id'    => $data->animal_id,
            'price'        => $price,
        ])->load('hunter', 'animal');
    }
    public function createOrUpdatePreparation(Booking $booking, StorePreparationData $data): BookingService
    {
        $preparation = AnimalPreparation::findOrFail($data->preparation_id);
        $price = $preparation->hotelPrices()->where('hotel_id', $booking->hotel_id)->value('price');
        $count = $data->count;
        $totalCost = $price * $count;

        $service = BookingService::where('booking_id', $booking->id)
            ->where('service_type', 'preparation')
            ->where('animal_id', $data->animal_id)
            ->first();

        if ($service) {
            $service->count += $count;
            $service->price = round($service->price + $totalCost, 2);
            $service->save();
        } else {
            $service = BookingService::create([
                'booking_id'   => $booking->id,
                'service_type' => 'preparation',
                'type'         => null,
                'service_id'   => null,
                'animal_id'    => $data->animal_id,
                'count'        => $count,
                'price'        => round($totalCost, 2),
            ]);
        }

        return $service->load('animal');
    }
    public function createFood(Booking $booking, $price, StoreFoodData $data): BookingService
    {
        $count = $data->count;
        $totalCost = $price * $count;

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'food',
            'type' => 'Питание',
            'price' => $totalCost,
            'count' => $count,
        ]);
    }
    public function createAddetional(Booking $booking, StoreAddetionalData $data): BookingService
    {
        $price = AddetionalPrice::where('id', $data->addetional_id)->value('price');
        $count = $data->count;
        $totalCost = number_format($price * $count, 2, '.', '');

        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'addetional',
            'type'       => $data->addetional,
            'count'       => $count,
            'hunter_id'    => $data->hunter_id,
            'price'       => $totalCost,
        ]);
    }
    public function createSpending(Booking $booking, StoreSpendingData $data): BookingService
    {
        return BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => 'spending',
            'price'        => $data->price,
            'comment'      => $data->comment,
            'service_id'   => null,
            'hunter_id'    => $data->hunter_id,
        ])->load('hunter');
    }
    public function getAnimalHunterData(Booking $booking): array
    {
        $booking->load('bookingHunter.invitations');

        $animals = Animal::forHotelWithService($booking->hotel_id, Animal::SERVICE_FINES)->get();

        $hunterIds = $booking->bookingHunter?->invitations?->pluck('hunter_id')->unique();

        $hunters = User::query()
            ->whereIn('id', $hunterIds)
            ->get(['id', 'name', 'first_name', 'last_name', 'user_name'])
            ->map(fn ($hunter) => [
                'id' => $hunter->id,
                'name' => $hunter->display_name,
            ]);

        return [
            'animals' => $animals,
            'hunters' => $hunters,
        ];
    }
    public function getHunterData(Booking $booking): array
    {
        $booking->load('bookingHunter.invitations');
        $hunterIds = $booking->bookingHunter?->invitations?->pluck('hunter_id')->unique();

        $hunters = User::query()
            ->whereIn('id', $hunterIds)
            ->get(['id', 'name', 'first_name', 'last_name', 'user_name'])
            ->map(fn ($hunter) => [
                'id' => $hunter->id,
                'name' => $hunter->display_name,
            ]);

        return [
            'hunters' => $hunters,
        ];
    }
    public function deleteService(int $serviceId, Booking $booking): void
    {
        $service = BookingService::where('id', $serviceId)->where('booking_id', $booking->id)->firstOrFail();
        $service->delete();
    }
}
