<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Services\FonnteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-low {--notify : Send WhatsApp notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock items and optionally send WhatsApp notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for low stock items...');

        // Get low stock items
        $lowStockItems = Item::lowStock()
            ->with(['category', 'supplier'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'sku' => $item->sku,
                    'current_stock' => $item->current_stock,
                    'low_stock_threshold' => $item->low_stock_threshold,
                    'unit' => $item->unit,
                    'category' => $item->category->category_name ?? 'N/A',
                    'supplier' => $item->supplier->supplier_name ?? null,
                ];
            })
            ->toArray();

        $count = count($lowStockItems);

        if ($count === 0) {
            $this->info('✅ No low stock items found.');
            return;
        }

        $this->info("⚠️ Found {$count} low stock item(s):");

        // Display table
        $this->table(
            ['Item Name', 'SKU', 'Current Stock', 'Threshold', 'Status'],
            array_map(function ($item) {
                $status = $item['current_stock'] <= 0 ? 'OUT OF STOCK' : 'LOW STOCK';
                return [
                    $item['item_name'],
                    $item['sku'],
                    $item['current_stock'] . ' ' . $item['unit'],
                    $item['low_stock_threshold'] . ' ' . $item['unit'],
                    $status
                ];
            }, $lowStockItems)
        );

        // Send WhatsApp notification if requested
        if ($this->option('notify')) {
            $this->info('📱 Sending WhatsApp notification...');
            
            $fonnteService = app(FonnteService::class);
            $result = $fonnteService->sendLowStockNotification($lowStockItems);

            if ($result['success']) {
                $this->info('✅ WhatsApp notification sent successfully!');
                Log::info('Low stock notification sent', ['items_count' => $count]);
            } else {
                $this->error('❌ Failed to send WhatsApp notification: ' . ($result['error'] ?? 'Unknown error'));
                Log::error('Failed to send low stock notification', ['error' => $result['error'] ?? 'Unknown']);
            }
        }

        return $count > 0 ? 0 : 1;
    }
}