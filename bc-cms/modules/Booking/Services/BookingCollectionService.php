<?php

namespace Modules\Booking\Services;

use App\User;
use Modules\Animals\Models\Animal;
use Modules\Booking\Events\BookingFinishEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\BookingRoomPlace;

class BookingCollectionService
{
    protected BookingTimerService $bookingTimerService;

    public function __construct(BookingTimerService $bookingTimerService)
    {
        $this->bookingTimerService = $bookingTimerService;
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

    public function updateStatusIfAllPlacesSelected(Booking $booking): void
    {
        $paidCount = $booking->countAcceptedAndPaidHunters();
        $alreadyHasPlace = BookingRoomPlace::where('booking_id', $booking->id)->count() === $paidCount;

        if ($paidCount > 0 && $paidCount === $alreadyHasPlace) {
            $booking->status = Booking::FINISHED_BED;
            $booking->save();
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

    public function finishCollection(Booking $booking, $user): void
    {
        $this->checkAccess($booking, $user);

        $this->checkStatus($booking);

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
    }

    private function checkAccess(Booking $booking, User $user): void
    {
        $isBaseAdmin = $user->hasRole('baseadmin')|| $user->hasPermission('baseAdmin_dashboard_access');

        if (!$isBaseAdmin && $booking->create_user !== $user->id) {
            throw new \DomainException(__("You don't have access."));
        }
    }
    private function checkStatus(Booking $booking): void
    {
        if ($booking->status !== Booking::START_COLLECTION) {
            throw new \DomainException("Сбор охотников не начат или уже завершён");
        }
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

    private function checkConfirmed($booking, $confirmed, int $requiredHunters): void
    {
        if ($booking->type === Booking::BookingTypeHotel)
        {
            return;
        }

        if ($confirmed->count() < $requiredHunters) {
            throw new \DomainException(
                __('Не все приглашённые участники подтвердили приглашение...')
            );
        }
    }
}
