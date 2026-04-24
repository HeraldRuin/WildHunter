<?php

namespace Modules\Booking\Services;

use App\User;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingUserService
{
    public function changeUser(Booking $booking, int $userId): void
    {
        $booking->create_user = $userId;
        $booking->save();

        if (!$userId) {
            return;
        }

        $user = User::find($userId);

        if (!$user) {
            return;
        }

        $bookingHunter = BookingHunter::where('booking_id', $booking->id)->first();

        if (!$bookingHunter) {
            return;
        }

        $bookingHunter->invited_by = $userId;
        $bookingHunter->is_master = $user->hasRole('hunter');
        $bookingHunter->creator_role = $user->role->code ?? null;
        $bookingHunter->save();
    }

    public function searchHunters(string $query, int $bookingId = null): array
    {
        $users = User::query()
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

        return $users->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->display_name,
            'user_name' => $user->user_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'invited' => $user->invited,
            'invitation_status' => $user->invitation_status,
        ]);
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
}
