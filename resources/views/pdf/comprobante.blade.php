<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .title { font-size: 18px; color: #27ae60; margin-top: 5px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table th, .details-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .details-table th { width: 30%; color: #7f8c8d; }
        .total-box { background-color: #f8f9fa; padding: 15px; text-align: right; border-radius: 5px; }
        .total-amount { font-size: 20px; font-weight: bold; color: #27ae60; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #95a5a6; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">Clínica Veterinaria</div>
        <div class="title">¡Pago Exitoso!</div>
        <div style="color: #7f8c8d; font-size: 12px; margin-top: 5px;">
            Comprobante #{{ str_pad($transaccion->id, 6, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    <table class="details-table">
        <tr>
            <th>Fecha de pago:</th>
            <td>{{ \Carbon\Carbon::parse($transaccion->fecha_pago)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>Cliente:</th>
            <td>{{ $clienteNombre ?? 'Desconocido' }}</td>
        </tr>
        <tr>
            <th>Paciente:</th>
            <td>{{ $mascotaNombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Método de pago:</th>
            <td>{{ ucfirst($transaccion->metodo_pago) }}</td>
        </tr>
    </table>

    <div class="total-box">
        <span style="color: #7f8c8d; text-transform: uppercase; font-weight: bold; font-size: 12px; margin-right: 15px;">Total Pagado</span>
        <span class="total-amount">${{ number_format($transaccion->monto_pagado, 0, ',', '.') }}</span>
    </div>

    <div class="footer">
        Gracias por confiar en nuestra clínica veterinaria.
    </div>

</body>
</html>
