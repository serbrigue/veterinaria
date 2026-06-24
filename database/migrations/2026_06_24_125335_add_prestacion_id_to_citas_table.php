<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->unsignedBigInteger('prestacion_id')->after('box_id')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn('prestacion_id');
        });
    }
};
