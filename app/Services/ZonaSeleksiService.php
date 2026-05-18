<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\PendaftaranSiswa;
use App\Models\PesertaEkskul;
use App\Models\PilihanEkskul;
use App\Models\SnapshotLaporan;
use Illuminate\Support\Facades\DB;

/**
 * ZonaSeleksiService- mengelola seluruh alur seleksi ekskul pasca pendaftaran.
 *
 * Tiga tanggung jawab utama:
 *
 * 1. hitungZona($periodeId)
 *    Menghitung jumlah pendaftar aktif per ekskul dan menetapkan zonanya.
 *    Dipanggil setiap saat untuk mendapatkan data kuota terkini (termasuk
 *    untuk dot kuota di katalog ekskul siswa).
 *
 * 2. updateStatusZona($periodeId)
 *    Menulis status zona (hijau/kuning/merah) ke kolom status_zona di tabel
 *    pilihan_ekskul. Dipanggil setelah tanggal_tutup pendaftaran terlewati
 *    (otomatis via scheduler atau manual oleh admin).
 *
 * 3. finalisasi($periodeId)
 *    Menetapkan ekskul_final untuk setiap pilihan, membuat peserta_ekskul,
 *    dan membuat snapshot_laporan. Dipanggil setelah masa pemilihan_ulang selesai.
 */
class ZonaSeleksiService
{
    /**
     * Hitung jumlah pendaftar aktif per ekskul dan tentukan zonanya.
     *
     * Aturan zona:
     * - hijau  : pendaftar >= kuota_minimal (ekskul PASTI buka)
     * - kuning : pendaftar == kuota_minimal - 1 (butuh satu orang lagi)
     * - merah  : pendaftar < kuota_minimal - 1 (tidak akan buka)
     *
     * @param  int   $periodeId
     * @return array [ekskul_id => ['jumlah' => int, 'zona' => string]]
     */
    public function hitungZona(int $periodeId): array
    {
        // Hitung pendaftar aktif (is_deleted = 0) per ekskul di periode ini
        $jumlahPendaftar = PilihanEkskul::join(
                'pendaftaran_siswa',
                'pilihan_ekskul.pendaftaran_id',
                '=',
                'pendaftaran_siswa.pendaftaran_id'
            )
            ->where('pendaftaran_siswa.periode_id', $periodeId)
            ->where('pendaftaran_siswa.status', '!=', 'draft')
            ->where('pilihan_ekskul.is_deleted', 0)
            ->groupBy('pilihan_ekskul.ekskul_id')
            ->select('pilihan_ekskul.ekskul_id', DB::raw('COUNT(*) as jumlah'))
            ->pluck('jumlah', 'ekskul_id')
            ->toArray();

        // Ambil semua ekskul aktif beserta kuota minimalnya
        $ekskulList = Ekskul::aktif()->get(['ekskul_id', 'kuota_minimal']);

        $hasil = [];
        foreach ($ekskulList as $ekskul) {
            $jumlah = $jumlahPendaftar[$ekskul->ekskul_id] ?? 0;
            $kuota  = $ekskul->kuota_minimal;

            $zona = match (true) {
                $jumlah >= $kuota      => 'hijau',
                $jumlah === $kuota - 1 => 'kuning',   // tepat satu dari batas
                default                => 'merah',
            };

            $hasil[$ekskul->ekskul_id] = [
                'jumlah' => $jumlah,
                'zona'   => $zona,
            ];
        }

        return $hasil;
    }

    /**
     * Tulis status zona ke kolom status_zona di tabel pilihan_ekskul.
     *
     * Dipanggil setelah tanggal_tutup pendaftaran terlewati.
     * Setelah ini, halaman pengumuman siswa akan menampilkan warna zona.
     */
    public function updateStatusZona(int $periodeId): void
    {
        $zonaMap = $this->hitungZona($periodeId);

        foreach ($zonaMap as $ekskulId => $data) {
            // Update semua baris pilihan_ekskul untuk ekskul ini di periode ini
            PilihanEkskul::join(
                    'pendaftaran_siswa',
                    'pilihan_ekskul.pendaftaran_id',
                    '=',
                    'pendaftaran_siswa.pendaftaran_id'
                )
                ->where('pendaftaran_siswa.periode_id', $periodeId)
                ->where('pilihan_ekskul.ekskul_id', $ekskulId)
                ->where('pilihan_ekskul.is_deleted', 0)
                ->update(['pilihan_ekskul.status_zona' => $data['zona']]);
        }
    }

