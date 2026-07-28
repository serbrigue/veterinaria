<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarMovimientoInventarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'insumo_id' => 'required|exists:insumos,id',
            'cantidad' => 'required|numeric|min:1',
            'motivo' => 'required|string|max:255',
        ];
    }
    
    public function messages()
    {
        return [
            'motivo.required' => 'Debe justificar este movimiento con un motivo obligatorio.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
