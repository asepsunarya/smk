<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->string('sub_elemen', 500)->nullable()->after('dimensi_id');
        });

        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->dropUnique(['siswa_id', 'p5_id', 'dimensi_id']);
        });

        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->unsignedBigInteger('dimensi_id')->nullable()->change();
        });

        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->unique(['siswa_id', 'p5_id', 'sub_elemen']);
        });
    }

    public function down(): void
    {
        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->dropUnique(['siswa_id', 'p5_id', 'sub_elemen']);
        });

        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->unsignedBigInteger('dimensi_id')->nullable(false)->change();
        });

        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->unique(['siswa_id', 'p5_id', 'dimensi_id']);
        });

        Schema::table('nilai_p5', function (Blueprint $table) {
            $table->dropColumn('sub_elemen');
        });
    }
};
