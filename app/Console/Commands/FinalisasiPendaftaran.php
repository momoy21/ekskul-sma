<?php

namespace App\Console\Commands;

use App\Models\PeriodePendaftaran;
use App\Services\ZonaSeleksiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * FinalisasiPendaftaran - Artisan command untuk auto-finalisasi setelah
 * masa pemilihan_ulang terlewati.
 *
 * Cara kerja:
 * Dijalankan setiap hari oleh scheduler.
 * Cek apakah ada periode yang:
 *   1. tanggal_pemilihan_ulang-nya sudah terlewati
 *   2. Masih ada pendaftaran_siswa dengan status = 'submitted' (belum finalized)
 *
 * Jika kondisi terpenuhi, panggil ZonaSeleksiService::finalisasi() yang:
 *   - Menentukan ekskul_final_id tiap pilihan
 *   - Membuat peserta_ekskul dengan snapshot data siswa
 *   - Membuat snapshot_laporan
 *   - Mengubah status pendaftaran ke 'finalized'
 *
 * Cara jalankan manual:
 *   php artisan ekskul:finalisasi
 */
class FinalisasiPendaftaran extends Command
{
    protected $signature   = 'ekskul:finalisasi';
    protected $description = 'Finalisasi pendaftaran setelah masa pemilihan ulang selesai';

    public function handle(ZonaSeleksiService $service): int
    {
        $today = Carbon::today();

        // Ambil periode yang tanggal_pemilihan_ulang sudah lewat dan belum finalized
        $periodeList = PeriodePendaftaran::whereNotNull('tanggal_pemilihan_ulang')
            ->whereDate('tanggal_pemilihan_ulang', '<', $today)
            ->whereHas('pendaftaranSiswa', fn($q) => $q->where('status', 'submitted'))
            ->with('tahunAjaran')
            ->get();

        if ($periodeList->isEmpty()) {
            $this->info('Tidak ada periode yang perlu difinalisasi.');
            return self::SUCCESS;
        }

        foreach ($periodeList as $periode) {
            $this->info("Memfinalisasi periode {$periode->tahunAjaran->label} - {$periode->semester}...");

            try {
                $service->finalisasi($periode->periode_id);
                $this->info("✓ Finalisasi selesai untuk periode ID {$periode->periode_id}.");
            } catch (\Throwable $e) {
                $this->error("✗ Gagal finalisasi periode ID {$periode->periode_id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
