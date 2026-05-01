<?php

namespace Modules\Booking\Application\UseCases\Hunters;

use Modules\Booking\Models\BookingHunterInvitation;

readonly class HunterResource
{
    public function __construct(private ReplaceHunterResult $result) {}

    public function toArray(): array
    {
        $invitation = $this->result->invitation;
        $dto = $this->result->dto;

        return [
            'hunter' => [
                'id' => $dto->newHunterId,
                'email' => $invitation->email?? null,
                'first_name' => $dto->firstName?? null,
                'last_name' => $dto->lastName?? null,
                'user_name' => $dto->userNik?? null,
                'is_external' => $dto->isExternal ?? false,
                'invitation_status' => $dto->invitationStatus ?? BookingHunterInvitation::STATUS_ACCEPTED,
                'prepayment_paid' => (bool) ($invitation->prepayment_paid ?? false),
                'prepayment_paid_status' => $invitation->prepayment_paid_status,
                'prepayment_badge' => $invitation->prepayment_badge,
            ],
        ];
    }
}
