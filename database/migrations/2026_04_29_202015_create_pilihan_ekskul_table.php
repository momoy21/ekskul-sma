<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pilihan_ekskul- detail pilihan ekskul per pendaftaran.
 * Satu pendaftaran bisa punya 1-4 pilihan ekskul (urutan_pilihan 1-4).
 * Setiap pilihan punya status zona sendiri setelah pendaftaran ditutup.
 *
 * Alur kolom:
 * ekskul_id           → pilihan utama siswa
 * status_zona         → diisi sistem setelah tanggal_tutup (hijau/kuning/merah)
 * ekskul_cadangan_id  → diisi siswa saat pengumuman jika zona = kuning
 * ekskul_final_id     → diisi sistem saat finalisasi (bisa = utama atau cadangan)
 * is_deleted          → soft delete: saat siswa hapus pilihan zona merah di pengumuman
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilihan_ekskul', function (Blueprint $table) {
            $table->id('pilihan_id');

            $table->unsignedBigInteger('pendaftaran_id');

            // Pilihan utama siswa
            $table->unsignedBigInteger('ekskul_id');

            // Urutan: 1 = pilihan pertama, 2 = kedua, dst (max 4)
            $table->tinyInteger('urutan_pilihan')->unsigned();

            // Diisi sistem setelah masa pendaftaran tutup
            $table->enum('status_zona', ['hijau', 'kuning', 'merah'])->nullable();

            // Diisi siswa saat fase pengumuman jika zona kuning
            $table->unsignedBigInteger('ekskul_cadangan_id')->nullable();

            // Hasil akhir setelah finalisasi- bisa sama dengan utama atau cadangan
            $table->unsignedBigInteger('ekskul_final_id')->nullable();

            // Soft delete: 1 = pilihan ini dihapus saat pemilihan ulang (zona merah)
            $table->tinyInteger('is_deleted')->default(0);

            $table->timestamps();

            $table->foreign('pendaftaran_id')
                ->references('pendaftaran_id')->on('pendaftaran_siswa')
                ->onDelete('cascade');

            $table->foreign('ekskul_id')
                ->references('ekskul_id')->on('ekskul');

            $table->foreign('ekskul_cadangan_id')
                ->references('ekskul_id')->on('ekskul');

            $table->foreign('ekskul_final_id')
                ->references('ekskul_id')->on('ekskul');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilihan_ekskul');
    }
};
