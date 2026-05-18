<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Pembina- daftar guru pembina ekskul.
 */
class Pembina extends Model
{
    protected $table      = 'pembina';
    protected $primaryKey = 'pembina_id';

    protected $fillable = [
        'nama_lengkap',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Ekskul yang diampu pembina ini (many-to-many via ekskul_pembina) */
    public function ekskul()
    {
        return $this->belongsToMany(
            Ekskul::class,
            'ekskul_pembina',
            'pembina_id',
            'ekskul_id'
        );
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', 1);
    }
}
