<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model SoalRekomendasi- bank soal untuk tes rekomendasi ekskul.
 * Setiap soal terkait ke satu kriteria dan ke banyak ekskul (via pivot soal_ekskul).
 * Soal dinonaktifkan- tidak tampil ke siswa, tapi data jawaban historis tetap ada.
 */
class SoalRekomendasi extends Model
{
    protected $table      = 'soal_rekomendasi';
    protected $primaryKey = 'soal_id';

    // Primary key smallint, bukan bigint default
    protected $keyType    = 'int';
    public    $incrementing = true;

    protected $fillable = [
        'kode_soal',
        'kriteria_id',
        'teks_soal',
        'urutan_tampil',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'urutan_tampil' => 'integer',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Kriteria yang dinilai oleh soal ini (C1-C5) */
    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id', 'kriteria_id');
    }

    /**
     * Ekskul yang relevan dengan soal ini.
     * Many-to-many via tabel pivot soal_ekskul.
     */
    public function ekskul()
    {
        return $this->belongsToMany(
            Ekskul::class,
            'soal_ekskul',
            'soal_id',
            'ekskul_id'
        );
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    /** Hanya soal aktif dan urutkan sesuai urutan_tampil */
    public function scopeAktif($query)
    {
        return $query->where('is_active', 1)->orderBy('urutan_tampil');
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    /**
     * Generate kode soal berikutnya secara otomatis: Q1, Q2, Q3, dst.
     * Dipanggil sebelum create soal baru.
     */
    public static function generateKodeSoal(): string
    {
        $last = static::orderByDesc('soal_id')->value('kode_soal');

        if (! $last) {
            return 'Q1';
        }

        // Ambil angka di belakang huruf "Q" lalu tambah 1
        $number = (int) ltrim($last, 'Q');

        return 'Q' . ($number + 1);
    }
}
