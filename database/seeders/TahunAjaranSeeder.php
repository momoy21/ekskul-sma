<?php

namespace Database\Seeders;

use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

/**
 * Seed tahun ajaran awal yang aktif.
 * Hanya satu yang boleh is_active = 1.
 * Tahun ajaran berikutnya dibuat manual oleh admin via menu Master Data Tahun Ajaran.
 */
class TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        TahunAjaran::create([
            'tahun_mulai'    => 2025,
            'tahun_selesai'  => 2026,
            'semester' => 'ganjil',
            'is_active'      => 1,
        ]);
    }
}
