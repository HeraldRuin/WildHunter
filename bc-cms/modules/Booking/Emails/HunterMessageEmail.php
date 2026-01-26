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

    public bool $showSeparateServices = false;
    public bool $hideCollectionAnimalButton = false;
    public bool $hideCollectHotelButton = false;

    public function __construct(Booking $booking, User $hunter, string $bodyText, bool $isInvitation = false, bool $showSeparateServices = false, bool $hideCollectionAnimalButton = false, bool $hideCollectHotelButton = false)
    {
        $this->booking  = $booking;
        $this->hunter   = $hunter;
        $this->bodyText = $bodyText;
        $this->isInvitation = $isInvitation;

        if ($isInvitation){
            $this->showSeparateServices = false;
            $this->hideCollectionAnimalButton = true;
            $this->hideCollectHotelButton = false;
        }
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

