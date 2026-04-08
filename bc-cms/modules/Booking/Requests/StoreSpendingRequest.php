<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpendingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price'      => 'required|integer',
            'hunter_id'     => 'required|integer',
            'comment'     => 'required|string',
        ];
    }
}
