<?php

namespace Modules\Booking\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddetionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'addetional'    => 'required|string',
            'addetional_id' => 'required|integer|exists:bc_addetional_prices,id',
            'count'     => 'required|integer|min:1',
            'hunter_id'     => 'required|integer',
        ];
    }
}
