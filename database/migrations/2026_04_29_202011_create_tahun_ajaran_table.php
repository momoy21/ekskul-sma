<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel tahun_ajaran- pengarsipan data per tahun ajaran.
 * Hanya SATU record yang boleh is_active = 1 pada satu waktu.
 * Saat tahun ajaran baru dibuat, yang lama otomatis di-nonaktifkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id('tahun_ajaran_id');

            // Contoh: tahun_mulai=2025, tahun_selesai=2026 → ditampilkan "2025/2026"
            $table->year('tahun_mulai');
            $table->year('tahun_selesai');

            // Semester yang sedang berjalan saat ini
            $table->enum('semester_aktif', ['ganjil', 'genap']);

            // Hanya satu yang boleh aktif- diatur via aplikasi bukan constraint DB
            $table->tinyInteger('is_active')->default(1);

            // Tidak ada updated_at karena tahun ajaran tidak diedit (kecuali semester_aktif)
            $table->timestamp('created_at')->nullable();

            // Kombinasi tahun_mulai + tahun_selesai harus unik
            $table->unique(['tahun_mulai', 'tahun_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_ajaran');
    }
};
