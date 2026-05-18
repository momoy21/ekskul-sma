<?php

namespace Database\Seeders;

use App\Models\Kriteria;
use Illuminate\Database\Seeder;

/**
 * Seed 5 kriteria SAW yang bersifat tetap.
 * Kode C1-C5 dan tipe_atribut (benefit/cost) TIDAK boleh diubah dari UI
 * karena berpengaruh langsung ke logika normalisasi perhitungan SAW.
 *
 * Yang boleh diedit admin via UI: nama_kriteria, deskripsi_siswa, is_active.
 *
 * Kriteria baru (revisi SAW):
 * C1: Minat (benefit) - dari soal siswa
 * C2: Jadwal (benefit) - dari soal siswa
 * C3: Biaya Tambahan (cost) - dari nilai admin
 * C4: Fasilitas (benefit) - dari nilai admin
 * C5: Intensitas Kegiatan (cost) - dari nilai admin
 */
class KriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $kriteria = [
            [
                'kode'            => 'C1',
                'nama_kriteria'   => 'Minat',
                'tipe_atribut'    => 'benefit',
                'deskripsi_siswa' => 'Seberapa penting ekskul yang kamu pilih sesuai dengan minat dan passion kamu?',
                'urutan_tampil'   => 1,
                'is_active'       => 1,
            ],
            [
                'kode'            => 'C2',
                'nama_kriteria'   => 'Jadwal',
                'tipe_atribut'    => 'benefit',
                'deskripsi_siswa' => 'Seberapa penting jadwal ekskul sesuai dengan ketersediaan waktu kamu?',
                'urutan_tampil'   => 2,
                'is_active'       => 1,
            ],
            [
                'kode'            => 'C3',
                'nama_kriteria'   => 'Biaya Tambahan',
                'tipe_atribut'    => 'cost',   // cost: semakin rendah biaya, semakin bagus
                'deskripsi_siswa' => 'Seberapa penting biaya yang kamu keluarkan tetap terjangkau?',
                'urutan_tampil'   => 3,
                'is_active'       => 1,
            ],
            [
                'kode'            => 'C4',
                'nama_kriteria'   => 'Fasilitas',
                'tipe_atribut'    => 'benefit',
                'deskripsi_siswa' => 'Seberapa penting fasilitas dan peralatan ekskul sudah disediakan oleh sekolah?',
                'urutan_tampil'   => 4,
                'is_active'       => 1,
            ],
            [
                'kode'            => 'C5',
                'nama_kriteria'   => 'Intensitas Kegiatan',
                'tipe_atribut'    => 'cost',   // cost: semakin rendah intensitas (lebih santai), semakin baik
                'deskripsi_siswa' => 'Seberapa penting kamu memilih ekskul dengan intensitas kegiatan yang sesuai dengan kesibukan kamu?',
                'urutan_tampil'   => 5,
                'is_active'       => 1,
            ],
        ];

        foreach ($kriteria as $item) {
            Kriteria::create($item);
        }
    }
}
