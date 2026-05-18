<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel peserta_ekskul- daftar final siswa yang terdaftar di tiap ekskul.
 * Dibuat saat finalisasi setelah masa pemilihan ulang selesai.
 *
 * Kolom snapshot_* menyimpan data siswa pada saat data dikunci (is_locked = 1).
 * Ini memastikan laporan absensi tidak berubah meski data siswa diedit di kemudian hari.
 * Contoh: jika siswa pindah kelas, snapshot_label_kelas tetap mencatat kelas lamanya.
 *
 * Constraint UNIQUE (siswa_id, ekskul_id, periode_id):
 * Satu siswa tidak boleh terdaftar di ekskul yang sama dua kali dalam satu periode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peserta_ekskul', function (Blueprint $table) {
            $table->id('peserta_id');

            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('ekskul_id');
            $table->unsignedBigInteger('periode_id');

            // Snapshot data siswa saat dikunci- tidak berubah meski data master diupdate
            $table->string('snapshot_nama', 100);
            $table->char('snapshot_nisn', 10);
            $table->enum('snapshot_jenis_kelamin', ['L', 'P']);
            $table->string('snapshot_label_kelas', 30); // contoh: "10A", "11 IPA 1"

            // 1 = terkunci permanen, data tidak bisa diubah (dipicu saat tahun ajaran baru)
            $table->tinyInteger('is_locked')->default(0);

            // Waktu siswa resmi masuk sebagai peserta ekskul
            $table->timestamp('enrolled_at')->nullable();

            // Satu siswa tidak bisa terdaftar di ekskul yang sama dua kali dalam satu periode
            $table->unique(['siswa_id', 'ekskul_id', 'periode_id']);

            $table->foreign('siswa_id')
                ->references('siswa_id')->on('siswa');

            $table->foreign('ekskul_id')
                ->references('ekskul_id')->on('ekskul');

            $table->foreign('periode_id')
                ->references('periode_id')->on('periode_pendaftaran')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_ekskul');
    }
};
