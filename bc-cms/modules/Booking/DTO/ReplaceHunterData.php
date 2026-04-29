<?php

namespace Modules\Booking\DTO;


use Illuminate\Http\Request;

class ReplaceHunterData
{
    public function __construct(
        public int $newHunterId,
        public ?string $email,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $userNik,
        public ?string $invitationStatus,
        public bool $isExternal,
        public int $oldHunterId
    ) {}

    public static function fromRequest(Request $request): self
    {
        $hunter = $request->input('hunter', []);

        return new self(
            newHunterId: (int) $hunter['id'],
            email: $hunter['email'] ?? null,
            firstName: $hunter['first_name'] ?? null,
            lastName: $hunter['last_name'] ?? null,
            userNik: $hunter['user_name'] ?? null,
            invitationStatus: $hunter['invitation_status'] ?? null,
            isExternal: (bool) ($hunter['is_external'] ?? false),
            oldHunterId: (int) $request->input('old_hunter_id')
        );
    }
}
