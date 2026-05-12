<?php

namespace Modules\Attendance\Services;

use App\Exceptions\NotFoundException;
use Modules\Attendance\DTO\StoreAdditionalData;
use Modules\Attendance\DTO\UpdateAdditionalData;
use Modules\Attendance\Models\AddetionalPrice;

class AddetionalService
{
    public function store(StoreAdditionalData $data, $hotelId, $user)
    {
        return AddetionalPrice::create([
            'name' => $data->name,
            'price' => $data->price,
            'hotel_id' => $hotelId,
            'user_id'  => $user,
        ]);
    }

    /**
     * @throws NotFoundException
     */
    public function update(UpdateAdditionalData $data, $id, $hotelId, $userId): void
    {
        $additional = AddetionalPrice::accessible($hotelId, $userId)
            ->where('id', $id)
            ->first();

        if (!$additional) {
            throw new NotFoundException(
                errorCode: 'additional_not_found',
                domain: 'additional'
            );
        }

        $additional->update([
            'name' => $data->name,
            'price' => $data->price,
            'count' => $data->count,
            'calculation_type' => $data->calculation_type,
        ]);
    }

    /**
     * @throws NotFoundException
     */
    public function delete($id, $hotelId, $userId): void
    {
        $additional = AddetionalPrice::accessible($hotelId, $userId)
            ->where('id', $id)
            ->first();

        if (!$additional) {
            throw new NotFoundException(
                errorCode: 'additional_not_found',
                domain: 'additional'
            );
        }

        $additional->delete();
    }
}
