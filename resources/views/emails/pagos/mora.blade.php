<x-mail::message>
# Aviso de Saldo Pendiente

Estimado/a **{{ $cliente->usuario->name ?? 'Cliente' }}**,

Nos comunicamos con usted desde la Veterinaria para recordarle que mantiene saldos pendientes asociados a los siguientes servicios y atenciones:

<x-mail::table>
| Fecha | Cita/Servicio | Monto |
| :--- | :--- | :--- |
@foreach ($transacciones as $transaccion)
| {{ $transaccion->created_at->format('d/m/Y') }} | {{ $transaccion->cita ? ($transaccion->cita->titulo ?? 'Cita #'.$transaccion->cita->id) : 'Servicio Médico' }} | ${{ number_format($transaccion->monto_total, 0, ',', '.') }} |
@endforeach
</x-mail::table>

Por favor, le solicitamos regularizar esta situación a la brevedad posible. 
Si ya realizó el pago, por favor ignore este mensaje.

<x-mail::button :url="config('app.url')">
Contactar con Administración
</x-mail::button>

Saludos cordiales,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
