<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * REFERENCIA (Módulo 0): única migración lista. Copia este patrón en Módulos 2-5.
     */
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion');
            $table->string('sexo');
            $table->date('fecha_nacimiento')->nullable();
            $table->decimal('peso_kg', 6, 2)->nullable();
            $table->string('color', 100)->nullable();
            $table->boolean('esterilizado')->default(false);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
