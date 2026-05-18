<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model JawabanTes- jawaban siswa untuk tiap soal dalam satu sesi tes.
 * PK composite (tes_id, soal_id)- tidak ada auto-increment id sendiri.
 * nilai_jawaban = skala Likert 1-5.
 */
class JawabanTes extends Model
{
    protected $table = 'jawaban_tes';

    // PK composite, bukan single column
    public $incrementing = false;
    public $timestamps   = false;

    protected $primaryKey = null; // tidak ada single PK

    protected $fillable = [
        'tes_id',
        'soal_id',
        'nilai_jawaban',
    ];

    protected $casts = [
        'tes_id'        => 'integer',
        'soal_id'       => 'integer',
        'nilai_jawaban' => 'integer',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Sesi tes yang memiliki jawaban ini */
    public function tes()
    {
        return $this->belongsTo(TesRekomendasi::class, 'tes_id', 'tes_id');
    }

    /** Soal yang dijawab */
    public function soal()
    {
        return $this->belongsTo(SoalRekomendasi::class, 'soal_id', 'soal_id');
    }
}
