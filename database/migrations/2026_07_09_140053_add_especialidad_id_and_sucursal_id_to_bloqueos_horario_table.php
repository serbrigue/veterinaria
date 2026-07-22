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
        Schema::table('bloqueos_horario', function (Blueprint $table) {
            $table->unsignedBigInteger('especialidad_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bloqueos_horario', function (Blueprint $table) {
            $table->dropColumn(['especialidad_id', 'sucursal_id']);
        });
    }
};
