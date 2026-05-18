<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use App\Models\Siswa;
use App\Models\PeriodePendaftaran;
use App\Services\ZonaSeleksiService;
use App\Http\Requests\StoreTimelinePendaftaranRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PendaftaranAdminController extends Controller
{
    /**
     * Tampilkan halaman pengaturan timeline pendaftaran.
     * Admin set tanggal buka, tutup, dan pemilihan ulang.
     */
    public function index()
    {
        $tahunAktif = TahunAjaran::aktif()->first();

        if (! $tahunAktif) {
            return view('admin.pendaftaran.index', [
                'tahunAktif'  => null,
                'periode'     => null,
                'ringkasan'   => [],
            ]);
        }

        // Ambil periode aktif untuk semester yang sedang berjalan
        $periode = PeriodePendaftaran::with('tahunAjaran')
            ->where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
            ->where('semester', $tahunAktif->semester)
            ->first();

        // Ringkasan jumlah pendaftar per ekskul jika periode sudah ada
        $ringkasan = [];
        if ($periode) {
            $ringkasan = app(ZonaSeleksiService::class)->hitungZona($periode->periode_id);
        }

        return view('admin.pendaftaran.index', compact('tahunAktif', 'periode', 'ringkasan'));
    }

    /**
     * Tampilkan hasil pendaftaran siswa (laporan data registrasi).
     * Menampilkan: nama, NISN, pilihan awal, pilihan ulang, dan pilihan final.
     * Dengan search, filter status, dan pagination.
     */
    public function hasil(Request $request)
    {
        $tahunAktif = TahunAjaran::aktif()->first();

        if (! $tahunAktif) {
            return view('admin.pendaftaran.hasil', [
                'tahunAktif'  => null,
                'periode'     => null,
                'pendaftaran' => $this->emptyPaginator($request),
            ]);
        }

        // Ambil periode aktif
        $periode = PeriodePendaftaran::with('tahunAjaran')
            ->where('tahun_ajaran_id', $tahunAktif->tahun_ajaran_id)
            ->where('semester', $tahunAktif->semester)
            ->first();

        if (! $periode) {
            return view('admin.pendaftaran.hasil', compact('tahunAktif', 'periode') + [
                'pendaftaran' => $this->emptyPaginator($request),
            ]);
        }

        // Build query semua siswa aktif + data pendaftaran di periode aktif (jika ada)
        $query = Siswa::query()
            ->where('status', 'aktif')
            ->with([
                'pendaftaran' => fn($q) => $q
                    ->where('periode_id', $periode->periode_id)
                    ->with([
                        'pilihanEkskul' => fn($pilihan) => $pilihan
                            ->with(['ekskul', 'ekskulCadangan', 'ekskulFinal'])
                            ->where('is_deleted', 0)
                            ->orderBy('urutan_pilihan'),
                    ]),
            ]);

        // Search: cari berdasarkan nama siswa atau NISN
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                    ->orWhere('nisn', 'like', "%{$request->search}%");
            });
        }

        // Filter status pendaftaran
        if ($request->status) {
            if (in_array($request->status, ['submitted', 'finalized'])) {
                $query->whereHas('pendaftaran', function ($q) use ($request, $periode) {
                    $q->where('periode_id', $periode->periode_id)
                        ->where('status', $request->status);
                });
            }

            if ($request->status === 'not_submitted') {
                $query->whereDoesntHave('pendaftaran', function ($q) use ($periode) {
                    $q->where('periode_id', $periode->periode_id)
                        ->whereIn('status', ['submitted', 'finalized']);
                });
            }
        }

        // Pagination: 10 per halaman (konsisten dengan master data lain)
        $pendaftaran = $query
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pendaftaran.hasil', compact('tahunAktif', 'periode', 'pendaftaran'));
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            10,
            (int) $request->get('page', 1),
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * Simpan timeline pendaftaran (tanggal buka, tutup, dan pemilihan ulang).
     * Jika periode belum ada, buat baru. Jika sudah ada, update.
     */
    public function simpanTimeline(StoreTimelinePendaftaranRequest $request)
    {
        $tahunAktif = TahunAjaran::aktif()->first();

        if (! $tahunAktif) {
            return back()->with('error', 'Tidak ada tahun ajaran yang aktif saat ini.');
        }

        // Cari atau buat periode pendaftaran
        $periode = PeriodePendaftaran::firstOrCreate(
            [
                'tahun_ajaran_id' => $tahunAktif->tahun_ajaran_id,
                'semester'        => $tahunAktif->semester,
            ],
            [
                'tanggal_buka'    => $request->tanggal_buka,
                'tanggal_tutup'   => $request->tanggal_tutup,
            ]
        );

        // Update timeline (selalu update jika ada perubahan)
        $periode->update([
            'tanggal_buka'            => $request->tanggal_buka,
            'tanggal_tutup'           => $request->tanggal_tutup,
            'tanggal_pemilihan_ulang' => $request->tanggal_pemilihan_ulang ?? null,
        ]);

        return back()->with('success', 'Timeline pendaftaran berhasil disimpan.');
    }
}
