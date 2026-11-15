<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportarPersonajeRequest extends FormRequest
{
    public const SWAPI_ID_MINIMO = 1;

    public const SWAPI_ID_MAXIMO = 83;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'swapi_id' => [
                'required',
                'integer',
                'between:'.self::SWAPI_ID_MINIMO.','.self::SWAPI_ID_MAXIMO,
                Rule::unique('personajes', 'swapi_id')->withoutTrashed(),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['swapi_id' => 'ID de SWAPI'];
    }

    public function messages(): array
    {
        return [
            'swapi_id.unique' => 'Este personaje de SWAPI ya fue importado.',
        ];
    }
}
