<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarCitaCargoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Las validaciones de permisos se manejarán vía Policies
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad' => 'required|integer|min:1',
        ];
    }
    public function messages(): array
    {
        return [
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
