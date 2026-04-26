<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use Modules\Booking\DTO\SelectPlaceData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingRoomPlace;

class BookingPlaceService
{
    public function getPlaces(Booking $booking): array
    {
        $rooms = $booking
            ->roomsBooking()
            ->with('room', 'booking:id,total_guests')
            ->get()
            ->map(function ($roomBooking) {
                $booking = $roomBooking->booking;
                $room = $roomBooking->room;

                return [
                    'booking_total_guests' => $booking->total_guests,
                    'booking_room_id' => $roomBooking->id,
                    'booking_number' => $roomBooking->number,
                    'room_id' => $room->id,
                    'title' => $room->title,
                    'number' => $room->number,
                    'total_guests_in_type' => $roomBooking->number * $room->number,
                ];
            });

        $places = BookingRoomPlace::with('user:id,first_name,last_name,user_name')
            ->where('booking_id', $booking->id)
            ->get()
            ->groupBy(['room_index', 'room_id', 'place_number']);

        return [
            'data' => [
                'rooms' => $rooms,
                'places' => $places,
            ],
        ];
    }

    /**
     * @throws ForbiddenException
     * @throws ConflictException
     */
    public function selectPlace(Booking $booking, SelectPlaceData $data): array
    {
        $alreadyHasPlace = BookingRoomPlace::where('booking_id', $booking->id)
            ->where('user_id', $data->userId)
            ->exists();

        if ($alreadyHasPlace) {
            throw new ForbiddenException(
                errorCode: 'cannot_select_more_than_one_place',
                domain: 'booking'
            );
        }

        $occupiedPlaceNumbers = BookingRoomPlace::where('booking_id', $booking->id)
            ->where('room_id', $data->roomId)
            ->where('room_index', $data->placeNumber)
            ->pluck('place_number')
            ->toArray();

        $totalPlaces = $booking->hotelRoom()
            ->find($data->roomId)
            ->number;


        $finalPlaceNumber = null;
        for ($i = 1; $i <= $totalPlaces; $i++) {
            if (!in_array($i, $occupiedPlaceNumbers, true)) {
                $finalPlaceNumber = $i;
                break;
            }
        }

        if (!$finalPlaceNumber) {
            throw new ConflictException(
                errorCode: 'no_free_places_in_room',
                domain: 'booking'
            );
        }

        BookingRoomPlace::create([
            'booking_id'   => $booking->id,
            'room_index'    => $data->roomIndex,
            'room_id'       => $data->roomId,
            'place_number'  => $finalPlaceNumber,
            'user_id'       => $data->userId,
        ]);

        $this->updateStatusIfAllPlacesSelected($booking);

            return [
                'code' => 'place_selected',
            ];
    }

    public function updateStatusIfAllPlacesSelected(Booking $booking): void
    {
        $paidCount = $booking->countAcceptedAndPaidHunters();
        $alreadyHasPlace = BookingRoomPlace::where('booking_id', $booking->id)->count() === $paidCount;

        if ($paidCount > 0 && $paidCount === $alreadyHasPlace) {
            $booking->status = Booking::FINISHED_BED;
            $booking->save();
        }
    }

    /**
     * @throws ForbiddenException
     */
    public function cancelSelectPlace(int $bookingId, int $placeId, int $userId): array
    {
        $place = BookingRoomPlace::where('booking_id', $bookingId)
            ->where('id', $placeId)
            ->where('user_id', $userId)
            ->first();

        if (!$place) {
            throw new ForbiddenException(
                errorCode: 'cancel_only_own_place',
                domain: 'booking'
            );
        }

        $place->delete();

        return [
            'code' => 'place_cancelled',
        ];
    }
}
