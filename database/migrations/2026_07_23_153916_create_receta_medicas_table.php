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
        Schema::create('receta_medicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ficha_clinica_id')->cascadeOnDelete();
            $table->json('medicamentos'); 
            $table->text('indicaciones_generales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_medicas');
    }
};
