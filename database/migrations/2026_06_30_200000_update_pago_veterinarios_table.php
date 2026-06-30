<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pago_veterinarios', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('pago_veterinarios_veterinario_id_mes_anio_unique');
            
            $table->unsignedBigInteger('veterinario_id')->nullable()->change();
            $table->unsignedBigInteger('usuario_id')->nullable()->after('id');
            
            // Create unique constraint on usuario_id + mes + anio
            $table->unique(['usuario_id', 'mes', 'anio']);
        });

        // Populate usuario_id for existing records using veterinarios table
        DB::table('pago_veterinarios')
            ->join('veterinarios', 'pago_veterinarios.veterinario_id', '=', 'veterinarios.id')
            ->update(['pago_veterinarios.usuario_id' => DB::raw('veterinarios.user_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago_veterinarios', function (Blueprint $table) {
            $table->dropUnique('pago_veterinarios_usuario_id_mes_anio_unique');
            $table->dropColumn('usuario_id');
            $table->unsignedBigInteger('veterinario_id')->nullable(false)->change();
            $table->unique(['veterinario_id', 'mes', 'anio']);
        });
    }
};
