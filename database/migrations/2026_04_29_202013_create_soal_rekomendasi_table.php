<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel soal_rekomendasi- bank soal untuk tes rekomendasi ekskul.
 * Setiap soal terhubung ke satu kriteria (C1-C5) dan ke banyak ekskul via pivot soal_ekskul.
 * Soal nonaktif tidak tampil ke siswa pada tes periode berikutnya, tapi data historis tetap ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_rekomendasi', function (Blueprint $table) {
            // smallint cukup karena soal tidak akan sampai ribuan
            $table->smallIncrements('soal_id');

            // Kode unik: Q1, Q2, Q3, dst- di-generate otomatis saat tambah soal
            $table->string('kode_soal', 5)->unique();

            // FK ke kriteria (C1-C5)
            $table->tinyInteger('kriteria_id')->unsigned();

            $table->text('teks_soal');

            // Urutan tampil soal dalam halaman tes rekomendasi
            $table->smallInteger('urutan_tampil')->unsigned();

            // Soal nonaktif tidak ikut tampil ke siswa, tapi jawaban lama tetap tersimpan
            $table->tinyInteger('is_active')->default(1);

            $table->timestamps();

            $table->foreign('kriteria_id')
                ->references('kriteria_id')->on('kriteria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_rekomendasi');
    }
};
