<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Jurusan;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusan = Jurusan::all()->keyBy('kode_jurusan');

        $kelasData = [
            // RPL Classes
            ['nama_kelas' => 'X RPL 1', 'tingkat' => '10', 'jurusan' => 'RPL', 'kapasitas' => 32],
            ['nama_kelas' => 'X RPL 2', 'tingkat' => '10', 'jurusan' => 'RPL', 'kapasitas' => 32],
            ['nama_kelas' => 'XI RPL 1', 'tingkat' => '11', 'jurusan' => 'RPL', 'kapasitas' => 30],
            ['nama_kelas' => 'XI RPL 2', 'tingkat' => '11', 'jurusan' => 'RPL', 'kapasitas' => 30],
            ['nama_kelas' => 'XII RPL 1', 'tingkat' => '12', 'jurusan' => 'RPL', 'kapasitas' => 28],
            ['nama_kelas' => 'XII RPL 2', 'tingkat' => '12', 'jurusan' => 'RPL', 'kapasitas' => 28],

            // TKJ Classes
            ['nama_kelas' => 'X TKJ 1', 'tingkat' => '10', 'jurusan' => 'TKJ', 'kapasitas' => 32],
            ['nama_kelas' => 'X TKJ 2', 'tingkat' => '10', 'jurusan' => 'TKJ', 'kapasitas' => 32],
            ['nama_kelas' => 'XI TKJ 1', 'tingkat' => '11', 'jurusan' => 'TKJ', 'kapasitas' => 30],
            ['nama_kelas' => 'XI TKJ 2', 'tingkat' => '11', 'jurusan' => 'TKJ', 'kapasitas' => 30],
            ['nama_kelas' => 'XII TKJ 1', 'tingkat' => '12', 'jurusan' => 'TKJ', 'kapasitas' => 28],
            ['nama_kelas' => 'XII TKJ 2', 'tingkat' => '12', 'jurusan' => 'TKJ', 'kapasitas' => 28],

            // MM Classes
            ['nama_kelas' => 'X MM 1', 'tingkat' => '10', 'jurusan' => 'MM', 'kapasitas' => 30],
            ['nama_kelas' => 'XI MM 1', 'tingkat' => '11', 'jurusan' => 'MM', 'kapasitas' => 28],
            ['nama_kelas' => 'XII MM 1', 'tingkat' => '12', 'jurusan' => 'MM', 'kapasitas' => 26],

            // OTKP Classes
            ['nama_kelas' => 'X OTKP 1', 'tingkat' => '10', 'jurusan' => 'OTKP', 'kapasitas' => 30],
            ['nama_kelas' => 'XI OTKP 1', 'tingkat' => '11', 'jurusan' => 'OTKP', 'kapasitas' => 28],
            ['nama_kelas' => 'XII OTKP 1', 'tingkat' => '12', 'jurusan' => 'OTKP', 'kapasitas' => 26],

            // AKL Classes
            ['nama_kelas' => 'X AKL 1', 'tingkat' => '10', 'jurusan' => 'AKL', 'kapasitas' => 30],
            ['nama_kelas' => 'XI AKL 1', 'tingkat' => '11', 'jurusan' => 'AKL', 'kapasitas' => 28],
            ['nama_kelas' => 'XII AKL 1', 'tingkat' => '12', 'jurusan' => 'AKL', 'kapasitas' => 26],

            // BDP Classes
            ['nama_kelas' => 'X BDP 1', 'tingkat' => '10', 'jurusan' => 'BDP', 'kapasitas' => 30],
            ['nama_kelas' => 'XI BDP 1', 'tingkat' => '11', 'jurusan' => 'BDP', 'kapasitas' => 28],
            ['nama_kelas' => 'XII BDP 1', 'tingkat' => '12', 'jurusan' => 'BDP', 'kapasitas' => 26],
        ];

        foreach ($kelasData as $data) {
            $jurusanId = $jurusan[$data['jurusan']]->id ?? null;

            Kelas::create([
                'nama_kelas' => $data['nama_kelas'],
                'tingkat' => $data['tingkat'],
                'jurusan_id' => $jurusanId,
                'kapasitas' => $data['kapasitas'],
            ]);
        }
    }
}
