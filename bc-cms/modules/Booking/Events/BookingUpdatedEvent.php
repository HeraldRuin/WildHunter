<?php
namespace Modules\Booking\Events;

use Modules\Booking\Models\Booking;
use Illuminate\Queue\SerializesModels;

class BookingUpdatedEvent
{
    use SerializesModels;
    public $booking;
    public bool $skipCustomerStatusEmail;

    public function __construct(Booking $booking, bool $skipCustomerStatusEmail = false)
    {
        $this->booking = $booking;
        $this->skipCustomerStatusEmail = $skipCustomerStatusEmail;
    }
}
