<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ObtenerHorariosDisponiblesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "fecha" => "required|date_format:Y-m-d",
            "veterinario_id" => "required|exists:veterinarios,id",
        ];
    }
}
