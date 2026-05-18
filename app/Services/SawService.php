<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\Kriteria;
use App\Models\TesRekomendasi;

/**
 * SawService- menghitung skor SAW (Simple Additive Weighting) untuk rekomendasi ekskul.
 *
 * Metode SAW bekerja dalam 6 tahap:
 *
 * TAHAP 1- Identifikasi Alternatif & Kriteria
 *   Alternatif (m): Semua ekskul aktif
 *   Kriteria (n): C1-C5 yang aktif
 *
 * TAHAP 2- Normalisasi Bobot Siswa
 *   w'ⱼ = wⱼ / Σ(w₁ + w₂ + w₃ + w₄ + w₅)
 *   Hasilnya: bobot ternormalisasi, sum = 1.0
 *
 * TAHAP 3- Bangun Matriks Keputusan X [m × n]
 *   - C1, C2: rata-rata jawaban siswa pada soal terkait ekskul & kriteria
 *   - C3 (Biaya): nilai dari kolom biaya_tambahan ekskul (admin, 1-5)
 *   - C4 (Fasilitas): nilai dari kolom fasilitas_level ekskul (admin, 1-5)
 *   - C5 (Intensitas): nilai dari kolom intensitas_kegiatan ekskul (admin, 1-5)
 *
 * TAHAP 4- Normalisasi Matriks Keputusan [m × n]
 *   Per kolom kriteria j:
 *   - Benefit (C1, C2, C4): r[i,j] = X[i,j] / max(X[*,j])
 *   - Cost (C3, C5): r[i,j] = min(X[*,j]) / X[i,j]
 *   Hasilnya: nilai 0-1
 *
 * TAHAP 5- Hitung Skor SAW
 *   Vᵢ = Σⱼ₌₁ⁿ (w'ⱼ × rᵢⱼ)
 *
 * TAHAP 6- Perangkingan
 *   Urutkan dari tertinggi ke terendah, ambil top 3
 */
class SawService
{
    /**
     * Hitung skor SAW untuk semua ekskul aktif berdasarkan tes siswa.
     *
     * @param  TesRekomendasi $tes  Harus sudah di-load relasinya: jawabanTes
     * @return array                Diurutkan dari skor tertinggi:
     *                              [['ekskul_id' => int, 'skor' => float], ...]
     */
    public function hitung(TesRekomendasi $tes): array
    {
        // ── TAHAP 1: Ambil Alternatif & Kriteria ────────────────────────────
        $kriteriaList = Kriteria::aktif()->get();
        $ekskulList = Ekskul::with('soal')->aktif()->get();

        // ── TAHAP 2: Normalisasi Bobot Siswa ──────────────────────────────────
        // Formula: w'ⱼ = wⱼ / Σ(wⱼ)
        $bobotTernormalisasi = $tes->getBobotTernormalisasi();

        // Jawaban siswa: [soal_id => nilai_jawaban]
        $jawaban = $tes->jawabanTes->pluck('nilai_jawaban', 'soal_id')->toArray();

        // ── TAHAP 3: Bangun Matriks Keputusan X [m × n] ───────────────────────
        $nilaiMentah = $this->hitungNilaiMentah($ekskulList, $kriteriaList, $jawaban);

        // ── TAHAP 4: Normalisasi Matriks X → R ─────────────────────────────────
        $nilaiNormal = $this->normalisasi($ekskulList, $kriteriaList, $nilaiMentah);

        // ── TAHAP 5: Hitung Skor SAW ───────────────────────────────────────────
        // Vᵢ = Σ(w'ⱼ × rᵢⱼ)
        $skorAkhir = $this->hitungSkorAkhir($ekskulList, $kriteriaList, $nilaiNormal, $bobotTernormalisasi);

        // ── TAHAP 6: Perangkingan ────────────────────────────────────────────
        usort($skorAkhir, fn($a, $b) => $b['skor'] <=> $a['skor']);

        return $skorAkhir;
    }

