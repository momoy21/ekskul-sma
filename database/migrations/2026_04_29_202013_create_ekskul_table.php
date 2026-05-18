<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel ekskul- data lengkap setiap ekstrakurikuler.
 * Ekskul nonaktif tidak tampil di katalog siswa maupun dropdown pendaftaran.
 * Perubahan data (nama pembina, deskripsi) langsung ter-update real-time di sisi siswa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul', function (Blueprint $table) {
            $table->id('ekskul_id');
            $table->string('nama_ekskul', 100);

            $table->unsignedBigInteger('kategori_ekskul_id');

            // Rabu = Pramuka (wajib, tidak dikelola di sini), jadi hanya 4 hari
            $table->enum('hari_pelaksanaan', ['Senin', 'Selasa', 'Kamis', 'Jumat']);

            $table->string('lokasi', 100);

            // none = tidak ada biaya, terjangkau = < 100rb, cukup_besar = > 100rb
            $table->enum('biaya_tambahan', ['none', 'terjangkau', 'cukup_besar']);

            // Deskripsi fasilitas: apa yang disediakan sekolah dan apa yang dibawa sendiri
            $table->text('deskripsi_fasilitas')->nullable();

            $table->text('deskripsi_kegiatan')->nullable();

            // Path relatif dari storage/app/public/ (disimpan setelah upload)
            $table->string('foto_path', 255)->nullable();

            // Ekskul hanya dibuka jika pendaftar >= kuota_minimal (default 10)
            $table->tinyInteger('kuota_minimal')->unsigned()->default(10);

            // 0 = hilang dari katalog siswa dan dropdown pendaftaran
            $table->tinyInteger('is_active')->default(1);

            $table->timestamps();

            $table->foreign('kategori_ekskul_id')
                ->references('kategori_ekskul_id')->on('kategori_ekskul');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul');
    }
};
