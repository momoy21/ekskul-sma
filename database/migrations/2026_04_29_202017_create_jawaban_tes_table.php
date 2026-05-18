<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel jawaban_tes- jawaban siswa untuk setiap soal dalam satu sesi tes.
 * PK composite (tes_id, soal_id)- satu soal hanya dijawab sekali per tes.
 * nilai_jawaban = skala Likert 1-5 (1=sangat tidak setuju, 5=sangat setuju).
 * Tabel ini yang menjadi input utama perhitungan SAW di SawService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_tes', function (Blueprint $table) {
            $table->unsignedBigInteger('tes_id');
            $table->unsignedSmallInteger('soal_id');

            // Skala 1-5 dari soal Likert di Tahap 2 tes rekomendasi
            $table->tinyInteger('nilai_jawaban')->unsigned();

            // PK composite: satu soal hanya ada satu jawaban per sesi tes
            $table->primary(['tes_id', 'soal_id']);

            $table->foreign('tes_id')
                ->references('tes_id')->on('tes_rekomendasi')
                ->onDelete('cascade');

            $table->foreign('soal_id')
                ->references('soal_id')->on('soal_rekomendasi')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_tes');
    }
};
