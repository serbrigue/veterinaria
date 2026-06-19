<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarClienteRequest extends FormRequest
{
    /**
     * ============================================
     * EJERCICIO: Request para actualizar cliente
     * ============================================
     * Define las reglas de validación según
     * los campos del modelo Cliente y el
     * ejemplo de ActualizarMascotaRequest.
     * ============================================
     */

    public function authorize(): bool
    {
        // TODO: Completar (debe retornar true)
        return true;
    }

    public function rules(): array
    {
        // TODO: Definir reglas de validación
        // nombre: required|string|max:255
        // email: required|email|max:255
        // telefono: required|string|max:20
        // direccion: required|string|max:500
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        // TODO: Personalizar mensajes de error
        return [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'direccion.required' => 'La dirección es obligatoria.',
        ];
    }
}
