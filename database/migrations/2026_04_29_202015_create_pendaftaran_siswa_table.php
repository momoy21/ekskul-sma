<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pendaftaran_siswa- record utama pendaftaran per siswa per periode.
 * Satu siswa hanya boleh punya satu record pendaftaran per periode (unique constraint).
 * Pilihan ekskul-nya ada di tabel pilihan_ekskul (relasi 1-to-many).
 *
 * Alur status:
 * draft     → siswa mulai isi form tapi belum submit
 * submitted → siswa sudah submit + ttd orang tua, menunggu pengumuman
 * finalized → masa pemilihan ulang selesai, data sudah dikunci
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_siswa', function (Blueprint $table) {
            $table->id('pendaftaran_id');

            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('periode_id');

            // Data e-signature orang tua dalam format Base64 (dari canvas HTML)
            $table->text('tanda_tangan_ortu')->nullable();
            $table->timestamp('waktu_ttd')->nullable();

            $table->enum('status', ['draft', 'submitted', 'finalized'])->default('draft');

            $table->timestamps();

            // Satu siswa hanya boleh daftar satu kali per periode
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
        Schema::dropIfExists('pendaftaran_siswa');
    }
};
