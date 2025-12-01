<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataPelajaran;
use App\Models\Guru;
use App\Models\Kelas;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing mata pelajaran
        MataPelajaran::truncate();

        $gurus = Guru::where('status', 'aktif')->get();
        $kelas = Kelas::all();

        if ($gurus->isEmpty() || $kelas->isEmpty()) {
            $this->command->warn('Tidak ada guru atau kelas yang tersedia untuk membuat mata pelajaran.');
            return;
        }

        // Define mata pelajaran dengan mapping ke jurusan
        $mataPelajaranConfig = [
            // Mata Pelajaran Umum (untuk semua kelas)
            'PAI' => ['nama_mapel' => 'Pendidikan Agama Islam', 'kkm' => 75, 'jurusan' => null],
            'PKN' => ['nama_mapel' => 'Pendidikan Pancasila dan Kewarganegaraan', 'kkm' => 75, 'jurusan' => null],
            'BIN' => ['nama_mapel' => 'Bahasa Indonesia', 'kkm' => 75, 'jurusan' => null],
            'MTK' => ['nama_mapel' => 'Matematika', 'kkm' => 75, 'jurusan' => null],
            'SEJ' => ['nama_mapel' => 'Sejarah Indonesia', 'kkm' => 75, 'jurusan' => null],
            'BIG' => ['nama_mapel' => 'Bahasa Inggris', 'kkm' => 75, 'jurusan' => null],
            'SBDP' => ['nama_mapel' => 'Seni Budaya', 'kkm' => 75, 'jurusan' => null],
            'PJOK' => ['nama_mapel' => 'Pendidikan Jasmani Olahraga dan Kesehatan', 'kkm' => 75, 'jurusan' => null],

            // Mata Pelajaran Kejuruan RPL
            'PBO' => ['nama_mapel' => 'Pemrograman Berorientasi Objek', 'kkm' => 75, 'jurusan' => 'RPL'],
            'BD' => ['nama_mapel' => 'Basis Data', 'kkm' => 75, 'jurusan' => 'RPL'],
            'WEB' => ['nama_mapel' => 'Pemrograman Web', 'kkm' => 75, 'jurusan' => 'RPL'],
            'MOBILE' => ['nama_mapel' => 'Pemrograman Mobile', 'kkm' => 75, 'jurusan' => 'RPL'],
            'RPL' => ['nama_mapel' => 'Rekayasa Perangkat Lunak', 'kkm' => 75, 'jurusan' => 'RPL'],

            // Mata Pelajaran Kejuruan TKJ
            'JARKOM' => ['nama_mapel' => 'Jaringan Komputer', 'kkm' => 75, 'jurusan' => 'TKJ'],
            'SO' => ['nama_mapel' => 'Sistem Operasi', 'kkm' => 75, 'jurusan' => 'TKJ'],
            'ADMIN' => ['nama_mapel' => 'Administrasi Sistem Jaringan', 'kkm' => 75, 'jurusan' => 'TKJ'],
            'KEAMANAN' => ['nama_mapel' => 'Keamanan Jaringan', 'kkm' => 75, 'jurusan' => 'TKJ'],

            // Mata Pelajaran Kejuruan MM
            'DG' => ['nama_mapel' => 'Desain Grafis', 'kkm' => 75, 'jurusan' => 'MM'],
            'ANIMASI' => ['nama_mapel' => 'Animasi 2D/3D', 'kkm' => 75, 'jurusan' => 'MM'],
            'VIDEO' => ['nama_mapel' => 'Produksi Video', 'kkm' => 75, 'jurusan' => 'MM'],
            'FOTOGRAFI' => ['nama_mapel' => 'Fotografi', 'kkm' => 75, 'jurusan' => 'MM'],

            // Mata Pelajaran Muatan Lokal
            'BJSD' => ['nama_mapel' => 'Bahasa Jawa/Sunda', 'kkm' => 75, 'jurusan' => null],
            'KEWIRAUSAHAAN' => ['nama_mapel' => 'Kewirausahaan', 'kkm' => 75, 'jurusan' => null],
        ];

        $guruIndex = 0;
        $totalCreated = 0;

        foreach ($mataPelajaranConfig as $kodeBase => $config) {
            // Get kelas based on jurusan
            $kelasToAssign = collect();
            
            if ($config['jurusan']) {
                // Kejuruan: hanya kelas dengan jurusan tertentu
                $kelasToAssign = $kelas->filter(function ($k) use ($config) {
                    return $k->jurusan && $k->jurusan->kode_jurusan === $config['jurusan'];
                });
            } else {
                // Umum/Muatan Lokal: semua kelas
                $kelasToAssign = $kelas;
            }

            // Create mata pelajaran for each kelas
            foreach ($kelasToAssign as $kelasItem) {
                // Generate unique kode_mapel: kodeBase-tingkat-kelas_id
                $kodeMapel = "{$kodeBase}-{$kelasItem->tingkat}-{$kelasItem->id}";

                // Get guru (round robin)
                $guru = $gurus->get($guruIndex % $gurus->count());
                $guruIndex++;

                // Check if already exists
                $existing = MataPelajaran::where('kode_mapel', $kodeMapel)->first();
                if ($existing) {
                    continue;
                }

                MataPelajaran::create([
                    'kode_mapel' => $kodeMapel,
                    'nama_mapel' => $config['nama_mapel'],
                    'kkm' => $config['kkm'],
                    'guru_id' => $guru->id,
                    'kelas_id' => $kelasItem->id,
                    'is_active' => true,
                ]);

                $totalCreated++;
                $this->command->info("Created: {$kodeMapel} - {$config['nama_mapel']} -> {$kelasItem->nama_kelas} ({$guru->nama_lengkap})");
            }
        }

        $this->command->info("Total mata pelajaran created: {$totalCreated}");
    }
}
