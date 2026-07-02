<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarHorarioVeterinarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'horario' => 'required|array',
            'horario.*.dia' => 'required|integer|between:1,7',
            'horario.*.normal' => 'required|array',
            'horario.*.normal.activo' => 'required|boolean',
            'horario.*.normal.inicio' => 'nullable|required_if:horario.*.normal.activo,true|date_format:H:i',
            'horario.*.normal.fin' => 'nullable|required_if:horario.*.normal.activo,true|date_format:H:i',
            'horario.*.urgencia' => 'required|array',
            'horario.*.urgencia.activo' => 'required|boolean',
            'horario.*.urgencia.inicio' => 'nullable|required_if:horario.*.urgencia.activo,true|date_format:H:i',
            'horario.*.urgencia.fin' => 'nullable|required_if:horario.*.urgencia.activo,true|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'horario.required' => 'El horario es obligatorio.',
            'horario.array' => 'El horario debe ser un array.',
            'horario.*.dia.required' => 'El día es obligatorio.',
            'horario.*.dia.integer' => 'El día debe ser un número entero.',
            'horario.*.dia.between' => 'El día debe estar entre 1 y 7.',
            'horario.*.normal.required' => 'El horario normal es obligatorio.',
            'horario.*.normal.array' => 'El horario normal debe ser un array.',
            'horario.*.normal.activo.required' => 'El horario normal activo es obligatorio.',
            'horario.*.normal.activo.boolean' => 'El horario normal activo debe ser un booleano.',
            'horario.*.normal.inicio.required_if' => 'El horario normal de inicio es obligatorio cuando el horario normal está activo.',
            'horario.*.normal.inicio.date_format' => 'El horario normal de inicio debe tener un formato HH:mm.',
            'horario.*.normal.fin.required_if' => 'El horario normal de fin es obligatorio cuando el horario normal está activo.',
            'horario.*.normal.fin.date_format' => 'El horario normal de fin debe tener un formato HH:mm.',
            'horario.*.urgencia.required' => 'El horario de urgencia es obligatorio.',
            'horario.*.urgencia.array' => 'El horario de urgencia debe ser un array.',
            'horario.*.urgencia.activo.required' => 'El horario de urgencia activo es obligatorio.',
            'horario.*.urgencia.activo.boolean' => 'El horario de urgencia activo debe ser un booleano.',
            'horario.*.urgencia.inicio.required_if' => 'El horario de urgencia de inicio es obligatorio cuando el horario de urgencia está activo.',
            'horario.*.urgencia.inicio.date_format' => 'El horario de urgencia de inicio debe tener un formato HH:mm.',
            'horario.*.urgencia.fin.required_if' => 'El horario de urgencia de fin es obligatorio cuando el horario de urgencia está activo.',
            'horario.*.urgencia.fin.date_format' => 'El horario de urgencia de fin debe tener un formato HH:mm.',
        ];
    }
}
