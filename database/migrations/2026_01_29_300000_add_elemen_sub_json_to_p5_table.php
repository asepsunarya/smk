<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p5', function (Blueprint $table) {
            $table->json('elemen_sub')->nullable()->after('sub_elemen');
        });
    }

    public function down(): void
    {
        Schema::table('p5', function (Blueprint $table) {
            $table->dropColumn('elemen_sub');
        });
    }
};
