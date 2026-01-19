<?php

namespace Modules\Booking\Emails;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class HunterMessageEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public User $hunter;
    public string $bodyText;

    /**
     * @param \Modules\Booking\Models\Booking $booking
     * @param \App\User $hunter
     * @param string $bodyText
     */
    public function __construct(Booking $booking, User $hunter, string $bodyText)
    {
        $this->booking  = $booking;
        $this->hunter   = $hunter;
        $this->bodyText = $bodyText;
    }

    public function build()
    {
        $subject = 'Сообщение по бронированию №' . $this->booking->id;

        return $this->subject($subject)
            ->view('Booking::emails.hunter-message')
            ->with([
                'booking'   => $this->booking,
                'hunter'    => $this->hunter,
                'bodyText'  => $this->bodyText,
            ]);
    }
}

