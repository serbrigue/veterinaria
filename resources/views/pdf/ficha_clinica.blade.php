<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha Clínica - {{ $cita->mascota->nombre ?? 'Paciente' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        .title { color: #0056b3; font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 16px; font-weight: bold; color: #0056b3; background: #f0f8ff; padding: 5px 10px; border-left: 4px solid #0056b3; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; width: 30%; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Ficha Clínica Veterinaria</div>
        <div>Cita: #{{ str_pad($cita->id, 6, '0', STR_PAD_LEFT) }} | Fecha: {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d/m/Y H:i') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Datos Generales</div>
        <table>
            <tr><th>Paciente</th><td>{{ $cita->mascota?->nombre ?? 'N/A' }}</td></tr>
            <tr><th>Especie/Raza</th><td>{{ $cita->mascota?->raza?->especie?->nombre ?? '' }} / {{ $cita->mascota?->raza?->nombre ?? '' }}</td></tr>
            <tr><th>Propietario</th><td>{{ $cita->mascota?->cliente?->usuario?->name ?? 'N/A' }}</td></tr>
            <tr><th>Médico Tratante</th><td>{{ $cita->veterinario?->usuario?->name ?? 'N/A' }}</td></tr>
        </table>
    </div>

    @if($ficha)
    <div class="section">
        <div class="section-title">Constantes Vitales</div>
        <table>
            <tr><th>Peso Actual</th><td>{{ $ficha->peso_actual ? $ficha->peso_actual . ' kg' : 'No registrado' }}</td></tr>
            <tr><th>Frecuencia Cardíaca</th><td>{{ $ficha->frecuencia_cardiaca ? $ficha->frecuencia_cardiaca . ' lpm' : 'No registrado' }}</td></tr>
            <tr><th>Temperatura</th><td>{{ $ficha->temperatura ? $ficha->temperatura . ' °C' : 'No registrado' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Examen Clínico</div>
        <table>
            <tr><th>Anamnesis</th><td>{{ $ficha->anamnesis ?: 'No registrado' }}</td></tr>
            <tr><th>Síntomas</th><td>{{ $ficha->sintomas ?: 'No registrado' }}</td></tr>
            <tr><th>Diagnóstico</th><td>{{ $ficha->diagnostico ?: 'No registrado' }}</td></tr>
        </table>
    </div>

    @if($ficha->recetas && count($ficha->recetas) > 0)
    <div class="section">
        <div class="section-title">Recetas Médicas</div>
        @foreach($ficha->recetas as $index => $receta)
            <div style="margin-bottom: 10px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                <strong>Receta #{{ $index + 1 }}</strong><br>
                <strong>Medicamentos:</strong> 
                @php
                    $meds = is_string($receta['medicamentos']) ? json_decode($receta['medicamentos'], true) : $receta['medicamentos'];
                    if (is_array($meds)) {
                        echo implode(', ', $meds);
                    } else {
                        echo $receta['medicamentos'];
                    }
                @endphp
                <br>
                <strong>Indicaciones:</strong> {{ $receta['indicaciones_generales'] ?? 'Ninguna' }}
            </div>
        @endforeach
    </div>
    @endif

    @if($ficha->vacunas && count($ficha->vacunas) > 0)
    <div class="section">
        <div class="section-title">Vacunas Aplicadas</div>
        <table>
            <tr>
                <th style="width: auto;">Vacuna</th>
                <th style="width: auto;">Fecha</th>
                <th style="width: auto;">Próxima Dosis</th>
                <th style="width: auto;">Lote</th>
            </tr>
            @foreach($ficha->vacunas as $vacuna)
            <tr>
                <td>{{ $vacuna['nombre_vacuna'] ?? '' }}</td>
                <td>{{ $vacuna['fecha_aplicacion'] ? \Carbon\Carbon::parse($vacuna['fecha_aplicacion'])->format('d/m/Y') : '' }}</td>
                <td>{{ $vacuna['fecha_proxima_dosis'] ? \Carbon\Carbon::parse($vacuna['fecha_proxima_dosis'])->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $vacuna['numero_lote'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    @else
    <p>No se encontraron registros clínicos para esta cita.</p>
    @endif

</body>
</html>
