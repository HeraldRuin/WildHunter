<?php

namespace Modules\Booking\Events;

use AllowDynamicProperties;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;

#[AllowDynamicProperties] class BookingCreatedEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $booking;
    protected $hotel;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->withoutRelations();
//        $this->booking = $booking;

//        $hotel = $booking->hotel;
//        if (!$hotel) {
//            $this->hotelData = [
//                'hotel_id' => null,
//                'rooms' => [],
//            ];
//            return;
//        }
//
//        $hotelRooms = $hotel->hotelRooms();
//
////        $hotelRooms = \Modules\Hotel\Models\HotelRoom::query()
////            ->where('parent_id', $hotel->id)
////            ->select(['id', 'title', 'number'])
////            ->get();
//
//        $rooms = [];
//
//        foreach ($hotelRooms as $room) {
//            $booked = \DB::table('bc_hotel_room_bookings')
//                ->where('room_id', $room->id)
//                ->where('booking_id', $booking->id)
//                ->sum('number');
//
//            $rooms[] = [
//                'room_id' => $room->id,
//                'title'   => $room->title,
//                'booked'  => (int) $booked,
//                'total'   => (int) $room->number,
//            ];
//        }
//
//        $hotelData = [
//            'hotel_id' => (int) $hotel->id,
//            'rooms'    => $rooms,
//        ];
//
////        dd(json_encode($this->hotelData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
            'test' => 123,
        ];
    }
}
