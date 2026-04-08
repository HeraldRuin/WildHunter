<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreparationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_id' => 'required|integer|exists:bc_animals,id',
            'count'     => 'required|integer|min:1',
            'preparation_id'     => 'required|integer|exists:bc_animal_preparations,id',
        ];
    }
}
