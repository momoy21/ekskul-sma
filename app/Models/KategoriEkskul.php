<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model KategoriEkskul- pengelompokan ekskul seperti Seni, Olahraga, Teknologi.
 * Digunakan sebagai label dan filter di katalog informasi ekskul siswa.
 */
class KategoriEkskul extends Model
{
    protected $table      = 'kategori_ekskul';
    protected $primaryKey = 'kategori_ekskul_id';

    protected $fillable = [
        'nama_kategori',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Semua ekskul dalam kategori ini */
    public function ekskul()
    {
        return $this->hasMany(Ekskul::class, 'kategori_ekskul_id', 'kategori_ekskul_id');
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', 1);
    }
}
