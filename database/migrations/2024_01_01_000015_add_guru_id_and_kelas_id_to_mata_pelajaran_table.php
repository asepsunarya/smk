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
        // First, drop pivot tables if they exist
        Schema::dropIfExists('guru_mata_pelajaran');
        Schema::dropIfExists('kelas_mata_pelajaran');

        // Delete all existing mata_pelajaran data (will be reseeded)
        DB::table('mata_pelajaran')->delete();

        // Check if columns already exist before adding
        if (!Schema::hasColumn('mata_pelajaran', 'guru_id')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->foreignId('guru_id')->after('nama_mapel')->constrained('guru')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('mata_pelajaran', 'kelas_id')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->foreignId('kelas_id')->after('guru_id')->constrained('kelas')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mata_pelajaran', 'guru_id')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->dropForeign(['guru_id']);
                $table->dropColumn('guru_id');
            });
        }

        if (Schema::hasColumn('mata_pelajaran', 'kelas_id')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            });
        }

        // Recreate pivot tables
        Schema::create('guru_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['guru_id', 'mata_pelajaran_id']);
        });

        Schema::create('kelas_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['kelas_id', 'mata_pelajaran_id']);
        });
    }
};
