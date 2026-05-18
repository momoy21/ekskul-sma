<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model PendaftaranSiswa- record utama pendaftaran per siswa per periode.
 * Satu siswa hanya boleh punya satu pendaftaran per periode.
 * Pilihan ekskul detail ada di tabel pilihan_ekskul (hasMany dari sini).
 *
 * Alur status:
 * draft     → siswa buka form, belum klik simpan
 * submitted → siswa sudah simpan + ttd orang tua
 * finalized → sistem kunci setelah masa pemilihan ulang selesai
 */
class PendaftaranSiswa extends Model
{
    protected $table      = 'pendaftaran_siswa';
    protected $primaryKey = 'pendaftaran_id';

    protected $fillable = [
        'siswa_id',
        'periode_id',
        'tanda_tangan_ortu',
        'waktu_ttd',
        'status',
    ];

    protected $casts = [
        'waktu_ttd' => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Siswa yang melakukan pendaftaran ini */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'siswa_id');
    }

    /** Periode pendaftaran yang bersangkutan */
    public function periode()
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'periode_id', 'periode_id');
    }

    /**
     * Pilihan ekskul dalam pendaftaran ini (1-4 pilihan).
     * Diurutkan berdasarkan urutan_pilihan.
     */
    public function pilihanEkskul()
    {
        return $this->hasMany(PilihanEkskul::class, 'pendaftaran_id', 'pendaftaran_id')
            ->orderBy('urutan_pilihan');
    }

    /**
     * Pilihan ekskul yang masih aktif (belum di-soft delete).
     * Dipakai untuk tampilan di halaman pengumuman.
     */
    public function pilihanEkskulAktif()
    {
        return $this->hasMany(PilihanEkskul::class, 'pendaftaran_id', 'pendaftaran_id')
            ->where('is_deleted', 0)
            ->orderBy('urutan_pilihan');
    }
}
