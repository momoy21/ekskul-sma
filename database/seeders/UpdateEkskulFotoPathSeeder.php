<?php

namespace Database\Seeders;

use App\Models\Ekskul;
use Illuminate\Database\Seeder;

/**
 * Update foto_path untuk semua ekskul berdasarkan file yang ada di public/storage/ekskul/
 *
 * Matching:
 * - Art → Art_IMG.png
 * - Futsal → Futsal_IMG.png
 * - Mandarin → Mandarin_IMG.png
 * - Monologue → Monologue_IMG.png
 * - Badminton → Badminton_IMG.png
 * - Traditional Dance → Dance_IMG.jpeg
 * - Web Programming → WebPro_IMG.png
 * - Web Design → WebDesign_IMG.png
 * - BTQ → BTQ_IMG.png
 * - Karate → Karate_IMG.png
 * - Basketball → Basket_IMG.png
 * - English Debate → EnglishDebate_IMG.png
 * - Teather → Teater_IMG.png
 * - Taekwondo → Taekwondo_IMG.png
 */
class UpdateEkskulFotoPathSeeder extends Seeder
{
    public function run(): void
    {
        $fotoMapping = [
            'Art' => 'ekskul/Art_IMG.png',
            'Futsal' => 'ekskul/Futsal_IMG.png',
            'Mandarin' => 'ekskul/Mandarin_IMG.png',
            'Monologue' => 'ekskul/Monologue_IMG.png',
            'Badminton' => 'ekskul/Badminton_IMG.png',
            'Traditional Dance' => 'ekskul/Dance_IMG.jpeg',
            'Web Programming' => 'ekskul/WebPro_IMG.png',
            'Web Design' => 'ekskul/WebDesign_IMG.png',
            'BTQ' => 'ekskul/BTQ_IMG.png',
            'Karate' => 'ekskul/Karate_IMG.png',
            'Basketball' => 'ekskul/Basket_IMG.png',
            'English Debate' => 'ekskul/EnglishDebate_IMG.png',
            'Teather' => 'ekskul/Teater_IMG.png',
            'Taekwondo' => 'ekskul/Taekwondo_IMG.png',
        ];

        foreach ($fotoMapping as $ekskulName => $fotoPath) {
            $ekskul = Ekskul::where('nama_ekskul', $ekskulName)->first();

            if ($ekskul) {
                $ekskul->update(['foto_path' => $fotoPath]);
                $this->command->info("✓ Updated: {$ekskulName} → {$fotoPath}");
            } else {
                $this->command->warn("✗ Not found: {$ekskulName}");
            }
        }

        $this->command->info("\n✅ Semua foto_path berhasil di-update!");
    }
}
