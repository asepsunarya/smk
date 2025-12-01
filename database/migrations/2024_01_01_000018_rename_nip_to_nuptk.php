<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename nip to nuptk in users table
        if (Schema::hasColumn('users', 'nip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('nip', 'nuptk');
            });
        }

        // Rename nip to nuptk in guru table
        if (Schema::hasColumn('guru', 'nip')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->renameColumn('nip', 'nuptk');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename nuptk back to nip in users table
        if (Schema::hasColumn('users', 'nuptk')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('nuptk', 'nip');
            });
        }

        // Rename nuptk back to nip in guru table
        if (Schema::hasColumn('guru', 'nuptk')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->renameColumn('nuptk', 'nip');
            });
        }
    }
};

