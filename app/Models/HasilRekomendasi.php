<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model HasilRekomendasi- top 3 ekskul hasil perhitungan SAW.
 * Disimpan setelah siswa submit tes agar tidak dihitung ulang tiap kali dibuka.
 * Muncul sebagai smart suggestion di halaman pendaftaran.
 *
 * Unique constraint:
 * (tes_id, peringkat)  → tidak boleh ada dua ekskul di peringkat sama dalam satu tes
 * (tes_id, ekskul_id)  → satu ekskul tidak boleh muncul dua kali dalam satu tes
 */
class HasilRekomendasi extends Model
{
    protected $table      = 'hasil_rekomendasi';
    protected $primaryKey = 'hasil_id';

    // Hanya created_at, tidak ada updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'tes_id',
        'ekskul_id',
        'peringkat',
        'skor_saw',
    ];

    protected $casts = [
        'peringkat' => 'integer',
        'skor_saw'  => 'float',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Sesi tes yang menghasilkan rekomendasi ini */
    public function tes()
    {
        return $this->belongsTo(TesRekomendasi::class, 'tes_id', 'tes_id');
    }

    /** Ekskul yang direkomendasikan */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id', 'ekskul_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Skor dalam persen untuk tampilan ke siswa.
     * Contoh: 0.9425 → "94%"
     */
    public function getSkorPersenAttribute(): string
    {
        return round($this->skor_saw * 100) . '%';
    }

    /**
     * Emoji medali sesuai peringkat untuk tampilan hasil tes.
     */
    public function getEmojPeringkatAttribute(): string
    {
        return match ($this->peringkat) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => '#' . $this->peringkat,
        };
    }
}
