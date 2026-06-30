<x-mail::message>
# Hola {{ $clienteNombre }},

{{ $mensaje }}

Atentamente,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
