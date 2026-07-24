<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cita;
use App\Models\Insumo;
use App\Models\FichaClinica;
use Illuminate\Support\Facades\Mail;
use App\Mail\CitaEstadoActualizadoMail;

// 1. Buscamos una vacuna en el inventario
$insumoVacuna = Insumo::whereHas('categoriaInsumo', function($q){
    $q->where('nombre', 'Vacunas')->orWhere('nombre', 'like', '%vacuna%');
})->first();

if (!$insumoVacuna) {
    echo "No se encontró un insumo de tipo Vacuna.\n";
    $insumoVacuna = Insumo::first(); // Fallback
}

// 2. Buscamos una cita completada
$cita = Cita::where('estado', 'completada')->has('fichaClinica')->first();

if (!$cita) {
    echo "No se encontró cita completada con ficha clínica.\n";
    exit;
}

// 3. Agregamos la vacuna a la ficha clínica
$ficha = $cita->fichaClinica;
if ($ficha->vacunas->where('nombre_vacuna', $insumoVacuna->nombre)->isEmpty()) {
    $ficha->vacunas()->create([
        'mascota_id' => $cita->mascota_id,
        'nombre_vacuna' => $insumoVacuna->nombre,
        'fecha_aplicacion' => now()
    ]);
    echo "Vacuna '{$insumoVacuna->nombre}' agregada a la ficha médica de la cita {$cita->id}.\n";
} else {
    echo "La ficha de la cita {$cita->id} ya tenía la vacuna '{$insumoVacuna->nombre}'.\n";
}

if ($cita->box) {
    $insumoVacuna->update(['sucursal_id' => $cita->box->sucursal_id]);
}

// 4. Enviamos el correo
echo "Enviando correo...\n";
$cita->estado = 'completada';
Mail::to('test@example.com')->send(new CitaEstadoActualizadoMail($cita, 'cliente'));

echo "¡Correo enviado y renderizado exitosamente!\n";
