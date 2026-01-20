<?php

namespace Modules\Booking\Emails;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Booking\Models\Booking;

class CollectionTimerFinishedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public User $hunter;

    public function __construct(Booking $booking, User $hunter)
    {
        $this->booking = $booking;
        $this->hunter  = $hunter;
    }

    public function build()
    {
        $subject = 'Таймер сбора по бронированию №' . $this->booking->id . ' завершён';

        return $this->subject($subject)
            ->view('Booking::emails.collection-timer-finished')
            ->with([
                'booking' => $this->booking,
                'hunter'  => $this->hunter,
            ]);
    }
}

