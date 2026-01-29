<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p5', function (Blueprint $table) {
            $table->dropForeign(['koordinator_id']);
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->unsignedBigInteger('koordinator_id')->nullable()->change();
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->foreign('koordinator_id')->references('id')->on('guru')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('p5', function (Blueprint $table) {
            $table->dropForeign(['koordinator_id']);
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->unsignedBigInteger('koordinator_id')->nullable(false)->change();
        });
        Schema::table('p5', function (Blueprint $table) {
            $table->foreign('koordinator_id')->references('id')->on('guru')->onDelete('cascade');
        });
    }
};
