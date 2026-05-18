<?php

namespace Database\Seeders;

use App\Models\Ekskul;
use App\Models\KategoriEkskul;
use App\Models\Pembina;
use Illuminate\Database\Seeder;

/**
 * Seed 14 ekskul SMA Global Indonesia beserta relasi pembinanya.
 * Data diambil persis dari dokumen rancangan sistem.
 *
 * Setelah ekskul dibuat, relasi ke pembina di-sync via pivot ekskul_pembina
 * menggunakan many-to-many attach().
 *
 * Catatan skala numeric fields:
 * biaya_tambahan (1-5):
 *  1: Tidak Ada Biaya
 *  3: Terjangkau
 *  5: Mahal
 *
 * fasilitas_level (1-5):
 *  1: Seluruhnya dibawa sendiri
 *  3: Sebagian disediakan, sebagian dibawa sendiri
 *  4: Beberapa dibawa sendiri, lebih banyak disediakan sekolah
 *  5: Sepenuhnya disediakan sekolah
 *
 * intensitas_kegiatan (1-5):
 *  2: Tinggi (untuk kegiatan kompetitif, seni performa, atau akademik intensif)
 *  3: Sedang (untuk kegiatan teknis dan kreatif regular)
 */
class EkskulSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID kategori dan pembina berdasarkan nama
        // supaya tidak hardcode ID yang bisa berubah
        $kategori = KategoriEkskul::pluck('kategori_ekskul_id', 'nama_kategori');
        $pembina  = Pembina::pluck('pembina_id', 'nama_lengkap');

        $ekskulData = [
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Art',
                    'kategori_ekskul_id' => $kategori['Seni dan Budaya'],
                    'hari_pelaksanaan'   => 'Senin',
                    'lokasi'             => 'Ruang Art',
                    'biaya_tambahan'     => 2,
                    'fasilitas_level'    => 1,
                    'intensitas_kegiatan' => 3,
                    'deskripsi_kegiatan' => 'Kegiatan seni rupa yang berfokus pada pengembangan kreativitas siswa melalui berbagai media gambar dan lukis. Siswa juga dipersiapkan untuk mengikuti lomba serta meningkatkan keterampilan dan wawasan di bidang seni.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Mr Pram'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Futsal',
                    'kategori_ekskul_id' => $kategori['Olahraga'],
                    'hari_pelaksanaan'   => 'Senin',
                    'lokasi'             => 'Lapangan Futsal',
                    'biaya_tambahan'     => 1,
                    'fasilitas_level'    => 5,
                    'intensitas_kegiatan' => 2,
                    'deskripsi_kegiatan' => 'Kegiatan olahraga futsal yang melatih teknik dasar, strategi permainan, serta kerja sama tim. Cocok untuk siswa yang ingin menyalurkan hobi sekaligus meningkatkan kebugaran dan prestasi.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Mr Alex'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Mandarin',
                    'kategori_ekskul_id' => $kategori['Bahasa dan Akademik'],
                    'hari_pelaksanaan'   => 'Kamis',
                    'lokasi'             => 'Gedung Baru',
                    'biaya_tambahan'     => 4,
                    'fasilitas_level'    => 3,
                    'intensitas_kegiatan' => 4,
                    'deskripsi_kegiatan' => 'Kegiatan pembelajaran bahasa Mandarin yang mencakup dasar percakapan, penulisan, dan pengenalan budaya Tiongkok untuk meningkatkan kemampuan komunikasi global siswa.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Ms Babay'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Monologue',
                    'kategori_ekskul_id' => $kategori['Seni dan Budaya'],
                    'hari_pelaksanaan'   => 'Senin',
                    'lokasi'             => 'Panggung Gedung Lama',
                    'biaya_tambahan'     => 3,
                    'fasilitas_level'    => 3,
                    'intensitas_kegiatan' => 2,
                    'deskripsi_kegiatan' => 'Kegiatan seni peran individu yang melatih ekspresi, kepercayaan diri, dan kemampuan berbicara di depan umum melalui pementasan monolog.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Ms Bani'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Badminton',
                    'kategori_ekskul_id' => $kategori['Olahraga'],
                    'hari_pelaksanaan'   => 'Selasa',
                    'lokasi'             => 'Lapangan Bulu Tangkis',
                    'biaya_tambahan'     => 2,
                    'fasilitas_level'    => 4,
                    'intensitas_kegiatan' => 3,
                    'deskripsi_kegiatan' => 'Kegiatan latihan bulu tangkis yang meliputi teknik dasar, drill, dan pertandingan. Bertujuan untuk mengembangkan kemampuan bermain serta membuka peluang prestasi siswa.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Mr Bams'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Traditional Dance',
                    'kategori_ekskul_id' => $kategori['Seni dan Budaya'],
                    'hari_pelaksanaan'   => 'Selasa',
                    'lokasi'             => 'Ruang Dance',
                    'biaya_tambahan'     => 1,
                    'fasilitas_level'    => 5,
                    'intensitas_kegiatan' => 2,
                    'deskripsi_kegiatan' => 'Kegiatan tari tradisional yang melatih kelenturan tubuh, teknik gerak, dan pemahaman budaya. Siswa juga dipersiapkan untuk tampil dan mengikuti perlombaan seni.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Ms Rini'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Web Programming',
                    'kategori_ekskul_id' => $kategori['Teknologi'],
                    'hari_pelaksanaan'   => 'Selasa',
                    'lokasi'             => 'Lab. Komputer',
                    'biaya_tambahan'     => 4,
                    'fasilitas_level'    => 2,
                    'intensitas_kegiatan' => 3,
                    'deskripsi_kegiatan' => 'Kegiatan pembuatan website dan sistem informasi menggunakan PHP, MySQL, dan framework Laravel. Cocok untuk siswa yang ingin mengembangkan kemampuan coding dan logika pemrograman.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Mr Riki'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Web Design',
                    'kategori_ekskul_id' => $kategori['Teknologi'],
                    'hari_pelaksanaan'   => 'Kamis',
                    'lokasi'             => 'Lab. Komputer',
                    'biaya_tambahan'     => 4,
                    'fasilitas_level'    => 2,
                    'intensitas_kegiatan' => 3,
                    'deskripsi_kegiatan' => 'Kegiatan desain website yang berfokus pada UI/UX dan dasar SEO. Siswa belajar membuat tampilan website yang menarik dan user-friendly.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Mr Riki'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'BTQ',
                    'kategori_ekskul_id' => $kategori['Keagamaan'],
                    'hari_pelaksanaan'   => 'Kamis',
                    'lokasi'             => 'Ruang Doa',
                    'biaya_tambahan'     => 3,
                    'fasilitas_level'    => 2,
                    'intensitas_kegiatan' => 4,
                    'deskripsi_kegiatan' => 'Kegiatan Baca Tulis Al-Qur\'an yang bertujuan meningkatkan kemampuan membaca, menulis, serta pemahaman dasar ilmu agama.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Mr Umam'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Karate',
                    'kategori_ekskul_id' => $kategori['Olahraga'],
                    'hari_pelaksanaan'   => 'Kamis',
                    'lokasi'             => 'Lapangan Basket',
                    'biaya_tambahan'     => 5,
                    'fasilitas_level'    => 2,
                    'intensitas_kegiatan' => 2,
                    'deskripsi_kegiatan' => 'Kegiatan bela diri karate yang melatih disiplin, kekuatan fisik, serta teknik dasar hingga lanjutan. Siswa juga berkesempatan mengikuti ujian kenaikan sabuk dan kompetisi.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                // Karate punya 2 pembina
                'pembina' => ['Ms Nurul', 'Ms Esty'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Basketball',
                    'kategori_ekskul_id' => $kategori['Olahraga'],
                    'hari_pelaksanaan'   => 'Jumat',
                    'lokasi'             => 'Lapangan Basket',
                    'biaya_tambahan'     => 1,
                    'fasilitas_level'    => 5,
                    'intensitas_kegiatan' => 2,
                    'deskripsi_kegiatan' => 'Kegiatan bola basket yang melatih teknik dasar, strategi permainan, dan kerja sama tim. Bertujuan meningkatkan kemampuan bermain dan prestasi siswa.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                // Basketball punya 2 pembina
                'pembina' => ['Mr Yetno', 'Mr Bams'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'English Debate',
                    'kategori_ekskul_id' => $kategori['Bahasa dan Akademik'],
                    'hari_pelaksanaan'   => 'Jumat',
                    'lokasi'             => 'Ruang Kelas',
                    'biaya_tambahan'     => 1,
                    'fasilitas_level'    => 5,
                    'intensitas_kegiatan' => 2,
                    'deskripsi_kegiatan' => 'Kegiatan debat bahasa Inggris yang melatih kemampuan berpikir kritis, public speaking, dan argumentasi. Cocok untuk siswa yang ingin meningkatkan kepercayaan diri dan kemampuan komunikasi.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Ms Fifin'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Teather',
                    'kategori_ekskul_id' => $kategori['Seni dan Budaya'],
                    'hari_pelaksanaan'   => 'Jumat',
                    'lokasi'             => 'Panggung Gedung Lama',
                    'biaya_tambahan'     => 3,
                    'fasilitas_level'    => 2,
                    'intensitas_kegiatan' => 3,
                    'deskripsi_kegiatan' => 'Kegiatan seni teater yang melatih akting, ekspresi, dan kerja sama tim dalam pementasan drama. Siswa juga dipersiapkan untuk tampil dalam berbagai acara.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                'pembina' => ['Ms Merlina'],
            ],
            [
                'ekskul' => [
                    'nama_ekskul'        => 'Taekwondo',
                    'kategori_ekskul_id' => $kategori['Olahraga'],
                    'hari_pelaksanaan'   => 'Jumat',
                    'lokasi'             => 'Lapangan Basket',
                    'biaya_tambahan'     => 5,
                    'fasilitas_level'    => 2,
                    'intensitas_kegiatan' => 2,
                    'deskripsi_kegiatan' => 'Kegiatan bela diri taekwondo yang melatih kekuatan, kelincahan, dan disiplin. Siswa dapat mengikuti ujian kenaikan tingkat dan kompetisi.',
                    'kuota_minimal'      => 10,
                    'is_active'          => 1,
                ],
                // Taekwondo punya 2 pembina
                'pembina' => ['Ms Cantika', 'Ms Calista'],
            ],
        ];

        foreach ($ekskulData as $data) {
            // Buat ekskul
            $ekskul = Ekskul::create($data['ekskul']);

            // Hubungkan ke pembina via pivot ekskul_pembina
            $pembinaIds = collect($data['pembina'])
                ->map(fn($nama) => $pembina[$nama])
                ->toArray();

            $ekskul->pembina()->attach($pembinaIds);
        }
    }
}
