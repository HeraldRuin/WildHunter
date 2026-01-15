<?php

namespace Modules\Booking\Events;

use AllowDynamicProperties;
use Modules\Booking\Models\Booking;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;

#[AllowDynamicProperties]
class BookingCreatedEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $booking;
    public $hotelData;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->withoutRelations();

        $hotel = $booking->hotel;

        if (!$hotel) {
            $this->hotelData = [
                'hotel_id' => null,
                'rooms' => [],
            ];
            return;
        }

        $hotelRooms = $hotel->hotelRooms()->get();

        if ($hotelRooms->isEmpty()) {
            $this->hotelData = [
                'hotel_id' => (int) $hotel->id,
                'rooms' => [],
            ];
            return;
        }

        $rooms = [];

        foreach ($hotelRooms as $room) {
            $booked = \DB::table('bc_hotel_room_bookings')
                ->where('room_id', $room->id)
                ->where('booking_id', $booking->id)
                ->sum('number');

            $rooms[] = [
                'room_id' => $room->id,
                'title'   => $room->title,
                'booked'  => (int) $booked,
                'total'   => (int) $room->number,
            ];
        }

        $this->hotelData = [
            'hotel_id' => (int) $hotel->id,
            'rooms'    => $rooms,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('booking');
    }

    public function broadcastAs()
    {
        return 'booking.created';
    }

    public function broadcastWith()
    {
        return [
            'booking'   => $this->booking,
            'hotelData' => $this->hotelData,
        ];
    }
}
