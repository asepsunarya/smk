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
            Schema::table('guru', function (Blueprint $table) {
                // SQLite doesn't support modify column with foreign key
                // We'll use raw SQL
            });
            
            // Use raw SQL for SQLite
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement('CREATE TABLE guru_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                nuptk TEXT NOT NULL UNIQUE,
                nama_lengkap TEXT NOT NULL,
                jenis_kelamin TEXT NOT NULL CHECK(jenis_kelamin IN (\'L\', \'P\')),
                tempat_lahir TEXT NOT NULL,
                tanggal_lahir DATE NOT NULL,
                agama TEXT NOT NULL CHECK(agama IN (\'Islam\', \'Kristen\', \'Katolik\', \'Hindu\', \'Buddha\', \'Konghucu\')),
                alamat TEXT NOT NULL,
                no_hp TEXT NOT NULL,
                pendidikan_terakhir TEXT NOT NULL,
                bidang_studi TEXT NOT NULL,
                tanggal_masuk DATE NOT NULL,
                status TEXT NOT NULL CHECK(status IN (\'aktif\', \'non_aktif\', \'pensiun\')),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );');
            DB::statement('INSERT INTO guru_new SELECT * FROM guru;');
            DB::statement('DROP TABLE guru;');
            DB::statement('ALTER TABLE guru_new RENAME TO guru;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For other databases (MySQL, PostgreSQL)
            Schema::table('guru', function (Blueprint $table) {
                // Drop the foreign key constraint first
                $table->dropForeign(['user_id']);
            });
            
            Schema::table('guru', function (Blueprint $table) {
                // Make user_id nullable
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
            
            Schema::table('guru', function (Blueprint $table) {
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
            // For SQLite, recreate table with NOT NULL
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement('CREATE TABLE guru_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                nuptk TEXT NOT NULL UNIQUE,
                nama_lengkap TEXT NOT NULL,
                jenis_kelamin TEXT NOT NULL CHECK(jenis_kelamin IN (\'L\', \'P\')),
                tempat_lahir TEXT NOT NULL,
                tanggal_lahir DATE NOT NULL,
                agama TEXT NOT NULL CHECK(agama IN (\'Islam\', \'Kristen\', \'Katolik\', \'Hindu\', \'Buddha\', \'Konghucu\')),
                alamat TEXT NOT NULL,
                no_hp TEXT NOT NULL,
                pendidikan_terakhir TEXT NOT NULL,
                bidang_studi TEXT NOT NULL,
                tanggal_masuk DATE NOT NULL,
                status TEXT NOT NULL CHECK(status IN (\'aktif\', \'non_aktif\', \'pensiun\')),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );');
            DB::statement('INSERT INTO guru_old SELECT * FROM guru WHERE user_id IS NOT NULL;');
            DB::statement('DROP TABLE guru;');
            DB::statement('ALTER TABLE guru_old RENAME TO guru;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For other databases
            Schema::table('guru', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            
            Schema::table('guru', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
            
            Schema::table('guru', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};

