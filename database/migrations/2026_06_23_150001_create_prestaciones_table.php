<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->decimal('precio_base', 10, 2);
            $table->unsignedBigInteger('especialidad_id')->nullable();
            $table->decimal('comision_vet', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestaciones');
    }
};
