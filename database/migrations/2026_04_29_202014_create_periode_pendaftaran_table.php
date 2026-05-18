<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel periode_pendaftaran- timeline pendaftaran per semester.
 * Satu tahun ajaran punya 2 periode (ganjil + genap), kombinasinya unik.
 *
 * Alur waktu:
 * tanggal_buka → pendaftaran dibuka untuk siswa
 * tanggal_tutup → pendaftaran ditutup, sistem hitung zona (hijau/kuning/merah)
 * tanggal_pemilihan_ulang → batas akhir siswa ganti/hapus pilihan zona merah/kuning
 *
 * Pengumuman tampil bersamaan dengan tanggal_tutup.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_pendaftaran', function (Blueprint $table) {
            $table->id('periode_id');

            $table->unsignedBigInteger('tahun_ajaran_id');

            $table->enum('semester', ['ganjil', 'genap']);

            $table->date('tanggal_buka');
            $table->date('tanggal_tutup');

            // Nullable karena admin mungkin set timeline bertahap
            $table->date('tanggal_pemilihan_ulang')->nullable();

            $table->timestamps();

            // Satu tahun ajaran hanya boleh punya satu periode per semester
            $table->unique(['tahun_ajaran_id', 'semester']);

            $table->foreign('tahun_ajaran_id')
                ->references('tahun_ajaran_id')->on('tahun_ajaran')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_pendaftaran');
    }
};
