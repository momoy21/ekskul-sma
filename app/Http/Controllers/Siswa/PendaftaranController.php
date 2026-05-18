<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePendaftaranRequest;
use App\Models\Ekskul;
use App\Models\HasilRekomendasi;
use App\Models\PendaftaranSiswa;
use App\Models\PeriodePendaftaran;
use App\Models\PilihanEkskul;
use App\Models\TahunAjaran;
use App\Models\TesRekomendasi;
use Illuminate\Support\Facades\DB;

class PendaftaranController extends Controller
{
    public function index()
    {
        [$periode, $pendaftaran] = $this->getPeriodeDanPendaftaran();

        // ── Cek apakah pendaftaran sudah ditutup ──────────────────────────────────
        $pendaftaranTutup = $periode && ! $periode->pendaftaran_sedang_buka;

        // ── FIX BUG 1 ─────────────────────────────────────────────────────────
        // Jika pendaftaran sudah submitted, blokir akses form- tampilkan halaman
        // konfirmasi "sudah daftar" tanpa form pilihan ekskul.
        // Sebelumnya: form tetap tampil walaupun sudah submitted.
        if ($pendaftaran && $pendaftaran->status === 'submitted') {
            $pilihanTersimpan = $pendaftaran->pilihanEkskul()
                ->with('ekskul')
                ->where('is_deleted', 0)
                ->orderBy('urutan_pilihan')
                ->get();

            return view('siswa.pendaftaran.index', [
                'periode'          => $periode,
                'pendaftaran'      => $pendaftaran,
                'pilihanTersimpan' => $pilihanTersimpan,
                'sudahSubmit'      => true,
                'pendaftaranTutup' => $pendaftaranTutup,
            ]);
        }

        // Ekskul aktif untuk dropdown
        $ekskulList = Ekskul::with('pembina')
            ->aktif()
            ->urutHari()
            ->get()
            ->groupBy('hari_pelaksanaan');

        // Smart suggestion dari hasil tes
        $rekomendasi = null;
        if ($periode) {
            $tes = TesRekomendasi::where('siswa_id', session('siswa_id'))
                ->where('periode_id', $periode->periode_id)
                ->whereNotNull('submitted_at')
                ->first();

            if ($tes) {
                $rekomendasi = HasilRekomendasi::with('ekskul.pembina')
                    ->where('tes_id', $tes->tes_id)
                    ->orderBy('peringkat')
                    ->get();
            }
        }

        // Pilihan yang sudah tersimpan sebelumnya (status draft)
        $pilihanTersimpan = collect();
        if ($pendaftaran) {
            $pilihanTersimpan = $pendaftaran->pilihanEkskul()
                ->with('ekskul')
                ->where('is_deleted', 0)
                ->orderBy('urutan_pilihan')
                ->get();
        }

        return view('siswa.pendaftaran.index', compact(
            'periode', 'pendaftaran', 'ekskulList', 'rekomendasi', 'pilihanTersimpan'
        ) + ['sudahSubmit' => false, 'pendaftaranTutup' => $pendaftaranTutup]);
    }

    public function simpan(StorePendaftaranRequest $request)
    {
        [$periode, $pendaftaranLama] = $this->getPeriodeDanPendaftaran();

        if (! $periode || ! $periode->pendaftaran_sedang_buka) {
            return back()->with('error', 'Pendaftaran tidak sedang dibuka saat ini.');
        }

        // Cek lagi di server-side: kalau sudah submitted, tolak request
        if ($pendaftaranLama && $pendaftaranLama->status === 'submitted') {
            return back()->with('error', 'Kamu sudah melakukan pendaftaran. Tidak bisa mendaftar ulang.');
        }

        $ekskulIds = array_values(array_filter($request->ekskul_ids));

        DB::transaction(function () use ($request, $periode, $pendaftaranLama, $ekskulIds) {
            $siswaId = session('siswa_id');

            $pendaftaran = PendaftaranSiswa::updateOrCreate(
                [
                    'siswa_id'   => $siswaId,
                    'periode_id' => $periode->periode_id,
                ],
                [
                    'tanda_tangan_ortu' => $request->tanda_tangan_ortu,
                    'waktu_ttd'         => now(),
                    'status'            => 'submitted',
                ]
            );

            PilihanEkskul::where('pendaftaran_id', $pendaftaran->pendaftaran_id)->delete();

            foreach ($ekskulIds as $urutan => $ekskulId) {
                PilihanEkskul::create([
                    'pendaftaran_id' => $pendaftaran->pendaftaran_id,
                    'ekskul_id'      => (int) $ekskulId,
                    'urutan_pilihan' => $urutan + 1,
                    'is_deleted'     => 0,
                ]);
            }
        });

        return redirect()->route('siswa.pendaftaran.index')
            ->with('toast_success', 'Pendaftaran ekskul berhasil disimpan! Pengumuman akan ditampilkan pada tanggal '
                . $periode->tanggal_tutup->format('d/m/Y') . ' jam 11:30.');
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
                ->first()
            : null;

        return [$periode, $pendaftaran];
    }
}
