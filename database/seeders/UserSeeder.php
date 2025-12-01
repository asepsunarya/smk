<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin/Operator
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@smk.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Kepala Sekolah
        User::create([
            'name' => 'Dr. Suherman, M.Pd',
            'email' => 'kepsek@smk.sch.id',
            'password' => Hash::make('password'),
            'role' => 'kepala_sekolah',
            'nuptk' => '196505151990031005',
            'is_active' => true,
        ]);

        // Sample Guru
        $guruUsers = [
            [
                'name' => 'Dra. Siti Aminah, M.Pd',
                'email' => 'siti.aminah@smk.sch.id',
                'nuptk' => '197203121998032001',
                'role' => 'guru',
            ],
            [
                'name' => 'Ahmad Fauzi, S.Kom',
                'email' => 'ahmad.fauzi@smk.sch.id',
                'nuptk' => '198506151010011002',
                'role' => 'guru',
            ],
            [
                'name' => 'Sri Wahyuni, S.Pd',
                'email' => 'sri.wahyuni@smk.sch.id',
                'nuptk' => '198012201005012003',
                'role' => 'wali_kelas',
            ],
            [
                'name' => 'Budi Santoso, S.T',
                'email' => 'budi.santoso@smk.sch.id',
                'nuptk' => '197908111006041004',
                'role' => 'wali_kelas',
            ],
            [
                'name' => 'Rina Marlina, S.Pd',
                'email' => 'rina.marlina@smk.sch.id',
                'nuptk' => '198204152006042005',
                'role' => 'guru',
            ],
        ];

        foreach ($guruUsers as $guru) {
            User::create([
                'name' => $guru['name'],
                'email' => $guru['email'],
                'password' => Hash::make('password'),
                'role' => $guru['role'],
                'nuptk' => $guru['nuptk'],
                'is_active' => true,
            ]);
        }

        // Sample Siswa
        $siswaUsers = [
            [
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@siswa.smk.sch.id',
                'nis' => '2024001',
                'role' => 'siswa',
            ],
            [
                'name' => 'Sari Dewi',
                'email' => 'sari.dewi@siswa.smk.sch.id',
                'nis' => '2024002',
                'role' => 'siswa',
            ],
            [
                'name' => 'Rizki Maulana',
                'email' => 'rizki.maulana@siswa.smk.sch.id',
                'nis' => '2024003',
                'role' => 'siswa',
            ],
            [
                'name' => 'Fitri Handayani',
                'email' => 'fitri.handayani@siswa.smk.sch.id',
                'nis' => '2024004',
                'role' => 'siswa',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.kurniawan@siswa.smk.sch.id',
                'nis' => '2024005',
                'role' => 'siswa',
            ],
        ];

        foreach ($siswaUsers as $siswa) {
            User::create([
                'name' => $siswa['name'],
                'email' => $siswa['email'],
                'password' => Hash::make('password'),
                'role' => $siswa['role'],
                'nis' => $siswa['nis'],
                'is_active' => true,
            ]);
        }

        // Demo accounts for easier testing
        User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Demo Guru',
            'email' => 'guru@demo.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nuptk' => 'DEMO001',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Demo Siswa',
            'email' => 'siswa@demo.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis' => 'DEMO001',
            'is_active' => true,
        ]);
    }
}