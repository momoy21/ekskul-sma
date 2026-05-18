<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Hitung zona setiap jam agar bisa deteksi jam tutup 11:00 hari yang sama
        $schedule->command('ekskul:hitung-zona')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/zona.log'));

        $schedule->command('ekskul:finalisasi')
            ->dailyAt('00:10')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/finalisasi.log'));

        $schedule->command('ekskul:kunci-data-lama')
            ->dailyAt('00:15')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/kunci.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
