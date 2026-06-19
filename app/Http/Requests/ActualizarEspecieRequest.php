<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarEspecieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // MÓDULO 2: reglas como ActualizarMascotaRequest
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
