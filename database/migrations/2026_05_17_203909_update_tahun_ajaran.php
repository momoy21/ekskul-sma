<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_ajaran', function (Blueprint $table) {
            // Hapus unique lama
            $table->dropUnique(['tahun_mulai', 'tahun_selesai']);

            // Hapus kolom semester_aktif- semester sekarang bagian dari identitas
            $table->dropColumn('semester_aktif');

            // Tambah kolom semester sebagai identitas
            $table->enum('semester', ['ganjil', 'genap'])->after('tahun_selesai');

            // Unique baru: satu kombinasi tahun+semester hanya boleh ada satu
            $table->unique(['tahun_mulai', 'tahun_selesai', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::table('tahun_ajaran', function (Blueprint $table) {
            $table->dropUnique(['tahun_mulai', 'tahun_selesai', 'semester']);
            $table->dropColumn('semester');
            $table->enum('semester_aktif', ['ganjil', 'genap'])->after('tahun_selesai');
            $table->unique(['tahun_mulai', 'tahun_selesai']);
        });
    }
};
