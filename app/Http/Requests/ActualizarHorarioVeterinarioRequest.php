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

            'horario.*.id' => 'required|string',
            'horario.*.nombre' => 'required|string|max:100',
            'horario.*.fecha_inicio' => 'required|date_format:Y-m-d',
            'horario.*.fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:horario.*.fecha_inicio',
            'horario.*.especialidad_id' => 'nullable|exists:especialidades,id',
            'horario.*.sucursal_id' => 'nullable|exists:sucursales,id',

            'horario.*.dias' => 'required|array',
            'horario.*.dias.*.dia' => 'required|integer|between:1,7',
            'horario.*.dias.*.normal' => 'required|array',
            'horario.*.dias.*.normal.activo' => 'required|boolean',
            'horario.*.dias.*.normal.inicio' => 'nullable|required_if:horario.*.dias.*.normal.activo,true|date_format:H:i',
            'horario.*.dias.*.normal.fin' => 'nullable|required_if:horario.*.dias.*.normal.activo,true|date_format:H:i',
            'horario.*.dias.*.urgencia' => 'required|array',
            'horario.*.dias.*.urgencia.activo' => 'required|boolean',
            'horario.*.dias.*.urgencia.inicio' => 'nullable|required_if:horario.*.dias.*.urgencia.activo,true|date_format:H:i',
            'horario.*.dias.*.urgencia.fin' => 'nullable|required_if:horario.*.dias.*.urgencia.activo,true|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'horario.required' => 'El horario es obligatorio.',
            'horario.array' => 'El horario debe ser un array.',
            'horario.*.id.required' => 'El identificador del plan es obligatorio.',
            'horario.*.nombre.required' => 'El nombre del plan es obligatorio.',
            'horario.*.nombre.max' => 'El nombre del plan no puede tener más de 100 caracteres.',
            'horario.*.fecha_inicio.required' => 'La fecha de inicio del plan es obligatoria.',
            'horario.*.fecha_inicio.date_format' => 'La fecha de inicio debe tener formato YYYY-MM-DD.',
            'horario.*.fecha_fin.required' => 'La fecha de fin del plan es obligatoria.',
            'horario.*.fecha_fin.date_format' => 'La fecha de fin debe tener formato YYYY-MM-DD.',
            'horario.*.fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
            'horario.*.dias.required' => 'La configuración de días es obligatoria.',
            'horario.*.dias.*.dia.required' => 'El día es obligatorio.',
            'horario.*.dias.*.dia.between' => 'El día debe estar entre 1 y 7.',
            'horario.*.dias.*.normal.activo.required' => 'El estado del horario normal es obligatorio.',
            'horario.*.dias.*.normal.inicio.required_if' => 'La hora de inicio normal es obligatoria cuando está activo.',
            'horario.*.dias.*.normal.inicio.date_format' => 'La hora de inicio normal debe tener formato HH:mm.',
            'horario.*.dias.*.normal.fin.required_if' => 'La hora de fin normal es obligatoria cuando está activo.',
            'horario.*.dias.*.normal.fin.date_format' => 'La hora de fin normal debe tener formato HH:mm.',
            'horario.*.dias.*.urgencia.activo.required' => 'El estado del horario de urgencia es obligatorio.',
            'horario.*.dias.*.urgencia.inicio.required_if' => 'La hora de inicio de urgencia es obligatoria cuando está activo.',
            'horario.*.dias.*.urgencia.inicio.date_format' => 'La hora de inicio de urgencia debe tener formato HH:mm.',
            'horario.*.dias.*.urgencia.fin.required_if' => 'La hora de fin de urgencia es obligatoria cuando está activo.',
            'horario.*.dias.*.urgencia.fin.date_format' => 'La hora de fin de urgencia debe tener formato HH:mm.',
        ];
    }
}
