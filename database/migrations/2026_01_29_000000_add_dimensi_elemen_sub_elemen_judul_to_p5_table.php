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
        Schema::table('p5', function (Blueprint $table) {
            $table->string('dimensi')->nullable()->after('tahun_ajaran_id');
            $table->string('elemen')->nullable()->after('dimensi');
            $table->string('sub_elemen')->nullable()->after('elemen');
            $table->string('judul')->nullable()->after('sub_elemen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p5', function (Blueprint $table) {
            $table->dropColumn(['dimensi', 'elemen', 'sub_elemen', 'judul']);
        });
    }
};
