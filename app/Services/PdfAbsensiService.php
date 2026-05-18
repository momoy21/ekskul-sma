<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\PeriodePendaftaran;
use App\Models\PesertaEkskul;
use App\Models\SnapshotLaporan;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * PdfAbsensiService- generate PDF daftar hadir ekskul.
 *
 * PDF layout landscape A4 dengan kolom W1–W17 (17 pertemuan per semester).
 * Data peserta diambil dari tabel peserta_ekskul- menggunakan snapshot
 * jika sudah dikunci (tahun ajaran nonaktif), atau data live jika masih aktif.
 *
 * Cara penggunaan di Controller:
 *   $pdf = app(PdfAbsensiService::class)->generate($ekskulId, $periodeId);
 *   return $pdf->download('nama_file.pdf');       // download
 *   return $pdf->stream('nama_file.pdf');          // preview di browser
 */
class PdfAbsensiService
{
    /**
     * Generate objek PDF untuk satu ekskul di satu periode.
     *
     * @param  int  $ekskulId
     * @param  int  $periodeId
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(int $ekskulId, int $periodeId)
    {
        $ekskul  = Ekskul::with('pembina')->findOrFail($ekskulId);
        $periode = PeriodePendaftaran::with('tahunAjaran')->findOrFail($periodeId);

        // Cek apakah data sudah dikunci- gunakan snapshot jika iya
        $snapshot = SnapshotLaporan::where('ekskul_id', $ekskulId)
            ->where('periode_id', $periodeId)
            ->first();

        // Peserta diurutkan alfabetis berdasarkan nama (snapshot atau live)
        $peserta = PesertaEkskul::where('ekskul_id', $ekskulId)
            ->where('periode_id', $periodeId)
            ->orderByRaw("CAST(SUBSTRING(snapshot_label_kelas, 1, 2) AS UNSIGNED)")
            ->orderByRaw("SUBSTRING(snapshot_label_kelas, 3)")
            ->orderBy('snapshot_nama')
            ->get();

        // Data header laporan- pakai snapshot jika ada, pakai live jika tidak
        $namaEkskul  = $snapshot?->snapshot_nama_ekskul  ?? $ekskul->nama_ekskul;
        $namaPembina = $snapshot?->snapshot_nama_pembina ?? $ekskul->nama_pembina;
        $hari        = $snapshot?->snapshot_hari         ?? $ekskul->hari_pelaksanaan;
        $lokasi      = $snapshot?->snapshot_lokasi       ?? $ekskul->lokasi;

        $data = [
            'nama_ekskul'  => $namaEkskul,
            'nama_pembina' => $namaPembina,
            'hari'         => $hari,
            'lokasi'       => $lokasi,
            'tahun_ajaran' => $periode->tahunAjaran->label,
            'semester'     => ucfirst($periode->semester),
            'peserta'      => $peserta,
            'pertemuan'    => 17,   // W1–W17, satu semester ± 17 minggu efektif
            'data_kunci'   => $snapshot !== null,
        ];

        $pdf = Pdf::loadView('admin.laporan-absensi.template', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf;
    }
}
