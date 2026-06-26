<x-mail::message>
# Cita Médica Agendada

Hola, se ha agendado una nueva cita médica.

**Detalles de la cita:**
- **Mascota:** {{ $cita->mascota->nombre ?? 'Desconocido' }}
- **Motivo:** {{ $cita->titulo }}
- **Fecha:** {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d-m-Y') }}
- **Hora:** {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}
- **Box:** {{ $cita->box->nombre ?? 'N/A' }} ({{ $cita->box->sucursal->nombre ?? 'N/A' }})

@if($rol === 'cliente')
**Veterinario Asignado:** Dr/Dra. {{ $cita->veterinario->usuario->name ?? 'No asignado' }}
@else
**Cliente:** {{ $cita->mascota->cliente->usuario->name ?? 'Sin asignar' }} (Tel: {{ $cita->mascota->cliente->telefono ?? 'N/A' }})
@endif

<x-mail::button :url="config('app.url')">
Ver Detalles en la Plataforma
</x-mail::button>

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
