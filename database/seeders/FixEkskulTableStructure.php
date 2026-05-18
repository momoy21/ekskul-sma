<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder untuk fix struktur tabel ekskul jika migration gagal.
 * Jalankan dengan: php artisan db:seed --class=FixEkskulTableStructure
 */
class FixEkskulTableStructure extends Seeder
{
    public function run(): void
    {
        // Cek apakah kolom sudah ada
        if (Schema::hasColumn('ekskul', 'fasilitas_level')) {
            echo "✓ Struktur tabel ekskul sudah benar.\n";
            return;
        }

        echo "Memperbaiki struktur tabel ekskul...\n";

        try {
            // Hapus deskripsi_fasilitas
            if (Schema::hasColumn('ekskul', 'deskripsi_fasilitas')) {
                DB::statement('ALTER TABLE `ekskul` DROP COLUMN `deskripsi_fasilitas`');
                echo "✓ Kolom deskripsi_fasilitas dihapus.\n";
            }

            // Ubah biaya_tambahan ke TINYINT
            if (Schema::hasColumn('ekskul', 'biaya_tambahan')) {
                DB::statement('ALTER TABLE `ekskul` MODIFY COLUMN `biaya_tambahan` TINYINT UNSIGNED DEFAULT 3');
                echo "✓ Kolom biaya_tambahan diubah ke TINYINT.\n";
            }

            // Tambah fasilitas_level
            if (!Schema::hasColumn('ekskul', 'fasilitas_level')) {
                DB::statement('ALTER TABLE `ekskul` ADD COLUMN `fasilitas_level` TINYINT UNSIGNED DEFAULT 3 AFTER `biaya_tambahan`');
                echo "✓ Kolom fasilitas_level ditambahkan.\n";
            }

            // Tambah intensitas_kegiatan
            if (!Schema::hasColumn('ekskul', 'intensitas_kegiatan')) {
                DB::statement('ALTER TABLE `ekskul` ADD COLUMN `intensitas_kegiatan` TINYINT UNSIGNED DEFAULT 3 AFTER `fasilitas_level`');
                echo "✓ Kolom intensitas_kegiatan ditambahkan.\n";
            }

            echo "\n✅ Struktur tabel ekskul berhasil diperbaiki!\n";
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
