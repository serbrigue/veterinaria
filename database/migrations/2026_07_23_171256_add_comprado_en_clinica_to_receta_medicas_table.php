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
        Schema::table('receta_medicas', function (Blueprint $table) {
            $table->boolean('comprado_en_clinica')->default(true)->after('medicamentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receta_medicas', function (Blueprint $table) {
            //
        });
    }
};
