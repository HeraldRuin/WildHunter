<?php

namespace Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdditionalRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'count' => 'nullable|integer|min:0',
            'calculation_type' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'calculation_type.required' => 'Поле "тип расчёта" обязательно для заполнения.',
            'name.required' => 'Поле "название" обязательно для заполнения.',
            'price.required' => 'Поле "цена" обязательно для заполнения.',
            'count.integer' => 'Поле "количество" должно быть числом.',
        ];
    }
}
