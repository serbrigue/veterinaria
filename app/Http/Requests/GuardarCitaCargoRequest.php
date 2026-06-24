<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCitaCargoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Las validaciones de permisos se manejarán vía Policies
        return true;
    }

    public function rules(): array
    {
        return [
            'cita_id' => 'required|exists:citas,id',
            'insumo_id' => 'required|exists:insumos,id',
            'cantidad' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'cita_id.required' => 'La cita es obligatoria.',
            'cita_id.exists' => 'La cita seleccionada no existe.',
            'insumo_id.required' => 'Debe seleccionar un insumo.',
            'insumo_id.exists' => 'El insumo seleccionado no existe.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
