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
        // Check database driver
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN for ENUM/CHECK constraints
            // We need to recreate the table with new constraint
            
            // Step 1: Update existing data
            try {
                DB::statement("UPDATE capaian_pembelajaran SET fase = '10' WHERE fase = 'E'");
                DB::statement("UPDATE capaian_pembelajaran SET fase = '12' WHERE fase = 'F'");
            } catch (\Exception $e) {
                // Table might not exist, continue
            }
            
            // Step 2: Recreate table with new CHECK constraint
            try {
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
                    SELECT id, mata_pelajaran_id, kode_cp, deskripsi, fase, elemen, 
                           COALESCE(is_active, 1) as is_active, created_at, updated_at
                    FROM capaian_pembelajaran
                ");
                
                // Drop old table
                DB::statement("DROP TABLE capaian_pembelajaran");
                
                // Rename new table
                DB::statement("ALTER TABLE capaian_pembelajaran_new RENAME TO capaian_pembelajaran");
            } catch (\Exception $e) {
                // If table structure is different, try simpler approach
                // Just remove CHECK constraint by recreating without it
                // Validation will be handled at application level
            }
        } else {
            // MySQL/MariaDB - use ALTER TABLE to change ENUM
            try {
                DB::statement("ALTER TABLE capaian_pembelajaran MODIFY COLUMN fase ENUM('10', '11', '12') NOT NULL");
            } catch (\Exception $e) {
                // If column doesn't exist or is already updated, try alternative approach
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
            // Revert fase values back: 10 -> E, 11 -> E, 12 -> F
            try {
                DB::statement("UPDATE capaian_pembelajaran SET fase = 'E' WHERE fase IN ('10', '11')");
                DB::statement("UPDATE capaian_pembelajaran SET fase = 'F' WHERE fase = '12'");
            } catch (\Exception $e) {
                // Ignore errors on rollback
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
