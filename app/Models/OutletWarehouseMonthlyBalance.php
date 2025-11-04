<?php
// filepath: app/Models/OutletWarehouseMonthlyBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutletWarehouseMonthlyBalance extends Model
{
    use HasFactory;

    protected $table = 'outlet_warehouse_monthly_balances';

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'year',
        'month',
        'opening_stock',
        'closing_stock',
        'received_from_branch_warehouse',
        'received_return_from_kitchen',
        'distributed_to_kitchen',
        'transfer_out',
        'adjustments',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'opening_stock' => 'decimal:3',
        'closing_stock' => 'decimal:3',
        'received_from_branch_warehouse' => 'decimal:3',
        'received_return_from_kitchen' => 'decimal:3',
        'distributed_to_kitchen' => 'decimal:3',
        'transfer_out' => 'decimal:3',
        'adjustments' => 'decimal:3',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Outlet warehouse yang memiliki balance ini
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Item yang di-track balance-nya
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * User yang menutup periode ini
     */
    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Transaksi stock yang terkait dengan balance ini
     */
    public function stockTransactions()
    {
        return $this->hasMany(OutletStockTransaction::class, 'outlet_warehouse_id', 'warehouse_id')
                    ->where('item_id', $this->item_id)
                    ->where('year', $this->year)
                    ->where('month', $this->month);
    }

    /**
     * Distribusi ke kitchen dari outlet warehouse ini
     */
    public function distributionsToKitchen()
    {
        return $this->hasMany(OutletWarehouseToKitchenTransaction::class, 'outlet_warehouse_id', 'warehouse_id')
                    ->where('item_id', $this->item_id)
                    ->whereYear('transaction_date', $this->year)
                    ->whereMonth('transaction_date', $this->month);
    }

    // ============================================================
    // STATIC METHODS - Get or Create Balance
    // ============================================================

    /**
     * Get or create balance untuk item tertentu di warehouse tertentu
     *
     * @param int $itemId
     * @param int $warehouseId
     * @param int $year
     * @param int $month
     * @return OutletWarehouseMonthlyBalance
     */
    public static function getOrCreateBalance($itemId, $warehouseId, $year, $month)
    {
        $balance = self::where('item_id', $itemId)
                      ->where('warehouse_id', $warehouseId)
                      ->where('year', $year)
                      ->where('month', $month)
                      ->first();

        if (!$balance) {
            // Get previous month's closing stock as opening stock
            $previousMonth = $month - 1;
            $previousYear = $year;
            
            if ($previousMonth < 1) {
                $previousMonth = 12;
                $previousYear = $year - 1;
            }

            $previousBalance = self::where('item_id', $itemId)
                                  ->where('warehouse_id', $warehouseId)
                                  ->where('year', $previousYear)
                                  ->where('month', $previousMonth)
                                  ->first();

            $openingStock = $previousBalance ? $previousBalance->closing_stock : 0;

            $balance = self::create([
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'year' => $year,
                'month' => $month,
                'opening_stock' => $openingStock,
                'closing_stock' => $openingStock,
            ]);

            Log::info('Outlet warehouse monthly balance created', [
                'balance_id' => $balance->id,
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'period' => "$year-$month",
                'opening_stock' => $openingStock,
            ]);
        }

        return $balance;
    }

    // ============================================================
    // INSTANCE METHODS - Update Movements
    // ============================================================

    /**
     * Update movement (IN/OUT) pada balance
     *
     * @param string $type - Type: received_from_branch, return_from_kitchen, distribute_to_kitchen, transfer_out, adjustment
     * @param float $quantity - Quantity (bisa + atau -)
     * @return bool
     */
    public function updateMovement($type, $quantity)
    {
        try {
            DB::beginTransaction();

            switch ($type) {
                case 'received_from_branch':
                case 'received_from_branch_warehouse':
                    $this->received_from_branch_warehouse += $quantity;
                    $this->closing_stock += $quantity;
                    break;

                case 'return_from_kitchen':
                case 'received_return_from_kitchen':
                    $this->received_return_from_kitchen += $quantity;
                    $this->closing_stock += $quantity;
                    break;

                case 'distribute_to_kitchen':
                case 'distributed_to_kitchen':
                    $this->distributed_to_kitchen += $quantity;
                    $this->closing_stock -= $quantity;
                    break;

                case 'transfer_out':
                    $this->transfer_out += $quantity;
                    $this->closing_stock -= $quantity;
                    break;

                case 'adjustment':
                case 'adjustments':
                    $this->adjustments += $quantity;
                    $this->closing_stock += $quantity; // bisa + atau - tergantung $quantity
                    break;

                default:
                    throw new \Exception("Invalid movement type: $type");
            }

            $this->save();

            DB::commit();

            Log::info('Outlet warehouse balance updated', [
                'balance_id' => $this->id,
                'type' => $type,
                'quantity' => $quantity,
                'new_closing_stock' => $this->closing_stock,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update outlet warehouse balance error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recalculate closing stock dari opening stock + semua movements
     *
     * @return bool
     */
    public function recalculateClosingStock()
    {
        try {
            $calculatedClosing = $this->opening_stock
                               + $this->received_from_branch_warehouse
                               + $this->received_return_from_kitchen
                               - $this->distributed_to_kitchen
                               - $this->transfer_out
                               + $this->adjustments;

            if ($calculatedClosing != $this->closing_stock) {
                $this->closing_stock = $calculatedClosing;
                $this->save();

                Log::info('Outlet warehouse closing stock recalculated', [
                    'balance_id' => $this->id,
                    'old_closing' => $this->closing_stock,
                    'new_closing' => $calculatedClosing,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Recalculate outlet warehouse closing stock error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Close month (tutup periode)
     *
     * @param int $userId - User yang menutup
     * @return bool
     */
    public function closeMonth($userId = null)
    {
        try {
            if ($this->is_closed) {
                return false; // Already closed
            }

            DB::beginTransaction();

            // Recalculate closing stock
            $this->recalculateClosingStock();

            // Mark as closed
            $this->is_closed = true;
            $this->closed_at = now();
            $this->closed_by = $userId ?? auth()->id();
            $this->save();

            // Sync opening stock untuk next month
            $this->syncOpeningStockToNextMonth();

            DB::commit();

            Log::info('Outlet warehouse month closed', [
                'balance_id' => $this->id,
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $this->item_id,
                'period' => "{$this->year}-{$this->month}",
                'closing_stock' => $this->closing_stock,
                'closed_by' => $this->closed_by,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Close outlet warehouse month error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync opening stock untuk next month
     *
     * @return bool
     */
    public function syncOpeningStockToNextMonth()
    {
        try {
            $nextMonth = $this->month + 1;
            $nextYear = $this->year;

            if ($nextMonth > 12) {
                $nextMonth = 1;
                $nextYear = $this->year + 1;
            }

            $nextBalance = self::where('warehouse_id', $this->warehouse_id)
                              ->where('item_id', $this->item_id)
                              ->where('year', $nextYear)
                              ->where('month', $nextMonth)
                              ->first();

            if ($nextBalance) {
                $nextBalance->opening_stock = $this->closing_stock;
                $nextBalance->save();

                Log::info('Opening stock synced to next month', [
                    'current_balance_id' => $this->id,
                    'next_balance_id' => $nextBalance->id,
                    'opening_stock' => $this->closing_stock,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Sync opening stock to next month error: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // QUERY SCOPES
    // ============================================================

    /**
     * Scope: Only open periods
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Scope: Only closed periods
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    /**
     * Scope: Filter by warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope: Filter by item
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope: Filter by period
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope: Current period
     */
    public function scopeCurrentPeriod($query)
    {
        return $query->where('year', date('Y'))
                    ->where('month', date('m'));
    }

    /**
     * Scope: With low stock (closing_stock below minimum)
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function($q) {
            $q->whereRaw('outlet_warehouse_monthly_balances.closing_stock < items.low_stock_threshold');
        });
    }

    // ============================================================
    // ACCESSORS & HELPERS
    // ============================================================

    /**
     * Get period formatted string
     */
    public function getPeriodAttribute()
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }

    /**
     * Get total stock IN
     */
    public function getTotalStockInAttribute()
    {
        return $this->received_from_branch_warehouse 
             + $this->received_return_from_kitchen
             + ($this->adjustments > 0 ? $this->adjustments : 0);
    }

    /**
     * Get total stock OUT
     */
    public function getTotalStockOutAttribute()
    {
        return $this->distributed_to_kitchen 
             + $this->transfer_out
             + ($this->adjustments < 0 ? abs($this->adjustments) : 0);
    }

    /**
     * Get turnover rate (keluar / rata-rata stock)
     */
    public function getTurnoverRateAttribute()
    {
        $avgStock = ($this->opening_stock + $this->closing_stock) / 2;
        
        if ($avgStock <= 0) {
            return 0;
        }

        return round(($this->distributed_to_kitchen / $avgStock), 2);
    }

    /**
     * Check if stock is low
     */
    public function getIsLowStockAttribute()
    {
        if (!$this->item) {
            return false;
        }

        return $this->closing_stock < $this->item->low_stock_threshold;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if (!$this->item) {
            return 'unknown';
        }

        if ($this->closing_stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->closing_stock < $this->item->low_stock_threshold) {
            return 'low_stock';
        }  else {
            return 'normal';
        }
    }

    /**
     * Get stock value (closing_stock * unit_cost)
     */
    public function getStockValueAttribute()
    {
        if (!$this->item) {
            return 0;
        }

        return $this->closing_stock * $this->item->unit_cost;
    }
}