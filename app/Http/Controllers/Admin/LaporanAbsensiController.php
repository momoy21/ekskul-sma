<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\PeriodePendaftaran;
use App\Models\TahunAjaran;
use App\Services\PdfAbsensiService;
use App\Services\ZonaSeleksiService;
use Illuminate\Http\Request;

class LaporanAbsensiController extends Controller
{
    /**
     * Finalisasi pendaftaran untuk periode tertentu.
     */
    public function finalisasi(Request $request, ZonaSeleksiService $service)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,tahun_ajaran_id',
        ]);

        $tahunAjaran = TahunAjaran::find($request->tahun_ajaran_id);
        $periode = PeriodePendaftaran::where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->where('semester', $tahunAjaran->semester)
            ->first();

        if (!$periode) {
            return back()->with('error', 'Periode tidak ditemukan.');
        }

        if ($periode->tanggal_pemilihan_ulang && \Carbon\Carbon::now()->lessThanOrEqualTo($periode->waktu_tutup_pemilihan_ulang)) {
            return back()->with('error', 'Masa pemilihan ulang masih berlangsung. Tunggu hingga ' . $periode->tanggal_pemilihan_ulang->format('d/m/Y') . '.');
        }

        try {
            $service->finalisasi($periode->periode_id);
            return back()->with('success', "✓ Finalisasi berhasil untuk {$tahunAjaran->label}. Data sudah siap di-download.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal melakukan finalisasi: ' . $e->getMessage());
        }
    }

    /**
     * Daftar ekskul untuk dipilih laporan absensinya.
     * Admin pilih tahun ajaran + semester → muncul daftar ekskul yang aktif di periode itu.
     */
    public function index(Request $request)
    {
        $tahunAjaranList = TahunAjaran::orderByDesc('tahun_mulai')
            ->orderByRaw("FIELD(semester, 'genap', 'ganjil')")
            ->get();

        // Default ke tahun ajaran aktif
        $tahunAjaranId = $request->get('tahun_ajaran_id',
            optional(TahunAjaran::aktif()->first())->tahun_ajaran_id
        );

        $tahunAjaran = TahunAjaran::find($tahunAjaranId);

        $periode = $tahunAjaran
            ? PeriodePendaftaran::where('tahun_ajaran_id', $tahunAjaranId)
                ->where('semester', $tahunAjaran->semester)
                ->with('tahunAjaran')
                ->first()
            : null;

        $ekskulList = collect();
        if ($periode) {
            $ekskulList = Ekskul::with('pembina')
                ->whereHas('pesertaEkskul', fn($q) =>
                    $q->where('periode_id', $periode->periode_id)
                )
                ->urutHari()
                ->withCount(['pesertaEkskul as jumlah_siswa' => fn($q) =>
                    $q->where('periode_id', $periode->periode_id)
                ])
                ->get();
        }

        return view('admin.laporan-absensi.index', compact(
            'tahunAjaranList', 'tahunAjaranId', 'periode', 'ekskulList'
        ));
    }

    /**
     * Download PDF daftar hadir untuk satu ekskul.
     */
    public function download(Request $request, PdfAbsensiService $service)
    {
        $request->validate([
            'ekskul_id'  => 'required|exists:ekskul,ekskul_id',
            'periode_id' => 'required|exists:periode_pendaftaran,periode_id',
        ]);

        $pdf      = $service->generate($request->ekskul_id, $request->periode_id);
        $ekskul   = Ekskul::find($request->ekskul_id);
        $filename = 'Absensi_' . str_replace(' ', '_', $ekskul->nama_ekskul) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Preview PDF di browser (inline, tidak langsung download).
     */
    public function preview(Request $request, PdfAbsensiService $service)
    {
        $request->validate([
            'ekskul_id'  => 'required|exists:ekskul,ekskul_id',
            'periode_id' => 'required|exists:periode_pendaftaran,periode_id',
        ]);

        $pdf      = $service->generate($request->ekskul_id, $request->periode_id);
        $ekskul   = Ekskul::find($request->ekskul_id);
        $filename = 'Absensi_' . str_replace(' ', '_', $ekskul->nama_ekskul) . '.pdf';

        return $pdf->stream($filename);
    }
}
