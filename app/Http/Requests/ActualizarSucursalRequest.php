<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // MÓDULO 2: reglas como GuardarMascotaRequest
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],
            'direccion' => [
                'required',
                'string',
                'max:255',
            ],
            'telefono' => [
                'nullable',
                'string',
                'max:255',
            ],
            'imagen_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.string' => 'La dirección debe ser una cadena de texto.',
            'direccion.max' => 'La dirección no puede exceder los 255 caracteres.',
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max' => 'El teléfono no puede exceder los 255 caracteres.',
            'imagen_url.image' => 'La imagen debe ser una imagen.',
            'imagen_url.mimes' => 'La imagen debe ser de tipo jpeg, png, jpg o webp.',
            'imagen_url.max' => 'La imagen no puede exceder los 2MB.',
        ];
    }
}
