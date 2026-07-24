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
        Schema::create('ficha_clinicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cita_id')->cascadeOnDelete();
            $table->unsignedBigInteger('mascota_id')->cascadeOnDelete();
            $table->unsignedBigInteger('veterinario_id')->cascadeOnDelete();

            // Constantes vitales
            $table->decimal('peso_actual', 5, 2)->nullable(); // kg
            $table->integer('frecuencia_cardiaca')->nullable(); // lpm
            $table->decimal('temperatura', 4, 1)->nullable(); // °C

            // Examen clínico
            $table->text('anamnesis')->nullable(); // Motivo de consulta
            $table->text('sintomas')->nullable();
            $table->text('diagnostico')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ficha_clinicas');
    }
};
