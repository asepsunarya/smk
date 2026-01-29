<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SQLite compatible: no need to modify enum, just ensure data is valid.
     * SQLite stores enum as TEXT, so we can just add 'pending' as valid value.
     */
    public function up(): void
    {
        // For SQLite, enum is stored as TEXT, so no need to modify column
        // Just ensure existing data is valid. The constraint is handled at application level.
        // If using MySQL/MariaDB, this would need MODIFY COLUMN, but for SQLite we skip it.
        
        // Check if we're using SQLite
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN, enum is just TEXT
            // No action needed - 'pending' can be stored as-is
            return;
        }
        
        // For MySQL/MariaDB
        DB::statement("ALTER TABLE rapor MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'published') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any 'pending' records to 'draft'
        DB::table('rapor')->where('status', 'pending')->update(['status' => 'draft']);
        
        // For SQLite, no need to modify column
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // For MySQL/MariaDB
        DB::statement("ALTER TABLE rapor MODIFY COLUMN status ENUM('draft', 'approved', 'published') DEFAULT 'draft'");
    }
};
