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
        Schema::table('ukk', function (Blueprint $table) {
            $table->string('nama_du_di')->nullable()->after('kelas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ukk', function (Blueprint $table) {
            $table->dropColumn('nama_du_di');
        });
    }
};
