<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel snapshot_laporan- snapshot info ekskul untuk keperluan laporan final.
 * Dibuat bersamaan dengan peserta_ekskul saat finalisasi.
 *
 * Tujuan: laporan PDF/Excel tetap akurat meski data ekskul diubah setelahnya.
 * Contoh: jika pembina ekskul diganti, laporan lama tetap menampilkan pembina lama.
 *
 * Constraint UNIQUE (periode_id, ekskul_id):
 * Satu ekskul hanya punya satu snapshot per periode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshot_laporan', function (Blueprint $table) {
            $table->id('snapshot_id');

            $table->unsignedBigInteger('periode_id');
            $table->unsignedBigInteger('ekskul_id');

            // Data ekskul saat dikunci- tidak berubah meski master data diedit
            $table->string('snapshot_nama_ekskul', 100);

            // Nama pembina digabung jika lebih dari satu: "Ms Nurul & Ms Esty"
            $table->string('snapshot_nama_pembina', 255);

            $table->string('snapshot_hari', 10);     // "Senin", "Selasa", dst
            $table->string('snapshot_lokasi', 100);

            // Waktu snapshot ini dibuat (= waktu finalisasi)
            $table->timestamp('locked_at')->nullable();

            // Satu ekskul hanya punya satu snapshot per periode
            $table->unique(['periode_id', 'ekskul_id']);

            $table->foreign('periode_id')
                ->references('periode_id')->on('periode_pendaftaran')
                ->onDelete('cascade');

            $table->foreign('ekskul_id')
                ->references('ekskul_id')->on('ekskul');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshot_laporan');
    }
};
