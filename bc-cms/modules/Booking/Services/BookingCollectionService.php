<?php

namespace Modules\Booking\Services;

use App\Exceptions\BaseException;
use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Service\MailService;
use App\User;
use Illuminate\Support\Facades\DB;
use Modules\Animals\Models\Animal;
use Modules\Booking\Emails\HunterMessageEmail;
use Modules\Booking\Emails\StatusUpdatedEmail;
use Modules\Booking\Events\BookingFinishEvent;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;

readonly class BookingCollectionService
{
    public function __construct(private BookingTimerService $bookingTimerService, private BookingInvitationService $bookingInvitationService, protected MailService $mailService, private BookingStatusService $bookingStatusService, private BookingAccessService $bookingAccessService)
    {
    }
    public function checkPrepaymentAllPaid(Booking $booking, ?BookingHunterInvitation $invitation = null): void
    {
        $unpaidHunters = $booking->unpaidInvitationsOfHunters();

        if ($unpaidHunters->isEmpty()) {
            $this->bookingTimerService->startBedTimer($booking);
        }else {
            $invitation->prepayment_paid_status = BookingHunterInvitation::PREPAYMENT_PENDING;
            $invitation->save();
            $this->bookingTimerService->startPaidTimer($booking);
        }
    }
    public function markAllPendingAsUnpaid(Booking $booking): void
    {
        $unpaidHunters = $booking->pendingInvitationsOfHunters();

        if ($unpaidHunters->isNotEmpty()) {
            $unpaidHunters->each(function (BookingHunterInvitation $invitation) {
                $invitation->prepayment_paid_status = BookingHunterInvitation::PREPAYMENT_UNPAID;
                $invitation->save();
            });
        }
    }

    /**
     * @throws ConflictException
     */
    public function confirmBooking(Booking $booking): array
    {
        if ($booking->status !== 'processing') {
            throw new ConflictException(
                errorCode: 'booking_not_confirmable',
                domain: 'booking'
            );
        }

        $booking->status = Booking::CONFIRMED;
        $booking->save();

        event(new BookingUpdatedEvent($booking));

        return [
            'code' => 'booking_confirmed',
        ];
    }

    /**
     * @throws ConflictException
     */
    public function finishCollection(Booking $booking, $user): array
    {
        $this->checkAccess($booking, $user);

        $this->checkCollectionStatus($booking);

        $allInvitations = $booking->getAllInvitations();

        $notConfirmedInvitations = $allInvitations->filter(function ($invitation) {
            return !in_array($invitation->status, ['accepted', 'declined', 'removed']);
        });

        $confirmedInvitations = $allInvitations->filter(function ($invitation) {
            return $invitation->status === 'accepted';
        });

        $acceptedInvitations = $allInvitations->filter(function ($invitation) {
            return !in_array($invitation->status, ['declined', 'removed']);
        });

        $animalName = '';
        $requiredHunters = 1;

        if ($booking->animal_id && $booking->hotel_id) {
            $animal = Animal::find($booking->animal_id);
            if ($animal) {
                $animalName = $booking->getAnimalName();
                $requiredHunters = $booking->getRequiredHuntersCount();
            }
        } else {
            if ($booking->type === 'hotel') {
                $requiredHunters = (int) ($booking->total_guests ?? 0);
            } elseif ($booking->type === 'animal' || $booking->type === 'hotel_animal') {
                $requiredHunters = (int) ($booking->total_hunting ?? 0);
            }

            if ($requiredHunters <= 0) {
                $requiredHunters = 1;
            }

            if ($booking->animal_id) {
                $animal = Animal::find($booking->animal_id);
                if ($animal) {
                    $animalName = $booking->getAnimalName();
                }
            }
        }

        $this->checkMinAnimal($booking, $acceptedInvitations, $requiredHunters, $animalName);

        $this->checkConfirmed($booking, $acceptedInvitations, $requiredHunters);


        if ($booking->type === 'animal') {
            $booking->status = Booking::FINISHED_COLLECTION;
        } else {
            $timerHour = $this->bookingTimerService->getTimerHours($booking, 'paid');
            $booking->status = Booking::PREPAYMENT_COLLECTION;
            $this->bookingTimerService->startTimer($booking->id, $timerHour, 'paid', ['collection']);
        }

        $booking->save();
        event(new BookingFinishEvent($booking));

        return [
            'code' => 'gathering_has_completed'
        ];
    }

    /**
     * @throws ForbiddenException
     */
    public function cancelCollection(Booking $booking, $user): array
    {
        $this->checkAccess($booking, $user);
        $this->checkCancelStatus($booking);

        DB::transaction(function () use ($booking) {

            $this->rollbackStatusAndClearTimers($booking);
            $this->notifyHunters($booking);
            $this->bookingInvitationService->deleteInvitations($booking);
            $this->notifyCreator($booking);

            event(new BookingUpdatedEvent($booking));
        });

        return [
            'code' => 'hunter_gathering_cancelled'
        ];
    }

    /**
     * @throws ForbiddenException
     */
    private function checkAccess(Booking $booking, User $user): void
    {
        $this->bookingAccessService->ensureCanAccessBooking();
    }
    private function checkCollectionStatus(Booking $booking): void
    {
        if ($booking->status !== Booking::START_COLLECTION) {
            throw new \DomainException("Сбор охотников не начат или уже завершён");
        }
    }
    private function checkCancelStatus(Booking $booking): void
    {
        if (in_array($booking->status, [Booking::CANCELLED, Booking::COMPLETED], true)) {
            throw new \DomainException(__('This booking cannot be modified'));
        }
    }
    private function rollbackStatusAndClearTimers(Booking $booking): void
    {
        if (in_array($booking->status, [Booking::START_COLLECTION, Booking::FINISHED_COLLECTION], true)) {
            $booking->status = Booking::CONFIRMED;

            $this->bookingTimerService->clearAllTimers($booking->id);

            $booking->save();
        }
    }
    private function notifyHunters(Booking $booking): void
    {
        foreach ($booking->getAllInvitations() as $invitation) {
            $hunter = $invitation->hunter;

            if (!$hunter || empty($hunter->email)) {
                continue;
            }

            $this->mailService->send(
                $hunter->email,
                new HunterMessageEmail($booking, $hunter, translate_successes('booking', 'hunter_gathering_cancelled'), false)
            );
        }
    }
    private function notifyCreator(Booking $booking): void
    {
        $this->withLocale($booking, function () use ($booking) {

            $creator = $booking->creator;

            if (!$creator || empty($creator->email)) {
                return;
            }

                $this->mailService->send(
                    $creator->email,
                    new StatusUpdatedEmail($booking, 'customer', translate_successes('booking', 'hunter_gathering_cancelled'))
                );
        });
    }
    private function checkMinAnimal($booking, $accepted, int $requiredHunters, string $animalName): void
    {
        if ($booking->type === Booking::BookingTypeHotel)
        {
            return;
        }

        if ($accepted->count() < $requiredHunters) {
            throw new \DomainException(
                __(' кол-во охотников для :animal :count', [
                    'animal' => $animalName ?: __('животного'),
                    'count' => $requiredHunters
                ])
            );
        }
    }

    /**
     * @throws ConflictException
     */
    private function checkConfirmed($booking, $confirmed, int $requiredHunters): void
    {
        if ($booking->type === Booking::BookingTypeHotel)
        {
            return;
        }

        if ($confirmed->count() < $requiredHunters) {
            throw new ConflictException(
                errorCode: 'not_all_hunters_confirmed',
                domain: 'booking'
            );
        }
    }

    /**
     * @throws ConflictException
     */
    public function complete(Booking $booking): array
    {
        $this->bookingStatusService->canChangeBookingState($booking);

        $booking->status = Booking::COMPLETED;
        $booking->save();

        event(new BookingUpdatedEvent($booking));

        return [
            'code' => 'booking_completed',
        ];
    }

    /**
     * @throws ConflictException
     * @throws BaseException
     */
    public function cancel(Booking $booking): array
    {
        $this->bookingStatusService->canChangeBookingState($booking);

        $this->markCancelled($booking);
        $this->cleanupHunterInvitations($booking);

        return [
            'code' => 'booking_cancelled',
        ];
    }

    public function markCancelled(Booking $booking): void
    {
        $booking->status = Booking::CANCELLED;
        $booking->save();

        $booking->skip_status_email = true;
        event(new BookingUpdatedEvent($booking));
    }

    /**
     * Удаляем все приглашения охотников, кроме мастера охотника (того, кто приглашал)
     * @throws BaseException
     */
    private function cleanupHunterInvitations(Booking $booking): void
    {
            $id = $booking->masterHunter()->pluck('id');

            if ($id->isEmpty()) {
                return;
            }

            BookingHunterInvitation::whereIn('booking_hunter_id', $id)->forceDelete();
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
