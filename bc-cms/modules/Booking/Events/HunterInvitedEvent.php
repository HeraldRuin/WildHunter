<?php

namespace Modules\Booking\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class HunterInvitedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bookingId;
    public $hunterId;
    public $booking;

    /**
     * Create a new event instance.
     */
    public function __construct(Booking $booking, int $hunterId)
    {
        $this->bookingId = $booking->id;
        $this->hunterId = $hunterId;
        // Отправляем только необходимые данные брони
        $this->booking = [
            'id' => $booking->id,
            'code' => $booking->code,
            'status' => $booking->status,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return ['user-channel-' . $this->hunterId];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'hunter.invited';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'hunter_id' => $this->hunterId,
            'booking' => $this->booking,
            'message' => 'Вас пригласили на бронь #' . $this->booking['code'],
        ];
    }
}
