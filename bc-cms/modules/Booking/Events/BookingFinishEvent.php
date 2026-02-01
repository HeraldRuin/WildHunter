<?php
namespace Modules\Booking\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Booking\Models\Booking;
use Illuminate\Queue\SerializesModels;

class BookingFinishEvent
{
    use Dispatchable, SerializesModels;
    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
}
