<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel tes_rekomendasi- record tes yang dilakukan siswa per periode.
 * Satu siswa hanya boleh punya satu tes per periode (unique constraint).
 * Bobot C1-C5 diisi siswa di Tahap 1 (nilai 1-5), mewakili seberapa penting tiap kriteria.
 * Jawaban soalnya tersimpan di tabel jawaban_tes.
 * Hasil SAW-nya tersimpan di tabel hasil_rekomendasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tes_rekomendasi', function (Blueprint $table) {
            $table->id('tes_id');

            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('periode_id');

            // Bobot tiap kriteria dari input siswa (1 = tidak penting, 5 = sangat penting)
            // Nullable karena mungkin siswa simpan bobot dulu sebelum isi soal
            $table->tinyInteger('bobot_c1')->unsigned()->nullable();
            $table->tinyInteger('bobot_c2')->unsigned()->nullable();
            $table->tinyInteger('bobot_c3')->unsigned()->nullable();
            $table->tinyInteger('bobot_c4')->unsigned()->nullable();
            $table->tinyInteger('bobot_c5')->unsigned()->nullable();

            // Diisi saat siswa klik submit di akhir Tahap 2
            $table->timestamp('submitted_at')->nullable();

            // Hanya created_at- tes tidak di-update, kalau ulang tes ya hapus dan buat baru
            $table->timestamp('created_at')->nullable();

            // Satu siswa hanya boleh satu tes aktif per periode
            $table->unique(['siswa_id', 'periode_id']);

            $table->foreign('siswa_id')
                ->references('siswa_id')->on('siswa')
                ->onDelete('cascade');

            $table->foreign('periode_id')
                ->references('periode_id')->on('periode_pendaftaran')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tes_rekomendasi');
    }
};
