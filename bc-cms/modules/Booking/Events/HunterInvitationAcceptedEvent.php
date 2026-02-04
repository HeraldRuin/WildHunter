<?php

namespace Modules\Booking\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class HunterInvitationAcceptedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bookingId;
    public $hunterId;
    public $booking;
    public $acceptedCount;
    public $totalHuntersNeeded;

    /**
     * Create a new event instance.
     */
    public function __construct(Booking $booking, int $hunterId)
    {
        $this->bookingId = $booking->id;
        $this->hunterId = $hunterId;

        // Получаем информацию о принятых приглашениях
        $allInvitations = $booking->getAllInvitations();
        $acceptedInvitations = $allInvitations->where('status', 'accepted');
        $acceptedCount = $acceptedInvitations->count();
        $totalHuntersNeeded = $booking->total_hunting ?? 0;

        $this->acceptedCount = $acceptedCount;
        $this->totalHuntersNeeded = $totalHuntersNeeded;

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
        // Отправляем на приватный канал бронирования, чтобы все заинтересованные пользователи получили обновление
        return new PrivateChannel('booking-' . $this->bookingId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'hunter.invitation.accepted';
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
            'accepted_count' => $this->acceptedCount,
            'total_hunters_needed' => $this->totalHuntersNeeded,
            'message' => 'Охотник принял приглашение на бронь #' . $this->booking['code'],
        ];
    }
}
