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
            $BaseAdmin = $booking->hotel->adminBase;
            Mail::to($BaseAdmin->email)->send(new StatusStartCollectionEmail($booking, 'BaseAdmin', $BaseAdmin));
        }
    }
