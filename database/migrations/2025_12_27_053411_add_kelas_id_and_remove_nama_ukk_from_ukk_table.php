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
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            
            // Create new table with kelas_id instead of nama_ukk
            DB::statement('
                CREATE TABLE ukk_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    siswa_id INTEGER NOT NULL,
                    jurusan_id INTEGER NOT NULL,
                    kelas_id INTEGER,
                    tanggal_ujian DATE NOT NULL,
                    nilai_teori INTEGER,
                    nilai_praktek INTEGER,
                    nilai_akhir DECIMAL(5,2),
                    predikat VARCHAR,
                    penguji_internal_id INTEGER NOT NULL,
                    penguji_eksternal VARCHAR,
                    tahun_ajaran_id INTEGER NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY(siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
                    FOREIGN KEY(jurusan_id) REFERENCES jurusan(id) ON DELETE CASCADE,
                    FOREIGN KEY(kelas_id) REFERENCES kelas(id) ON DELETE SET NULL,
                    FOREIGN KEY(penguji_internal_id) REFERENCES guru(id) ON DELETE CASCADE,
                    FOREIGN KEY(tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE CASCADE
                );
            ');
            
            // Copy data - get kelas_id from siswa
            DB::statement('
                INSERT INTO ukk_new (id, siswa_id, jurusan_id, kelas_id, tanggal_ujian, nilai_teori, nilai_praktek, nilai_akhir, predikat, penguji_internal_id, penguji_eksternal, tahun_ajaran_id, created_at, updated_at)
                SELECT 
                    u.id,
                    u.siswa_id,
                    u.jurusan_id,
                    s.kelas_id,
                    u.tanggal_ujian,
                    u.nilai_teori,
                    u.nilai_praktek,
                    u.nilai_akhir,
                    u.predikat,
                    u.penguji_internal_id,
                    u.penguji_eksternal,
                    u.tahun_ajaran_id,
                    u.created_at,
                    u.updated_at
                FROM ukk u
                LEFT JOIN siswa s ON s.id = u.siswa_id
            ');
            
            // Drop old table and rename new one
            DB::statement('DROP TABLE ukk;');
            DB::statement('ALTER TABLE ukk_new RENAME TO ukk;');
            
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For other databases (MySQL, PostgreSQL)
            Schema::table('ukk', function (Blueprint $table) {
                $table->foreignId('kelas_id')->nullable()->after('jurusan_id')->constrained('kelas')->onDelete('set null');
            });
            
            // Migrate data - get kelas_id from siswa
            DB::statement('
                UPDATE ukk u
                INNER JOIN siswa s ON s.id = u.siswa_id
                SET u.kelas_id = s.kelas_id
            ');
            
            // Drop nama_ukk column
            Schema::table('ukk', function (Blueprint $table) {
                $table->dropColumn('nama_ukk');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            
            // Create new table with nama_ukk
            DB::statement('
                CREATE TABLE ukk_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                    siswa_id INTEGER NOT NULL,
                    jurusan_id INTEGER NOT NULL,
                    nama_ukk VARCHAR NOT NULL,
                    tanggal_ujian DATE NOT NULL,
                    nilai_teori INTEGER,
                    nilai_praktek INTEGER,
                    nilai_akhir DECIMAL(5,2),
                    predikat VARCHAR,
                    penguji_internal_id INTEGER NOT NULL,
                    penguji_eksternal VARCHAR,
                    tahun_ajaran_id INTEGER NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY(siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
                    FOREIGN KEY(jurusan_id) REFERENCES jurusan(id) ON DELETE CASCADE,
                    FOREIGN KEY(penguji_internal_id) REFERENCES guru(id) ON DELETE CASCADE,
                    FOREIGN KEY(tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE CASCADE
                );
            ');
            
            // Copy data - set nama_ukk to default value
            DB::statement('
                INSERT INTO ukk_new (id, siswa_id, jurusan_id, nama_ukk, tanggal_ujian, nilai_teori, nilai_praktek, nilai_akhir, predikat, penguji_internal_id, penguji_eksternal, tahun_ajaran_id, created_at, updated_at)
                SELECT 
                    id,
                    siswa_id,
                    jurusan_id,
                    "UKK" as nama_ukk,
                    tanggal_ujian,
                    nilai_teori,
                    nilai_praktek,
                    nilai_akhir,
                    predikat,
                    penguji_internal_id,
                    penguji_eksternal,
                    tahun_ajaran_id,
                    created_at,
                    updated_at
                FROM ukk
            ');
            
            // Drop old table and rename new one
            DB::statement('DROP TABLE ukk;');
            DB::statement('ALTER TABLE ukk_new RENAME TO ukk;');
            
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // Add nama_ukk column
            Schema::table('ukk', function (Blueprint $table) {
                $table->string('nama_ukk')->default('UKK')->after('jurusan_id');
            });
            
            // Drop foreign key and kelas_id column
            Schema::table('ukk', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            });
        }
    }
};
