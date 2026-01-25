<?php
namespace Modules\Booking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;

class NewBookingEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $booking;
    protected $email_type;
    protected $baseAdmin;
    public $isNewBooking;

    public function __construct(Booking $booking,$to = 'admin', $baseAdmin = null)
    {
        $this->booking = $booking;
        $this->email_type = $to;
        $this->baseAdmin = $baseAdmin;
        $this->isNewBooking = true;
    }

    public function build()
    {
        $subject = '';
        switch ($this->email_type){
            case "admin":
                if($this->baseAdmin) {
                    $subject = __('Your service got new booking');
                } else {
                    $subject = __('New booking has been made');
                }
            break;

            case "vendor":
                $subject = __('Your service got new booking');

            break;

            case "customer":
                $subject = __('Thank you for booking with us');
            break;

        }

        return $this->subject($subject)->view('Booking::emails.new-booking')->with([
            'booking' => $this->booking,
            'service' => $this->booking->service,
            'to'=>$this->email_type,
            'baseAdmin' => $this->baseAdmin,
            'isNewBooking' => $this->isNewBooking
        ]);
    }
}
