<?php

namespace Modules\Attendance\Application\UseCases\Addetionals;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdditionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'html' => view(
                'Additional::frontend.partials.additional-row',
                ['additional' => $this->resource]
            )->render(),
        ];
    }
}
