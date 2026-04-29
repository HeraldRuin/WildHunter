<?php

namespace Modules\Animals\Services;


use App\Exceptions\BusinessException;
use Modules\Animals\DTO\UpdateEntityData;

class AnimalService
{
    /**
     * @throws BusinessException
     */
    public function update(UpdateEntityData $data, $entity, string $type): array
    {
        $service = $entity->forHotel(get_user_hotel_id())->where('id', $entity->id)->first();

        if (!$service) {
            throw new BusinessException(
                errorCode: $type . '_not_found',
                domain: 'animal'
            );
        }

        $service->setHotelPrice(get_user_hotel_id(), $data->price);
        $service->priceForHotel(get_user_hotel_id());

        return [
            'code' => $type . '_saved'
        ];
    }
}
