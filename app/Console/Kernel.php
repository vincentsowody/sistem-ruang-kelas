<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Bersihkan notifikasi lama setiap hari jam 02.00 pagi
        $schedule->command('notifikasi:bersihkan --hari=30')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Restart queue worker setiap jam untuk mencegah memory leak
        $schedule->command('queue:restart')
                 ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
