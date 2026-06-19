<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mascotas')) {
            return;
        }

        Schema::table('mascotas', function (Blueprint $table) {
            if (! Schema::hasColumn('mascotas', 'sexo')) {
                $table->string('sexo')->default('macho')->after('descripcion');
            }
            if (! Schema::hasColumn('mascotas', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('sexo');
            }
            if (! Schema::hasColumn('mascotas', 'peso_kg')) {
                $table->decimal('peso_kg', 6, 2)->nullable()->after('fecha_nacimiento');
            }
            if (! Schema::hasColumn('mascotas', 'color')) {
                $table->string('color', 100)->nullable()->after('peso_kg');
            }
            if (! Schema::hasColumn('mascotas', 'esterilizado')) {
                $table->boolean('esterilizado')->default(false)->after('color');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mascotas')) {
            return;
        }

        $columnas = array_filter(
            ['sexo', 'fecha_nacimiento', 'peso_kg', 'color', 'esterilizado'],
            fn ($columna) => Schema::hasColumn('mascotas', $columna)
        );

        if ($columnas === []) {
            return;
        }

        Schema::table('mascotas', function (Blueprint $table) use ($columnas) {
            $table->dropColumn($columnas);
        });
    }
};
