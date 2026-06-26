<x-mail::message>
# Actualización en Cita Médica

Hola, el estado de la cita médica para **{{ $cita->mascota->nombre ?? 'Desconocido' }}** ha cambiado.

**Nuevo Estado:** {{ ucfirst(str_replace('_', ' ', $cita->estado)) }}

**Detalles de la cita:**
- **Motivo:** {{ $cita->titulo }}
- **Fecha:** {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('d-m-Y') }}
- **Hora:** {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') }}

@if($cita->estado === 'completada' && $rol === 'cliente')
La consulta ha finalizado con éxito. A continuación, encontrarás las notas clínicas y el detalle del cobro.

@if($cita->notas)
### Notas Clínicas:
<x-mail::panel>
{{ $cita->notas }}
</x-mail::panel>
@endif

### Detalle de Cobro:
<x-mail::table>
| Concepto | Monto |
| :--- | :--- |
@if($cita->prestacion)
| **{{ $cita->prestacion->nombre }}** | ${{ number_format($cita->prestacion->precio_base, 0, ',', '.') }} |
@endif
@if($cita->cargos && $cita->cargos->count() > 0)
@foreach($cita->cargos as $cargo)
| {{ $cargo->insumo->nombre ?? 'Insumo' }} (x{{ $cargo->cantidad }}) | ${{ number_format($cargo->subtotal, 0, ',', '.') }} |
@endforeach
@endif
| **TOTAL A PAGAR** | **${{ number_format($cita->transaccion ? $cita->transaccion->monto_total : 0, 0, ',', '.') }}** |
</x-mail::table>

### ¿Cómo realizar el pago?
1. Ingresa a tu cuenta en la plataforma.
2. Ve a la sección **Mis Citas**.
3. Selecciona la opción **Pagar en Línea** en el comprobante de esta cita.

<x-mail::button :url="route('citas.listado')">
Ingresar a la Plataforma
</x-mail::button>
@else
<x-mail::button :url="route('citas.detalle', $cita->id)">
Revisar Cita
</x-mail::button>
@endif

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
