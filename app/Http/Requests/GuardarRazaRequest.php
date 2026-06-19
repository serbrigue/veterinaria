<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarRazaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // TODO: Agregar reglas de validación (ej. 'nombre' => 'required|string|max:255')
        return [
            //
        ];
    }

    public function messages(): array
    {
        // TODO: Agregar mensajes de error personalizados
        return [
            //
        ];
    }
}
