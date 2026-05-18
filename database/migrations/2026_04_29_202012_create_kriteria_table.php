<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel kriteria- 5 kriteria tetap untuk perhitungan SAW.
 * C1-C5 di-seed saat setup awal dan tidak bisa ditambah/dihapus dari UI.
 * Yang bisa diedit: nama_kriteria, deskripsi_siswa, dan status aktif.
 * tipe_atribut (benefit/cost) TIDAK bisa diubah karena mempengaruhi logika normalisasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria', function (Blueprint $table) {
            // smallint cukup karena hanya 5 baris (C1-C5)
            $table->tinyIncrements('kriteria_id');

            // C1, C2, C3, C4, C5- unik dan tetap
            $table->char('kode', 2)->unique();

            $table->string('nama_kriteria', 80);

            // benefit = semakin tinggi semakin baik
            // cost    = semakin rendah semakin baik (ex: biaya tambahan)
            $table->enum('tipe_atribut', ['benefit', 'cost']);

            // Kalimat penjelasan yang muncul kepada siswa saat mengisi bobot
            $table->text('deskripsi_siswa')->nullable();

            // Urutan tampil di form tes rekomendasi
            $table->tinyInteger('urutan_tampil')->unsigned();

            // Kriteria nonaktif tidak ikut perhitungan SAW dan tidak tampil di tes
            $table->tinyInteger('is_active')->default(1);

            // Kriteria tidak punya created_at karena di-seed satu kali
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria');
    }
};
