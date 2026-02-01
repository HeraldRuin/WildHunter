<?php
namespace Modules\Booking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class StatusFinishCollectionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    protected $email_type;
    protected $user;

    public function __construct(Booking $booking, $to = 'BaseAdmin', $user = null)
    {
        $this->booking = $booking;
        $this->email_type = $to;
        $this->user = $user;
    }

    public function build()
    {
        $subject = '';
        switch ($this->email_type){
            case "customer":
            case "BaseAdmin":
                $subject = __('[:site_name] The booking status has been updated',['site_name'=>setting_item('site_title')]);
                break;
        }

        $service = $this->booking->service;

        return $this->subject($subject)->view('Booking::emails.finish-collection')->with([
            'booking'       => $this->booking,
            'service'       => $service,
            'to'            => $this->email_type,
            'user'     => $this->user,
        ]);
    }
}
