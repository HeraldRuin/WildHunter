<?php

namespace Modules\Booking\Services;

use App\Service\MailService;
use App\User;
use Modules\Booking\Emails\StatusUpdatedEmail;
use Modules\Booking\Models\Booking;

class BookingNotificationService
{
    public function __construct(
        protected MailService $mailService
    ) {}

    public function sendCompletedEmail(Booking $booking): void
    {
        $this->withLocale($booking, function () use ($booking) {

            $creator = $booking->creator;

                if ($creator->email) {
                    $this->mailService->send(
                        $creator->email,
                        new StatusUpdatedEmail($booking, 'customer')
                    );
                }
        });
    }

    public function sendCancelledEmail(Booking $booking, bool $isBaseAdmin): void
    {
        $this->withLocale($booking, function () use ($booking, $isBaseAdmin) {

            if ($isBaseAdmin) {
                if ($booking->creator?->email) {
                    $this->mailService->send(
                        $booking->creator->email,
                        new StatusUpdatedEmail($booking, 'customer')
                    );
                }

                return;
            }

            $booking->loadMissing('hotel');

            if ($booking->hotel?->admin_base) {
                if ($admin = User::find($booking->hotel->admin_base)) {
                    $this->mailService->send(
                        $admin->email,
                        new StatusUpdatedEmail($booking, 'admin', null, $admin)
                    );
                }
            }

            if (!empty($booking->email)) {
                $this->mailService->send(
                    $booking->email,
                    new StatusUpdatedEmail($booking, 'customer')
                );
            }
        });
    }
    private function withLocale(Booking $booking, \Closure $callback): void
    {
        $old = app()->getLocale();

        if ($locale = $booking->getMeta('locale')) {
            app()->setLocale($locale);
        }

        $callback();

        app()->setLocale($old);
    }
}
