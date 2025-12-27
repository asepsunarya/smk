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
        // SQLite doesn't support dropping foreign keys directly
        // We need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, we need to recreate the table
            // Use raw SQL for SQLite
            DB::statement('PRAGMA foreign_keys=off;');
            
            // Drop siswa_new if it exists from previous failed migration
            DB::statement('DROP TABLE IF EXISTS siswa_new;');
            
            DB::statement('CREATE TABLE siswa_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                nis TEXT NOT NULL UNIQUE,
                nisn TEXT NULL UNIQUE,
                nama_lengkap TEXT NOT NULL,
                jenis_kelamin TEXT NOT NULL CHECK(jenis_kelamin IN (\'L\', \'P\')),
                tempat_lahir TEXT NOT NULL,
                tanggal_lahir DATE NOT NULL,
                agama TEXT NOT NULL CHECK(agama IN (\'Islam\', \'Kristen\', \'Katolik\', \'Hindu\', \'Buddha\', \'Konghucu\')),
                alamat TEXT NOT NULL,
                no_hp TEXT NULL,
                nama_ayah TEXT NULL,
                nama_ibu TEXT NULL,
                pekerjaan_ayah TEXT NULL,
                pekerjaan_ibu TEXT NULL,
                no_hp_ortu TEXT NULL,
                kelas_id INTEGER NULL,
                tanggal_masuk DATE NOT NULL,
                status TEXT NOT NULL CHECK(status IN (\'aktif\', \'lulus\', \'pindah\', \'keluar\')),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL
            );');
            DB::statement('INSERT INTO siswa_new SELECT * FROM siswa;');
            DB::statement('DROP TABLE siswa;');
            DB::statement('ALTER TABLE siswa_new RENAME TO siswa;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For other databases (MySQL, PostgreSQL)
            Schema::table('siswa', function (Blueprint $table) {
                // Drop the foreign key constraint first
                $table->dropForeign(['user_id']);
            });
            
            Schema::table('siswa', function (Blueprint $table) {
                // Make user_id nullable
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
            
            Schema::table('siswa', function (Blueprint $table) {
                // Re-add foreign key constraint with nullable
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
            
            // Drop siswa_new if it exists from previous failed migration
            DB::statement('DROP TABLE IF EXISTS siswa_new;');
            
            DB::statement('CREATE TABLE siswa_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                nis TEXT NOT NULL UNIQUE,
                nisn TEXT NULL UNIQUE,
                nama_lengkap TEXT NOT NULL,
                jenis_kelamin TEXT NOT NULL CHECK(jenis_kelamin IN (\'L\', \'P\')),
                tempat_lahir TEXT NOT NULL,
                tanggal_lahir DATE NOT NULL,
                agama TEXT NOT NULL CHECK(agama IN (\'Islam\', \'Kristen\', \'Katolik\', \'Hindu\', \'Buddha\', \'Konghucu\')),
                alamat TEXT NOT NULL,
                no_hp TEXT NULL,
                nama_ayah TEXT NULL,
                nama_ibu TEXT NULL,
                pekerjaan_ayah TEXT NULL,
                pekerjaan_ibu TEXT NULL,
                no_hp_ortu TEXT NULL,
                kelas_id INTEGER NULL,
                tanggal_masuk DATE NOT NULL,
                status TEXT NOT NULL CHECK(status IN (\'aktif\', \'lulus\', \'pindah\', \'keluar\')),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL
            );');
            DB::statement('INSERT INTO siswa_new SELECT * FROM siswa WHERE user_id IS NOT NULL;');
            DB::statement('DROP TABLE siswa;');
            DB::statement('ALTER TABLE siswa_new RENAME TO siswa;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For other databases
            Schema::table('siswa', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            
            Schema::table('siswa', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
            
            Schema::table('siswa', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};

