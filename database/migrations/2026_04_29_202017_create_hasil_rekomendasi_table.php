<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel hasil_rekomendasi- 3 teratas hasil perhitungan SAW per tes.
 * Disimpan setelah siswa submit tes agar tidak dihitung ulang setiap kali dibuka.
 * Muncul sebagai "smart suggestion" saat siswa buka halaman pendaftaran.
 *
 * Constraint UNIQUE:
 * (tes_id, peringkat)  → tidak boleh ada dua ekskul di peringkat yang sama
 * (tes_id, ekskul_id)  → satu ekskul tidak boleh muncul dua kali di hasil yang sama
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_rekomendasi', function (Blueprint $table) {
            $table->id('hasil_id');

            $table->unsignedBigInteger('tes_id');
            $table->unsignedBigInteger('ekskul_id');

            // Hanya peringkat 1, 2, atau 3 yang disimpan
            $table->tinyInteger('peringkat')->unsigned();

            // Skor SAW hasil normalisasi, range 0.0000–1.0000
            $table->decimal('skor_saw', 5, 4);

            $table->timestamp('created_at')->nullable();

            // Satu tes tidak boleh punya dua ekskul di peringkat yang sama
            $table->unique(['tes_id', 'peringkat']);

            // Satu ekskul tidak boleh muncul dua kali dalam hasil tes yang sama
            $table->unique(['tes_id', 'ekskul_id']);

            $table->foreign('tes_id')
                ->references('tes_id')->on('tes_rekomendasi')
                ->onDelete('cascade');

            $table->foreign('ekskul_id')
                ->references('ekskul_id')->on('ekskul');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_rekomendasi');
    }
};
