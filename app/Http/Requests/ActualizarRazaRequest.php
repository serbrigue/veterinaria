<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarRazaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'especie_id' => ['required', 'integer', 'exists:especies,id'],
            'imagen_url' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la raza es obligatorio.',
            'nombre.max' => 'El nombre de la raza no puede exceder los 255 caracteres.',
            'especie_id.required' => 'La especie de la raza es obligatoria.',
            'especie_id.exists' => 'La especie seleccionada no es válida.',
        ];
    }
}
