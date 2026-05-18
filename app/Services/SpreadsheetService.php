<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\PeriodePendaftaran;
use App\Models\PesertaEkskul;
use App\Models\SnapshotLaporan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * SpreadsheetService- generate file Excel laporan final ekskul.
 *
 * Struktur file:
 * - Sheet 1 : REKAP SEMUA EKSKUL (ringkasan nama + pembina + jumlah + total)
 * - Sheet 2+ : Detail per ekskul (NISN, nama, jenis kelamin, kelas)
 *
 * Data diambil dari peserta_ekskul. Jika periode sudah dikunci (ada snapshot),
 * header laporan menggunakan data snapshot agar akurat untuk arsip.
 *
 * Cara penggunaan di Controller:
 *   $spreadsheet = app(SpreadsheetService::class)->generate($periodeId);
 *   $writer = new Xlsx($spreadsheet);
 *   return response()->streamDownload(fn() => $writer->save('php://output'), 'nama.xlsx', [...]);
 */
class SpreadsheetService
{
    // Warna header utama (biru muda)
    private const WARNA_HEADER = 'BDD7EE';

    // Warna header sub-tabel (abu-abu muda)
    private const WARNA_SUBHEADER = 'D9D9D9';

    // Warna baris total (kuning muda)
    private const WARNA_TOTAL = 'FFF2CC';

