<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model PilihanEkskul- detail setiap pilihan ekskul dalam satu pendaftaran.
 * Satu pendaftaran bisa punya 1-4 baris di tabel ini (urutan_pilihan 1-4).
 *
 * Kolom zona diisi sistem setelah pendaftaran ditutup:
 * hijau  → pendaftar >= kuota_minimal (ekskul pasti buka, siswa diterima)
 * kuning → pendaftar tepat 9 dari minimal 10 (minta siswa siapkan cadangan)
 * merah  → pendaftar jauh dari kuota (ekskul tidak buka, siswa wajib ganti)
 */
class PilihanEkskul extends Model
{
    protected $table      = 'pilihan_ekskul';
    protected $primaryKey = 'pilihan_id';

    protected $fillable = [
        'pendaftaran_id',
        'ekskul_id',
        'urutan_pilihan',
        'status_zona',
        'ekskul_cadangan_id',
        'ekskul_final_id',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted'    => 'boolean',
        'urutan_pilihan' => 'integer',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Record pendaftaran yang memiliki pilihan ini */
    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranSiswa::class, 'pendaftaran_id', 'pendaftaran_id');
    }

    /** Ekskul pilihan utama */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id', 'ekskul_id');
    }

    /**
     * Ekskul cadangan- diisi siswa saat fase pengumuman jika zona = kuning.
     * Nullable: tidak semua pilihan punya cadangan.
     */
    public function ekskulCadangan()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_cadangan_id', 'ekskul_id');
    }

    /**
     * Ekskul final- hasil akhir setelah finalisasi.
     * Bisa sama dengan utama (zona hijau) atau cadangan (zona kuning yang tidak terpenuhi).
     */
    public function ekskulFinal()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_final_id', 'ekskul_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Badge warna zona untuk tampilan Blade.
     * Mengembalikan class Bootstrap yang sesuai.
     */
    public function getBadgeZonaAttribute(): string
    {
        return match ($this->status_zona) {
            'hijau'  => 'success',
            'kuning' => 'warning',
            'merah'  => 'danger',
            default  => 'secondary',
        };
    }

    /**
     * Label zona yang ramah dibaca.
     */
    public function getLabelZonaAttribute(): string
    {
        return match ($this->status_zona) {
            'hijau'  => '🟢 Zona Hijau',
            'kuning' => '🟡 Zona Kuning',
            'merah'  => '🔴 Zona Merah',
            default  => '-',
        };
    }
}
