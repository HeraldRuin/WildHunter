<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnimalPricePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'amount'     => ['min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'start_date.required' => 'Укажите дату начала периода',
            'end_date.required' => 'Укажите дату окончания периода',
            'end_date.after_or_equal' => 'Дата окончания не может быть раньше начала',
            'amount.min' => 'Сумма не может быть отрицательной',
        ];
    }
}
