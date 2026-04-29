<?php

namespace Modules\Booking\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\User;
use Illuminate\Support\Collection;
use Modules\Booking\DTO\ReplaceHunterData;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingUserService
{
    public function __construct(protected BookingCollectionService $bookingCollectionService){}

    /**
     * @throws NotFoundException
     */
    public function changeUser(Booking $booking, int $userId): array
    {
        $booking->create_user = $userId;
        $booking->save();

        $user = User::find($userId);

        if (!$user) {
            throw new NotFoundException(
                errorCode: 'user_not_found',
                domain: 'booking'
            );
        }

        $bookingHunter = $booking->masterHunter;

        if (!$bookingHunter) {
            throw new NotFoundException(
                errorCode: 'master_not_found',
                domain: 'booking'
            );
        }

        $bookingHunter->invited_by = $userId;
        $bookingHunter->is_master = $user->hasRole('hunter');
        $bookingHunter->creator_role = $user->role->code ?? null;
        $bookingHunter->save();

        return [
            'code' => 'customer_changed',
        ];
    }

    public function searchHunters(string $query, int $bookingId = null)
    {
        $users = User::query()
            ->whereNotIn('role_id', function ($q) {
                $q->select('id')
                    ->from('core_roles')
                    ->whereIn('code', ['administrator']);
            })
            ->where(function ($q) use ($query) {
                $q->where('user_name', 'LIKE', $query.'%')
                    ->orWhere('first_name', 'LIKE', $query.'%')
                    ->orWhere('last_name', 'LIKE', $query.'%')
                    ->orWhere('email', 'LIKE', $query.'%')
                    ->orWhere('id', 'LIKE', $query.'%');
            })
            ->limit(10)
            ->get(['id','user_name','first_name','last_name','email','phone']);

        $users->each(function ($user) {
            $user->invited = false;
            $user->invitation_status = null;
        });

        if ($bookingId) {
            $this->applyInvitationStatus($users, $bookingId);
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
     * @throws \Exception
     */
    public function replaceHunter(Booking $booking, ReplaceHunterData $data): array
    {
        if ($booking->invitationUser($data->newHunterId)) {
            throw new ConflictException(
                errorCode: 'hunter_already_in_booking',
                domain: 'booking'
            );
        }

        $invitation = $booking->invitationUser($data->oldHunterId);

        if (!$invitation) {
            throw new NotFoundException(
                errorCode: 'booking_invitation_not_found',
                domain: 'booking'
            );
        }

            $invitation->hunter_id = $data->newHunterId;
            $invitation->email = !empty($data->email) ? $data->email : null;
            $invitation->save();

            if ($booking->shouldCheckPrepayment()){
                $this->bookingCollectionService->checkPrepaymentAllPaid($booking, $invitation);
            }

        return [
            'code' => 'hunter_replace',
            'data' => [
                'hunter' => [
                    'id' => $data->newHunterId,
                    'email' => $invitation->email?? null,
                    'first_name' => $data->firstName?? null,
                    'last_name' => $data->lastName?? null,
                    'user_name' => $data->userNik?? null,
                    'is_external' => $data->isExternal ?? false,
                    'invitation_status' => $data->invitationStatus ?? BookingHunterInvitation::STATUS_ACCEPTED,
                    'prepayment_paid' => (bool) ($invitation->prepayment_paid ?? false),
                    'prepayment_paid_status' => $invitation->prepayment_paid_status,
                    'prepayment_badge' => $invitation->prepayment_badge,
                ],
            ],
        ];
    }
}
