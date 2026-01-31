<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * NUPTK nullable: simpan NULL ketika kosong/hanya strip; saat get tampilkan "-".
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement("CREATE TABLE guru_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                nuptk TEXT NULL UNIQUE,
                nama_lengkap TEXT NOT NULL,
                jenis_kelamin TEXT NOT NULL CHECK(jenis_kelamin IN ('L', 'P')),
                tempat_lahir TEXT NOT NULL,
                tanggal_lahir DATE NOT NULL,
                agama TEXT NOT NULL CHECK(agama IN ('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu')),
                alamat TEXT NOT NULL,
                no_hp TEXT NOT NULL,
                pendidikan_terakhir TEXT NOT NULL,
                bidang_studi TEXT NOT NULL,
                tanggal_masuk DATE NOT NULL,
                status TEXT NOT NULL CHECK(status IN ('aktif', 'non_aktif', 'pensiun')),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );");
            // Copy data: set nuptk to NULL when empty or only hyphens (no digit)
            DB::statement("INSERT INTO guru_new (id, user_id, nuptk, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, alamat, no_hp, pendidikan_terakhir, bidang_studi, tanggal_masuk, status, created_at, updated_at)
                SELECT id, user_id,
                CASE
                    WHEN nuptk IS NULL OR trim(nuptk) = '' THEN NULL
                    WHEN nuptk NOT LIKE '%0%' AND nuptk NOT LIKE '%1%' AND nuptk NOT LIKE '%2%' AND nuptk NOT LIKE '%3%' AND nuptk NOT LIKE '%4%' AND nuptk NOT LIKE '%5%' AND nuptk NOT LIKE '%6%' AND nuptk NOT LIKE '%7%' AND nuptk NOT LIKE '%8%' AND nuptk NOT LIKE '%9%' THEN NULL
                    ELSE nuptk
                END,
                nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, alamat, no_hp, pendidikan_terakhir, bidang_studi, tanggal_masuk, status, created_at, updated_at
                FROM guru;");
            DB::statement('DROP TABLE guru;');
            DB::statement('ALTER TABLE guru_new RENAME TO guru;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('guru', function (Blueprint $table) {
                $table->string('nuptk')->nullable()->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement("CREATE TABLE guru_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                nuptk TEXT NOT NULL UNIQUE,
                nama_lengkap TEXT NOT NULL,
                jenis_kelamin TEXT NOT NULL CHECK(jenis_kelamin IN ('L', 'P')),
                tempat_lahir TEXT NOT NULL,
                tanggal_lahir DATE NOT NULL,
                agama TEXT NOT NULL CHECK(agama IN ('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu')),
                alamat TEXT NOT NULL,
                no_hp TEXT NOT NULL,
                pendidikan_terakhir TEXT NOT NULL,
                bidang_studi TEXT NOT NULL,
                tanggal_masuk DATE NOT NULL,
                status TEXT NOT NULL CHECK(status IN ('aktif', 'non_aktif', 'pensiun')),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );");
            DB::statement("INSERT INTO guru_old (id, user_id, nuptk, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, alamat, no_hp, pendidikan_terakhir, bidang_studi, tanggal_masuk, status, created_at, updated_at)
                SELECT id, user_id, COALESCE(nullif(trim(nuptk), ''), '-'), nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, alamat, no_hp, pendidikan_terakhir, bidang_studi, tanggal_masuk, status, created_at, updated_at FROM guru;");
            DB::statement('DROP TABLE guru;');
            DB::statement('ALTER TABLE guru_old RENAME TO guru;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('guru', function (Blueprint $table) {
                $table->string('nuptk')->nullable(false)->unique()->change();
            });
        }
    }
};
