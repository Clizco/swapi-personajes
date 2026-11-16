<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarPersonajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Los duplicados se revisan en PersonajeService::importarDesdeSwapi().
    public function rules(): array
    {
        return [
            'swapi_id' => ['required', 'integer', 'between:1,83'],
        ];
    }

    public function attributes(): array
    {
        return ['swapi_id' => 'ID de SWAPI'];
    }
}
