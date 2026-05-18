<?php

namespace App\Console\Commands;

use App\Models\PeriodePendaftaran;
use App\Models\TahunAjaran;
use App\Services\ZonaSeleksiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * HitungZonaPendaftaran - Artisan command untuk auto-hitung zona setelah
 * tanggal_tutup pendaftaran terlewati.
 *
 * Cara kerja:
 * Dijalankan berkala oleh scheduler (lihat app/Console/Kernel.php).
 * Cek apakah ada periode pendaftaran yang:
 *   1. tanggal_tutup-nya sudah terlewati (kemarin atau sebelumnya), ATAU
 *   2. tanggal_tutup-nya hari ini DAN waktu sekarang >= 11:00 (jam tutup)
 *   3. Tapi pilihan_ekskul masih ada yang status_zona = NULL (belum dihitung)
 *
 * Jika kondisi terpenuhi, panggil ZonaSeleksiService::updateStatusZona().
 *
 * Cara jalankan manual:
 *   php artisan ekskul:hitung-zona
 */
class HitungZonaPendaftaran extends Command
{
    protected $signature   = 'ekskul:hitung-zona';
    protected $description = 'Hitung zona (hijau/kuning/merah) setelah masa pendaftaran ditutup';

    public function handle(ZonaSeleksiService $service): int
    {
        $today = Carbon::today();
        $now   = Carbon::now();
        $tutupTime = Carbon::today()->setHour(11)->setMinute(0)->setSecond(0);

        // Ambil semua periode yang:
        // 1. tanggal_tutupnya sudah lewat (kemarin atau lebih awal), ATAU
        // 2. tanggal_tutupnya hari ini DAN waktu sekarang sudah >= 11:00 (jam tutup)
        $periodeList = PeriodePendaftaran::where(
            DB::raw("DATE(tanggal_tutup) < DATE(NOW()) OR (DATE(tanggal_tutup) = DATE(NOW()) AND NOW() >= ?)"),
            [$tutupTime]
        )
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_active', 1))
            ->get();

        if ($periodeList->isEmpty()) {
            $this->info('Tidak ada periode pendaftaran yang perlu dihitung zonanya.');
            return self::SUCCESS;
        }

        foreach ($periodeList as $periode) {
            // Cek apakah masih ada pilihan yang status_zona-nya NULL
            $adaBelumDihitung = \App\Models\PilihanEkskul::join(
                    'pendaftaran_siswa',
                    'pilihan_ekskul.pendaftaran_id',
                    '=',
                    'pendaftaran_siswa.pendaftaran_id'
                )
                ->where('pendaftaran_siswa.periode_id', $periode->periode_id)
                ->where('pilihan_ekskul.is_deleted', 0)
                ->whereNull('pilihan_ekskul.status_zona')
                ->exists();

            if (! $adaBelumDihitung) {
                $this->line("Periode {$periode->tahunAjaran->label} {$periode->semester}: Zona sudah dihitung sebelumnya, lewati.");
                continue;
            }

            $this->info("Menghitung zona untuk periode {$periode->tahunAjaran->label} - {$periode->semester}...");

            $service->updateStatusZona($periode->periode_id);

            $this->info("✓ Zona berhasil dihitung untuk periode ID {$periode->periode_id}.");
        }

        return self::SUCCESS;
    }
}
