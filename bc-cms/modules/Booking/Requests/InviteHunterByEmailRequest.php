<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteHunterByEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Не передан email',
            'email.email'    => 'Некорректный email',
        ];
    }
}
