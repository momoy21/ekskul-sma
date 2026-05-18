<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Ekskul- data lengkap setiap kegiatan ekstrakurikuler.
 * Perubahan data di sini langsung terlihat real-time di katalog siswa.
 * Ekskul nonaktif menghilang dari katalog dan dropdown pendaftaran.
 */
class Ekskul extends Model
{
    protected $table      = 'ekskul';
    protected $primaryKey = 'ekskul_id';

    protected $fillable = [
        'nama_ekskul',
        'kategori_ekskul_id',
        'hari_pelaksanaan',
        'lokasi',
        'biaya_tambahan',
        'fasilitas_level',
        'intensitas_kegiatan',
        'deskripsi_kegiatan',
        'foto_path',
        'kuota_minimal',
        'is_active',
    ];

    protected $casts = [
        'kuota_minimal' => 'integer',
        'is_active'     => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────

    /** Kategori ekskul ini (Seni, Olahraga, dst) */
    public function kategori()
    {
        return $this->belongsTo(KategoriEkskul::class, 'kategori_ekskul_id', 'kategori_ekskul_id');
    }

    /**
     * Pembina ekskul ini- many-to-many via tabel pivot ekskul_pembina.
     * Satu ekskul bisa punya lebih dari satu pembina (misal Karate: Ms Nurul & Ms Esty).
     */
    public function pembina()
    {
        return $this->belongsToMany(
            Pembina::class,
            'ekskul_pembina',
            'ekskul_id',
            'pembina_id'
        );
    }

    /**
     * Soal rekomendasi yang relevan untuk ekskul ini.
     * Many-to-many via tabel pivot soal_ekskul.
     */
    public function soal()
    {
        return $this->belongsToMany(
            SoalRekomendasi::class,
            'soal_ekskul',
            'ekskul_id',
            'soal_id'
        );
    }

    /** Semua peserta ekskul ini lintas periode */
    public function pesertaEkskul()
    {
        return $this->hasMany(PesertaEkskul::class, 'ekskul_id', 'ekskul_id');
    }

    /** Semua hasil rekomendasi yang pernah merekomendasikan ekskul ini */
    public function hasilRekomendasi()
    {
        return $this->hasMany(HasilRekomendasi::class, 'ekskul_id', 'ekskul_id');
    }

    /** Snapshot laporan per periode */
    public function snapshotLaporan()
    {
        return $this->hasMany(SnapshotLaporan::class, 'ekskul_id', 'ekskul_id');
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Nama semua pembina digabung dengan "&".
     * Contoh: "Ms Nurul & Ms Esty"
     * Catatan: pastikan relasi pembina sudah di-eager load sebelum memanggil ini.
     */
    public function getNamaPembinaAttribute(): string
    {
        return $this->pembina->pluck('nama_lengkap')->join(' & ') ?: '-';
    }

    /**
     * Label biaya yang ramah dibaca untuk tampilan ke siswa.
     * Mapping: 1=Tidak Ada, 2=Sedikit, 3=Terjangkau, 4=Sedikit Mahal, 5=Mahal
     */
    public function getLabelBiayaAttribute(): string
    {
        return match ($this->biaya_tambahan) {
            1 => 'Tidak Ada Biaya (Rp 0)',
            2 => 'Sedikit Biaya (Rp 1.000 - Rp 100.000)',
            3 => 'Terjangkau (Rp 101.000 - Rp 200.000)',
            4 => 'Sedikit Mahal (Rp 201.000 - Rp 300.000)',
            5 => 'Mahal (Rp 301.000+)',
            default => '-',
        };
    }

    /**
     * Label fasilitas yang ramah dibaca untuk tampilan ke siswa.
     * Mapping: 1=Dibawa Sendiri, 2=Beberapa Sekolah, 3=Sebagian, 4=Beberapa Sendiri, 5=Disediakan Sekolah
     */
    public function getLabelFasilitasAttribute(): string
    {
        return match ($this->fasilitas_level) {
            1 => 'Seluruhnya dibawa sendiri',
            2 => 'Beberapa dari sekolah, lebih banyak dibawa sendiri',
            3 => 'Sebagian disediakan sekolah, sebagian sendiri',
            4 => 'Beberapa dari sekolah, lebih banyak disediakan sekolah',
            5 => 'Dari semua disediakan sekolah',
            default => '-',
        };
    }

    /**
     * Label intensitas kegiatan yang ramah dibaca untuk tampilan ke siswa.
     * Mapping: 1=Sangat Tinggi, 2=Tinggi, 3=Sedang, 4=Rendah, 5=Sangat Rendah
     */
    public function getLabelIntensitasAttribute(): string
    {
        return match ($this->intensitas_kegiatan) {
            1 => 'Intensitas Sangat Tinggi',
            2 => 'Intensitas Tinggi',
            3 => 'Intensitas Sedang',
            4 => 'Intensitas Rendah',
            5 => 'Intensitas Sangat Rendah',
            default => '-',
        };
    }

    /**
     * URL foto untuk ditampilkan di kartu ekskul.
     * Fallback ke placeholder jika belum ada foto.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_path && file_exists(public_path('storage/' . $this->foto_path))) {
            return asset('storage/' . $this->foto_path);
        }
        // Fallback ke warna solid placeholder jika tidak ada foto
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="240"%3E%3Crect fill="%23e5e7eb" width="400" height="240"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%236b7280" font-size="18" font-family="system-ui"%3EFoto Ekskul%3C/text%3E%3C/svg%3E';
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    /** Hanya ekskul aktif- untuk katalog siswa dan dropdown pendaftaran */
    public function scopeAktif($query)
    {
        return $query->where('is_active', 1);
    }

    /** Urutkan sesuai hari (Senin → Selasa → Kamis → Jumat) */
    public function scopeUrutHari($query)
    {
        return $query->orderByRaw("FIELD(hari_pelaksanaan, 'Senin', 'Selasa', 'Kamis', 'Jumat')");
    }
}
