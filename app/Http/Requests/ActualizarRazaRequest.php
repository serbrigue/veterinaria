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
            'imagen_url' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la raza es obligatorio.',
            'nombre.max' => 'El nombre de la raza no puede exceder los 255 caracteres.',
            'especie_id.required' => 'La especie de la raza es obligatoria.',
            'especie_id.exists' => 'La especie seleccionada no es válida.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'La foto debe ser en formato jpeg, png, jpg o webp.',
            'foto.max' => 'La foto no puede superar los 2 MB.',
        ];
    }
}
