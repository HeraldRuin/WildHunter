<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrophyRequest extends FormRequest
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
            'count'     => 'required|integer|min:1',
            'trophy_id'     => 'required|integer|exists:bc_animal_trophies,id',
        ];
    }
}
