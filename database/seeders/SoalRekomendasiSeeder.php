<?php

namespace Database\Seeders;

use App\Models\Ekskul;
use App\Models\Kriteria;
use App\Models\SoalRekomendasi;
use Illuminate\Database\Seeder;

/**
 * Seed 23 soal rekomendasi lengkap beserta mapping ke ekskul terkait.
 *
 * Setiap soal dipetakan ke ekskul yang relevan via pivot soal_ekskul.
 * Jawaban siswa pada soal ini akan masuk ke perhitungan nilai ekskul terkait.
 *
 * Contoh logika:
 * Q1 "Saya suka menggambar/melukis" → relevan untuk Art
 * Q3 "Saya suka berolahraga"        → relevan untuk Futsal, Badminton, Basketball, Karate, Taekwondo
 * Q10 "Tersedia hari Senin"         → relevan untuk Art, Futsal, Monologue (semua ekskul hari Senin)
 */
class SoalRekomendasiSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID kriteria berdasarkan kode
        $kriteria = Kriteria::pluck('kriteria_id', 'kode');

        // Ambil ID ekskul berdasarkan nama
        $ekskul = Ekskul::pluck('ekskul_id', 'nama_ekskul');

        // Definisi soal + ekskul yang relevan
        // Format: ['kode_soal', kriteria_kode, 'teks_soal', urutan, [ekskul_terkait...]]
        $soalData = [

            // ─── C1- Kesesuaian Minat ────────────────────────────────────────
            [
                'kode_soal'    => 'Q1',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya suka menggambar, melukis, atau membuat karya visual.',
                'urutan'       => 1,
                'ekskul'       => ['Art'],
            ],
            [
                'kode_soal'    => 'Q2',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya tertarik dengan kegiatan seni pertunjukan seperti akting atau monolog.',
                'urutan'       => 2,
                'ekskul'       => ['Monologue', 'Teather'],
            ],
            [
                'kode_soal'    => 'Q3',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya suka berolahraga dan melakukan aktivitas fisik yang kompetitif.',
                'urutan'       => 3,
                'ekskul'       => ['Futsal', 'Badminton', 'Basketball'],
            ],
            [
                'kode_soal'    => 'Q4',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya tertarik dengan olahraga yang menggunakan teknik bela diri.',
                'urutan'       => 4,
                'ekskul'       => ['Karate', 'Taekwondo'],
            ],
            [
                'kode_soal'    => 'Q5',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya suka belajar bahasa asing dan berkomunikasi secara internasional.',
                'urutan'       => 5,
                'ekskul'       => ['Mandarin', 'English Debate'],
            ],
            [
                'kode_soal'    => 'Q6',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya tertarik dengan dunia teknologi, coding, dan pembuatan website.',
                'urutan'       => 6,
                'ekskul'       => ['Web Programming', 'Web Design'],
            ],
            [
                'kode_soal'    => 'Q7',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya suka tampil dan berbicara di depan banyak orang.',
                'urutan'       => 7,
                'ekskul'       => ['Monologue', 'English Debate', 'Teather'],
            ],
            [
                'kode_soal'    => 'Q8',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya tertarik dengan budaya dan tarian tradisional Indonesia.',
                'urutan'       => 8,
                'ekskul'       => ['Traditional Dance'],
            ],
            [
                'kode_soal'    => 'Q9',
                'kriteria'     => 'C1',
                'teks_soal'    => 'Saya ingin memperdalam ilmu agama dan kemampuan membaca Al-Qur\'an.',
                'urutan'       => 9,
                'ekskul'       => ['BTQ'],
            ],

            // ─── C2- Kecocokan Jadwal ────────────────────────────────────────
            // Setiap soal jadwal dipetakan ke semua ekskul di hari tersebut
            [
                'kode_soal'    => 'Q10',
                'kriteria'     => 'C2',
                'teks_soal'    => 'Saya tersedia dan tidak ada halangan di hari Senin sepulang sekolah.',
                'urutan'       => 10,
                // Ekskul hari Senin: Art, Futsal, Monologue
                'ekskul'       => ['Art', 'Futsal', 'Monologue'],
            ],
            [
                'kode_soal'    => 'Q11',
                'kriteria'     => 'C2',
                'teks_soal'    => 'Saya tersedia dan tidak ada halangan di hari Selasa sepulang sekolah.',
                'urutan'       => 11,
                // Ekskul hari Selasa: Badminton, Traditional Dance, Web Programming
                'ekskul'       => ['Badminton', 'Traditional Dance', 'Web Programming'],
            ],
            [
                'kode_soal'    => 'Q12',
                'kriteria'     => 'C2',
                'teks_soal'    => 'Saya tersedia dan tidak ada halangan di hari Kamis sepulang sekolah.',
                'urutan'       => 12,
                // Ekskul hari Kamis: Mandarin, Web Design, BTQ, Karate
                'ekskul'       => ['Mandarin', 'Web Design', 'BTQ', 'Karate'],
            ],
            [
                'kode_soal'    => 'Q13',
                'kriteria'     => 'C2',
                'teks_soal'    => 'Saya tersedia dan tidak ada halangan di hari Jumat sepulang sekolah.',
                'urutan'       => 13,
                // Ekskul hari Jumat: Basketball, English Debate, Teather, Taekwondo
                'ekskul'       => ['Basketball', 'English Debate', 'Teather', 'Taekwondo'],
            ],

            // ─── C3- Biaya Tambahan (Cost) ───────────────────────────────────
            // Soal C3 DIHAPUS- nilai biaya diambil langsung dari data admin (kolom biaya_tambahan)
            // Soal diubah menjadi: C4- Fasilitas

            // ─── C4- Fasilitas (Benefit) ─────────────────────────────────────
            // Soal C4 DIHAPUS- nilai fasilitas diambil langsung dari data admin (kolom fasilitas_level)

            // ─── C5- Intensitas Kegiatan (Cost) ───────────────────────────────
            // Soal C5 DIHAPUS- nilai intensitas kegiatan diambil langsung dari data admin (kolom intensitas_kegiatan)
            // Catatan: Tidak ada soal untuk C3, C4, C5 karena nilainya datang dari admin, bukan dari jawaban siswa.
            // Nilai admin untuk kriteria ini akan langsung masuk ke matriks keputusan di SawService::hitungNilaiMentah()

        ];

        foreach ($soalData as $data) {
            // Buat soal
            $soal = SoalRekomendasi::create([
                'kode_soal'    => $data['kode_soal'],
                'kriteria_id'  => $kriteria[$data['kriteria']],
                'teks_soal'    => $data['teks_soal'],
                'urutan_tampil'=> $data['urutan'],
                'is_active'    => 1,
            ]);

            // Hubungkan ke ekskul terkait via pivot soal_ekskul
            $ekskulIds = collect($data['ekskul'])
                ->map(fn($nama) => $ekskul[$nama])
                ->filter()       // buang null kalau ada nama ekskul yang tidak ketemu
                ->toArray();

            $soal->ekskul()->attach($ekskulIds);
        }
    }
}
