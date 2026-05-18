<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model SnapshotLaporan- snapshot info ekskul untuk header laporan final.
 * Dibuat bersamaan dengan PesertaEkskul saat proses finalisasi.
 *
 * Tujuan: header laporan PDF dan Excel tetap menampilkan data ekskul yang benar
 * meski nama ekskul, pembina, atau lokasi diubah oleh admin setelahnya.
 *
 * Unique (periode_id, ekskul_id): satu ekskul hanya punya satu snapshot per periode.
 */
class SnapshotLaporan extends Model
{
    protected $table      = 'snapshot_laporan';
    protected $primaryKey = 'snapshot_id';

    public $timestamps = false;

    protected $fillable = [
        'periode_id',
        'ekskul_id',
        'snapshot_nama_ekskul',
        'snapshot_nama_pembina',
        'snapshot_hari',
        'snapshot_lokasi',
        'locked_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Periode pendaftaran terkait */
    public function periode()
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'periode_id', 'periode_id');
    }

    /** Ekskul yang di-snapshot (untuk akses data live jika perlu) */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id', 'ekskul_id');
    }

    /**
     * Peserta ekskul dalam periode ini- dipakai saat generate laporan.
     * Diurutkan berdasarkan nama snapshot supaya absensi rapi secara alfabetis.
     */
    public function peserta()
    {
        return $this->hasMany(PesertaEkskul::class, 'ekskul_id', 'ekskul_id')
            ->where('periode_id', $this->periode_id)
            ->orderBy('snapshot_nama');
    }
}
