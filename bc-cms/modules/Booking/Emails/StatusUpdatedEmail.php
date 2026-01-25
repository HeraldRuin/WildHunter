<?php
namespace Modules\Booking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class StatusUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $booking;
    public $oldStatus;
    protected $email_type;
    public $customMessage;
    protected $baseAdmin;

    public function __construct(Booking $booking,$to = 'admin', $customMessage = null, $baseAdmin = null)
    {
        $this->booking = $booking;
        $this->email_type = $to;
        $this->customMessage = $customMessage;
        $this->baseAdmin = $baseAdmin;
    }

    public function build()
    {
        $subject = '';
        switch ($this->email_type){
            case "admin":
            case "vendor":
                $subject = __('[:site_name] The booking status has been updated',['site_name'=>setting_item('site_title')]);
                break;

            case "customer":
                $subject = __('Your booking status has been updated',['site_name'=>setting_item('site_title')]);
                break;

        }

        $service = $this->booking->service;
        
        return $this->subject($subject)->view('Booking::emails.status-updated-booking')->with([
            'booking'       => $this->booking,
            'service'       => $service,
            'to'            => $this->email_type,
            'customMessage' => $this->customMessage,
            'baseAdmin'     => $this->baseAdmin,
        ]);
    }
}
