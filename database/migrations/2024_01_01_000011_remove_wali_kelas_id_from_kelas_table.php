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
        // Migrate existing data from kelas.wali_kelas_id to wali_kelas table
        $kelasWithWaliKelas = DB::table('kelas')
            ->whereNotNull('wali_kelas_id')
            ->get();

        foreach ($kelasWithWaliKelas as $kelas) {
            // Get guru_id from user
            $guru = DB::table('guru')
                ->where('user_id', $kelas->wali_kelas_id)
                ->first();

            if ($guru) {
                DB::table('wali_kelas')->insert([
                    'guru_id' => $guru->id,
                    'kelas_id' => $kelas->id,
                    'is_active' => true,
                    'tanggal_mulai' => $kelas->created_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Remove wali_kelas_id column from kelas table
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['wali_kelas_id']);
            $table->dropColumn('wali_kelas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->onDelete('set null');
        });

        // Migrate data back from wali_kelas to kelas.wali_kelas_id
        $waliKelas = DB::table('wali_kelas')
            ->where('is_active', true)
            ->get();

        foreach ($waliKelas as $wali) {
            $guru = DB::table('guru')->where('id', $wali->guru_id)->first();
            if ($guru) {
                DB::table('kelas')
                    ->where('id', $wali->kelas_id)
                    ->update(['wali_kelas_id' => $guru->user_id]);
            }
        }
    }
};

