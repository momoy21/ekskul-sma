<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model TesRekomendasi- sesi tes yang dilakukan siswa.
 * Satu siswa hanya boleh punya satu tes per periode (unique constraint di DB).
 * Bobot C1-C5 dari Tahap 1, jawaban soal ada di tabel jawaban_tes.
 * Hasil SAW top 3 disimpan di tabel hasil_rekomendasi.
 */
class TesRekomendasi extends Model
{
    protected $table      = 'tes_rekomendasi';
    protected $primaryKey = 'tes_id';

    // Tidak ada updated_at karena tes tidak di-update (kalau ulang = hapus lalu buat baru)
    const UPDATED_AT = null;

    protected $fillable = [
        'siswa_id',
        'periode_id',
        'bobot_c1',
        'bobot_c2',
        'bobot_c3',
        'bobot_c4',
        'bobot_c5',
        'submitted_at',
    ];

    protected $casts = [
        'bobot_c1'     => 'integer',
        'bobot_c2'     => 'integer',
        'bobot_c3'     => 'integer',
        'bobot_c4'     => 'integer',
        'bobot_c5'     => 'integer',
        'submitted_at' => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'siswa_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'periode_id', 'periode_id');
    }

    /** Semua jawaban soal dalam tes ini */
    public function jawabanTes()
    {
        return $this->hasMany(JawabanTes::class, 'tes_id', 'tes_id');
    }

    /**
     * Hasil rekomendasi top 3 setelah SAW dihitung.
     * Diurutkan dari peringkat 1 ke 3.
     */
    public function hasilRekomendasi()
    {
        return $this->hasMany(HasilRekomendasi::class, 'tes_id', 'tes_id')
            ->orderBy('peringkat');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /** Cek apakah tes sudah selesai disubmit */
    public function getSudahSubmitAttribute(): bool
    {
        return ! is_null($this->submitted_at);
    }

    /**
     * Ambil bobot untuk kode kriteria tertentu.
     * Contoh: $tes->getBobotKriteria('C1') → 4
     */
    public function getBobotKriteria(string $kode): int
    {
        $map = [
            'C1' => $this->bobot_c1,
            'C2' => $this->bobot_c2,
            'C3' => $this->bobot_c3,
            'C4' => $this->bobot_c4,
            'C5' => $this->bobot_c5,
        ];

        return $map[strtoupper($kode)] ?? 1;
    }

    /**
     * Hitung bobot ternormalisasi untuk semua kriteria.
     * Formula: w'ⱼ = wⱼ / Σ(w₁ + w₂ + w₃ + w₄ + w₅)
     * Hasilnya: array [kode => bobot_ternormalisasi (0-1)]
     *
     * Contoh:
     * Jika bobot_c1=5, bobot_c2=4, bobot_c3=3, bobot_c4=2, bobot_c5=1
     * Sum = 15
     * Hasil: ['C1' => 0.333, 'C2' => 0.267, 'C3' => 0.200, 'C4' => 0.133, 'C5' => 0.067]
     */
    public function getBobotTernormalisasi(): array
    {
        $bobot = [
            'C1' => $this->bobot_c1 ?? 1,
            'C2' => $this->bobot_c2 ?? 1,
            'C3' => $this->bobot_c3 ?? 1,
            'C4' => $this->bobot_c4 ?? 1,
            'C5' => $this->bobot_c5 ?? 1,
        ];

        $totalBobot = array_sum($bobot);

        // Hindari division by zero
        if ($totalBobot <= 0) {
            $totalBobot = 1;
        }

        // Normalisasi setiap bobot
        return array_map(fn($b) => round($b / $totalBobot, 6), $bobot);
    }
}
