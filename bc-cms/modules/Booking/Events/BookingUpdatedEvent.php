<?php
namespace Modules\Booking\Events;

use Modules\Booking\Models\Booking;
use Illuminate\Queue\SerializesModels;

class BookingUpdatedEvent
{
    use SerializesModels;
    public $booking;
    public bool $skipStatusEmail;

    public function __construct(Booking $booking, bool $skipStatusEmail = false)
    {
        $this->booking = $booking;
        $this->skipStatusEmail = $skipStatusEmail;
    }
}
