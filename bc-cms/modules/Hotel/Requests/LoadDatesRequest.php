<?php

namespace Modules\Hotel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadDatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'for_single' => 'nullable|boolean',
            'start' => 'required|date',
            'end' => 'required|date',
        ];
    }
}
