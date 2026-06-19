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
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('cliente_id')->nullable()->index();
            $table->unsignedBigInteger('raza_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index();
            $table->dropColumn('cliente_id');
            $table->dropColumn('raza_id');
        });
    }
};
