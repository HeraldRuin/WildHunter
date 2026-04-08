<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'count'     => 'required|integer|min:1',
        ];
    }
}
