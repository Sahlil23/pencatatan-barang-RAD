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


        $schedule->command('stock:monthly-closing')
                 ->monthlyOn(1, '00:30')
                 ->withoutOverlapping()
                 ->onOneServer()
                 ->emailOutputOnFailure('admin@chickingbjm.com')
                 ->appendOutputTo(storage_path('logs/monthly-closing.log'));

        // TAMBAH: Backup harian
        $schedule->command('backup:run')
                 ->daily()
                 ->at('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected $commands = [
            Commands\CheckLowStockCommand::class,
            Commands\MonthlyStockClosing::class,
    ];

}