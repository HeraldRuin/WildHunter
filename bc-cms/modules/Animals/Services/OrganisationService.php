<?php

namespace Modules\Animals\Services;

use Modules\Animals\DTO\AnimalPricePeriodUpdateDTO;
use Modules\Animals\Models\AnimalPricePeriod;

class OrganisationService
{
    public function createPeriod(int $animalId): array
    {
        $period = AnimalPricePeriod::create([
            'animal_id' => $animalId,
            'start_date' => null,
            'end_date' => null,
            'amount' => null,
        ]);

        return [
            'code' => 'period_saved',
            'data' => [
                'animal_id' => $animalId,
                'period' => $period,
            ],
        ];
    }

    public function updatePeriod($period, AnimalPricePeriodUpdateDTO $data): array
    {
        $period->update([
            'start_date' => $data->startDate,
            'end_date'   => $data->endDate,
            'price'     => $data->price,
        ]);

        return [
            'code' => 'period_updated'
        ];
    }
    public function deletePeriod($period): array
    {
        $period->delete();

        return [
            'code' => 'period_deleted',
            'data' => [
                'id' => $period->id
            ]
        ];
    }
}
