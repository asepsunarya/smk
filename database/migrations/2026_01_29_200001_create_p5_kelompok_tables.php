<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p5_kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_id')->constrained('p5')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('p5_kelompok_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_kelompok_id')->constrained('p5_kelompok')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['p5_kelompok_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p5_kelompok_siswa');
        Schema::dropIfExists('p5_kelompok');
    }
};
