<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

/**
 * Seed kelas awal: 10A, 10B, 11A, 11B, 12A, 12B.
 * Admin bisa tambah/edit kelas lain via menu Master Data Kelas.
 * Urutan insert penting- saat tahun ajaran baru dibuat, sistem ambil
 * kelas pertama per tingkat (paling atas) sebagai kelas default siswa naik.
 * Jadi 10A, 11A, 12A yang pertama per tingkat.
 */
class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = [
            // Tingkat 10- A dulu supaya jadi kelas default saat naik tingkat
            ['tingkat' => 10, 'nama_kelas' => 'A', 'is_active' => 1],
            ['tingkat' => 10, 'nama_kelas' => 'B', 'is_active' => 1],

            // Tingkat 11
            ['tingkat' => 11, 'nama_kelas' => 'A', 'is_active' => 1],
            ['tingkat' => 11, 'nama_kelas' => 'B', 'is_active' => 1],

            // Tingkat 12
            ['tingkat' => 12, 'nama_kelas' => 'A', 'is_active' => 1],
            ['tingkat' => 12, 'nama_kelas' => 'B', 'is_active' => 1],
        ];

        foreach ($kelas as $item) {
            Kelas::create($item);
        }
    }
}
