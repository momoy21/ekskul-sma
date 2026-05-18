<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\KelasSeeder;
use Database\Seeders\TahunAjaranSeeder;
use Database\Seeders\PembinaSeeder;
use Database\Seeders\KategoriEkskulSeeder;
use Database\Seeders\KriteriaSeeder;
use Database\Seeders\EkskulSeeder;
use Database\Seeders\SoalRekomendasiSeeder;
use Database\Seeders\UpdateEkskulFotoPathSeeder;

/**
 * DatabaseSeeder- menjalankan semua seeder dalam urutan yang benar.
 *
 * Urutan wajib diikuti karena ada dependensi antar data:
 * - EkskulSeeder butuh KategoriEkskul dan Pembina sudah ada
 * - SoalRekomendasiSeeder butuh Kriteria dan Ekskul sudah ada
 *
 * Cara menjalankan:
 *   php artisan migrate --seed          (fresh install)
 *   php artisan db:seed                 (seed saja tanpa migrate ulang)
 *   php artisan db:seed --class=NamaSeeder  (seed satu kelas saja)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Akun admin- harus paling pertama
            AdminSeeder::class,

            // 2. Master data dasar (tidak saling bergantung)
            KelasSeeder::class,
            TahunAjaranSeeder::class,
            PembinaSeeder::class,
            KategoriEkskulSeeder::class,
            KriteriaSeeder::class,

            // 3. Ekskul- butuh KategoriEkskul dan Pembina sudah ada
            EkskulSeeder::class,

            // 4. Soal- butuh Kriteria dan Ekskul sudah ada
            SoalRekomendasiSeeder::class,

            // 5. Update foto path- butuh Ekskul sudah ada
            UpdateEkskulFotoPathSeeder::class,
        ]);
    }
}
