<?php

namespace Modules\Booking\Application\UseCases\Hunters;

use Modules\Booking\DTO\ReplaceHunterData;

class ReplaceHunterResult
{
    public function __construct(
        public $invitation,
        public ReplaceHunterData $dto,
    ) {}
}
