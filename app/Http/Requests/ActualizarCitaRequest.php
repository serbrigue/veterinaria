<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarCitaRequest extends FormRequest
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
            'cliente_id' => 'required|integer|exists:clientes,id',
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
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
        ];
    }
}
