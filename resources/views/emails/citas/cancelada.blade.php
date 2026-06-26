<x-mail::message>
# Cita Médica Cancelada

Hola, la cita médica agendada para **{{ $cita->mascota->nombre ?? 'Desconocido' }}** ha sido cancelada.

**Detalles de la cita cancelada:**
- **Motivo Original:** {{ $cita->titulo }}
- **Fecha Original:** {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d-m-Y') }}
- **Hora Original:** {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}

@if($cita->notas)
<x-mail::panel>
**Razón de la cancelación:**
{{ $cita->notas }}
</x-mail::panel>
@endif

Si tienes alguna duda o necesitas reagendar, por favor contáctanos.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
