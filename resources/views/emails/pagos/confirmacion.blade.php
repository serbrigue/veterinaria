<x-mail::message>
# Comprobante de Pago

Hola **{{ $transaccion->cliente->usuario->name ?? 'Cliente' }}**, hemos recibido tu pago correctamente.
A continuación, adjuntamos tu comprobante digital.

<x-mail::panel>
**Detalles del Recibo**

**Nro. Transacción:** #{{ str_pad($transaccion->id, 6, '0', STR_PAD_LEFT) }}<br>
**Fecha de Pago:** {{ \Carbon\Carbon::parse($transaccion->fecha_pago)->format('d-m-Y H:i') }}<br>
**Método de Pago:** {{ ucfirst($transaccion->metodo_pago ?? 'N/A') }}<br>
**Estado:** <span style="color: green;">Pagado</span>

---
**Paciente:** {{ $transaccion->cita->mascota->nombre ?? 'N/A' }}<br>
**Servicio Base:** {{ $transaccion->cita->prestacion->nombre ?? 'Atención Veterinaria' }}
</x-mail::panel>

<x-mail::table>
| Resumen | Monto |
| :--- | :--- |
| **Monto Original** | ${{ number_format($transaccion->monto_total, 0, ',', '.') }} |
| **TOTAL PAGADO** | **${{ number_format($transaccion->monto_pagado, 0, ',', '.') }}** |
</x-mail::table>

Si necesitas asistencia, no dudes en contactarnos respondiendo a este correo.

<x-mail::button :url="route('citas.listado')">
Ir a la Plataforma
</x-mail::button>

Gracias por preferirnos,<br>
{{ config('app.name') }}
</x-mail::message>
