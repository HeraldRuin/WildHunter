<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Application\UseCases\Hunters\ReplaceHunterResult;
use Modules\Booking\DTO\ReplaceHunterData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Repositories\BookingHunterRepository;
use Modules\Booking\Repositories\BookingRepository;
use Modules\User\Repositories\UserRepository;

class BookingUserService
{
    public function __construct(
        protected BookingCollectionService $bookingCollectionService,
        private UserRepository $userRepository,
        private BookingRepository $bookingRepository,
        private BookingHunterRepository $bookingHunterRepository) {}

    public function changeUser(Booking $booking, int $userId): array
    {
        return DB::transaction(function () use ($booking, $userId) {

            $user = $this->userRepository->findHunterById($userId);

            if (!$user) {
                throw new NotFoundException(
                    errorCode: 'user_not_found',
                    domain: 'booking'
                );
            }

            $booking->changeCreator($user);
            $booking->changeMasterHunterCreator($user);

            $this->bookingRepository->save($booking);
            $this->bookingHunterRepository->save($booking->masterHunter);

            return [
                'code' => 'customer_changed',
            ];
        });


    }

    public function searchHunters(string $query, int $bookingId = null): Collection
    {
        $users = $this->userRepository->searchHuntersByQuery($query);

//        $users->each(function ($user) {
//            $user->invited = false;
//            $user->invitation_status = null;
//        });

        if ($bookingId) {
//            $this->applyInvitationStatus($users, $bookingId);
        }

        return $users;
    }

    private function applyInvitationStatus($users, int $bookingId): void
    {
        $invitations = BookingHunterInvitation::query()
            ->whereHas('bookingHunter', function ($q) use ($bookingId) {
                $q->where('booking_id', $bookingId);
            })
            ->whereIn('hunter_id', $users->pluck('id'))
            ->whereNull('deleted_at')
            ->get(['hunter_id', 'status']);

        foreach ($users as $user) {
            $invitation = $invitations->firstWhere('hunter_id', $user->id);

            if ($invitation) {
                if ($invitation->status === 'declined') {
                    $user->invited = false;
                    $user->invitation_status = 'declined';
                } else {
                    $user->invited = true;
                    $user->invitation_status = $invitation->status;
                }
            }
        }
    }


    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function replaceHunter(Booking $booking, ReplaceHunterData $data): ReplaceHunterResult
    {
        $invitation = $booking->replaceHunter(
            oldHunterId: $data->oldHunterId,
            newHunterId: $data->newHunterId,
            email: $data->email
        );

        $invitation->save();

        if ($booking->shouldCheckPrepayment()){
             $this->bookingCollectionService->checkPrepaymentAllPaid($booking, $invitation);
        }

        return new ReplaceHunterResult(
            invitation: $invitation,
            dto: $data
        );
    }
}
