<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarFichaClinicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // MÓDULO 2: reglas como GuardarMascotaRequest
        return [
            'peso_actual' => 'nullable|numeric|min:0',
            'frecuencia_cardiaca' => 'nullable|integer|min:0',
            'temperatura' => 'nullable|numeric|min:0',
            'anamnesis' => 'nullable|string',
            'sintomas' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'recetas' => 'nullable|array',
            'recetas.*.medicamentos' => 'required|array',
            'recetas.*.indicaciones_generales' => 'nullable|string',
            'recetas.*.comprado_en_clinica' => 'boolean',
            'vacunas' => 'nullable|array',
            'vacunas.*.nombre_vacuna' => 'required|string',
            'vacunas.*.fecha_aplicacion' => 'required|date',
            'vacunas.*.fecha_proxima_dosis' => 'nullable|date',
            'vacunas.*.numero_lote' => 'nullable|string',
            'vacunas.*.notas' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'peso_actual.numeric' => 'El peso debe ser un número.',
            'peso_actual.min' => 'El peso debe ser mayor o igual a 0.',
            'frecuencia_cardiaca.integer' => 'La frecuencia cardíaca debe ser un número entero.',
            'frecuencia_cardiaca.min' => 'La frecuencia cardíaca debe ser mayor o igual a 0.',
            'temperatura.numeric' => 'La temperatura debe ser un número.',
            'temperatura.min' => 'La temperatura debe ser mayor o igual a 0.',
            'anamnesis.string' => 'La anamnesis debe ser una cadena de texto.',
            'sintomas.string' => 'Los síntomas deben ser una cadena de texto.',
            'diagnostico.string' => 'El diagnóstico debe ser una cadena de texto.',
            'recetas.array' => 'Las recetas deben ser un array.',
            'recetas.*.medicamentos.required' => 'Las recetas deben tener medicamentos.',
            'recetas.*.medicamentos.array' => 'Los medicamentos deben ser un array.',
            'recetas.*.indicaciones_generales.string' => 'Las indicaciones generales deben ser una cadena de texto.',
            'vacunas.array' => 'Las vacunas deben ser un array.',
            'vacunas.*.nombre_vacuna.required' => 'Las vacunas deben tener nombre.',
            'vacunas.*.nombre_vacuna.string' => 'El nombre de la vacuna debe ser una cadena de texto.',
            'vacunas.*.fecha_aplicacion.required' => 'Las vacunas deben tener fecha de aplicación.',
            'vacunas.*.fecha_aplicacion.date' => 'La fecha de aplicación debe ser una fecha.',
            'vacunas.*.fecha_proxima_dosis.date' => 'La fecha de próxima dosis debe ser una fecha.',
            'vacunas.*.numero_lote.string' => 'El número de lote debe ser una cadena de texto.',
            'vacunas.*.notas.string' => 'Las notas deben ser una cadena de texto.',
        ];
    }
}

        