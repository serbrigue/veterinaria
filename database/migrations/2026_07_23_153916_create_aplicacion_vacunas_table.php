<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aplicacion_vacunas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cita_id')->nullable()->nullOnDelete();
            $table->unsignedBigInteger('mascota_id');
            $table->string('nombre_vacuna');
            $table->date('fecha_aplicacion');
            $table->date('fecha_proxima_dosis')->nullable();
            $table->string('numero_lote')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacion_vacunas');
    }
};
