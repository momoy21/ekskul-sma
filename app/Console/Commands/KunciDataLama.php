<?php

namespace App\Console\Commands;

use App\Models\PeriodePendaftaran;
use App\Models\TahunAjaran;
use App\Services\ZonaSeleksiService;
use Illuminate\Console\Command;

/**
 * KunciDataLama - Artisan command untuk mengunci peserta_ekskul (is_locked = 1)
 * dari tahun ajaran yang sudah tidak aktif.
 *
 * Cara kerja:
 * Dipanggil otomatis dari TahunAjaranService::buatTahunAjaranBaru() setelah
 * tahun ajaran lama dinonaktifkan. Bisa juga dijalankan manual.
 *
 * Cara jalankan manual:
 *   php artisan ekskul:kunci-data-lama
 */
class KunciDataLama extends Command
{
    protected $signature   = 'ekskul:kunci-data-lama';
    protected $description = 'Kunci data peserta ekskul dari tahun ajaran yang sudah tidak aktif';

    public function handle(ZonaSeleksiService $service): int
    {
        // Ambil semua periode dari tahun ajaran yang sudah nonaktif
        $periodeList = PeriodePendaftaran::whereHas('tahunAjaran', fn($q) => $q->where('is_active', 0))
            ->whereHas('pesertaEkskul', fn($q) => $q->where('is_locked', 0))
            ->with('tahunAjaran')
            ->get();

        if ($periodeList->isEmpty()) {
            $this->info('Tidak ada data yang perlu dikunci.');
            return self::SUCCESS;
        }

        foreach ($periodeList as $periode) {
            $this->info("Mengunci data periode {$periode->tahunAjaran->label} - {$periode->semester}...");
            $service->kunciDataPeserta($periode->periode_id);
            $this->info("✓ Data dikunci untuk periode ID {$periode->periode_id}.");
        }

        return self::SUCCESS;
    }
}
