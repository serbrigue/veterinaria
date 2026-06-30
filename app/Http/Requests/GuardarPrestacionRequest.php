<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarPrestacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sucursal_id' => 'required|exists:sucursales,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'precio_base' => 'required|numeric|min:0',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'comision_vet' => 'nullable|numeric|min:0|max:100',
            'categoria_prestacion_id' => 'nullable|exists:categorias_prestaciones,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'precio_base.required' => 'El precio base es obligatorio.',
            'precio_base.numeric' => 'El precio base debe ser un número.',
            'precio_base.min' => 'El precio base no puede ser negativo.',
            'especialidad_id.required' => 'La especialidad es obligatoria.',
            'especialidad_id.exists' => 'La especialidad seleccionada no existe.',
            'comision_vet.required' => 'La comisión del veterinario es obligatoria.',
            'comision_vet.numeric' => 'La comisión del veterinario debe ser un número.',
            'comision_vet.min' => 'La comisión del veterinario no puede ser negativa.',
            'comision_vet.max' => 'La comisión del veterinario no puede superar el 100%.',
        ];
    }
}
