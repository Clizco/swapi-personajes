<?php

namespace App\Http\Requests;

use App\Models\Personaje;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only([
            'nombre', 'color_cabello', 'color_ojos', 'planeta_origen',
        ]))->map(fn ($valor) => is_string($valor) ? trim($valor) : $valor)->all());
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:100'],
            'altura' => ['nullable', 'integer', 'min:1', 'max:300'],
            'masa' => ['nullable', 'integer', 'min:1', 'max:500'],
            'color_cabello' => ['nullable', 'string', 'max:100'],
            'color_ojos' => ['nullable', 'string', 'max:100'],
            'genero' => ['required', Rule::in(Personaje::GENEROS)],
            'planeta_origen' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'color_cabello' => 'color de cabello',
            'color_ojos' => 'color de ojos',
            'genero' => 'género',
            'planeta_origen' => 'planeta de origen',
        ];
    }

    public function messages(): array
    {
        return [
            'genero.in' => 'El género debe ser masculino, femenino o n/a.',
            'altura.min' => 'La altura debe ser un número entero positivo.',
            'masa.min' => 'La masa debe ser un número entero positivo.',
        ];
    }
}
