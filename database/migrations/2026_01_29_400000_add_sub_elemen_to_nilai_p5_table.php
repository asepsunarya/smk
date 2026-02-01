<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skipped: changing unique from (siswa_id, p5_id, dimensi_id) to (siswa_id, p5_id, sub_elemen)
     * causes MySQL 1553 (index needed by FK). Only add column sub_elemen so app can use it.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('nilai_p5', 'sub_elemen')) {
            Schema::table('nilai_p5', function (Blueprint $table) {
                $table->string('sub_elemen', 500)->nullable()->after('dimensi_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nilai_p5', 'sub_elemen')) {
            Schema::table('nilai_p5', function (Blueprint $table) {
                $table->dropColumn('sub_elemen');
            });
        }
    }
};
