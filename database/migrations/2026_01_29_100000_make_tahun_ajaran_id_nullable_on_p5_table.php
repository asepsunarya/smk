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
            $table->dropForeign(['tahun_ajaran_id']);
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->unsignedBigInteger('tahun_ajaran_id')->nullable()->change();
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p5', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->unsignedBigInteger('tahun_ajaran_id')->nullable(false)->change();
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->onDelete('cascade');
        });
    }
};
