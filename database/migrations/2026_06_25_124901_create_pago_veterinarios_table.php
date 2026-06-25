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
        Schema::create('pago_veterinarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('veterinario_id');
            $table->integer('mes');
            $table->integer('anio');
            $table->decimal('monto_total', 10, 2);
            $table->string('estado')->default('pendiente');
            $table->timestamps();

            // Garantizar que no se pague dos veces el mismo mes al mismo veterinario
            $table->unique(['veterinario_id', 'mes', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_veterinarios');
    }
};
