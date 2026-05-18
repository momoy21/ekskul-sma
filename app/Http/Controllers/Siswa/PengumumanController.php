<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePemilihanUlangRequest;
use App\Models\Ekskul;
use App\Models\PendaftaranSiswa;
use App\Models\PeriodePendaftaran;
use App\Models\TahunAjaran;
use App\Services\ZonaSeleksiService;
use Illuminate\Support\Facades\DB;

class PengumumanController extends Controller
{
    public function index()
    {
        [$periode, $pendaftaran] = $this->getPeriodeDanPendaftaran();

        if (! $periode || ! $pendaftaran) {
            return view('siswa.pengumuman.index', [
                'periode'         => $periode,
                'pendaftaran'     => null,
                'pilihan'         => collect(),
                'fase'            => 'belum',
                'ekskulPengganti' => collect(),
                'semuaZonaHijau'  => false,
            ]);
        }

        $fase = $this->tentikanFase($periode);

        $pilihan = $pendaftaran->pilihanEkskul()
            ->with(['ekskul', 'ekskulCadangan', 'ekskulFinal'])
            ->where('is_deleted', 0)
            ->orderBy('urutan_pilihan')
            ->get();

        // Hitung zona semua ekskul di periode ini
        $zonaMap = app(ZonaSeleksiService::class)->hitungZona($periode->periode_id);
        $pilihan = $pilihan->map(function ($item) use ($zonaMap) {
            // Selalu pakai zona hasil hitung terbaru agar konsisten dengan informasi kuota lain
            $item->status_zona = $zonaMap[$item->ekskul_id]['zona'] ?? 'merah';

            return $item;
        });

        // Kumpulkan ekskul_id yang zona-nya hijau atau kuning (layak dipilih)
        $ekskulLayakIds = collect($zonaMap)
            ->filter(fn($data) => in_array($data['zona'], ['hijau', 'kuning']))
            ->keys()
            ->toArray();

        // Hari yang sudah terpakai oleh pilihan aktif (hijau/kuning) milik siswa ini
        $hariTerpakai = $pilihan
            ->filter(fn($p) => in_array($p->status_zona, ['hijau', 'kuning']))
            ->map(fn($p) => $p->ekskul->hari_pelaksanaan)
            ->unique()
            ->toArray();

        // Ekskul yang sudah dipilih (tidak boleh double)
        $ekskulSudahDipilihIds = $pilihan
            ->whereIn('status_zona', ['hijau', 'kuning'])
            ->pluck('ekskul_id')
            ->toArray();

        $ekskulPengganti = Ekskul::with('pembina')
            ->aktif()
            // Hanya ekskul yang zona-nya hijau atau kuning
            ->whereIn('ekskul_id', $ekskulLayakIds)
            // Jangan tampilkan hari yang sudah terpakai pilihan aktif siswa
            ->whereNotIn('hari_pelaksanaan', $hariTerpakai)
            // Jangan tampilkan ekskul yang sudah dipilih
            ->whereNotIn('ekskul_id', $ekskulSudahDipilihIds)
            ->urutHari()
            ->get()
            ->groupBy('hari_pelaksanaan');

        // Cek apakah semua pilihan siswa zona hijau
        $semuaZonaHijau = $pilihan->every(fn($p) => $p->status_zona === 'hijau');

        // Auto-finalize jika semua zona hijau di fase pengumuman
        if ($fase === 'pengumuman' && $semuaZonaHijau) {
            $this->autoFinalizePilihan($pendaftaran);
        }

        return view('siswa.pengumuman.index', compact(
            'periode', 'pendaftaran', 'pilihan', 'fase', 'ekskulPengganti', 'semuaZonaHijau'
        ));
    }

    public function simpanFinal(StorePemilihanUlangRequest $request)
    {
        [$periode, $pendaftaran] = $this->getPeriodeDanPendaftaran();

        if (! $periode || ! $pendaftaran) {
            return back()->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        if (! $periode->pemilihan_ulang_aktif) {
            return back()->with('error', 'Masa pemilihan ulang tidak sedang aktif.');
        }

        $pilihan = $pendaftaran->pilihanEkskul()
            ->with('ekskul')
            ->where('is_deleted', 0)
            ->get();

        $hapusIds     = array_filter($request->input('hapus', []));
        $penggantiMap = array_filter($request->input('pengganti', []));
        $cadanganMap  = array_filter($request->input('cadangan', []));

        $adaPerubahanMerah = count($hapusIds) > 0 || count($penggantiMap) > 0;

        DB::transaction(function () use (
            $request, $pendaftaran, $pilihan,
            $hapusIds, $penggantiMap, $cadanganMap, $adaPerubahanMerah, $periode
        ) {
            foreach ($pilihan as $p) {
                $idStr = (string) $p->pilihan_id;

                if (in_array($idStr, array_map('strval', $hapusIds))) {
                    $p->update(['is_deleted' => 1]);
                    continue;
                }

                if (! empty($penggantiMap[$idStr])) {
                    $p->update([
                        'ekskul_id'   => (int) $penggantiMap[$idStr],
                        'status_zona' => null,
                    ]);
                    continue;
                }

                if (! empty($cadanganMap[$idStr])) {
                    $p->update(['ekskul_cadangan_id' => (int) $cadanganMap[$idStr]]);
                }
            }

            if ($adaPerubahanMerah) {
                $pendaftaran->update([
                    'tanda_tangan_ortu' => $request->tanda_tangan_ortu,
                    'waktu_ttd'         => now(),
                ]);
            }

            // ✅ Recalculate zona untuk periode ini (update semua siswa)
            app(ZonaSeleksiService::class)->updateStatusZona($periode->periode_id);
        });

        return back()->with('success', 'Perubahan pilihan berhasil disimpan.');
    }

    private function tentikanFase(PeriodePendaftaran $periode): string
    {
        // FIX BUG 2: pengumuman tersedia = hari SETELAH tanggal_tutup
        // (sudah diperbaiki di model PeriodePendaftaran)
        if (! $periode->pengumuman_tersedia) {
            return 'belum';
        }

        if ($periode->pemilihan_ulang_aktif) {
            return 'pengumuman';
        }

        return 'selesai';
    }

    /**
     * Auto-finalize pilihan siswa jika semua zona hijau
     * (tidak perlu manual simpan final)
     */
    private function autoFinalizePilihan(PendaftaranSiswa $pendaftaran): void
    {
        // Cek apakah sudah di-finalize (status bukan 'submitted')
        if ($pendaftaran->status !== 'submitted') {
            return;
        }

        $pendaftaran->update(['status' => 'finalized']);
    }

    private function getPeriodeDanPendaftaran(): array
    {
        $tahunAktif = TahunAjaran::aktif()->first();

        if (! $tahunAktif) {
            return [null, null];
        }

        $periode = PeriodePendaftaran::where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
            ->where('semester', $tahunAktif->semester)
            ->first();

        $pendaftaran = $periode
            ? PendaftaranSiswa::where('siswa_id', session('siswa_id'))
                ->where('periode_id', $periode->periode_id)
                ->where('status', '!=', 'draft')
                ->first()
            : null;

        return [$periode, $pendaftaran];
    }
}
