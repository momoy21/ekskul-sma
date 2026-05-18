<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model PesertaEkskul- daftar final siswa yang terdaftar di tiap ekskul per periode.
 * Dibuat saat finalisasi setelah masa pemilihan ulang selesai.
 *
 * Kolom snapshot_* menyimpan data siswa SAAT data dikunci.
 * Tujuannya: laporan absensi tidak ikut berubah kalau siswa pindah kelas atau ganti nama.
 *
 * is_locked = 1 dipicu saat tahun ajaran baru dibuat- data benar-benar dibekukan.
 */
class PesertaEkskul extends Model
{
    protected $table      = 'peserta_ekskul';
    protected $primaryKey = 'peserta_id';

    // Tidak ada updated_at- setelah dikunci, data tidak pernah berubah
    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'ekskul_id',
        'periode_id',
        'snapshot_nama',
        'snapshot_nisn',
        'snapshot_jenis_kelamin',
        'snapshot_label_kelas',
        'is_locked',
        'enrolled_at',
    ];

    protected $casts = [
        'is_locked'   => 'boolean',
        'enrolled_at' => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Data siswa aktual (untuk akses data yang tidak di-snapshot) */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'siswa_id');
    }

    /** Ekskul yang diikuti */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id', 'ekskul_id');
    }

    /** Periode pendaftaran terkait */
    public function periode()
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'periode_id', 'periode_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Nama tampilan- pakai snapshot jika sudah dikunci, pakai data live jika belum.
     * Ini memastikan laporan tahun lama tetap akurat.
     */
    public function getNamaDisplayAttribute(): string
    {
        return $this->is_locked
            ? $this->snapshot_nama
            : ($this->siswa->nama_lengkap ?? $this->snapshot_nama);
    }

    /**
     * Label kelas display- sama dengan nama, pakai snapshot jika sudah dikunci.
     */
    public function getKelasDisplayAttribute(): string
    {
        return $this->is_locked
            ? $this->snapshot_label_kelas
            : ($this->siswa->label_kelas ?? $this->snapshot_label_kelas);
    }
}
