<?php

namespace Database\Seeders;

use App\Models\KategoriEkskul;
use Illuminate\Database\Seeder;

/**
 * Seed kategori ekskul sesuai rancangan sistem.
 * nama_kategori bersifat UNIQUE di database.
 */
class KategoriEkskulSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['nama_kategori' => 'Seni dan Budaya',    'is_active' => 1],
            ['nama_kategori' => 'Olahraga',            'is_active' => 1],
            ['nama_kategori' => 'Bahasa dan Akademik', 'is_active' => 1],
            ['nama_kategori' => 'Teknologi',           'is_active' => 1],
            ['nama_kategori' => 'Keagamaan',           'is_active' => 1],
        ];

        foreach ($kategori as $item) {
            KategoriEkskul::create($item);
        }
    }
}
