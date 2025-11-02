<?php
// filepath: app/Models/BranchMonthlyBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class BranchMonthlyBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'branch_id',
        'warehouse_id',
        'year',
        'month',
        'opening_stock',
        'closing_stock',
        'stock_in',
        'stock_out',
        'adjustments',
        'receive_from_central',
        'transfer_to_kitchen',
        'return_to_central',
        'local_purchase_in'
    ];

    protected $casts = [
        'opening_stock' => 'decimal:3',
        'closing_stock' => 'decimal:3',
        'stock_in' => 'decimal:3',
        'stock_out' => 'decimal:3',
        'adjustments' => 'decimal:3',
        'receive_from_central' => 'decimal:3',
        'transfer_to_kitchen' => 'decimal:3',
        'return_to_central' => 'decimal:3',
        'local_purchase_in' => 'decimal:3',
        'year' => 'integer',
        'month' => 'integer',
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
     * Balance belongs to branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Balance belongs to warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Related stock transactions untuk period ini
     */
    public function stockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class, 'item_id', 'item_id')
            ->where('branch_id', $this->branch_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month);
    }

    /**
     * Related stock period
     */
    public function stockPeriod()
    {
        return $this->belongsTo(StockPeriod::class, 'warehouse_id', 'warehouse_id')
            ->where('branch_id', $this->branch_id)
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Dapatkan atau buat balance untuk bulan tertentu
     */
    public static function getOrCreateBalance($itemId, $branchId, $warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        $balance = static::where([
            'item_id' => $itemId,
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseId,
            'year' => $year,
            'month' => $month
        ])->first();
        
        if (!$balance) {
            // Cari opening stock dari bulan sebelumnya
            $openingStock = static::getOpeningStock($itemId, $branchId, $warehouseId, $year, $month);
            
            $balance = static::create([
                'item_id' => $itemId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'year' => $year,
                'month' => $month,
                'opening_stock' => $openingStock,
                'closing_stock' => $openingStock,
                'stock_in' => 0,
                'stock_out' => 0,
                'adjustments' => 0,
                'receive_from_central' => 0,
                'transfer_to_kitchen' => 0,
                'return_to_central' => 0,
                'local_purchase_in' => 0
            ]);
        }
        
        return $balance;
    }

    /**
     * Hitung opening stock dari closing stock bulan sebelumnya
     */
    public static function getOpeningStock($itemId, $branchId, $warehouseId, $year, $month)
    {
        // Tentukan bulan sebelumnya
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
        }
        
        // Cari closing stock bulan sebelumnya
        $previousBalance = static::where([
            'item_id' => $itemId,
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseId,
            'year' => $prevYear,
            'month' => $prevMonth
        ])->first();
        
        if ($previousBalance) {
            return $previousBalance->closing_stock;
        }
        
        // Jika tidak ada data bulan sebelumnya, coba ambil dari central stock balance initial
        // atau gunakan 0 sebagai starting point
        return 0;
    }

    /**
     * Dapatkan balance untuk bulan tertentu (tanpa create)
     */
    public static function getBalance($itemId, $branchId, $warehouseId, $year, $month)
    {
        return static::where([
            'item_id' => $itemId,
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseId,
            'year' => $year,
            'month' => $month
        ])->first();
    }

    /**
     * Dapatkan balance bulan sebelumnya
     */
    public static function getPreviousMonthBalance($itemId, $branchId, $warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        // Tentukan bulan sebelumnya
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
        }

        return static::getBalance($itemId, $branchId, $warehouseId, $prevYear, $prevMonth);
    }

    /**
     * Dapatkan all balance untuk item tertentu di branch/warehouse (riwayat bulanan)
     */
    public static function getItemHistory($itemId, $branchId, $warehouseId, $limit = 12)
    {
        return static::where([
                'item_id' => $itemId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId
            ])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Dapatkan summary untuk periode tertentu di branch
     */
    public static function getBranchPeriodSummary($branchId, $year, $month, $warehouseId = null)
    {
        $query = static::where([
            'branch_id' => $branchId,
            'year' => $year,
            'month' => $month
        ]);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->selectRaw('
                COUNT(*) as total_items,
                SUM(opening_stock) as total_opening_stock,
                SUM(stock_in) as total_stock_in,
                SUM(stock_out) as total_stock_out,
                SUM(closing_stock) as total_closing_stock,
                SUM(adjustments) as total_adjustments,
                SUM(receive_from_central) as total_receive_from_central,
                SUM(transfer_to_kitchen) as total_transfer_to_kitchen,
                SUM(return_to_central) as total_return_to_central,
                SUM(local_purchase_in) as total_local_purchase_in
            ')
            ->first();
    }

    /**
     * Dapatkan summary untuk semua branch di periode tertentu
     */
    public static function getAllBranchPeriodSummary($year, $month)
    {
        return static::where('year', $year)
            ->where('month', $month)
            ->selectRaw('
                branch_id,
                warehouse_id,
                COUNT(*) as total_items,
                SUM(opening_stock) as total_opening_stock,
                SUM(stock_in) as total_stock_in,
                SUM(stock_out) as total_stock_out,
                SUM(closing_stock) as total_closing_stock,
                SUM(adjustments) as total_adjustments,
                SUM(receive_from_central) as total_receive_from_central,
                SUM(transfer_to_kitchen) as total_transfer_to_kitchen,
                SUM(return_to_central) as total_return_to_central,
                SUM(local_purchase_in) as total_local_purchase_in
            ')
            ->with('branch', 'warehouse')
            ->groupBy('branch_id', 'warehouse_id')
            ->get();
    }

    /**
     * Dapatkan available periods untuk branch
     */
    public static function getAvailablePeriods($branchId = null, $warehouseId = null)
    {
        $query = static::selectRaw('DISTINCT year, month');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'label' => Carbon::create($item->year, $item->month, 1)->format('F Y'),
                    'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT)
                ];
            });
    }

    /**
     * Get branch performance analysis
     */
    public static function getBranchPerformanceAnalysis($branchId, $startYear, $startMonth, $endYear, $endMonth)
    {
        $results = [];
        $currentYear = $startYear;
        $currentMonth = $startMonth;

        while (($currentYear < $endYear) || ($currentYear == $endYear && $currentMonth <= $endMonth)) {
            $summary = static::getBranchPeriodSummary($branchId, $currentYear, $currentMonth);
            
            if ($summary) {
                $results[] = [
                    'year' => $currentYear,
                    'month' => $currentMonth,
                    'period_label' => Carbon::create($currentYear, $currentMonth, 1)->format('M Y'),
                    'summary' => $summary
                ];
            }

            // Next month
            if ($currentMonth == 12) {
                $currentMonth = 1;
                $currentYear++;
            } else {
                $currentMonth++;
            }
        }

        return collect($results);
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Update opening stock dari bulan sebelumnya
     */
    public function syncOpeningStock()
    {
        $openingStock = static::getOpeningStock(
            $this->item_id, 
            $this->branch_id, 
            $this->warehouse_id, 
            $this->year, 
            $this->month
        );
        
        $this->update(['opening_stock' => $openingStock]);
        
        // Recalculate closing stock
        $this->recalculateClosingStock();
    }
    
    /**
     * Hitung ulang closing stock berdasarkan formula untuk branch
     */
    public function recalculateClosingStock()
    {
        $newClosingStock = $this->opening_stock + $this->stock_in - $this->stock_out + $this->adjustments;
        $this->update(['closing_stock' => $newClosingStock]);
    }

    /**
     * Recalculate balance from actual transactions
     */
    public function recalculateFromTransactions()
    {
        $transactions = $this->stockTransactions;

        // Reset detailed columns
        $this->update([
            'stock_in' => 0,
            'stock_out' => 0,
            'adjustments' => 0,
            'receive_from_central' => 0,
            'transfer_to_kitchen' => 0,
            'return_to_central' => 0,
            'local_purchase_in' => 0
        ]);

        // Recalculate from transactions
        foreach ($transactions as $transaction) {
            $this->processTransaction($transaction, false); // false = don't save each time
        }

        // Recalculate closing stock
        $this->recalculateClosingStock();
        $this->save();

        return $this;
    }
    
    /**
     * Update stock movement dengan branch-specific logic
     */
    public function updateMovement($type, $quantity)
    {
        switch (strtoupper($type)) {
            case 'IN':
                $this->increment('stock_in', $quantity);
                $this->increment('closing_stock', $quantity);
                break;
                
            case 'OUT':
                $this->increment('stock_out', $quantity);
                $this->decrement('closing_stock', $quantity);
                break;
                
            case 'ADJUSTMENT':
                // Adjustment bisa positif atau negatif
                $this->increment('adjustments', $quantity);
                $this->increment('closing_stock', $quantity);
                break;

            // Branch-specific movements
            case 'RECEIVE_FROM_CENTRAL':
                $this->increment('receive_from_central', $quantity);
                $this->increment('stock_in', $quantity);
                $this->increment('closing_stock', $quantity);
                break;

            case 'TRANSFER_TO_KITCHEN':
                $this->increment('transfer_to_kitchen', $quantity);
                $this->increment('stock_out', $quantity);
                $this->decrement('closing_stock', $quantity);
                break;

            case 'RETURN_TO_CENTRAL':
                $this->increment('return_to_central', $quantity);
                $this->increment('stock_out', $quantity);
                $this->decrement('closing_stock', $quantity);
                break;

            case 'LOCAL_PURCHASE_IN':
                $this->increment('local_purchase_in', $quantity);
                $this->increment('stock_in', $quantity);
                $this->increment('closing_stock', $quantity);
                break;
        }
    }

    /**
     * Process individual transaction untuk detailed tracking
     */
    public function processTransaction($transaction, $save = true)
    {
        $quantity = $transaction->quantity;

        switch ($transaction->transaction_type) {
            case BranchStockTransaction::TYPE_RECEIVE_FROM_CENTRAL:
                $this->receive_from_central += $quantity;
                $this->stock_in += $quantity;
                break;

            case BranchStockTransaction::TYPE_RECEIVE_FROM_SUPPLIER:
            case BranchStockTransaction::TYPE_RETURN_FROM_KITCHEN:
                $this->local_purchase_in += $quantity;
                $this->stock_in += $quantity;
                break;

            case BranchStockTransaction::TYPE_TRANSFER_TO_KITCHEN:
                $this->transfer_to_kitchen += $quantity;
                $this->stock_out += $quantity;
                break;

            case BranchStockTransaction::TYPE_TRANSFER_TO_CENTRAL:
                $this->return_to_central += $quantity;
                $this->stock_out += $quantity;
                break;

            case BranchStockTransaction::TYPE_TRANSFER_TO_BRANCH:
            case BranchStockTransaction::TYPE_WASTAGE:
            case BranchStockTransaction::TYPE_SOLD:
                $this->stock_out += $quantity;
                break;

            case BranchStockTransaction::TYPE_ADJUSTMENT_IN:
                $this->adjustments += $quantity;
                $this->stock_in += $quantity;
                break;

            case BranchStockTransaction::TYPE_ADJUSTMENT_OUT:
                $this->adjustments -= $quantity;
                $this->stock_out += $quantity;
                break;

            case BranchStockTransaction::TYPE_STOCK_TAKE:
                // Stock take is treated as adjustment
                $currentStock = $this->closing_stock;
                $difference = $quantity - $currentStock;
                $this->adjustments += $difference;
                break;
        }

        if ($save) {
            $this->recalculateClosingStock();
        }

        return $this;
    }

    /**
     * Get comparison with previous month
     */
    public function getPreviousMonthComparison()
    {
        $previousBalance = static::getPreviousMonthBalance(
            $this->item_id, 
            $this->branch_id, 
            $this->warehouse_id, 
            $this->year, 
            $this->month
        );
        
        if (!$previousBalance) {
            return null;
        }

        return [
            'previous_balance' => $previousBalance,
            'opening_stock_change' => $this->opening_stock - $previousBalance->closing_stock,
            'closing_stock_change' => $this->closing_stock - $previousBalance->closing_stock,
            'stock_in_change' => $this->stock_in - $previousBalance->stock_in,
            'stock_out_change' => $this->stock_out - $previousBalance->stock_out,
            'net_change_comparison' => $this->net_change - $previousBalance->net_change,
            'central_receive_change' => $this->receive_from_central - $previousBalance->receive_from_central,
            'kitchen_transfer_change' => $this->transfer_to_kitchen - $previousBalance->transfer_to_kitchen
        ];
    }

    /**
     * Get branch movement breakdown
     */
    public function getMovementBreakdown()
    {
        $totalIn = $this->stock_in;
        $totalOut = $this->stock_out;

        return [
            'stock_in_breakdown' => [
                'receive_from_central' => [
                    'amount' => $this->receive_from_central,
                    'percentage' => $totalIn > 0 ? ($this->receive_from_central / $totalIn) * 100 : 0
                ],
                'local_purchase' => [
                    'amount' => $this->local_purchase_in,
                    'percentage' => $totalIn > 0 ? ($this->local_purchase_in / $totalIn) * 100 : 0
                ],
                'adjustments_in' => [
                    'amount' => max(0, $this->adjustments),
                    'percentage' => $totalIn > 0 ? (max(0, $this->adjustments) / $totalIn) * 100 : 0
                ]
            ],
            'stock_out_breakdown' => [
                'transfer_to_kitchen' => [
                    'amount' => $this->transfer_to_kitchen,
                    'percentage' => $totalOut > 0 ? ($this->transfer_to_kitchen / $totalOut) * 100 : 0
                ],
                'return_to_central' => [
                    'amount' => $this->return_to_central,
                    'percentage' => $totalOut > 0 ? ($this->return_to_central / $totalOut) * 100 : 0
                ],
                'other_out' => [
                    'amount' => $totalOut - $this->transfer_to_kitchen - $this->return_to_central,
                    'percentage' => $totalOut > 0 ? (($totalOut - $this->transfer_to_kitchen - $this->return_to_central) / $totalOut) * 100 : 0
                ]
            ]
        ];
    }

    // ========================================
    // SCOPES
    // ========================================
    
    public function scopeForMonth($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }
    
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
    
    public function scopeCurrentMonth($query)
    {
        return $query->where('year', now()->year)->where('month', now()->month);
    }

    public function scopePreviousMonth($query)
    {
        $prevDate = now()->subMonth();
        return $query->where('year', $prevDate->year)->where('month', $prevDate->month);
    }

    public function scopeForPeriod($query, $yearMonth)
    {
        if (strpos($yearMonth, '-') !== false) {
            [$year, $month] = explode('-', $yearMonth);
            return $query->where('year', $year)->where('month', $month);
        }
        return $query;
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function($q) {
            $q->whereRaw('branch_monthly_balances.closing_stock <= items.low_stock_threshold')
              ->where('branch_monthly_balances.closing_stock', '>', 0);
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('closing_stock', '<=', 0);
    }

    public function scopeHighMovement($query, $threshold = 100)
    {
        return $query->whereRaw('(stock_in + stock_out) >= ?', [$threshold]);
    }

    // ========================================
    // ACCESSORS
    // ========================================
    
    public function getNetChangeAttribute()
    {
        return $this->closing_stock - $this->opening_stock;
    }
    
    public function getMonthNameAttribute()
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
    
    public function getTotalMovementAttribute()
    {
        return $this->stock_in + $this->stock_out + abs($this->adjustments);
    }
    
    public function getFormattedPeriodAttribute()
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$this->month] . ' ' . $this->year;
    }

    public function getPeriodValueAttribute()
    {
        return $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
    }

    public function getIsCurrentMonthAttribute()
    {
        return $this->year == now()->year && $this->month == now()->month;
    }

    public function getIsPreviousMonthAttribute()
    {
        $prevDate = now()->subMonth();
        return $this->year == $prevDate->year && $this->month == $prevDate->month;
    }

    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->branch_name : 'Unknown Branch';
    }

    public function getWarehouseNameAttribute()
    {
        return $this->warehouse ? $this->warehouse->warehouse_name : 'Unknown Warehouse';
    }

    public function getItemNameAttribute()
    {
        return $this->item ? $this->item->item_name : 'Unknown Item';
    }

    public function getStockStatusAttribute()
    {
        if ($this->closing_stock <= 0) {
            return 'Habis';
        } elseif ($this->item && $this->closing_stock <= $this->item->low_stock_threshold) {
            return 'Menipis';
        } else {
            return 'Tersedia';
        }
    }

    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'Habis' => 'danger',
            'Menipis' => 'warning',
            'Tersedia' => 'success',
            default => 'secondary'
        };
    }

    public function getCentralDependencyRateAttribute()
    {
        return $this->stock_in > 0 ? ($this->receive_from_central / $this->stock_in) * 100 : 0;
    }

    public function getKitchenTransferRateAttribute()
    {
        return $this->stock_out > 0 ? ($this->transfer_to_kitchen / $this->stock_out) * 100 : 0;
    }

    public function getStockTurnoverAttribute()
    {
        $avgStock = ($this->opening_stock + $this->closing_stock) / 2;
        return $avgStock > 0 ? $this->stock_out / $avgStock : 0;
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'item_id' => 'required|exists:items,id',
            'branch_id' => 'required|exists:branches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'opening_stock' => 'required|numeric|min:0',
            'closing_stock' => 'required|numeric|min:0',
            'stock_in' => 'required|numeric|min:0',
            'stock_out' => 'required|numeric|min:0',
            'adjustments' => 'nullable|numeric',
            'receive_from_central' => 'nullable|numeric|min:0',
            'transfer_to_kitchen' => 'nullable|numeric|min:0',
            'return_to_central' => 'nullable|numeric|min:0',
            'local_purchase_in' => 'nullable|numeric|min:0'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_id.required' => 'Item wajib dipilih',
            'branch_id.required' => 'Branch wajib dipilih',
            'warehouse_id.required' => 'Warehouse wajib dipilih',
            'year.required' => 'Tahun wajib diisi',
            'month.required' => 'Bulan wajib diisi',
            'opening_stock.required' => 'Opening stock wajib diisi',
            'closing_stock.required' => 'Closing stock wajib diisi',
            'stock_in.required' => 'Stock in wajib diisi',
            'stock_out.required' => 'Stock out wajib diisi'
        ];
    }
}