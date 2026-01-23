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
    public bool $isInvitation;

    /**
     * @param \Modules\Booking\Models\Booking $booking
     * @param \App\User $hunter
     * @param string $bodyText
     * @param bool $isInvitation
     */
    public function __construct(Booking $booking, User $hunter, string $bodyText, bool $isInvitation = false)
    {
        $this->booking  = $booking;
        $this->hunter   = $hunter;
        $this->bodyText = $bodyText;
        $this->isInvitation = $isInvitation;
    }

    public function build()
    {
        $subject = 'Сообщение по бронированию №' . $this->booking->id;

        $service = $this->booking->service;
        
        return $this->subject($subject)
            ->view('Booking::emails.hunter-message')
            ->with([
                'booking'   => $this->booking,
                'hunter'    => $this->hunter,
                'bodyText'  => $this->bodyText,
                'service'   => $service,
                'isInvitation' => $this->isInvitation,
            ]);
    }
}

