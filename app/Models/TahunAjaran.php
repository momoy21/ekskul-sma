<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model TahunAjaran- arsip per tahun ajaran per semester.
 * Hanya satu yang boleh is_active = 1 pada satu waktu.
 * Kombinasi (tahun_mulai, tahun_selesai, semester) harus unik.
 */
class TahunAjaran extends Model
{
    protected $table      = 'tahun_ajaran';
    protected $primaryKey = 'tahun_ajaran_id';

    const UPDATED_AT = null;

    protected $fillable = [
        'tahun_mulai',
        'tahun_selesai',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Semua periode pendaftaran dalam tahun ajaran+semester ini */
    public function periodePendaftaran()
    {
        return $this->hasMany(PeriodePendaftaran::class, 'tahun_ajaran_id', 'tahun_ajaran_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Label tampilan: "2025/2026- Ganjil"
     */
    public function getLabelAttribute(): string
    {
        return $this->tahun_mulai . '/' . $this->tahun_selesai . ' - ' . ucfirst($this->semester);
    }

    /**
     * Label tahun saja tanpa semester: "2025/2026"
     */
    public function getLabelTahunAttribute(): string
    {
        return $this->tahun_mulai . '/' . $this->tahun_selesai;
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', 1);
    }
}
