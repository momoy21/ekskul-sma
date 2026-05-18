<?php

namespace App\Console\Commands;

use App\Models\PeriodePendaftaran;
use App\Services\ZonaSeleksiService;
use Illuminate\Console\Command;

/**
 * ResetZonaPendaftaran- Command untuk reset dan hitung ulang zona dari data lama.
 *
 * Digunakan ketika ada data lama dengan status_zona yang sudah ketinggalan.
 * Reset status_zona ke NULL untuk periode tertentu, lalu hitung ulang.
 *
 * Cara jalankan:
 *   php artisan ekskul:reset-zona
 */
class ResetZonaPendaftaran extends Command
{
    protected $signature   = 'ekskul:reset-zona {--periode-id= : ID periode yang akan direset (jika kosong, reset semua)}';
    protected $description = 'Reset dan hitung ulang zona untuk data lama atau periode tertentu';

    public function handle(ZonaSeleksiService $service): int
    {
        $periodeId = $this->option('periode-id');

        if ($periodeId) {
            // Reset periode tertentu
            $periode = PeriodePendaftaran::find($periodeId);
            if (!$periode) {
                $this->error("Periode dengan ID {$periodeId} tidak ditemukan.");
                return self::FAILURE;
            }

            $this->resetPeriode($periode, $service);
            return self::SUCCESS;
        }

        // Reset semua periode
        $periodeList = PeriodePendaftaran::with('tahunAjaran')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', 1))
            ->get();

        if ($periodeList->isEmpty()) {
            $this->info('Tidak ada periode untuk direset.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$periodeList->count()} periode. Mulai reset...\n");

        foreach ($periodeList as $periode) {
            $this->resetPeriode($periode, $service);
        }

        $this->info("\n✓ Semua periode selesai direset dan zona berhasil dihitung.");
        return self::SUCCESS;
    }

    private function resetPeriode(PeriodePendaftaran $periode, ZonaSeleksiService $service): void
    {
        $this->line("Periode: {$periode->tahunAjaran->label} - {$periode->semester}");

        // Reset status_zona ke NULL
        \App\Models\PilihanEkskul::join(
                'pendaftaran_siswa',
                'pilihan_ekskul.pendaftaran_id',
                '=',
                'pendaftaran_siswa.pendaftaran_id'
            )
            ->where('pendaftaran_siswa.periode_id', $periode->periode_id)
            ->where('pilihan_ekskul.is_deleted', 0)
            ->update(['pilihan_ekskul.status_zona' => null]);

        $this->line("  └─ Status zona direset ke NULL");

        // Hitung ulang zona
        $service->updateStatusZona($periode->periode_id);

        $this->info("  └─ ✓ Zona berhasil dihitung ulang\n");
    }
}
