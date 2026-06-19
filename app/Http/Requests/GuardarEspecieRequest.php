<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarEspecieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // MÓDULO 2: reglas como GuardarMascotaRequest
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