    /**
     * Finalisasi- jalankan setelah masa pemilihan ulang selesai.
     *
     * Tiga langkah dalam satu transaction:
     * 1. Tentukan ekskul_final_id untuk setiap pilihan aktif
     * 2. Buat record peserta_ekskul dari pilihan final yang valid
     * 3. Buat snapshot_laporan untuk header laporan PDF/Excel
     */
    public function finalisasi(int $periodeId): void
    {
        DB::transaction(function () use ($periodeId) {

            // Hitung zona final setelah semua pemilihan ulang selesai
            $zonaFinal = $this->hitungZona($periodeId);

            // Ambil semua pilihan aktif di periode ini
            $semuaPilihan = PilihanEkskul::join(
                    'pendaftaran_siswa',
                    'pilihan_ekskul.pendaftaran_id',
                    '=',
                    'pendaftaran_siswa.pendaftaran_id'
                )
                ->where('pendaftaran_siswa.periode_id', $periodeId)
                ->where('pilihan_ekskul.is_deleted', 0)
                ->select('pilihan_ekskul.*', 'pendaftaran_siswa.siswa_id')
                ->get();

            // ── Langkah 1: Tentukan ekskul_final_id ──────────────────────────
            foreach ($semuaPilihan as $pilihan) {
                $zona = $zonaFinal[$pilihan->ekskul_id]['zona'] ?? 'merah';

                $ekskulFinalId = match ($zona) {
                    // Zona hijau → pakai pilihan utama
                    'hijau'  => $pilihan->ekskul_id,

                    // Zona kuning → cek apakah kuota akhirnya terpenuhi
                    // Jika iya pakai utama, jika tidak pakai cadangan
                    'kuning' => $this->resolveEkskulKuning($pilihan, $zonaFinal),

                    // Zona merah → ekskul tidak buka, cek apakah siswa sudah ganti
                    // Kalau sudah diganti (ekskul_id berbeda dari yang merah), pakai yang baru
                    // Kalau belum diganti dan masih merah → null (tidak jadi ikut ekskul ini)
                    'merah'  => null,

                    default  => null,
                };

                // Update ekskul_final_id dan status_zona final
                PilihanEkskul::where('pilihan_id', $pilihan->pilihan_id)
                    ->update([
                        'ekskul_final_id' => $ekskulFinalId,
                        'status_zona'     => $zona,
                    ]);
            }

            // ── Langkah 2: Buat peserta_ekskul ───────────────────────────────
            $this->buatPesertaEkskul($periodeId);

            // ── Langkah 3: Buat snapshot_laporan ─────────────────────────────
            $this->buatSnapshotLaporan($periodeId);

            // Ubah status pendaftaran semua siswa jadi 'finalized'
            PendaftaranSiswa::where('periode_id', $periodeId)
                ->where('status', 'submitted')
                ->update(['status' => 'finalized']);
        });
    }

    /**
     * Tentukan ekskul final untuk pilihan dengan zona kuning.
     *
     * Logika:
     * - Hitung ulang zona SETELAH pemilihan ulang selesai
     * - Kalau ekskul kuning akhirnya terpenuhi (zona hijau/kuning) → pakai utama
     * - Kalau tidak terpenuhi (masih merah) → pakai cadangan (jika ada)
     */
    private function resolveEkskulKuning(object $pilihan, array $zonaFinal): int
    {
        $zonaAkhirEkskulUtama = $zonaFinal[$pilihan->ekskul_id]['zona'] ?? 'merah';

        // Ekskul utama akhirnya terpenuhi → siswa tetap di situ
        if (in_array($zonaAkhirEkskulUtama, ['hijau', 'kuning'])) {
            return $pilihan->ekskul_id;
        }

        // Ekskul utama tidak terpenuhi → pindah ke cadangan (kalau ada)
        return $pilihan->ekskul_cadangan_id ?? $pilihan->ekskul_id;
    }

