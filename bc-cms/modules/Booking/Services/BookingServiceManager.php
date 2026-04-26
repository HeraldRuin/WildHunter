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
            ->where('bc_booking_services.service_type', AddetionalPrice::TROPHY)
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
            ->where('bc_booking_services.service_type', AddetionalPrice::PENALTY)
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
            ->where('bc_booking_services.service_type', AddetionalPrice::PREPARATION)
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
        ->where('bc_booking_services.service_type', AddetionalPrice::SPENDING)
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
        ->where('bc_booking_services.service_type', AddetionalPrice::ADDETIONAL)
        ->leftJoin('users', 'users.id', '=', 'bc_booking_services.hunter_id')
        ->select([
            'bc_booking_services.id',
            'bc_booking_services.booking_id',
            'bc_booking_services.service_type',
            'bc_booking_services.count',
            'bc_booking_services.type',
            'bc_booking_services.calculation_type',
            'users.id as hunter_id',
            'users.name as hunter_name',
            'bc_booking_services.created_at',
            'bc_booking_services.updated_at',
        ])
        ->get();

        return [
            'data' => [
                'trophies'      => $trophies,
                'penalties'     => $penalties,
                'preparations'  => $preparations,
                'foods'         => $services->where('service_type', AddetionalPrice::FOOD)->values(),
                'addetionals'   => $addetionals,
                'spendings'     => $spendings,
            ],
        ];
    }

    public function getTrophyData(Booking $booking): array
    {
        $animals = Animal::forHotelWithService($booking->hotel_id, Animal::SERVICE_TROPHIES)->get();

        return [
            'data' => [
                'animals' => $animals,
            ],
        ];
    }
    public function createTrophy(Booking $booking, StoreTrophyData $data): array
    {
        $trophy = AnimalTrophy::find($data->trophy_id);
        $price = $trophy->hotelPrices()->where('hotel_id', $booking->hotel_id)->first()?->price;
        $count = $data->count;
        $totalCost = number_format($price * $count, 2, '.', '');

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => AddetionalPrice::TROPHY,
            'type'         => $data->type,
            'service_id'   => null,
            'animal_id'    => $data->animal_id,
            'count'        => $data->count,
            'price'        => $totalCost,
        ])->load('animal');

        return [
            'data' => [
                'id'           => $service->id,
                'animal_title' => $service->animal->title ?? '—',
                'type'         => $service->type,
                'count'        => $service->count,
            ],
        ];
    }
    public function createPenalty(Booking $booking, StorePenaltyData $data): array
    {
        $penalty = AnimalFine::find($data->penalty_id);
        $price = $penalty->hotelPrices()->where('hotel_id', $booking->hotel_id)->first()?->price;

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => AddetionalPrice::PENALTY,
            'type'         => $data->type,
            'service_id'   => null,
            'hunter_id'    => $data->hunter_id,
            'animal_id'    => $data->animal_id,
            'price'        => $price,
        ])->load('hunter', 'animal');

        return [
            'data' => [
                'id'           => $service->id,
                'animal_title' => $service->animal->title ?? '—',
                'type'         => $service->type,
                'count'        => 1,
                'hunter_name'  => $service->hunter->name ?? '—',
            ],
        ];
    }

    public function getPreparationData(Booking $booking): array
    {
        $animals = Animal::forHotelWithService($booking->hotel_id, Animal::SERVICE_PREPARATIONS)->get();

        return [
            'data' => [
                'animals'  => $animals
            ],
        ];
    }
    public function createOrUpdatePreparation(Booking $booking, StorePreparationData $data): array
    {
        $preparation = AnimalPreparation::findOrFail($data->preparation_id);
        $price = $preparation->hotelPrices()->where('hotel_id', $booking->hotel_id)->value('price');
        $count = $data->count;
        $totalCost = $price * $count;

        $service = BookingService::where('booking_id', $booking->id)
            ->where('service_type', AddetionalPrice::PREPARATION)
            ->where('animal_id', $data->animal_id)
            ->first();

        if ($service) {
            $service->count += $count;
            $service->price = round($service->price + $totalCost, 2);
            $service->save();
        } else {
            $service = BookingService::create([
                'booking_id'   => $booking->id,
                'service_type' => AddetionalPrice::PREPARATION,
                'type'         => null,
                'service_id'   => null,
                'animal_id'    => $data->animal_id,
                'count'        => $count,
                'price'        => round($totalCost, 2),
            ])->load('animal');
        }

        return [
            'data' => [
                'id'           => $service->id,
                'animal_title' => $service->animal->title ?? '—',
                'count'        => $service->count,
            ],
        ];
    }
    public function createFood(Booking $booking, StoreFoodData $data): array
    {

        $price = AddetionalPrice::where('type', 'food')->where('hotel_id', $booking->hotel_id)->value('price');

        if (!$price) {
//            return response()->json([
//                'status' => false,
//                'message' => 'Цена питания не найдена'
//            ], 400);
        }


        $count = $data->count;
        $totalCost = $price * $count;

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => AddetionalPrice::FOOD,
            'type' => 'Питание',
            'price' => $totalCost,
            'count' => $count,
        ]);

        return [
            'data' => [
                'id'           => $service->id,
                'count'        => $service->count,
            ],
        ];
    }
    public function createAddetional(Booking $booking, StoreAddetionalData $data): array
    {
        $addetional = AddetionalPrice::where('id', $data->addetional_id)->first();
        $price = $addetional->price;
        $calculation_type = $addetional->calculation_type;
        $count = $data->count;
        $totalCost = number_format($price * $count, 2, '.', '');

        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' =>AddetionalPrice::ADDETIONAL,
            'type'       => $data->addetional,
            'calculation_type'   => $calculation_type,
            'count'       => $count,
            'hunter_id' => $calculation_type === AddetionalPrice::INDIVIDUAL? $data->hunter_id: null,
            'price'       => $totalCost,
        ]);

        return [
            'data' => [
                'id'           => $service->id,
                'type'         => $service->type,
                'calculation_type'   => $service->calculation_type,
                'count'         => $service->count,
                'hunter_name'  => $service->hunter->name ?? '—',
            ],
        ];

    }
    public function createSpending(Booking $booking, StoreSpendingData $data): array
    {
        $service = BookingService::create([
            'booking_id'   => $booking->id,
            'service_type' => AddetionalPrice::SPENDING,
            'price'        => $data->price,
            'comment'      => $data->comment,
            'service_id'   => null,
            'hunter_id'    => $data->hunter_id,
        ])->load('hunter');

        return [
            'data' => [
                'id'           => $service->id,
                'count'        => $service->price,
                'comment'      => $service->comment,
                'hunter_name'  => $service->hunter->name ?? '—',
            ],
        ];
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
            'data' => [
                'animals' => $animals,
                'hunters' => $hunters,
            ],
        ];
    }
    public function getAddetionalHunterData(Booking $booking): array
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

        $addetionals = AddetionalPrice::whereNull('type')->where('hotel_id', $booking->hotel_id)->where('price', '>', 0)->get()
            ->map(fn ($addetional) => [
                'id'   => $addetional->id,
                'type'   => $addetional->type,
                'calculation_type'   => $addetional->calculation_type,
                'name'   => $addetional->name,
                'count'   => $addetional->count,
                'price'   => $addetional->price,
            ])
            ->values()
            ->toArray();

        return [
            'data' => [
                'addetionals' => $addetionals,
                'hunters' => $hunters
            ],
        ];
    }
    public function deleteService(int $serviceId, Booking $booking): void
    {
        $service = BookingService::where('id', $serviceId)->where('booking_id', $booking->id)->firstOrFail();
        $service->delete();
    }
}
