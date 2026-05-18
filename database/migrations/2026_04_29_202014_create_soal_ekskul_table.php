<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel soal_ekskul- pivot many-to-many antara soal dan ekskul.
 * Menentukan soal mana yang relevan untuk ekskul mana.
 * Contoh: soal "Saya suka menggambar" relevan untuk ekskul Art dan Monologue.
 * Jawaban siswa pada soal ini akan masuk ke perhitungan nilai Art dan Monologue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_ekskul', function (Blueprint $table) {
            $table->unsignedSmallInteger('soal_id');
            $table->unsignedBigInteger('ekskul_id');

            // PK composite: satu soal tidak boleh dikaitkan ke ekskul yang sama dua kali
            $table->primary(['soal_id', 'ekskul_id']);

            $table->foreign('soal_id')
                ->references('soal_id')->on('soal_rekomendasi')
                ->onDelete('cascade');

            $table->foreign('ekskul_id')
                ->references('ekskul_id')->on('ekskul')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_ekskul');
    }
};
