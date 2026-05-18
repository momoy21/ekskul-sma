<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Pengguna- akun login untuk admin dan siswa.
 * Tidak extend Authenticatable karena pakai custom session auth.
 */
class Pengguna extends Model
{
    protected $table      = 'pengguna';
    protected $primaryKey = 'pengguna_id';

    protected $fillable = [
        'username',
        'password',
        'role',
        'is_active',
    ];

    // Jangan tampilkan password saat di-serialize ke JSON
    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Profil siswa yang terhubung ke akun ini (null jika role = admin) */
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'pengguna_id', 'pengguna_id');
    }
}