    /**
     * Buat record peserta_ekskul dari pilihan yang sudah punya ekskul_final_id.
     *
     * Snapshot data siswa diambil dari data aktual saat finalisasi dijalankan.
     * Data ini yang akan digunakan untuk laporan absensi dan tidak akan berubah
     * meski data siswa diedit di kemudian hari.
     */
    private function buatPesertaEkskul(int $periodeId): void
    {
        // Hapus peserta lama jika finalisasi dijalankan ulang
        PesertaEkskul::where('periode_id', $periodeId)->delete();

        // Ambil semua pilihan yang sudah punya ekskul_final
        $pilihanFinal = PilihanEkskul::join(
                'pendaftaran_siswa',
                'pilihan_ekskul.pendaftaran_id',
                '=',
                'pendaftaran_siswa.pendaftaran_id'
            )
            ->join('siswa', 'pendaftaran_siswa.siswa_id', '=', 'siswa.siswa_id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.kelas_id')
            ->where('pendaftaran_siswa.periode_id', $periodeId)
            ->where('pilihan_ekskul.is_deleted', 0)
            ->whereNotNull('pilihan_ekskul.ekskul_final_id')
            ->select(
                'pendaftaran_siswa.siswa_id',
                'pilihan_ekskul.ekskul_final_id as ekskul_id',
                'siswa.nama_lengkap',
                'siswa.nisn',
                'siswa.jenis_kelamin',
                DB::raw("CONCAT(kelas.tingkat, kelas.nama_kelas) as label_kelas")
            )
            ->get();

        $insert = [];
        $now    = now();

        foreach ($pilihanFinal as $p) {
            $insert[] = [
                'siswa_id'               => $p->siswa_id,
                'ekskul_id'              => $p->ekskul_id,
                'periode_id'             => $periodeId,
                'snapshot_nama'          => $p->nama_lengkap,
                'snapshot_nisn'          => $p->nisn,
                'snapshot_jenis_kelamin' => $p->jenis_kelamin,
                'snapshot_label_kelas'   => $p->label_kelas,
                'is_locked'              => 0,
                'enrolled_at'            => $now,
            ];
        }

        // Pakai upsert supaya aman dari duplikasi jika ada siswa yang sama-ekskul-periode
        if (! empty($insert)) {
            PesertaEkskul::upsert(
                $insert,
                ['siswa_id', 'ekskul_id', 'periode_id'],    // unique keys
                ['snapshot_nama', 'snapshot_nisn', 'snapshot_jenis_kelamin', 'snapshot_label_kelas', 'enrolled_at']
            );
        }
    }

    /**
     * Buat snapshot_laporan untuk setiap ekskul yang punya peserta di periode ini.
     *
     * Snapshot menyimpan nama ekskul, pembina, hari, dan lokasi saat ini.
     * Setelah snapshot dibuat, perubahan data ekskul tidak akan mempengaruhi laporan.
     */
    private function buatSnapshotLaporan(int $periodeId): void
    {
        // Hapus snapshot lama jika finalisasi dijalankan ulang
        SnapshotLaporan::where('periode_id', $periodeId)->delete();

        // Ambil ekskul yang punya peserta di periode ini
        $ekskulAdaPeserta = PesertaEkskul::where('periode_id', $periodeId)
            ->distinct()
            ->pluck('ekskul_id')
            ->toArray();

        $ekskulList = Ekskul::with('pembina')
            ->whereIn('ekskul_id', $ekskulAdaPeserta)
            ->get();

        $insert = [];
        $now    = now();

        foreach ($ekskulList as $ekskul) {
            $insert[] = [
                'periode_id'            => $periodeId,
                'ekskul_id'             => $ekskul->ekskul_id,
                'snapshot_nama_ekskul'  => $ekskul->nama_ekskul,
                // Gabungkan nama pembina: "Ms Nurul & Ms Esty"
                'snapshot_nama_pembina' => $ekskul->nama_pembina,
                'snapshot_hari'         => $ekskul->hari_pelaksanaan,
                'snapshot_lokasi'       => $ekskul->lokasi,
                'locked_at'             => $now,
            ];
        }

        if (! empty($insert)) {
            SnapshotLaporan::insert($insert);
        }
    }

    /**
     * Kunci data peserta_ekskul secara permanen (is_locked = 1).
     *
     * Dipanggil dari TahunAjaranService saat tahun ajaran baru dibuat.
     * Setelah dikunci, data tidak akan berubah meski data siswa atau ekskul diedit.
     *
     * @param int $periodeId ID periode yang akan dikunci
     */
    public function kunciDataPeserta(int $periodeId): void
    {
        PesertaEkskul::where('periode_id', $periodeId)
            ->update(['is_locked' => 1]);
    }
}
