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
        // Check if pembimbing_sekolah column exists (string)
        if (Schema::hasColumn('pkl', 'pembimbing_sekolah')) {
            // For SQLite, we need to recreate the table
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys=off;');
                
                // Create new table with pembimbing_sekolah_id
                DB::statement('
                    CREATE TABLE pkl_new (
                        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                        nama_perusahaan VARCHAR NOT NULL,
                        alamat_perusahaan TEXT NOT NULL,
                        pembimbing_perusahaan VARCHAR NOT NULL,
                        pembimbing_sekolah_id INTEGER,
                        tanggal_mulai DATE NOT NULL,
                        tanggal_selesai DATE NOT NULL,
                        tahun_ajaran_id INTEGER NOT NULL,
                        created_at DATETIME,
                        updated_at DATETIME,
                        FOREIGN KEY(pembimbing_sekolah_id) REFERENCES guru(id) ON DELETE SET NULL,
                        FOREIGN KEY(tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE CASCADE
                    );
                ');
                
                // Copy data - try to match pembimbing_sekolah string to guru nama_lengkap
                // Use subquery to get first matching guru
                DB::statement('
                    INSERT INTO pkl_new (id, nama_perusahaan, alamat_perusahaan, pembimbing_perusahaan, pembimbing_sekolah_id, tanggal_mulai, tanggal_selesai, tahun_ajaran_id, created_at, updated_at)
                    SELECT 
                        p.id,
                        p.nama_perusahaan,
                        p.alamat_perusahaan,
                        p.pembimbing_perusahaan,
                        (SELECT g2.id FROM guru g2 
                         WHERE (g2.nama_lengkap = p.pembimbing_sekolah OR g2.nama_lengkap LIKE "%" || p.pembimbing_sekolah || "%")
                         LIMIT 1) as pembimbing_sekolah_id,
                        p.tanggal_mulai,
                        p.tanggal_selesai,
                        p.tahun_ajaran_id,
                        p.created_at,
                        p.updated_at
                    FROM pkl p
                ');
                
                // Drop old table and rename new one
                DB::statement('DROP TABLE pkl;');
                DB::statement('ALTER TABLE pkl_new RENAME TO pkl;');
                
                DB::statement('PRAGMA foreign_keys=on;');
            } else {
                // For other databases (MySQL, PostgreSQL)
                // Add new column
                Schema::table('pkl', function (Blueprint $table) {
                    $table->foreignId('pembimbing_sekolah_id')->nullable()->after('pembimbing_perusahaan')->constrained('guru')->onDelete('set null');
                });
                
                // Migrate data - try to match pembimbing_sekolah string to guru nama_lengkap
                DB::statement('
                    UPDATE pkl p
                    LEFT JOIN guru g ON g.nama_lengkap = p.pembimbing_sekolah 
                        OR g.nama_lengkap LIKE CONCAT("%", p.pembimbing_sekolah, "%")
                    SET p.pembimbing_sekolah_id = g.id
                    WHERE g.id IS NOT NULL
                ');
                
                // Drop old column
                Schema::table('pkl', function (Blueprint $table) {
                    $table->dropColumn('pembimbing_sekolah');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if pembimbing_sekolah_id column exists
        if (Schema::hasColumn('pkl', 'pembimbing_sekolah_id')) {
            $driver = DB::connection()->getDriverName();
            
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys=off;');
                
                // Create new table with pembimbing_sekolah as string
                DB::statement('
                    CREATE TABLE pkl_new (
                        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                        nama_perusahaan VARCHAR NOT NULL,
                        alamat_perusahaan TEXT NOT NULL,
                        pembimbing_perusahaan VARCHAR NOT NULL,
                        pembimbing_sekolah VARCHAR,
                        tanggal_mulai DATE NOT NULL,
                        tanggal_selesai DATE NOT NULL,
                        tahun_ajaran_id INTEGER NOT NULL,
                        created_at DATETIME,
                        updated_at DATETIME,
                        FOREIGN KEY(tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE CASCADE
                    );
                ');
                
                // Copy data - get nama_lengkap from guru
                DB::statement('
                    INSERT INTO pkl_new (id, nama_perusahaan, alamat_perusahaan, pembimbing_perusahaan, pembimbing_sekolah, tanggal_mulai, tanggal_selesai, tahun_ajaran_id, created_at, updated_at)
                    SELECT 
                        p.id,
                        p.nama_perusahaan,
                        p.alamat_perusahaan,
                        p.pembimbing_perusahaan,
                        g.nama_lengkap as pembimbing_sekolah,
                        p.tanggal_mulai,
                        p.tanggal_selesai,
                        p.tahun_ajaran_id,
                        p.created_at,
                        p.updated_at
                    FROM pkl p
                    LEFT JOIN guru g ON g.id = p.pembimbing_sekolah_id
                ');
                
                // Drop old table and rename new one
                DB::statement('DROP TABLE pkl;');
                DB::statement('ALTER TABLE pkl_new RENAME TO pkl;');
                
                DB::statement('PRAGMA foreign_keys=on;');
            } else {
                // Add pembimbing_sekolah column
                Schema::table('pkl', function (Blueprint $table) {
                    $table->string('pembimbing_sekolah')->nullable()->after('pembimbing_perusahaan');
                });
                
                // Migrate data - get nama_lengkap from guru
                DB::statement('
                    UPDATE pkl p
                    INNER JOIN guru g ON g.id = p.pembimbing_sekolah_id
                    SET p.pembimbing_sekolah = g.nama_lengkap
                ');
                
                // Drop foreign key and column
                Schema::table('pkl', function (Blueprint $table) {
                    $table->dropForeign(['pembimbing_sekolah_id']);
                    $table->dropColumn('pembimbing_sekolah_id');
                });
            }
        }
    }
};
