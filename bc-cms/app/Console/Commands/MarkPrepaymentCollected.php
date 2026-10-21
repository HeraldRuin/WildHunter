<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\Payment;

class MarkPrepaymentCollected extends Command
{
    protected $signature = 'booking:mark-prepayment-collected {booking_id : ID брони}';

    protected $description = 'Пропустить оплату и перевести бронь из сбора предоплаты в «предоплата собрана»';

    public function handle(): int
    {
        $bookingId = (int) $this->argument('booking_id');

        if ($bookingId < 1) {
            $this->error('ID брони должен быть больше 0.');

            return self::FAILURE;
        }

        $result = DB::transaction(function () use ($bookingId): ?string {
            $booking = Booking::query()->lockForUpdate()->find($bookingId);

            if (!$booking) {
                return 'Бронь не найдена.';
            }

            if ($booking->status !== Booking::PREPAYMENT_COLLECTION) {
                return 'Бронь не на этапе сбора предоплаты (текущий статус: '.$booking->status.').';
            }

            $masterId = $booking->masterHunterRowId();

            if (!$masterId) {
                return 'У брони нет главного охотника.';
            }

            $invitations = BookingHunterInvitation::query()
                ->where('booking_hunter_id', $masterId)
                ->where('status', BookingHunterInvitation::STATUS_ACCEPTED)
                ->lockForUpdate()
                ->get();

            if ($invitations->isEmpty()) {
                return 'Нет принятых охотников.';
            }

            if ($invitations->contains(fn (BookingHunterInvitation $invitation): bool => !$invitation->hunter_id)) {
                return 'У принятого охотника не заполнен hunter_id.';
            }

            $amount = $booking->getAmountPerPerson();

            foreach ($invitations as $invitation) {
                $this->markHunterPaid($booking, $invitation, $amount);
            }

            $booking->prepayment_paid = true;
            $booking->status = Booking::FINISHED_PREPAYMENT;
            $booking->save();

            return null;
        }, 3);

        if (is_string($result)) {
            $this->error($result);

            return self::FAILURE;
        }

        $booking = Booking::query()->find($bookingId);

        if ($booking) {
            event(new BookingUpdatedEvent($booking));
        }

        $this->info('Бронь '.$bookingId.' переведена в статус «предоплата собрана».');

        return self::SUCCESS;
    }

    private function markHunterPaid(Booking $booking, BookingHunterInvitation $invitation, float $amount): void
    {
        $payment = Payment::query()
            ->where('booking_id', $booking->id)
            ->where('create_user', $invitation->hunter_id)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        $invitation->update([
            'prepayment_paid' => 1,
            'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_PAID,
        ]);

        if ($payment?->status === Payment::PAID) {
            return;
        }

        if ($payment?->status !== Payment::PROCESSING) {
            $payment = new Payment();
            $payment->booking_id = $booking->id;
            $payment->object_id = $booking->object_id;
            $payment->object_model = $booking->type;
            $payment->create_user = $invitation->hunter_id;
            $payment->attempts = 0;
        }

        $payment->payment_gateway = 'paykeeper';
        $payment->status = Payment::PAID;
        $payment->amount = $amount;
        $payment->payment_url = null;
        $payment->invoice_id = null;
        $payment->expires_at = null;
        $payment->last_checked_at = now();
        $payment->next_check_at = null;
        $payment->logs = json_encode(['source' => 'booking:mark-prepayment-collected'], JSON_UNESCAPED_UNICODE);
        $payment->save();
    }
}
