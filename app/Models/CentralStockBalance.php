<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CentralStockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'year',
        'month',
        'opening_stock',
        'stock_in',
        'stock_out',
        'closing_stock',
        'unit_cost',
        'total_value'
    ];

    protected $casts = [
        'opening_stock' => 'decimal:3',
        'stock_in' => 'decimal:3',
        'stock_out' => 'decimal:3',
        'closing_stock' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2'
    ];

    protected $dates = [
        'last_updated',
        'created_at',
        'updated_at'
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Balance belongs to item
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Balance belongs to warehouse (central warehouse)
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Related central stock transactions for this period
     */
    public function centralStockTransactions()
    {
        return $this->hasMany(CentralStockTransaction::class, 'item_id', 'item_id')
            ->where('warehouse_id', $this->warehouse_id)
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope untuk current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('year', now()->year)
                    ->where('month', now()->month);
    }

    /**
     * Scope untuk previous month
     */
    public function scopePreviousMonth($query)
    {
        $prevMonth = now()->subMonth();
        return $query->where('year', $prevMonth->year)
                    ->where('month', $prevMonth->month);
    }

    /**
     * Scope untuk specific period
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope untuk specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope untuk items dengan stock
     */
    public function scopeWithStock($query)
    {
        return $query->where('closing_stock', '>', 0);
    }

    /**
     * Scope untuk low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function($q) {
            $q->whereRaw('central_stock_balances.closing_stock <= items.low_stock_threshold')
              ->where('central_stock_balances.closing_stock', '>', 0);
        });
    }

    /**
     * Scope untuk out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('closing_stock', '<=', 0);
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Calculate closing stock berdasarkan movements
     */
    public function calculateClosingStock()
    {
        $this->closing_stock = $this->opening_stock
            + $this->stock_in
            - $this->stock_out;

        return $this->closing_stock;
    }

    /**
     * Update balance berdasarkan transaction baru
     */
    public function updateFromTransaction(CentralStockTransaction $transaction)
    {
        $quantity = $transaction->quantity;
        
        if ($quantity > 0) {
            // Stock in
            $this->stock_in += abs($quantity);
            $this->closing_stock += abs($quantity);
        } else {
            // Stock out
            $this->stock_out += abs($quantity);
            $this->closing_stock -= abs($quantity);
        }
        
        // Update unit cost if provided
        if ($transaction->unit_cost > 0) {
            $this->unit_cost = $transaction->unit_cost;
        }
        
        // Update total value
        $this->total_value = $this->closing_stock * $this->unit_cost;
        
        $this->save();
        
        return $this;
    }

    /**
     * Get atau create balance untuk period tertentu
     */
    public static function getOrCreateBalance($itemId, $warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $balance = self::where([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'year' => $year,
            'month' => $month
        ])->first();

        if (!$balance) {
            $balance = self::create([
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'year' => $year,
                'month' => $month,
                'opening_stock' => self::getOpeningStock($itemId, $warehouseId, $year, $month),
                'stock_in' => 0,
                'stock_out' => 0,
                'closing_stock' => 0,
                'unit_cost' => 0,
                'total_value' => 0
            ]);

            $balance->calculateClosingStock();
            $balance->save();
        }

        return $balance;
    }

    /**
     * Get opening stock dari closing stock bulan sebelumnya
     */
    protected static function getOpeningStock($itemId, $warehouseId, $year, $month)
    {
        $prevDate = Carbon::create($year, $month, 1)->subMonth();
        
        $prevBalance = self::where([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'year' => $prevDate->year,
            'month' => $prevDate->month
        ])->first();

        return $prevBalance ? $prevBalance->closing_stock : 0;
    }

    /**
     * Recalculate balance dari semua transactions
     */
    public function recalculateFromTransactions()
    {
        // Reset semua movement columns
        $this->stock_in = 0;
        $this->stock_out = 0;

        // Get semua transactions untuk period ini
        $transactions = CentralStockTransaction::where([
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id
        ])
        ->whereYear('transaction_date', $this->year)
        ->whereMonth('transaction_date', $this->month)
        ->get();

        // Update balance berdasarkan each transaction
        foreach ($transactions as $transaction) {
            $this->updateFromTransaction($transaction);
        }

        $this->calculateClosingStock();
        $this->save();

        return $this;
    }

    /**
     * Get stock value (quantity * unit cost)
     */
    public function getStockValue()
    {
        return $this->closing_stock * ($this->item->unit_cost ?? 0);
    }

    /**
     * Get total stock movement untuk period ini
     */
    public function getTotalMovement()
    {
        return $this->stock_in 
            + $this->stock_out;
    }

    /**
     * Check apakah item low stock
     */
    public function isLowStock()
    {
        $threshold = $this->item->low_stock_threshold ?? 0;
        return $this->closing_stock <= $threshold && $this->closing_stock > 0;
    }

    /**
     * Check apakah out of stock
     */
    public function isOutOfStock()
    {
        return $this->closing_stock <= 0;
    }

    /**
     * Get turnover ratio untuk period ini
     */
    public function getTurnoverRatio()
    {
        $avgStock = ($this->opening_stock + $this->closing_stock) / 2;
        
        if ($avgStock <= 0) {
            return 0;
        }

        $outgoingStock = $this->stock_out;
        return $outgoingStock / $avgStock;
    }

    /**
     * Get distribution efficiency (berapa % yang berhasil di-distribute)
     */
    public function getDistributionEfficiency()
    {
        $availableForDistribution = $this->opening_stock + $this->stock_in;
        
        if ($availableForDistribution <= 0) {
            return 0;
        }

        return ($this->stock_out / $availableForDistribution) * 100;
    }

    /**
     * Close period (lock the balance)
     */
    public function closePeriod()
    {
        // Implementation untuk close period
        // Bisa include validation, period locking, etc.
        $this->last_updated = now();
        $this->save();

        return $this;
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Get summary untuk semua items di warehouse
     */
    public static function getWarehouseSummary($warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $balances = self::forWarehouse($warehouseId)
            ->forPeriod($year, $month)
            ->with('item')
            ->get();

        return [
            'total_items' => $balances->count(),
            'items_with_stock' => $balances->where('closing_stock', '>', 0)->count(),
            'low_stock_items' => $balances->filter->isLowStock()->count(),
            'total_stock_value' => $balances->sum->getStockValue(),
            'total_purchase' => $balances->sum('stock_in'),
            'total_distribution' => $balances->sum('stock_out'),
            'avg_turnover_ratio' => $balances->avg->getTurnoverRatio()
        ];
    }

    /**
     * Get items ready for distribution
     */
    public static function getItemsReadyForDistribution($warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return self::forWarehouse($warehouseId)
            ->forPeriod($year, $month)
            ->withStock()
            ->with('item')
            ->get()
            ->filter(function($balance) {
                // Only items dengan stock > minimum requirement
                $minRequired = $balance->item->central_min_stock ?? 0;
                return $balance->closing_stock > $minRequired;
            });
    }

    /**
     * Process month end closing
     */
    public static function processMonthEndClosing($warehouseId, $year, $month)
    {
        $balances = self::forWarehouse($warehouseId)
            ->forPeriod($year, $month)
            ->get();

        foreach ($balances as $balance) {
            $balance->recalculateFromTransactions();
            $balance->closePeriod();
        }

        return $balances->count();
    }

    // ========================================
    // ATTRIBUTES
    // ========================================

    /**
     * Get period display
     */
    public function getPeriodDisplayAttribute()
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $months[$this->month] . ' ' . $this->year;
    }

    /**
     * Get stock status untuk UI
     */
    public function getStockStatusAttribute()
    {
        if ($this->closing_stock <= 0) {
            return ['status' => 'empty', 'color' => 'danger', 'text' => 'Habis'];
        } elseif ($this->isLowStock()) {
            return ['status' => 'low', 'color' => 'warning', 'text' => 'Rendah'];
        } else {
            return ['status' => 'normal', 'color' => 'success', 'text' => 'Normal'];
        }
    }

    /**
     * Get movement summary
     */
    public function getMovementSummaryAttribute()
    {
        return [
            'in' => $this->stock_in,
            'out' => $this->stock_out,
            'net' => $this->stock_in - $this->stock_out
        ];
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'opening_stock' => 'required|numeric|min:0',
            'stock_in' => 'numeric|min:0',
            'stock_out' => 'numeric|min:0',
            'closing_stock' => 'numeric|min:0',
            'unit_cost' => 'numeric|min:0',
            'total_value' => 'numeric|min:0'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_id.required' => 'Item wajib dipilih',
            'item_id.exists' => 'Item tidak valid',
            'warehouse_id.required' => 'Warehouse wajib dipilih',
            'warehouse_id.exists' => 'Warehouse tidak valid',
            'year.required' => 'Tahun wajib diisi',
            'month.required' => 'Bulan wajib diisi',
            'opening_stock.required' => 'Opening stock wajib diisi',
            '*.numeric' => 'Harus berupa angka',
            '*.min' => 'Tidak boleh kurang dari 0'
        ];
    }
}