<?php

namespace Database\Seeders;

use App\Models\Ekskul;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk membersihkan ekskul duplikat dari database.
 * Jalankan dengan: php artisan db:seed --class=CleanupDuplicateEkskul
 */
class CleanupDuplicateEkskul extends Seeder
{
    public function run(): void
    {
        echo "\n=== Cleanup Duplicate Ekskul ===\n";

        // Group by nama_ekskul dan ambil yang duplikat
        $duplicates = Ekskul::selectRaw('nama_ekskul, COUNT(*) as count, GROUP_CONCAT(ekskul_id) as ids')
            ->groupBy('nama_ekskul')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            echo "✅ Tidak ada duplikat ekskul.\n";
            return;
        }

        $totalDeleted = 0;

        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup->ids);
            $keepId = $ids[0]; // Keep first, delete the rest

            echo "\n🔍 Ditemukan {$dup->count} duplikat untuk '{$dup->nama_ekskul}':\n";
            echo "   IDs: " . implode(', ', $ids) . "\n";
            echo "   Keep ID: {$keepId}\n";

            // Delete duplicates (keep the first one)
            $toDelete = array_slice($ids, 1);
            foreach ($toDelete as $id) {
                echo "   Deleting ID: {$id}...\n";
                Ekskul::find($id)?->delete();
                $totalDeleted++;
            }
        }

        echo "\n✅ Selesai! Dihapus {$totalDeleted} ekskul duplikat.\n\n";
    }
}
