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

        DB::transaction(function () use ($booking, &$notSetBedHunterIds, &$huntersWithoutPlace) {

            $roomBookings = $booking->roomsBooking()->with('room')->get();

            $roomsState = [];

            foreach ($roomBookings as $roomBooking) {

                $roomId = $roomBooking->room_id;
                $capacity = $roomBooking->room->adults;

                for ($index = 1; $index <= $roomBooking->number; $index++) {

                    $currentPlaces = DB::table('bc_booking_room_places')
                        ->where('booking_id', $booking->id)
                        ->where('room_id', $roomId)
                        ->where('room_index', $index)
                        ->count();

                    $roomsState[] = [
                        'room_id' => $roomId,
                        'room_index' => $index,
                        'capacity' => $capacity,
                        'current' => $currentPlaces
                    ];
                }
            }

            while ($notSetBedHunterIds->isNotEmpty()) {

                $placedSomeone = false;

                foreach ($roomsState as &$room) {

                    if ($notSetBedHunterIds->isEmpty()) {
                        break;
                    }

                    if ($room['current'] >= $room['capacity']) {
                        continue;
                    }

                    $hunterId = $notSetBedHunterIds->shift();

                    DB::table('bc_booking_room_places')->insert([
                        'booking_id' => $booking->id,
                        'room_id' => $room['room_id'],
                        'room_index' => $room['room_index'],
                        'place_number' => $room['current'] + 1,
                        'user_id' => $hunterId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $room['current']++;
                    $placedSomeone = true;
                }

                if (!$placedSomeone) {
                    break;
                }
            }

            if ($notSetBedHunterIds->isNotEmpty()) {
                $huntersWithoutPlace = $notSetBedHunterIds->toArray();
            }
        });

        if (!empty($huntersWithoutPlace)) {
            Log::warning('Не удалось присвоить место охотникам', [
                'booking_id' => $booking->id,
                'hunters_without_place' => $huntersWithoutPlace
            ]);
        }

        if (empty($huntersWithoutPlace)) {
            $booking->update(['is_all_places_assigned' => true]);
        }
    }
}

