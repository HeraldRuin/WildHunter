<?php
    namespace Modules\Booking\Listeners;

    use App\User;
    use Illuminate\Support\Facades\Mail;
    use Modules\Booking\Emails\StatusFinishCollectionEmail;
    use Modules\Booking\Events\BookingFinishEvent;

    class BookingFinishListen
    {
        public function handle(BookingFinishEvent $event)
        {
            $booking = $event->booking;

            // Уведомляем участников (но не создателя брони)
            $booking_hunter = $booking->masterHunter()->first();
            $BaseAdmin = $booking->hotel->adminBase;
            Mail::to($BaseAdmin->email)->send(new StatusFinishCollectionEmail($booking, 'BaseAdmin', $BaseAdmin));

            if (!$booking_hunter) {
                return;
            }

            $invitations = $booking_hunter->invitations()->with('hunter') ->get();
            $filtered_invitations = $invitations->filter(function($invitation) use ($booking_hunter) {
                return $invitation->hunter_id != $booking_hunter->invited_by;
            });

            foreach ($filtered_invitations as $invitation) {
                if ($invitation->hunter_id == $booking_hunter->id) {
                    continue;
                }

                $email = $invitation->email;

                Mail::to($email)->send(new StatusFinishCollectionEmail($booking, 'customer', $invitation->hunter));
            }
        }
    }
