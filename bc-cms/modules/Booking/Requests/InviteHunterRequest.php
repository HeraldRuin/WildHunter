<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteHunterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hunter_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'hunter_id.required' => 'Не передан hunter_id',
            'hunter_id.integer'  => 'hunter_id должен быть числом',
            'hunter_id.exists'   => 'Пользователь не найден',
        ];
    }
}
