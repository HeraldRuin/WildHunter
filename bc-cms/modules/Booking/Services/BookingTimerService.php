<?php

namespace Modules\Booking\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;

class BookingTimerService
{
    protected BookingBedAllocatorService $allocatorBedsService;

    public function __construct(BookingBedAllocatorService $service)
    {
        $this->allocatorBedsService = $service;
    }

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
            $this->clearTimer($bookingId, $prefix);

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

    public function processExpiredBeds(): void
    {
        $now = Carbon::now()->toIso8601String();

        $expiredBookings = DB::table('bc_bookings as b')
            ->join('bc_booking_meta as m', function ($join) {
                $join->on('b.id', '=', 'm.booking_id')
                    ->where('m.name', '=', 'beds_end_at');
            })
            ->where('b.status', Booking::FINISHED_PREPAYMENT)
            ->where('m.val', '<', $now)
            ->select('b.id')
            ->get();

        foreach ($expiredBookings as $row) {

            $booking = Booking::find($row->id);
            if (!$booking) {
                continue;
            }
//            $this->clearTimer($booking);
            $this->handleBooking($booking);
        }
    }

    protected function handleBooking(Booking $booking): void
    {
        // Распределения охотников
        $this->allocatorBedsService->allocateBeds($booking);
    }

    protected function clearTimer($bookingId, $prefix): void
    {
        DB::transaction(function () use ($bookingId, $prefix) {
            DB::table('bc_booking_meta')
                ->where('booking_id', $bookingId)
                ->whereIn('name', [
                    "{$prefix}_start_at",
                    "{$prefix}_timer_hours",
                    "{$prefix}_end_at"
                ])->delete();
        });
    }
}

