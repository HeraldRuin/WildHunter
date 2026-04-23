<?php

namespace Modules\Booking\Services;

use App\Service\MailService;
use App\User;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Emails\StatusUpdatedEmail;
use Modules\Booking\Models\Booking;

class BookingMailService
{
    public function __construct(
        protected MailService $mailService
    ) {}

    public function sendCompletedEmail(Booking $booking): void
    {
        try {
            $old = app()->getLocale();

            if ($bookingLocale = $booking->getMeta('locale')) {
                app()->setLocale($bookingLocale);
            }

            if ($booking->create_user) {
                $creator = User::find($booking->create_user);

                if ($creator && $creator->email) {
                    $this->mailService->send(
                        $creator->email,
                        new StatusUpdatedEmail($booking, 'customer')
                    );
                }
            }

            app()->setLocale($old);

        } catch (\Throwable $e) {
            Log::warning('sendCompletedStatusEmail: '.$e->getMessage());
        }
    }
}
