<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeUserBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Не передан user_id',
            'user_id.integer'  => 'user_id должен быть числом',
        ];
    }
}
