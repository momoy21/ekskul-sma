<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pengguna- menyimpan akun login untuk admin dan siswa.
 * Username siswa = NISN mereka, password di-hash dengan bcrypt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id('pengguna_id');

            // Username unik: 'admin' untuk guru, NISN 10 digit untuk siswa
            $table->string('username', 20)->unique();

            // Bcrypt hash- password default siswa dari tanggal lahir (DDMMYYYY)
            $table->string('password', 255);

            $table->enum('role', ['admin', 'siswa']);

            // 1 = aktif, 0 = nonaktif (alumni otomatis di-set 0 saat tahun ajaran baru)
            $table->tinyInteger('is_active')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
