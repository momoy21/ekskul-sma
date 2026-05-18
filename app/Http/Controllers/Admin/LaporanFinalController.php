<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\PeriodePendaftaran;
use App\Models\TahunAjaran;
use App\Services\SpreadsheetService;
use App\Services\ZonaSeleksiService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanFinalController extends Controller
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

        // Cek apakah tanggal pemilihan ulang sudah lewat atau tidak ada
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
     * Tampilkan halaman laporan final- preview data sebelum download spreadsheet.
     */
    public function index(Request $request)
    {
        $tahunAjaranList = TahunAjaran::orderByDesc('tahun_mulai')
            ->orderByRaw("FIELD(semester, 'genap', 'ganjil')")
            ->get();

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

        // Ambil data ringkasan ekskul untuk tabel preview
        $ekskulList = collect();
        $totalSiswa = 0;

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

            $totalSiswa = $ekskulList->sum('jumlah_siswa');
        }

        // Tandai apakah data sudah dikunci (tahun ajaran tidak aktif)
        $dataKunci = $tahunAjaran && ! $tahunAjaran->is_active;

        return view('admin.laporan-final.index', compact(
            'tahunAjaranList', 'tahunAjaranId',
            'periode', 'ekskulList', 'totalSiswa', 'dataKunci'
        ));
    }

    /**
     * Generate dan download file Excel laporan final.
     */
    public function download(Request $request, SpreadsheetService $service)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,tahun_ajaran_id',
        ]);

        $tahunAjaran = TahunAjaran::find($request->tahun_ajaran_id);

        $periode = PeriodePendaftaran::where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->where('semester', $tahunAjaran->semester)
            ->with('tahunAjaran')
            ->firstOrFail();

        $spreadsheet = $service->generate($periode->periode_id);

        // Buat nama file dari data tahun ajaran
        $ta       = $periode->tahunAjaran;
        $filename = "Laporan_Ekskul_{$ta->tahun_mulai}_{$ta->tahun_selesai}_" . ucfirst($ta->semester) . ".xlsx";

        // Stream langsung ke browser sebagai download
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) { $writer->save('php://output'); },
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
