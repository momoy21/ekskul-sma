<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\PeriodePendaftaran;
use App\Models\Pengguna;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * TahunAjaranService- mengelola semua efek samping saat tahun ajaran baru dibuat.
 *
 * Dua skenario pembuatan:
 *
 * A. Ganjil → Genap (tahun ajaran sama):
 *    - Nonaktifkan semester ganjil
 *    - Buat semester genap
 *    - Kunci data peserta semester ganjil
 *    - TIDAK ada naik kelas
 *
 * B. Genap → Ganjil tahun baru (tahun ajaran beda):
 *    - Nonaktifkan semester genap
 *    - Buat semester ganjil tahun baru
 *    - Kunci data peserta semester genap
 *    - Alumni kelas 12, naik kelas 11→12 dan 10→11
 */
class TahunAjaranService
{
    /**
     * Buat tahun ajaran baru dan jalankan semua efek sampingnya.
     *
     * @param  int    $tahunMulai
     * @param  int    $tahunSelesai
     * @param  string $semester     'ganjil' atau 'genap'
     * @return TahunAjaran
     */
    public function buatTahunAjaranBaru(int $tahunMulai, int $tahunSelesai, string $semester): TahunAjaran
    {
        return DB::transaction(function () use ($tahunMulai, $tahunSelesai, $semester) {

            $tahunAjaranLama = TahunAjaran::aktif()->first();

            // ── Langkah 1: Nonaktifkan tahun ajaran yang sedang aktif ──────────
            TahunAjaran::where('is_active', 1)->update(['is_active' => 0]);

            // ── Langkah 2: Buat tahun ajaran baru ─────────────────────────────
            $tahunAjaran = TahunAjaran::create([
                'tahun_mulai'  => $tahunMulai,
                'tahun_selesai' => $tahunSelesai,
                'semester'     => $semester,
                'is_active'    => 1,
            ]);

            // ── Langkah 3: Kunci data peserta semester lama ───────────────────
            Artisan::call('ekskul:kunci-data-lama');

            // ── Langkah 4: Naik kelas- hanya jika tahun ajaran BEDA ─────────
            // Ganjil→Genap tahun sama = tidak naik kelas
            // Genap→Ganjil tahun baru = naik kelas
            $isTahunBaru = $tahunAjaranLama &&
                ($tahunMulai != $tahunAjaranLama->tahun_mulai ||
                 $tahunSelesai != $tahunAjaranLama->tahun_selesai);

            if ($isTahunBaru) {
                // Alumni-kan siswa kelas 12
                $siswaKelas12 = Siswa::whereHas('kelas', fn($q) => $q->where('tingkat', 12))
                    ->where('status', 'aktif')
                    ->get();

                foreach ($siswaKelas12 as $siswa) {
                    $siswa->update(['status' => 'alumni']);
                    $siswa->pengguna()->update(['is_active' => 0]);
                }

                // Naikkan kelas- 11→12 dulu baru 10→11
                $this->naikkanKelas(11, 12);
                $this->naikkanKelas(10, 11);
            }

            return $tahunAjaran;
        });
    }

    /**
     * Naikkan siswa aktif dari satu tingkat ke tingkat berikutnya.
     */
    private function naikkanKelas(int $dariTingkat, int $keTingkat): void
    {
        $kelasDefault = Kelas::where('tingkat', $keTingkat)
            ->where('is_active', 1)
            ->orderBy('kelas_id')
            ->first();

        if (! $kelasDefault) {
            return;
        }

        Siswa::whereHas('kelas', fn($q) => $q->where('tingkat', $dariTingkat))
            ->where('status', 'aktif')
            ->update(['kelas_id' => $kelasDefault->kelas_id]);
    }
}
