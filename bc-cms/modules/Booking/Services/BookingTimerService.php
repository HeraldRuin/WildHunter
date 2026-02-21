<?php

namespace Modules\Booking\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingTimerService
{
    /**
     * Запускает таймер и возвращает время начала и окончания
     */
    public function startTimer(int $bookingId, int $hours, string $prefix): array
    {
        $now = Carbon::now();
        $startAt = $now->toIso8601String();
        $endAt = $now->copy()->addHours($hours)->toIso8601String();

        DB::transaction(function () use ($bookingId, $hours, $startAt, $endAt, $prefix) {
            // Удаляем старые значения этого таймера
            DB::table('bc_booking_meta')
                ->where('booking_id', $bookingId)
                ->whereIn('name', [
                    "{$prefix}_start_at",
                    "{$prefix}_timer_hours",
                    "{$prefix}_end_at"
                ])
                ->delete();

            // Вставляем новые
            DB::table('bc_booking_meta')->insert([
                [
                    'booking_id' => $bookingId,
                    'name' => "{$prefix}_start_at",
                    'val' => $startAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'booking_id' => $bookingId,
                    'name' => "{$prefix}_timer_hours",
                    'val' => (string)$hours,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'booking_id' => $bookingId,
                    'name' => "{$prefix}_end_at",
                    'val' => $endAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });

        return [
            'start_at' => $startAt,
            'end_at' => $endAt,
            'hours' => $hours,
        ];
    }
}

