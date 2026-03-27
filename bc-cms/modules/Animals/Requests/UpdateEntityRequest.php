<?php

namespace Modules\Animals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => 'required|string|in:preparation,trophy,finest',
            'price' => 'nullable|numeric|min:0',
        ];

        // Динамически проверяем ID в зависимости от type
        switch ($this->input('type')) {
            case 'preparation':
                $rules['id'] = 'required|exists:bc_animal_preparations,id';
                break;
            case 'trophy':
                $rules['id'] = 'required|exists:bc_animal_trophies,id';
                break;
            case 'fine':
                $rules['id'] = 'required|exists:bc_animal_fines,id';
                break;
        }

        return $rules;
    }
}
