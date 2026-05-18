<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Kriteria- 5 kriteria SAW yang bersifat tetap (C1-C5).
 * Tidak ada created_at karena data di-seed satu kali saat setup.
 * Yang boleh diedit dari UI: nama_kriteria, deskripsi_siswa, is_active.
 * tipe_atribut (benefit/cost) TIDAK boleh diubah- berpengaruh ke logika normalisasi SAW.
 */
class Kriteria extends Model
{
    protected $table      = 'kriteria';
    protected $primaryKey = 'kriteria_id';

    // Tidak ada created_at, hanya updated_at
    const CREATED_AT = null;

    // Tipe data integer kecil karena hanya 5 baris
    protected $keyType = 'int';

    protected $fillable = [
        'kode',
        'nama_kriteria',
        'tipe_atribut',
        'deskripsi_siswa',
        'urutan_tampil',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'urutan_tampil'  => 'integer',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Soal-soal yang berkaitan dengan kriteria ini */
    public function soal()
    {
        return $this->hasMany(SoalRekomendasi::class, 'kriteria_id', 'kriteria_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Label lengkap untuk dropdown soal: "C1- Kesesuaian Minat"
     */
    public function getLabelAttribute(): string
    {
        return $this->kode . '- ' . $this->nama_kriteria;
    }

    /**
     * Label sifat untuk tampilan tabel: "Benefit" atau "Cost"
     */
    public function getLabelTipeAttribute(): string
    {
        return ucfirst($this->tipe_atribut);
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    /** Hanya kriteria aktif- dipakai saat perhitungan SAW dan tampil tes */
    public function scopeAktif($query)
    {
        return $query->where('is_active', 1)->orderBy('urutan_tampil');
    }
}
