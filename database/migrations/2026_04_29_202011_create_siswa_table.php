<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel siswa- data profil lengkap siswa.
 * Relasi 1-to-1 dengan tabel pengguna (satu akun = satu profil siswa).
 * NISN sama dengan username di tabel pengguna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id('siswa_id');

            // FK ke pengguna- UNIQUE karena relasi 1-to-1
            $table->unsignedBigInteger('pengguna_id')->unique();

            // NISN 10 digit, unik, sama dengan username di tabel pengguna
            $table->char('nisn', 10)->unique();

            $table->string('nama_lengkap', 100);

            // Disimpan YYYY-MM-DD, tapi diformat DDMMYYYY sebagai password default
            $table->date('tanggal_lahir');

            $table->enum('jenis_kelamin', ['L', 'P']);

            // FK ke kelas aktif siswa saat ini
            $table->unsignedBigInteger('kelas_id');

            // Alumni = tidak bisa login, akun pengguna ikut di-nonaktifkan
            $table->enum('status', ['aktif', 'alumni'])->default('aktif');

            $table->timestamps();

            $table->foreign('pengguna_id')
                ->references('pengguna_id')->on('pengguna')
                ->onDelete('cascade'); // hapus pengguna → hapus data siswa

            $table->foreign('kelas_id')
                ->references('kelas_id')->on('kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
