<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Siswa- profil lengkap siswa.
 * Relasi 1-to-1 dengan Pengguna (satu akun = satu profil siswa).
 */
/**
 * @property \Carbon\Carbon $tanggal_lahir
 */

class Siswa extends Model
{
    protected $table      = 'siswa';
    protected $primaryKey = 'siswa_id';

    protected $fillable = [
        'pengguna_id',
        'nisn',
        'nama_lengkap',
        'tanggal_lahir',
        'jenis_kelamin',
        'kelas_id',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Akun login siswa ini */
    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id', 'pengguna_id');
    }

    /** Kelas aktif siswa saat ini */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    /** Semua record pendaftaran siswa ini lintas periode */
    public function pendaftaran()
    {
        return $this->hasMany(PendaftaranSiswa::class, 'siswa_id', 'siswa_id');
    }

    /** Semua sesi tes rekomendasi yang pernah diikuti */
    public function tesRekomendasi()
    {
        return $this->hasMany(TesRekomendasi::class, 'siswa_id', 'siswa_id');
    }

    /** Semua ekskul yang diikuti (hasil finalisasi) */
    public function pesertaEkskul()
    {
        return $this->hasMany(PesertaEkskul::class, 'siswa_id', 'siswa_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Label kelas untuk tampilan, contoh: "10A" atau "11 IPA 1".
     * Dipakai di tabel daftar siswa, profil, dan laporan.
     */
    public function getLabelKelasAttribute(): string
    {
        return $this->kelas
            ? $this->kelas->tingkat . $this->kelas->nama_kelas
            : '-';
    }

    /**
     * Password default siswa dari tanggal lahir format DDMMYYYY.
     * Dipakai saat reset password atau buat akun baru.
     */

    public function getPasswordDefaultAttribute(): string
    {
        return $this->tanggal_lahir
            ? $this->tanggal_lahir->format('dmY')
            : '';
    }

    /** Label jenis kelamin untuk tampilan */
    public function getLabelJenisKelaminAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }
}
