<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // MÓDULO 2: reglas como GuardarMascotaRequest
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'categoria_prestacion_id' => ['nullable', 'exists:categorias_prestaciones,id'],
            'imagen_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
            'sucursal_id.required' => 'La sucursal es obligatoria.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'imagen_url.image' => 'La imagen debe ser una imagen.',
            'imagen_url.mimes' => 'La imagen debe ser de tipo jpeg, png, jpg o webp.',
            'imagen_url.max' => 'La imagen no puede exceder los 2MB.',
        ];
    }
}
