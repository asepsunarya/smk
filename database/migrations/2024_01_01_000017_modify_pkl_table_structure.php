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
        Schema::table('pkl', function (Blueprint $table) {
            // Drop foreign keys and columns
            $table->dropForeign(['siswa_id']);
            $table->dropForeign(['pembimbing_sekolah_id']);
            $table->dropColumn(['siswa_id', 'pembimbing_sekolah_id', 'nilai_perusahaan', 'nilai_sekolah', 'keterangan']);
        });

        // Add new pembimbing_sekolah as string
        Schema::table('pkl', function (Blueprint $table) {
            $table->string('pembimbing_sekolah')->after('pembimbing_perusahaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pkl', function (Blueprint $table) {
            $table->dropColumn('pembimbing_sekolah');
        });

        Schema::table('pkl', function (Blueprint $table) {
            $table->foreignId('siswa_id')->after('id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('pembimbing_sekolah_id')->after('pembimbing_perusahaan')->constrained('guru')->onDelete('cascade');
            $table->enum('nilai_perusahaan', ['A', 'B', 'C', 'D'])->nullable()->after('tanggal_selesai');
            $table->enum('nilai_sekolah', ['A', 'B', 'C', 'D'])->nullable()->after('nilai_perusahaan');
            $table->text('keterangan')->nullable()->after('nilai_sekolah');
        });
    }
};

