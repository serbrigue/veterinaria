<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarVeterinarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            'foto_perfil_url' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'especialidad_id.required' => 'La especialidad es obligatoria.',
            'especialidad_id.exists' => 'La especialidad seleccionada no es válida.',
            'sucursal_id.required' => 'La sucursal es obligatoria.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'La foto debe ser en formato jpeg, png, jpg o webp.',
            'foto.max' => 'La foto no puede superar los 2 MB.',
        ];
    }
}
