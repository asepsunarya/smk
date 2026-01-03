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
        Schema::table('capaian_pembelajaran', function (Blueprint $table) {
            $table->enum('target', ['tengah_semester', 'akhir_semester'])->nullable()->after('kode_cp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capaian_pembelajaran', function (Blueprint $table) {
            $table->dropColumn('target');
        });
    }
};