    /**
     * Generate Spreadsheet lengkap untuk satu periode.
     *
     * @param  int         $periodeId
     * @return Spreadsheet
     */
    public function generate(int $periodeId): Spreadsheet
    {
        $periode = PeriodePendaftaran::with('tahunAjaran')->findOrFail($periodeId);

        // Ambil ekskul yang punya peserta di periode ini, diurutkan per hari
        $ekskulList = Ekskul::with('pembina')
            ->whereHas('pesertaEkskul', fn($q) => $q->where('periode_id', $periodeId))
            ->urutHari()
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle("Laporan Ekskul {$periode->tahunAjaran->label}")
            ->setCreator('Sistem Ekstrakurikuler SMA Global Indonesia');

        // ── Sheet 1: Rekap semua ekskul ───────────────────────────────────────
        $sheetRekap = $spreadsheet->getActiveSheet();
        $sheetRekap->setTitle('REKAP SEMUA EKSKUL');
        $this->tulisSheetRekap($sheetRekap, $periode, $ekskulList);

        // ── Sheet per ekskul ──────────────────────────────────────────────────
        foreach ($ekskulList as $index => $ekskul) {
            $sheetDetail = $spreadsheet->createSheet($index + 1);

            // Nama sheet max 31 karakter (batasan Excel)
            $namaSheet = mb_substr($ekskul->nama_ekskul, 0, 31);
            $sheetDetail->setTitle($namaSheet);

            $this->tulisSheetEkskul($sheetDetail, $ekskul, $periode);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Tulis sheet rekap semua ekskul.
     *
     * Format:
     * Baris 1-2 : Judul
     * Baris 3   : Kosong
     * Baris 4   : Header kolom (No, Nama Ekskul, Pembina, Jumlah Siswa)
     * Baris 5+  : Data per ekskul
     * Baris N   : Total
     */
    private function tulisSheetRekap($sheet, $periode, $ekskulList): void
    {
        $ta = $periode->tahunAjaran;

        // ── Judul ─────────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'REKAP EKSTRAKURIKULER');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', "Tahun Ajaran {$ta->label} - Semester " . ucfirst($periode->semester));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Header kolom ──────────────────────────────────────────────────────
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Nama Ekstrakurikuler');
        $sheet->setCellValue('C4', 'Pembina');
        $sheet->setCellValue('D4', 'Jumlah Siswa');

        $sheet->getStyle('A4:D4')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::WARNA_HEADER]],
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // ── Data ekskul ───────────────────────────────────────────────────────
        $baris = 5;
        $total = 0;

        foreach ($ekskulList as $no => $ekskul) {
            // Ambil snapshot jika ada, fallback ke data live
            $snapshot    = SnapshotLaporan::where('ekskul_id', $ekskul->ekskul_id)
                ->where('periode_id', $ekskul->pesertaEkskul->first()?->periode_id ?? 0)
                ->first();
            $namaPembina = $snapshot?->snapshot_nama_pembina ?? $ekskul->nama_pembina;

            $jumlahSiswa = PesertaEkskul::where('ekskul_id', $ekskul->ekskul_id)
                ->where('periode_id', request()->route('periodeId') ?? 0)
                ->count();

            // Hitung dari relasi yang sudah di-eager load
            $jumlahSiswa = $ekskul->pesertaEkskul->count();
            $total      += $jumlahSiswa;

            $sheet->setCellValue("A{$baris}", $no + 1);
            $sheet->setCellValue("B{$baris}", $ekskul->nama_ekskul);
            $sheet->setCellValue("C{$baris}", $namaPembina);
            $sheet->setCellValue("D{$baris}", $jumlahSiswa);

            $sheet->getStyle("A{$baris}:D{$baris}")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $baris++;
        }

        // ── Baris total ───────────────────────────────────────────────────────
        $sheet->mergeCells("A{$baris}:C{$baris}");
        $sheet->setCellValue("A{$baris}", 'TOTAL');
        $sheet->setCellValue("D{$baris}", $total);

        $sheet->getStyle("A{$baris}:D{$baris}")->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::WARNA_TOTAL]],
            'font'      => ['bold' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Lebar kolom ───────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(16);
    }

    /**
     * Tulis sheet detail per ekskul.
     *
     * Format:
     * Baris 1   : "DAFTAR SISWA EKSTRAKURIKULER"
     * Baris 2-5 : Info header (tahun ajaran, semester, nama ekskul, pembina)
     * Baris 6   : Kosong
     * Baris 7   : Header tabel (No, NISN, Nama, Jenis Kelamin, Kelas)
     * Baris 8+  : Data siswa diurutkan alfabetis
     */
    private function tulisSheetEkskul($sheet, Ekskul $ekskul, $periode): void
    {
        $ta = $periode->tahunAjaran;

        // Cek snapshot untuk header
        $snapshot    = SnapshotLaporan::where('ekskul_id', $ekskul->ekskul_id)
            ->where('periode_id', $periode->periode_id)
            ->first();
        $namaEkskul  = $snapshot?->snapshot_nama_ekskul  ?? $ekskul->nama_ekskul;
        $namaPembina = $snapshot?->snapshot_nama_pembina ?? $ekskul->nama_pembina;

        // ── Header info ───────────────────────────────────────────────────────
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'DAFTAR SISWA EKSTRAKURIKULER');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $infoRows = [
            2 => ['Tahun Ajaran', $ta->label],
            3 => ['Semester',     ucfirst($periode->semester)],
            4 => ['Ekskul',       $namaEkskul],
            5 => ['Pembina',      $namaPembina],
        ];

        foreach ($infoRows as $baris => [$label, $nilai]) {
            $sheet->setCellValue("A{$baris}", $label);
            $sheet->setCellValue("B{$baris}", ": {$nilai}");
            $sheet->mergeCells("B{$baris}:E{$baris}");
        }

        // ── Header tabel ──────────────────────────────────────────────────────
        $sheet->setCellValue('A7', 'No');
        $sheet->setCellValue('B7', 'NISN');
        $sheet->setCellValue('C7', 'Nama Lengkap');
        $sheet->setCellValue('D7', 'Jenis Kelamin');
        $sheet->setCellValue('E7', 'Kelas');

        $sheet->getStyle('A7:E7')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::WARNA_SUBHEADER]],
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // ── Data siswa ────────────────────────────────────────────────────────
        $peserta = PesertaEkskul::where('ekskul_id', $ekskul->ekskul_id)
            ->where('periode_id', $periode->periode_id)
            ->orderByRaw("CAST(SUBSTRING(snapshot_label_kelas, 1, 2) AS UNSIGNED)")
            ->orderByRaw("SUBSTRING(snapshot_label_kelas, 3)")
            ->orderBy('snapshot_nama')
            ->get();

        $baris = 8;
        foreach ($peserta as $no => $p) {
            $sheet->setCellValue("A{$baris}", $no + 1);
            $sheet->setCellValue("B{$baris}", $p->snapshot_nisn);
            $sheet->setCellValue("C{$baris}", $p->snapshot_nama);
            $sheet->setCellValue("D{$baris}", $p->snapshot_jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');
            $sheet->setCellValue("E{$baris}", $p->snapshot_label_kelas);

            $sheet->getStyle("A{$baris}:E{$baris}")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical'   => Alignment::VERTICAL_CENTER],
            ]);

            // Format NISN sebagai teks supaya angka 0 di depan tidak hilang
            $sheet->getCell("B{$baris}")->getStyle()
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            $baris++;
        }

        // ── Lebar kolom ───────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(12);
    }
}
