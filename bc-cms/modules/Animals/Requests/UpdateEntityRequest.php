<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Animals\Models\Animal;

class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => 'required|string|in:preparations,trophies,fines',
            'price' => 'nullable|numeric|min:0',
        ];

        // Динамически проверяем ID в зависимости от type
        switch ($this->input('type')) {
            case Animal::SERVICE_PREPARATIONS:
                $rules['id'] = 'required|exists:bc_animal_preparations,id';
                break;
            case Animal::SERVICE_TROPHIES:
                $rules['id'] = 'required|exists:bc_animal_trophies,id';
                break;
            case Animal::SERVICE_FINES:
                $rules['id'] = 'required|exists:bc_animal_fines,id';
                break;
        }

        return $rules;
    }
}
