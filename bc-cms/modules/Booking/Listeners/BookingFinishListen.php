<?php
    namespace Modules\Booking\Listeners;
    use App\Notifications\AdminChannelServices;
    use App\Notifications\PrivateChannelServices;
    use App\User;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Mail;
    use Modules\Booking\Emails\StatusFinishCollectionEmail;
    use Modules\Booking\Events\BookingFinishEvent;
    use Modules\Booking\Models\BookingHunter;
    use Modules\Booking\Models\BookingHunterInvitation;

    class BookingFinishListen
    {
        public function handle(BookingFinishEvent $event)
        {
            $booking = $event->booking;

            // Уведомляем участников (но не создателя брони)
            $booking_hunter = BookingHunter::where('booking_id', $booking->id)->where('is_master', true)->first();
            $BaseAdmin = $booking->hotel->creator;
            Mail::to($BaseAdmin->email)->send(new StatusFinishCollectionEmail($booking, 'BaseAdmin', $BaseAdmin));

            if (!$booking_hunter) {
                return;
            }

            $invitations = $booking_hunter->invitations;
            $filtered_invitations = $invitations->filter(function($invitation) use ($booking_hunter) {
                return $invitation->hunter_id != $booking_hunter->invited_by;
            });

            foreach ($filtered_invitations as $invitation) {
                if ($invitation->hunter_id == $booking_hunter->id) {
                    continue;
                }

                $email = null;
                $hunter = null;

                if ($invitation->hunter_id) {
                    $hunter = User::find($invitation->hunter_id);
                    if ($hunter) {
                        $email = $hunter->email;
                    }
                } else {
                    // hunter_id пустой → используем email из записи
                    $email = $invitation->email;
                }

                Mail::to($email)->send(new StatusFinishCollectionEmail($booking, 'customer', $hunter));
            }
        }
    }
