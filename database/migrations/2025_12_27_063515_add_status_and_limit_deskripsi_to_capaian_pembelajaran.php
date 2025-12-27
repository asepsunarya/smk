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
            // SQLite doesn't support ALTER COLUMN for TEXT/VARCHAR
            // We'll add the is_active column and note that deskripsi limit is application-level
            Schema::table('capaian_pembelajaran', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('elemen');
            });
        } else {
            // MySQL/MariaDB
            Schema::table('capaian_pembelajaran', function (Blueprint $table) {
                $table->string('deskripsi', 200)->change();
                $table->boolean('is_active')->default(true)->after('elemen');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            Schema::table('capaian_pembelajaran', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        } else {
            Schema::table('capaian_pembelajaran', function (Blueprint $table) {
                $table->text('deskripsi')->change();
                $table->dropColumn('is_active');
            });
        }
    }
};
