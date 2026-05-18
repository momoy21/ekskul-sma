<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration untuk revisi SAW:
 * - Ubah biaya_tambahan dari ENUM ke TINYINT (skala 1-5)
 * - Hapus deskripsi_fasilitas (diganti dengan kolom numerik)
 * - Tambah fasilitas_level (TINYINT 1-5)
 * - Tambah intensitas_kegiatan (TINYINT 1-5)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Hanya jalankan jika belum ada kolom baru (idempotent)
        if (!Schema::hasColumn('ekskul', 'fasilitas_level')) {
            Schema::table('ekskul', function (Blueprint $table) {
                // Hapus deskripsi_fasilitas jika ada
                if (Schema::hasColumn('ekskul', 'deskripsi_fasilitas')) {
                    $table->dropColumn('deskripsi_fasilitas');
                }
            });

            // Ubah biaya_tambahan dari ENUM ke TINYINT menggunakan raw SQL (ENUM tidak bisa .change())
            DB::statement('ALTER TABLE `ekskul` CHANGE `biaya_tambahan` `biaya_tambahan` TINYINT UNSIGNED DEFAULT 3');

            Schema::table('ekskul', function (Blueprint $table) {
                // Tambah kolom baru
                $table->unsignedTinyInteger('fasilitas_level')->default(3)->after('biaya_tambahan');
                $table->unsignedTinyInteger('intensitas_kegiatan')->default(3)->after('fasilitas_level');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ekskul', function (Blueprint $table) {
            // Hapus kolom baru
            if (Schema::hasColumn('ekskul', 'fasilitas_level')) {
                $table->dropColumn('fasilitas_level');
            }
            if (Schema::hasColumn('ekskul', 'intensitas_kegiatan')) {
                $table->dropColumn('intensitas_kegiatan');
            }

            // Hapus index
            if (Schema::hasIndex('ekskul', 'ekskul_is_active_index')) {
                $table->dropIndex('ekskul_is_active_index');
            }
            if (Schema::hasIndex('ekskul', 'ekskul_kategori_ekskul_id_index')) {
                $table->dropIndex('ekskul_kategori_ekskul_id_index');
            }
        });

        // Ubah kembali biaya_tambahan ke ENUM
        DB::statement("ALTER TABLE ekskul MODIFY COLUMN biaya_tambahan ENUM('none', 'terjangkau', 'cukup_besar') DEFAULT 'none'");

        Schema::table('ekskul', function (Blueprint $table) {
            // Kembalikan deskripsi_fasilitas
            if (!Schema::hasColumn('ekskul', 'deskripsi_fasilitas')) {
                $table->text('deskripsi_fasilitas')->nullable()->after('biaya_tambahan');
            }
        });
    }
};

