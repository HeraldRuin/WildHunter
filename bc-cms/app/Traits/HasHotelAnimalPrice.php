<?php


namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Animals\Models\HotelAnimalPrice;

trait HasHotelAnimalPrice
{
    public function hotelPrices(): MorphMany
    {
        return $this->morphMany(HotelAnimalPrice::class, 'priceable');
    }

    public function setHotelPrice(int $hotelId, $price)
    {
        return HotelAnimalPrice::updateOrCreate(
            [
                'hotel_id' => $hotelId,
                'priceable_id' => $this->id,
                'priceable_type' => get_class($this),
            ],
            [
                'price' => $price ?? null,
            ]
        );
    }

    public function priceForHotel(int $hotelId)
    {
        $price = $this->hotelPrices
            ->where('hotel_id', $hotelId)
            ->first();

        return $price ? $price->price : null;
    }
}
