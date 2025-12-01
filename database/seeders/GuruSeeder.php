<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use App\Models\User;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guruData = [
            [
                'email' => 'siti.aminah@smk.sch.id',
                'nuptk' => '197203121998032001',
                'nama_lengkap' => 'Dra. Siti Aminah, M.Pd',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1972-03-12',
                'agama' => 'Islam',
                'alamat' => 'Jl. Merdeka No. 15, Cianjur',
                'no_hp' => '081234567801',
                'pendidikan_terakhir' => 'S2 Pendidikan Bahasa Indonesia',
                'bidang_studi' => 'Bahasa Indonesia',
                'tanggal_masuk' => '1998-03-01',
            ],
            [
                'email' => 'ahmad.fauzi@smk.sch.id',
                'nuptk' => '198506151010011002',
                'nama_lengkap' => 'Ahmad Fauzi, S.Kom',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1985-06-15',
                'agama' => 'Islam',
                'alamat' => 'Jl. Sudirman No. 25, Cianjur',
                'no_hp' => '081234567802',
                'pendidikan_terakhir' => 'S1 Teknik Informatika',
                'bidang_studi' => 'Rekayasa Perangkat Lunak',
                'tanggal_masuk' => '2010-07-01',
            ],
            [
                'email' => 'sri.wahyuni@smk.sch.id',
                'nuptk' => '198012201005012003',
                'nama_lengkap' => 'Sri Wahyuni, S.Pd',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '1980-12-20',
                'agama' => 'Islam',
                'alamat' => 'Jl. Pahlawan No. 10, Cianjur',
                'no_hp' => '081234567803',
                'pendidikan_terakhir' => 'S1 Pendidikan Matematika',
                'bidang_studi' => 'Matematika',
                'tanggal_masuk' => '2005-01-15',
            ],
            [
                'email' => 'budi.santoso@smk.sch.id',
                'nuptk' => '197908111006041004',
                'nama_lengkap' => 'Budi Santoso, S.T',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '1979-08-11',
                'agama' => 'Islam',
                'alamat' => 'Jl. Pemuda No. 8, Cianjur',
                'no_hp' => '081234567804',
                'pendidikan_terakhir' => 'S1 Teknik Elektro',
                'bidang_studi' => 'Teknik Komputer dan Jaringan',
                'tanggal_masuk' => '2006-04-01',
            ],
            [
                'email' => 'rina.marlina@smk.sch.id',
                'nuptk' => '198204152006042005',
                'nama_lengkap' => 'Rina Marlina, S.Pd',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Medan',
                'tanggal_lahir' => '1982-04-15',
                'agama' => 'Kristen',
                'alamat' => 'Jl. Diponegoro No. 12, Cianjur',
                'no_hp' => '081234567805',
                'pendidikan_terakhir' => 'S1 Pendidikan Seni Rupa',
                'bidang_studi' => 'Multimedia',
                'tanggal_masuk' => '2006-04-01',
            ],
        ];

        foreach ($guruData as $data) {
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                Guru::create([
                    'user_id' => $user->id,
                    'nuptk' => $data['nuptk'],
                    'nama_lengkap' => $data['nama_lengkap'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'agama' => $data['agama'],
                    'alamat' => $data['alamat'],
                    'no_hp' => $data['no_hp'],
                    'pendidikan_terakhir' => $data['pendidikan_terakhir'],
                    'bidang_studi' => $data['bidang_studi'],
                    'tanggal_masuk' => $data['tanggal_masuk'],
                    'status' => 'aktif',
                ]);
            }
        }

        // Create demo guru
        $demoUser = User::where('email', 'guru@demo.com')->first();
        if ($demoUser) {
            Guru::create([
                'user_id' => $demoUser->id,
                'nuptk' => 'DEMO001',
                'nama_lengkap' => 'Demo Guru',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1985-01-01',
                'agama' => 'Islam',
                'alamat' => 'Jl. Demo No. 1',
                'no_hp' => '081234567890',
                'pendidikan_terakhir' => 'S1 Pendidikan',
                'bidang_studi' => 'Umum',
                'tanggal_masuk' => '2020-01-01',
                'status' => 'aktif',
            ]);
        }
    }
}