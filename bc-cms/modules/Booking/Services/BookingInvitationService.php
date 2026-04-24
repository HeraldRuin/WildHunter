<?php

namespace Modules\Booking\Services;

use App\Service\MailService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Emails\HunterMessageEmail;
use Modules\Booking\Events\HunterInvitationAcceptedEvent;
use Modules\Booking\Events\HunterInvitedEvent;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunter;
use Modules\Booking\Models\BookingHunterInvitation;

class BookingInvitationService
{
    public function __construct(
        protected MailService $mailService
    ) {}
    public function getInvitedHunters(Booking $booking, ?int $currentUserId): array
    {
        $invitations = $booking->getAllInvitations()
            ->whereNotIn('status', ['removed']);

        return $invitations->map(function ($invitation) use ($currentUserId) {

            $hunter = $invitation->hunter;
            $isCurrentUser = $hunter && $hunter->id === $currentUserId;

            if ($hunter) {
                return [
                    'id' => $hunter->id,
                    'name' => $hunter->display_name ?? null,
                    'user_name' => $hunter->user_name,
                    'first_name' => $hunter->first_name,
                    'last_name' => $hunter->last_name,
                    'email' => $hunter->email,
                    'phone' => $hunter->phone,
                    'invited' => true,
                    'is_self' => $isCurrentUser,
                    'invitation_status' => $invitation->status,
                    'prepayment_paid' => (bool) ($invitation->prepayment_paid ?? false),
                    'prepayment_paid_status' => $invitation->prepayment_paid_status,
                    'prepayment_badge' => $invitation->prepayment_badge,
                ];
            }

            if (!$hunter && $invitation->email) {
                return [
                    'id' => null,
                    'name' => $invitation->email,
                    'user_name' => null,
                    'first_name' => '',
                    'last_name' => '',
                    'email' => $invitation->email,
                    'phone' => null,
                    'invited' => true,
                    'is_self' => $isCurrentUser,
                    'invitation_status' => $invitation->status,
                    'is_external' => true,
                    'prepayment_paid' => (bool) ($invitation->prepayment_paid ?? false),
                    'prepayment_paid_status' => $invitation->prepayment_paid_status,
                    'prepayment_badge' => $invitation->prepayment_badge,
                ];
            }

            return null;
        })
            ->filter()
            ->values()
            ->toArray();
    }


    public function invite(Booking $booking, int $hunterId): BookingHunterInvitation
    {
        $hunter = User::findOrFail($hunterId);

        $bookingHunter = $booking->masterHunter()->firstOrFail();

        return DB::transaction(function () use ($booking, $hunter, $hunterId, $bookingHunter) {

            $invitation = BookingHunterInvitation::updateOrCreate(
                [
                    'booking_hunter_id' => $bookingHunter->id,
                    'hunter_id' => $hunterId,
                ],
                [
                    'invited' => true,
                    'status' => 'pending',
                    'invited_at' => now(),
                    'invitation_token' => $booking->code . '-' . $hunterId,
                ]
            );

            $this->sendEmail($booking, $hunter, $hunterId);
            $this->dispatchEvent($booking, $hunterId);

            return $invitation;
        });
    }
    private function sendEmail(Booking $booking, User $hunter, int $hunterId): void
    {
        if (empty($hunter->email) || $hunterId === $booking->create_user) {
            return;
        }

        $this->mailService->send(
            $hunter->email,
            new HunterMessageEmail(
                $booking,
                $hunter,
                __('Вас пригласили в сбор для брони №:id', ['id' => $booking->id]),
                true
            )
        );
    }
    private function dispatchEvent(Booking $booking, int $hunterId): void
    {
        try {
            event(new HunterInvitedEvent($booking, $hunterId));
        } catch (\Throwable $e) {
            Log::error('HunterInvitedEvent failed', [
                'booking_id' => $booking->id,
                'hunter_id' => $hunterId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // Удаляем все приглашения охотников, кроме мастера охотника (того, кто приглашал)
    public function deleteInvitations(Booking $booking): void
    {
        $masterHunter = $booking->masterHunter;

        if (!$masterHunter) {
            throw new \DomainException('Не найден мастер охотник этой брони');
        }

        $booking->getInvitationsExceptMaster()->each(fn($invitation) => $invitation->delete());
    }

    public function inviteByEmail(Booking $booking, string $email): BookingHunterInvitation
    {
        $email = trim($email);

        $hunter = User::where('email', $email)->first();

        if ($hunter) {
            return $this->invite($booking, $hunter->id);
        }

        $bookingHunter = $booking->bookingHunter()->firstOrFail();
        $invitationMaster = BookingHunterInvitation::where('booking_hunter_id', $bookingHunter->id)
            ->where('email', $email)
            ->whereNull('hunter_id')
            ->first();

        $invitation = $this->createOrUpdateEmailInvitation($invitationMaster, $booking, $email);

        $this->sendEmailIfNeeded($booking, $email);

        return $invitation;
    }

    private function createOrUpdateEmailInvitation($bookingHunter, Booking $booking, string $email): BookingHunterInvitation
    {
        return BookingHunterInvitation::updateOrCreate(
            [
                'booking_hunter_id' => $bookingHunter->id,
                'email' => $email,
                'hunter_id' => null,
            ],
            [
                'invited' => true,
                'status' => 'pending',
                'invited_at' => now(),
                'invitation_token' => $booking->code . '-' . md5($email),
            ]
        );
    }

    private function sendEmailIfNeeded(Booking $booking, string $email): void
    {
        // НЕ отправляем письмо создателю брони - он уже приглашен автоматически
        $creatorEmail = optional(User::find($booking->create_user))->email;

        if ($email === $creatorEmail) {
            return;
        }

        $this->mailService->send(
            $email,
            new HunterMessageEmail(
                $booking,
                $this->makeVirtualUser($email),
                __('Вас пригласили в сбор для брони №:id', ['id' => $booking->id]),
                true
            )
        );
    }

    private function makeVirtualUser(string $email): User
    {
        $user = new User();
        $user->id = 0;
        $user->email = $email;
        $user->name = $email;

        return $user;
    }

    public function accept(Booking $booking, int $userId): void
    {
        $invitation = $booking->getCurrentUserInvitation();

        if (!$invitation) {
            throw new \DomainException('Invitation not found');
        }

        $invitation->status = 'accepted';
        $invitation->accepted_at = now();
        $invitation->save();

        event(new HunterInvitationAcceptedEvent($booking, $userId));
    }

    public function handleCodeAccess($code, $authUser): void
    {
        if ($code) {
            $booking = Booking::where('code', $code)->first();

            if (!$booking) {
                abort(403);
            }

            $masterBookingHunter = BookingHunter::where('booking_id', $booking->id)->where('is_master', true)->first();
            if ($masterBookingHunter) {
                $exists = BookingHunterInvitation::where('booking_hunter_id', $masterBookingHunter->id)
                    ->where('hunter_id', $authUser->id)
                    ->exists();

                if (!$exists) {
                    BookingHunterInvitation::create([
                        'booking_hunter_id' => $masterBookingHunter->id,
                        'hunter_id' => $authUser->id,
                        'invited' => true
                    ]);
                }
            }
        }
    }
}
