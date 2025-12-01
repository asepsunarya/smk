<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaliKelas;
use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class WaliKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active gurus who can be wali kelas
        $gurus = Guru::whereHas('user', function ($query) {
            $query->whereIn('role', ['wali_kelas', 'guru', 'kepala_sekolah'])
                  ->where('is_active', true);
        })
        ->where('status', 'aktif')
        ->get();

        // Get all kelas
        $kelas = Kelas::all();

        if ($gurus->isEmpty() || $kelas->isEmpty()) {
            $this->command->warn('Tidak ada guru atau kelas yang tersedia untuk ditetapkan sebagai wali kelas.');
            return;
        }

        // Assign wali kelas to each kelas
        foreach ($kelas as $index => $kelasItem) {
            // Skip if kelas already has active wali kelas
            $existingWaliKelas = WaliKelas::where('kelas_id', $kelasItem->id)
                                         ->where('is_active', true)
                                         ->first();

            if ($existingWaliKelas) {
                continue;
            }

            // Assign guru to kelas (round robin)
            $guru = $gurus->get($index % $gurus->count());

            WaliKelas::create([
                'guru_id' => $guru->id,
                'kelas_id' => $kelasItem->id,
                'tanggal_mulai' => now()->subMonths(rand(1, 12)),
                'is_active' => true,
                'keterangan' => 'Ditugaskan sebagai wali kelas melalui seeder',
            ]);

            $this->command->info("Wali kelas ditetapkan: {$guru->nama_lengkap} -> {$kelasItem->nama_kelas}");
        }

        $this->command->info('Wali kelas seeder selesai!');
    }
}