    /**
     * TAHAP 3- Hitung Matriks Nilai Mentah X [m × n].
     *
     * - C1, C2: rata-rata jawaban siswa pada soal terkait ekskul & kriteria
     * - C3 (Biaya): nilai dari kolom biaya_tambahan ekskul (admin, 1-5)
     * - C4 (Fasilitas): nilai dari kolom fasilitas_level ekskul (admin, 1-5)
     * - C5 (Intensitas): nilai dari kolom intensitas_kegiatan ekskul (admin, 1-5)
     */
    private function hitungNilaiMentah($ekskulList, $kriteriaList, array $jawaban): array
    {
        $nilaiMentah = [];

        foreach ($ekskulList as $ekskul) {
            foreach ($kriteriaList as $k) {
                $kode = $k->kode;

                if (in_array($kode, ['C1', 'C2'])) {
                    // Dari jawaban soal siswa
                    $soalTerkait = $ekskul->soal->filter(
                        fn($s) => $s->kriteria_id === $k->kriteria_id
                                  && array_key_exists($s->soal_id, $jawaban)
                    );

                    if ($soalTerkait->isEmpty()) {
                        $nilaiMentah[$ekskul->ekskul_id][$kode] = 1;
                    } else {
                        $total = $soalTerkait->sum(fn($s) => $jawaban[$s->soal_id]);
                        $nilaiMentah[$ekskul->ekskul_id][$kode] = $total / $soalTerkait->count();
                    }
                } else if ($kode === 'C3') {
                    // Biaya: dari kolom biaya_tambahan (admin)
                    $nilaiMentah[$ekskul->ekskul_id][$kode] = $ekskul->biaya_tambahan ?? 3;
                } else if ($kode === 'C4') {
                    // Fasilitas: dari kolom fasilitas_level (admin)
                    $nilaiMentah[$ekskul->ekskul_id][$kode] = $ekskul->fasilitas_level ?? 3;
                } else if ($kode === 'C5') {
                    // Intensitas: dari kolom intensitas_kegiatan (admin)
                    $nilaiMentah[$ekskul->ekskul_id][$kode] = $ekskul->intensitas_kegiatan ?? 3;
                }
            }
        }

        return $nilaiMentah;
    }

    /**
     * TAHAP 4- Normalisasi Matriks Keputusan ke Skala 0–1.
     *
     * Per kolom kriteria j:
     * - Benefit (C1, C2, C4): r[i,j] = X[i,j] / max(X[*,j])
     * - Cost (C3, C5): r[i,j] = min(X[*,j]) / X[i,j]
     */
    private function normalisasi($ekskulList, $kriteriaList, array $nilaiMentah): array
    {
        $nilaiNormal = [];

        foreach ($kriteriaList as $k) {
            // Kumpulkan semua nilai ekskul untuk kriteria ini
            $semuaNilai = array_map(
                fn($ekskul) => $nilaiMentah[$ekskul->ekskul_id][$k->kode],
                $ekskulList->all()
            );

            $max = ! empty($semuaNilai) ? max($semuaNilai) : 1;
            $min = ! empty($semuaNilai) ? min($semuaNilai) : 1;

            foreach ($ekskulList as $ekskul) {
                $nilai = $nilaiMentah[$ekskul->ekskul_id][$k->kode];

                $nilaiNormal[$ekskul->ekskul_id][$k->kode] = match ($k->tipe_atribut) {
                    'benefit' => $max > 0 ? round($nilai / $max, 6) : 0,
                    'cost'    => $nilai > 0 ? round($min / $nilai, 6) : 0,
                    default   => 0,
                };
            }
        }

        return $nilaiNormal;
    }

    /**
     * TAHAP 5- Hitung Skor SAW Akhir per Ekskul.
     *
     * Rumus: Vᵢ = Σⱼ₌₁ⁿ (w'ⱼ × rᵢⱼ)
     *
     * di mana w'ⱼ sudah ternormalisasi (sum = 1.0)
     */
    private function hitungSkorAkhir($ekskulList, $kriteriaList, array $nilaiNormal, array $bobotTernormalisasi): array
    {
        $hasil = [];

        foreach ($ekskulList as $ekskul) {
            $skorWeighted = 0;

            foreach ($kriteriaList as $k) {
                $bobotNorm      = $bobotTernormalisasi[$k->kode] ?? 0;
                $nilaiNormKini  = $nilaiNormal[$ekskul->ekskul_id][$k->kode] ?? 0;
                $skorWeighted  += $bobotNorm * $nilaiNormKini;
            }

            $hasil[] = [
                'ekskul_id' => $ekskul->ekskul_id,
                'skor'      => round($skorWeighted, 4),
            ];
        }

        return $hasil;
    }
}
