<?php
    namespace Modules\Booking\Listeners;
    use App\Notifications\AdminChannelServices;
    use App\Notifications\PrivateChannelServices;
    use App\User;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Mail;
    use Modules\Booking\Emails\StatusFinishCollectionEmail;
    use Modules\Booking\Emails\StatusStartCollectionEmail;
    use Modules\Booking\Events\BookingFinishEvent;
    use Modules\Booking\Events\BookingStartCollectionEvent;
    use Modules\Booking\Models\BookingHunter;
    use Modules\Booking\Models\BookingHunterInvitation;

    class BookingStartCollectionListen
    {
        public function handle(BookingStartCollectionEvent $event)
        {
            $booking = $event->booking;

            // Уведомляем админа базы о начале сбора
//            $booking_hunter = BookingHunter::where('booking_id', $booking->id)->where('is_master', true)->first();
            $BaseAdmin = $booking->hotel->adminBase;
            Mail::to($BaseAdmin->email)->send(new StatusStartCollectionEmail($booking, 'BaseAdmin', $BaseAdmin));

//            if (!$booking_hunter) {
//                return;
//            }

//            $invitations = $booking_hunter->invitations;
//            $filtered_invitations = $invitations->filter(function($invitation) use ($booking_hunter) {
//                return $invitation->hunter_id != $booking_hunter->invited_by;
//            });
//
//            foreach ($filtered_invitations as $invitation) {
//                if ($invitation->hunter_id == $booking_hunter->id) {
//                    continue;
//                }
//
//                $email = null;
//                $hunter = null;
//
//                if ($invitation->hunter_id) {
//                    $hunter = User::find($invitation->hunter_id);
//                    if ($hunter) {
//                        $email = $hunter->email;
//                    }
//                } else {
//                    // hunter_id пустой → используем email из записи
//                    $email = $invitation->email;
//                }
//
//                Mail::to($email)->send(new StatusStartCollectionEmail($booking, 'customer', $hunter));
//            }
        }
    }
