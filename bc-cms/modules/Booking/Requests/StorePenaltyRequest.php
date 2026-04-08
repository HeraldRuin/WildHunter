<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenaltyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_id' => 'required|integer|exists:bc_animals,id',
            'type'      => 'required|string',
            'hunter_id'     => 'required|integer',
            'penalty_id'     => 'required|integer|exists:bc_animal_fines,id',
        ];
    }
}
