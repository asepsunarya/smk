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
        // Ensure pivot table exists
        if (!Schema::hasTable('kelas_mata_pelajaran')) {
            Schema::create('kelas_mata_pelajaran', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
                $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['kelas_id', 'mata_pelajaran_id']);
            });
        }

        // Migrate existing data from kelas_id to pivot table
        if (Schema::hasColumn('mata_pelajaran', 'kelas_id')) {
            $mataPelajaranWithKelas = DB::table('mata_pelajaran')
                ->whereNotNull('kelas_id')
                ->select('id', 'kelas_id')
                ->get();

            foreach ($mataPelajaranWithKelas as $mapel) {
                // Check if pivot record already exists
                $exists = DB::table('kelas_mata_pelajaran')
                    ->where('mata_pelajaran_id', $mapel->id)
                    ->where('kelas_id', $mapel->kelas_id)
                    ->exists();

                if (!$exists) {
                    DB::table('kelas_mata_pelajaran')->insert([
                        'mata_pelajaran_id' => $mapel->id,
                        'kelas_id' => $mapel->kelas_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Remove kelas_id column - SQLite compatible approach
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'sqlite') {
                // For SQLite, we need to recreate the table
                DB::statement('PRAGMA foreign_keys=off;');
                
                // Create new table without kelas_id
                DB::statement('
                    CREATE TABLE mata_pelajaran_new (
                        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                        kode_mapel VARCHAR NOT NULL UNIQUE,
                        nama_mapel VARCHAR NOT NULL,
                        kkm INTEGER NOT NULL DEFAULT 75,
                        is_active TINYINT(1) NOT NULL DEFAULT 1,
                        created_at DATETIME,
                        updated_at DATETIME,
                        guru_id INTEGER,
                        FOREIGN KEY(guru_id) REFERENCES guru(id) ON DELETE CASCADE
                    );
                ');
                
                // Copy data
                DB::statement('
                    INSERT INTO mata_pelajaran_new (id, kode_mapel, nama_mapel, kkm, is_active, created_at, updated_at, guru_id)
                    SELECT id, kode_mapel, nama_mapel, kkm, is_active, created_at, updated_at, guru_id
                    FROM mata_pelajaran;
                ');
                
                // Drop old table and rename new one
                DB::statement('DROP TABLE mata_pelajaran;');
                DB::statement('ALTER TABLE mata_pelajaran_new RENAME TO mata_pelajaran;');
                
                DB::statement('PRAGMA foreign_keys=on;');
            } else {
                // For other databases (MySQL, PostgreSQL)
                Schema::table('mata_pelajaran', function (Blueprint $table) {
                    $table->dropForeign(['kelas_id']);
                    $table->dropColumn('kelas_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add kelas_id column back
        if (!Schema::hasColumn('mata_pelajaran', 'kelas_id')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->foreignId('kelas_id')->nullable()->after('guru_id')->constrained('kelas')->onDelete('cascade');
            });

            // Migrate data back from pivot table (take first kelas for each mata pelajaran)
            $pivotData = DB::table('kelas_mata_pelajaran')
                ->select('mata_pelajaran_id', 'kelas_id')
                ->orderBy('mata_pelajaran_id')
                ->orderBy('id')
                ->get()
                ->groupBy('mata_pelajaran_id');

            foreach ($pivotData as $mapelId => $kelasList) {
                $firstKelas = $kelasList->first();
                DB::table('mata_pelajaran')
                    ->where('id', $mapelId)
                    ->update(['kelas_id' => $firstKelas->kelas_id]);
            }
        }
    }
};
