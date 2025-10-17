<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Auto backup setiap hari jam 2 pagi
        $schedule->call(function () {
            app(\App\Http\Controllers\BackupController::class)->schedule();
        })->daily()->at('02:00');
        
        // Backup mingguan dengan full backup
        $schedule->call(function () {
            $controller = app(\App\Http\Controllers\BackupController::class);
            $filename = 'weekly_backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $controller->createBackup($filename, 'full');
        })->weekly()->sundays()->at('03:00');

                // Check low stock every hour and send notification
        $schedule->command('stock:check-low --notify')
                 >dailyAt('09:00')
                 ->when(function () {
                     // Only run if notifications are enabled
                     return config('services.fonnte.low_stock_enabled', true);
                 })
                 ->runInBackground();

            // Alternative: Check daily at 9 AM
            // $schedule->command('stock:check-low --notify')
            //          ->dailyAt('09:00')
            //          ->when(function () {
            //              return config('services.fonnte.low_stock_enabled', true);
            //          });
        }

        /**
         * Register the commands for the application.
         */
        protected $commands = [
            Commands\CheckLowStockCommand::class,
    ];

}