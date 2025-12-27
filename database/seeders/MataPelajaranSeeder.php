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

            // Skip if no kelas to assign
            if ($kelasToAssign->isEmpty()) {
                continue;
            }

            // Check if mata pelajaran with this kode already exists
            $existingMapel = MataPelajaran::where('kode_mapel', $kodeBase)->first();

            if ($existingMapel) {
                // If exists, just sync the kelas
                $kelasIds = $kelasToAssign->pluck('id')->toArray();
                $existingMapel->kelas()->syncWithoutDetaching($kelasIds);
                $this->command->info("Synced kelas to existing: {$kodeBase} - {$config['nama_mapel']}");
            } else {
                // Get guru (round robin)
                $guru = $gurus->get($guruIndex % $gurus->count());
                $guruIndex++;

                // Create one mata pelajaran with the base kode
                $mataPelajaran = MataPelajaran::create([
                    'kode_mapel' => $kodeBase,
                    'nama_mapel' => $config['nama_mapel'],
                    'kkm' => $config['kkm'],
                    'guru_id' => $guru->id,
                    'is_active' => true,
                ]);

                // Attach all kelas to this mata pelajaran
                $kelasIds = $kelasToAssign->pluck('id')->toArray();
                $mataPelajaran->kelas()->attach($kelasIds);

                $kelasNames = $kelasToAssign->pluck('nama_kelas')->join(', ');
                $this->command->info("Created: {$kodeBase} - {$config['nama_mapel']} -> {$kelasNames} ({$guru->nama_lengkap})");
                $totalCreated++;
            }
        }

        $this->command->info("Total mata pelajaran created: {$totalCreated}");
    }
}
