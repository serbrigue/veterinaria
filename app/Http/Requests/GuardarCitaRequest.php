<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'fecha_hora' => 'required|date',
            'mascota_id' => 'required|integer|exists:mascotas,id',
            'veterinario_id' => 'required|integer|exists:veterinarios,id',
            'box_id' => 'required|integer|exists:boxes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título de la cita es obligatorio.',
            'titulo.max' => 'El título no puede tener más de 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'fecha_hora.required' => 'La fecha y hora son obligatorias.',
            'fecha_hora.date' => 'La fecha y hora no tienen un formato válido.',
            'mascota_id.required' => 'La mascota es obligatoria.',
            'mascota_id.exists' => 'La mascota seleccionada no existe.',
            'veterinario_id.required' => 'El veterinario es obligatorio.',
            'veterinario_id.exists' => 'El veterinario seleccionado no existe.',
            'box_id.required' => 'El box es obligatorio.',
            'box_id.exists' => 'El box seleccionado no existe.',
        ];
    }
}
