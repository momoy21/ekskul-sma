<?php

namespace Database\Seeders;

use App\Models\Pembina;
use Illuminate\Database\Seeder;

/**
 * Seed daftar pembina ekskul SMA Global Indonesia.
 * Data diambil dari dokumen rancangan sistem.
 */
class PembinaSeeder extends Seeder
{
    public function run(): void
    {
        $pembina = [
            ['nama_lengkap' => 'Mr Pram',    'is_active' => 1],
            ['nama_lengkap' => 'Mr Alex',    'is_active' => 1],
            ['nama_lengkap' => 'Ms Babay',   'is_active' => 1],
            ['nama_lengkap' => 'Ms Bani',    'is_active' => 1],
            ['nama_lengkap' => 'Mr Bams',    'is_active' => 1],
            ['nama_lengkap' => 'Ms Rini',    'is_active' => 1],
            ['nama_lengkap' => 'Mr Riki',    'is_active' => 1],
            ['nama_lengkap' => 'Mr Umam',    'is_active' => 1],
            ['nama_lengkap' => 'Ms Nurul',   'is_active' => 1],
            ['nama_lengkap' => 'Ms Esty',    'is_active' => 1],
            ['nama_lengkap' => 'Mr Yetno',   'is_active' => 1],
            ['nama_lengkap' => 'Ms Fifin',   'is_active' => 1],
            ['nama_lengkap' => 'Ms Merlina', 'is_active' => 1],
            ['nama_lengkap' => 'Ms Cantika', 'is_active' => 1],
            ['nama_lengkap' => 'Ms Calista', 'is_active' => 1],
        ];

        foreach ($pembina as $item) {
            Pembina::create($item);
        }
    }
}
