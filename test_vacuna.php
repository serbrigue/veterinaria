<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cita = \App\Models\Cita::find(1);
$sucursalId = $cita->box->sucursal_id;
echo "Sucursal de cita 1: " . $sucursalId . "\n";
$vacunas = \App\Models\Insumo::where('categoria_insumo_id', 3)->get();
foreach($vacunas as $v) {
    echo "Vacuna: {$v->nombre} | Sucursal: {$v->sucursal_id} | Stock: {$v->stock_actual}\n";
}
