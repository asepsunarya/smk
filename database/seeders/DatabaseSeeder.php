<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TahunAjaranSeeder::class,
            JurusanSeeder::class,
            MataPelajaranSeeder::class,
            DimensiP5Seeder::class,
            UserSeeder::class,
            GuruSeeder::class,
            KelasSeeder::class,
            WaliKelasSeeder::class,
            SiswaSeeder::class,
            EkstrakurikulerSeeder::class,
            CapaianPembelajaranSeeder::class,
            TujuanPembelajaranSeeder::class,
            // Add more seeders as needed
        ]);
    }
}