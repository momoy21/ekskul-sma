<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel kategori_ekskul- pengelompokan ekskul seperti Seni, Olahraga, Teknologi.
 * Digunakan sebagai filter dan label di katalog informasi ekskul siswa.
 * Nama kategori bersifat unik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_ekskul', function (Blueprint $table) {
            $table->id('kategori_ekskul_id');

            // Unik: tidak boleh ada dua kategori dengan nama sama
            $table->string('nama_kategori', 80)->unique();

            $table->tinyInteger('is_active')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_ekskul');
    }
};
