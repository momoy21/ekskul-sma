<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel kelas- master data kelas yang digunakan di seluruh sistem.
 * Tingkat hanya boleh 10, 11, atau 12 (divalidasi di level aplikasi).
 * Nonaktif = tidak muncul di dropdown pemilihan kelas siswa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id('kelas_id');

            // Tingkat kelas: hanya 10, 11, 12- divalidasi di Controller
            $table->tinyInteger('tingkat')->unsigned();

            // Nama bebas: A, B, IPA 1, IPS 2, dst
            $table->string('nama_kelas', 20);

            // 0 = tidak muncul di dropdown saat memilih kelas siswa
            $table->tinyInteger('is_active')->default(1);

            $table->timestamps();

            // Kombinasi tingkat + nama_kelas harus unik (misal: tidak boleh ada dua "10 A")
            $table->unique(['tingkat', 'nama_kelas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
