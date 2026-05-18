<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * PeriodePendaftaran
 *
 * Alur waktu yang dipakai:
 *
 *   tanggal_buka 00:00 ──────────────────────► tanggal_tutup 11:00
 *                                                      │
 *                                               +30 menit jeda
 *                                                      ↓
 *                                          tanggal_tutup 11:30
 *                                      PENGUMUMAN TAMPIL & PEMILIHAN
 *                                          ULANG DIBUKA (hari yang sama)
 *                                                      │
 *                                                      ▼
 *                                    tanggal_pemilihan_ulang 23:59
 *                                      PEMILIHAN ULANG DITUTUP
 *
 * Jam yang di-hardcode:
 *   JAM_TUTUP_PENDAFTARAN  = 11:00 → pendaftaran tidak bisa diisi setelah jam ini
 *   JAM_PENGUMUMAN         = 11:30 → pengumuman dan pemilihan ulang mulai tampil
 *   JAM_TUTUP_PEMILIHAN    = 23:59 → pemilihan ulang ditutup di hari tanggal_pemilihan_ulang
 */
class PeriodePendaftaran extends Model
{
    protected $table      = 'periode_pendaftaran';
    protected $primaryKey = 'periode_id';

    protected $fillable = [
        'tahun_ajaran_id',
        'semester',
        'tanggal_buka',
        'tanggal_tutup',
        'tanggal_pemilihan_ulang',
    ];

    protected $casts = [
        'tanggal_buka'            => 'date',
        'tanggal_tutup'           => 'date',
        'tanggal_pemilihan_ulang' => 'date',
    ];

    // ── Jam hardcode- ganti di sini kalau mau diubah ─────────────────────────
    const JAM_TUTUP_PENDAFTARAN = '11:00'; // pendaftaran ditutup jam ini
    const JAM_PENGUMUMAN        = '11:30'; // pengumuman + pemilihan ulang dibuka jam ini
    const JAM_TUTUP_PEMILIHAN   = '23:59'; // pemilihan ulang ditutup jam ini

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id', 'tahun_ajaran_id');
    }

    public function pendaftaranSiswa()
    {
        return $this->hasMany(PendaftaranSiswa::class, 'periode_id', 'periode_id');
    }

    public function tesRekomendasi()
    {
        return $this->hasMany(TesRekomendasi::class, 'periode_id', 'periode_id');
    }

    public function pesertaEkskul()
    {
        return $this->hasMany(PesertaEkskul::class, 'periode_id', 'periode_id');
    }

    public function snapshotLaporan()
    {
        return $this->hasMany(SnapshotLaporan::class, 'periode_id', 'periode_id');
    }

    // ── Accessor / Status Checker ─────────────────────────────────────────────

    /**
     * Waktu persis pendaftaran ditutup: tanggal_tutup jam 11:00
     * Contoh: 2025-07-03 11:00:00
     */
    public function getWaktuTutupPendaftaranAttribute(): Carbon
    {
        return Carbon::parse(
            $this->tanggal_tutup->format('Y-m-d') . ' ' . self::JAM_TUTUP_PENDAFTARAN
        );
    }

    /**
     * Waktu pengumuman tampil: tanggal_tutup jam 11:30
     * 30 menit setelah tutup, di hari yang sama.
     */
    public function getWaktuPengumumanAttribute(): Carbon
    {
        return Carbon::parse(
            $this->tanggal_tutup->format('Y-m-d') . ' ' . self::JAM_PENGUMUMAN
        );
    }

    /**
     * Waktu pemilihan ulang ditutup: tanggal_pemilihan_ulang jam 23:59
     */
    public function getWaktuTutupPemilihanUlangAttribute(): ?Carbon
    {
        if (! $this->tanggal_pemilihan_ulang) {
            return null;
        }

        return Carbon::parse(
            $this->tanggal_pemilihan_ulang->format('Y-m-d') . ' ' . self::JAM_TUTUP_PEMILIHAN
        );
    }

    /**
     * Pendaftaran sedang buka:
     * Mulai  : tanggal_buka jam 00:00
     * Berakhir: tanggal_tutup jam 11:00
     */
    public function getPendaftaranSedangBukaAttribute(): bool
    {
        $sekarang   = Carbon::now();
        $mulai      = Carbon::parse($this->tanggal_buka->format('Y-m-d') . ' 00:00');
        $tutup      = $this->waktu_tutup_pendaftaran;

        return $sekarang->greaterThanOrEqualTo($mulai)
            && $sekarang->lessThanOrEqualTo($tutup);
    }

    /**
     * Pengumuman tersedia mulai tanggal_tutup jam 11:30.
     * Ini di hari yang SAMA dengan penutupan pendaftaran,
     * tapi 30 menit setelah jam tutup- siswa tidak perlu bingung.
     */
    public function getPengumumanTersediaAttribute(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->waktu_pengumuman);
    }

    /**
     * Pemilihan ulang aktif:
     * Mulai  : tanggal_tutup jam 11:30 (= waktu pengumuman)
     * Berakhir: tanggal_pemilihan_ulang jam 23:59
     */
    public function getPemilihanUlangAktifAttribute(): bool
    {
        if (! $this->tanggal_pemilihan_ulang) {
            return false;
        }

        $sekarang = Carbon::now();

        return $sekarang->greaterThanOrEqualTo($this->waktu_pengumuman)
            && $sekarang->lessThanOrEqualTo($this->waktu_tutup_pemilihan_ulang);
    }

    /**
     * Label durasi pendaftaran untuk ditampilkan di form admin.
     * Contoh: "3 hari (01/07 - 03/07, tutup jam 11:00)"
     */
    public function getDurasiPendaftaranAttribute(): string
    {
        if (! $this->tanggal_buka || ! $this->tanggal_tutup) {
            return '0 hari';
        }

        $durasi = $this->tanggal_buka->diffInDays($this->tanggal_tutup) + 1;

        return "{$durasi} hari "
            . "({$this->tanggal_buka->format('d/m')} – {$this->tanggal_tutup->format('d/m')}, "
            . "tutup jam " . self::JAM_TUTUP_PENDAFTARAN . ")";
    }
}
