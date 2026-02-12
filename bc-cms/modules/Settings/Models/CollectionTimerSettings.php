<?php

namespace Modules\Settings\Models;

use Modules\Hotel\Models\Hotel;

class CollectionTimerSettings
{
    const DEFAULT_TIMER_HOURS = 24;
    const TYPE_COLLECT = 'collect';
    const TYPE_BEDS    = 'beds';
    const COLLECT_COLUMN = 'collection_timer_hours';
    const BEDS_COLUMN    = 'bed_timer_hours';


    /**
     * Получить таймер сбора или койко-места в часах для отеля
     *
     * @param int|null $hotelId
     * @return int
     */
    public static function getTimerHours($type, int $hotelId = null): int
    {
        if ($hotelId === null) {
            $hotelId = get_user_hotel_id();
        }

        if (!$hotelId) {
            return self::DEFAULT_TIMER_HOURS;
        }

        $hotel = Hotel::find($hotelId);

        if (!$hotel) {
            return self::DEFAULT_TIMER_HOURS;
        }


        $map = [
            self::TYPE_COLLECT => self::COLLECT_COLUMN,
            self::TYPE_BEDS    => self::BEDS_COLUMN,
        ];

        if (!isset($map[$type])) {
            return self::DEFAULT_TIMER_HOURS;
        }

        $column = $map[$type];

        return (int) ($hotel->{$column} ?? self::DEFAULT_TIMER_HOURS);
    }

    /**
     * Сохранить таймер сбора или койко-мест в часах для отеля
     *
     * @param int $hours
     * @param int|null $hotelId
     * @return bool
     */
    public static function saveTimerHours(int $hours, $type, int $hotelId = null): bool
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

        $map = [
            self::TYPE_COLLECT => self::COLLECT_COLUMN,
            self::TYPE_BEDS    => self::BEDS_COLUMN,
        ];

        if (!isset($map[$type])) {
            return false;
        }

        $column = $map[$type];
        $hotel->{$column} = $hours;

        return $hotel->save();
    }
}
