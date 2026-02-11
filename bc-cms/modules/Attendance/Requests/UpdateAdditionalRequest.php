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
        ];
    }
}
