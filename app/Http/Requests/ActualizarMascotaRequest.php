<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarMascotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:500',
            'sexo' => 'required|in:macho,hembra',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:today',
            'peso_kg' => 'nullable|numeric|min:0|max:500',
            'color' => 'nullable|string|max:100',
            'esterilizado' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la mascota es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
            'sexo.required' => 'El sexo es obligatorio.',
            'sexo.in' => 'El sexo debe ser macho o hembra.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'peso_kg.numeric' => 'El peso debe ser un número.',
            'peso_kg.min' => 'El peso no puede ser negativo.',
            'peso_kg.max' => 'El peso no puede superar 500 kg.',
            'color.max' => 'El color no puede tener más de 100 caracteres.',
            'esterilizado.required' => 'Indica si la mascota está esterilizada.',
            'esterilizado.boolean' => 'El valor de esterilizado no es válido.',
        ];
    }
}
