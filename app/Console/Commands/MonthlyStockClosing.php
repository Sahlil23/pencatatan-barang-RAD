<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonthlyStockBalance;
use Carbon\Carbon;

class MonthlyStockClosing extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stock:monthly-closing {--date=} {--force}';

    /**
     * The console command description.
     */
    protected $description = 'Process monthly stock closing and create new month balances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Monthly Stock Closing Process...');

        // Parse target date
        $targetDate = $this->option('date') ? 
            Carbon::parse($this->option('date')) : 
            now();

        $this->info("Target Date: {$targetDate->toDateString()}");

        // Konfirmasi jika bukan tanggal 1
        if ($targetDate->day !== 1 && !$this->option('force')) {
            if (!$this->confirm("Target date is not the 1st of the month. Continue anyway?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Proses monthly closing
        $result = MonthlyStockBalance::processMonthlyClosing($targetDate);

        if ($result['success']) {
            $this->info('✅ Monthly closing completed successfully!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Period', $result['period']],
                    ['Closed Balances', $result['closed_balances']],
                    ['New Balances Created', $result['new_balances']],
                ]
            );
            $this->info($result['message']);
        } else {
            $this->error('❌ Monthly closing failed!');
            $this->error('Error: ' . $result['error']);
            return 1;
        }

        return 0;
    }
}