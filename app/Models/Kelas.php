<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Kelas- master data kelas SMA (tingkat 10, 11, 12).
 */
class Kelas extends Model
{
    protected $table      = 'kelas';
    protected $primaryKey = 'kelas_id';

    protected $fillable = [
        'tingkat',
        'nama_kelas',
        'is_active',
    ];

    protected $casts = [
        'tingkat'   => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Semua siswa yang sedang berada di kelas ini */
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_id', 'kelas_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Label gabungan untuk dropdown dan tampilan.
     * Contoh: "10A", "11 IPA 1", "12 IPS 2"
     */
    public function getLabelAttribute(): string
    {
        return $this->tingkat . ' - ' . $this->nama_kelas;
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    /** Hanya kelas yang aktif- dipakai di dropdown pemilihan kelas */
    public function scopeAktif($query)
    {
        return $query->where('is_active', 1);
    }

    /** Filter berdasarkan tingkat */
    public function scopeTingkat($query, int $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }
}
