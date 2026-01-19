<?php

namespace Modules\Settings\Models;

use Modules\Hotel\Models\Hotel;

class CollectionTimerSettings
{
    /**
     * Получить таймер сбора в часах для отеля
     *
     * @param int|null $hotelId
     * @return int
     */
    public static function getTimerHours($hotelId = null): int
    {
        if ($hotelId === null) {
            $hotelId = get_user_hotel_id();
        }

        if (!$hotelId) {
            return 24;
        }

        $hotel = Hotel::find($hotelId);

        if (!$hotel) {
            return 24;
        }

        return (int) ($hotel->collection_timer_hours ?? 24);
    }

    /**
     * Сохранить таймер сбора в часах для отеля
     *
     * @param int $hours
     * @param int|null $hotelId
     * @return bool
     */
    public static function saveTimerHours(int $hours, $hotelId = null): bool
    {
        if ($hotelId === null) {
            $hotelId = get_user_hotel_id();
        }

        if (!$hotelId) {
            return false;
        }

        $hotel = Hotel::find($hotelId);

        if (!$hotel) {
            return false;
        }

        $hotel->collection_timer_hours = $hours;
        return $hotel->save();
    }
}
