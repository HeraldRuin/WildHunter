<?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\BookingRoomPlace;

class BookingBedAllocatorService
{
    public function areAllHuntersAssigned(Booking $booking)
    {
        $masterBookingHunterIds = $booking->bookingHunter()
            ->where('is_master', true)
            ->pluck('id');

        return BookingHunterInvitation::whereIn('booking_hunter_id', $masterBookingHunterIds)
            ->where('status', 'accepted')
            ->where('prepayment_paid', true)
            ->pluck('hunter_id');
    }

    /**
     * Основной метод для распределения охотников по койкам
     */
    public function allocateBeds(Booking $booking): void
    {
        $invitedHunters = $this->areAllHuntersAssigned($booking);

        $places = BookingRoomPlace::whereIn('user_id', $invitedHunters)
            ->where('booking_id',$booking->id)
            ->get();

        if ($invitedHunters->count() === $places->count()) {
            return;
        }

        $this->assignRemainingHunters($booking, $invitedHunters, $places);
    }

    protected function assignRemainingHunters(Booking $booking, $invitedHunters, $places): void
    {
        $notSetBedHunterIds = $invitedHunters->diff($places->pluck('user_id'));
        $this->assignToPartiallyFilledRooms($booking, $notSetBedHunterIds);
    }

    protected function assignToPartiallyFilledRooms(Booking $booking, $notSetBedHunterIds): void
    {
        if ($notSetBedHunterIds->isEmpty()) {
            return;
        }

        $huntersWithoutPlace = [];

        DB::transaction(function () use ($booking, $notSetBedHunterIds, &$huntersWithoutPlace) {

            $roomBookings = $booking->roomsBooking()->get();
            $rooms = DB::table('bc_hotel_rooms')
                ->whereIn('id', $roomBookings->pluck('room_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($notSetBedHunterIds as $hunterId) {
                $assigned = false;

                // Этап 1: частично заполненные комнаты
                foreach ($roomBookings as $roomBooking) {
                    $roomId = $roomBooking->room_id;
                    $capacity = $rooms[$roomId]->adults ?? 0;

                    $currentPlaces = DB::table('bc_booking_room_places')
                        ->where('booking_id', $booking->id)
                        ->where('room_id', $roomId)
                        ->orderByDesc('place_number')
                        ->lockForUpdate()
                        ->get();

                    $currentCount = $currentPlaces->count();

                    if ($currentCount > 0 && $currentCount < $capacity) {
                        $maxPlaceId = $currentPlaces->first()->place_number;
                        DB::table('bc_booking_room_places')->insert([
                            'booking_id'   => $booking->id,
                            'room_id'      => $roomId,
                            'place_number' => $maxPlaceId + 1,
                            'user_id'      => $hunterId,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                        $assigned = true;
                        break;
                    }
                }

                // Этап 2: полностью пустые комнаты, если ещё не назначено
                if (!$assigned) {
                    foreach ($roomBookings as $roomBooking) {
                        $roomId = $roomBooking->room_id;
                        $capacity = $rooms[$roomId]->adults ?? 0;

                        $currentPlaces = DB::table('bc_booking_room_places')
                            ->where('booking_id', $booking->id)
                            ->where('room_id', $roomId)
                            ->lockForUpdate()
                            ->get();

                        if ($currentPlaces->count() == 0) {
                            // вставляем первое место
                            DB::table('bc_booking_room_places')->insert([
                                'booking_id'   => $booking->id,
                                'room_id'      => $roomId,
                                'place_number' => 1,
                                'user_id'      => $hunterId,
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                            $assigned = true;
                            break;
                        }
                    }
                }

                // Этап 3: если нигде не удалось разместить
                if (!$assigned) {
                    $huntersWithoutPlace[] = $hunterId;
                }
            }
        });

        if (!empty($huntersWithoutPlace)) {
            Log::warning('Не удалось присвоить место охотникам', [
                'booking_id' => $booking->id,
                'hunters_without_place' => $huntersWithoutPlace
            ]);
        }

        $allAssigned = empty($huntersWithoutPlace);

        if ($allAssigned) {
            $booking->update(['is_all_places_assigned' => true]);
        }
    }
}

