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
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN for CHECK constraints
            // We need to recreate the table with new CHECK constraint
            
            // Step 1: Update existing data first
            try {
                DB::statement("UPDATE capaian_pembelajaran SET fase = '10' WHERE fase = 'E'");
                DB::statement("UPDATE capaian_pembelajaran SET fase = '12' WHERE fase = 'F'");
            } catch (\Exception $e) {
                // Continue even if update fails
            }
            
            // Step 2: Recreate table with new CHECK constraint
            try {
                // Disable foreign key checks temporarily
                DB::statement("PRAGMA foreign_keys=OFF");
                
                // Create new table with updated constraint
                DB::statement("
                    CREATE TABLE capaian_pembelajaran_new (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        mata_pelajaran_id INTEGER NOT NULL,
                        kode_cp TEXT NOT NULL,
                        deskripsi TEXT NOT NULL,
                        fase TEXT NOT NULL CHECK(fase IN ('10', '11', '12')),
                        elemen TEXT NOT NULL CHECK(elemen IN ('pemahaman', 'keterampilan', 'sikap')),
                        is_active INTEGER DEFAULT 1,
                        created_at TIMESTAMP,
                        updated_at TIMESTAMP,
                        UNIQUE(kode_cp, mata_pelajaran_id),
                        FOREIGN KEY (mata_pelajaran_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE
                    )
                ");
                
                // Copy data from old table
                DB::statement("
                    INSERT INTO capaian_pembelajaran_new 
                    (id, mata_pelajaran_id, kode_cp, deskripsi, fase, elemen, is_active, created_at, updated_at)
                    SELECT id, mata_pelajaran_id, kode_cp, deskripsi, fase, elemen, 
                           COALESCE(is_active, 1) as is_active, created_at, updated_at
                    FROM capaian_pembelajaran
                ");
                
                // Drop old table
                DB::statement("DROP TABLE capaian_pembelajaran");
                
                // Rename new table
                DB::statement("ALTER TABLE capaian_pembelajaran_new RENAME TO capaian_pembelajaran");
                
                // Re-enable foreign key checks
                DB::statement("PRAGMA foreign_keys=ON");
            } catch (\Exception $e) {
                // Re-enable foreign key checks even if error
                DB::statement("PRAGMA foreign_keys=ON");
                throw $e;
            }
        } else {
            // MySQL/MariaDB - use ALTER TABLE to change ENUM
            try {
                DB::statement("ALTER TABLE capaian_pembelajaran MODIFY COLUMN fase ENUM('10', '11', '12') NOT NULL");
            } catch (\Exception $e) {
                Schema::table('capaian_pembelajaran', function (Blueprint $table) {
                    $table->enum('fase', ['10', '11', '12'])->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // Revert back to old constraint
            try {
                DB::statement("PRAGMA foreign_keys=OFF");
                
                // Update data back
                DB::statement("UPDATE capaian_pembelajaran SET fase = 'E' WHERE fase IN ('10', '11')");
                DB::statement("UPDATE capaian_pembelajaran SET fase = 'F' WHERE fase = '12'");
                
                // Recreate with old constraint
                DB::statement("
                    CREATE TABLE capaian_pembelajaran_old (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        mata_pelajaran_id INTEGER NOT NULL,
                        kode_cp TEXT NOT NULL,
                        deskripsi TEXT NOT NULL,
                        fase TEXT NOT NULL CHECK(fase IN ('E', 'F')),
                        elemen TEXT NOT NULL CHECK(elemen IN ('pemahaman', 'keterampilan', 'sikap')),
                        is_active INTEGER DEFAULT 1,
                        created_at TIMESTAMP,
                        updated_at TIMESTAMP,
                        UNIQUE(kode_cp, mata_pelajaran_id),
                        FOREIGN KEY (mata_pelajaran_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE
                    )
                ");
                
                DB::statement("
                    INSERT INTO capaian_pembelajaran_old 
                    SELECT * FROM capaian_pembelajaran
                ");
                
                DB::statement("DROP TABLE capaian_pembelajaran");
                DB::statement("ALTER TABLE capaian_pembelajaran_old RENAME TO capaian_pembelajaran");
                
                DB::statement("PRAGMA foreign_keys=ON");
            } catch (\Exception $e) {
                DB::statement("PRAGMA foreign_keys=ON");
            }
        } else {
            // MySQL/MariaDB
            try {
                DB::statement("ALTER TABLE capaian_pembelajaran MODIFY COLUMN fase ENUM('E', 'F') NOT NULL");
            } catch (\Exception $e) {
                Schema::table('capaian_pembelajaran', function (Blueprint $table) {
                    $table->enum('fase', ['E', 'F'])->change();
                });
            }
        }
    }
};
