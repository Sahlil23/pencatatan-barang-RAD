<?php
// filepath: app/Models/MonthlyKitchenStockBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\db;

class MonthlyKitchenStockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'item_id',
        'year',
        'month',
        'opening_stock',
        'closing_stock',
        'transfer_in',
        'received_from_branch_warehouse',      // ✅ NEW FIELD
        'received_from_outlet_warehouse',      // ✅ NEW FIELD
        'usage',
        'waste',
        'transfer_out',
        'adjustments',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'opening_stock' => 'decimal:3',
        'closing_stock' => 'decimal:3',
        'transfer_in' => 'decimal:3',
        'received_from_branch_warehouse' => 'decimal:3',   // ✅ NEW
        'received_from_outlet_warehouse' => 'decimal:3',   // ✅ NEW
        'usage' => 'decimal:3',
        'waste' => 'decimal:3',
        'transfer_out' => 'decimal:3',
        'adjustments' => 'decimal:3',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
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
     * Related kitchen stock transactions untuk period ini
     */
    public function kitchenStockTransactions()
    {
        return $this->hasMany(KitchenStockTransaction::class, 'item_id', 'item_id')
            ->where('branch_id', $this->branch_id)
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month);
    }

    /**
     * Related stock period
     */
    public function stockPeriod()
    {
        return $this->belongsTo(StockPeriod::class, 'branch_id', 'branch_id')
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    // ========================================
    // STATIC METHODS
    // ========================================
    
    /**
     * Dapatkan atau buat balance untuk bulan tertentu
     */
    public static function getOrCreateBalance($itemId, $branchId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        $balance = static::where([
            'item_id' => $itemId,
            'branch_id' => $branchId,
            'year' => $year,
            'month' => $month
        ])->first();
        
        if (!$balance) {
            // Cari opening stock dari bulan sebelumnya
            $openingStock = static::getOpeningStock($itemId, $branchId, $year, $month);
            
            $balance = static::create([
                'item_id' => $itemId,
                'branch_id' => $branchId,
                'year' => $year,
                'month' => $month,
                'opening_stock' => $openingStock,
                'closing_stock' => $openingStock,
                'transfer_in' => 0,
                'usage_out' => 0,
                'adjustments' => 0,
                'is_closed' => false,
                'closed_at' => null
            ]);
        }
        
        return $balance;
    }

    /**
     * Hitung opening stock dari closing stock bulan sebelumnya
     */
    public static function getOpeningStock($itemId, $branchId, $year, $month)
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
            'year' => $prevYear,
            'month' => $prevMonth
        ])->first();
        
        if ($previousBalance) {
            return $previousBalance->closing_stock;
        }
        
        // Jika tidak ada data bulan sebelumnya, mulai dari 0 (kitchen stock awalnya kosong)
        return 0;
    }

    /**
     * Dapatkan balance untuk bulan tertentu (tanpa create)
     */
    public static function getBalance($itemId, $branchId, $year, $month)
    {
        return static::where([
            'item_id' => $itemId,
            'branch_id' => $branchId,
            'year' => $year,
            'month' => $month
        ])->first();
    }

    /**
     * Dapatkan balance bulan sebelumnya
     */
    public static function getPreviousMonthBalance($itemId, $branchId, $year = null, $month = null)
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

        return static::getBalance($itemId, $branchId, $prevYear, $prevMonth);
    }

    /**
     * Dapatkan all balance untuk item tertentu di branch (riwayat bulanan)
     */
    public static function getItemHistory($itemId, $branchId, $limit = 12)
    {
        return static::where([
                'item_id' => $itemId,
                'branch_id' => $branchId
            ])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Dapatkan summary untuk periode tertentu di branch
     */
    public static function getBranchPeriodSummary($branchId, $year, $month)
    {
        return static::where([
            'branch_id' => $branchId,
            'year' => $year,
            'month' => $month
        ])->selectRaw('
                COUNT(*) as total_items,
                SUM(opening_stock) as total_opening_stock,
                SUM(transfer_in) as total_transfer_in,
                SUM(usage_out) as total_usage_out,
                SUM(closing_stock) as total_closing_stock,
                SUM(adjustments) as total_adjustments
            ')
            ->first();
    }

    /**
     * Dapatkan summary untuk periode tertentu (semua branch)
     */
    public static function getPeriodSummary($year, $month)
    {
        return static::where('year', $year)
            ->where('month', $month)
            ->selectRaw('
                COUNT(*) as total_items,
                SUM(opening_stock) as total_opening_stock,
                SUM(transfer_in) as total_transfer_in,
                SUM(usage_out) as total_usage_out,
                SUM(closing_stock) as total_closing_stock,
                SUM(adjustments) as total_adjustments
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
                COUNT(*) as total_items,
                SUM(opening_stock) as total_opening_stock,
                SUM(transfer_in) as total_transfer_in,
                SUM(usage_out) as total_usage_out,
                SUM(closing_stock) as total_closing_stock,
                SUM(adjustments) as total_adjustments
            ')
            ->with('branch')
            ->groupBy('branch_id')
            ->get();
    }

    /**
     * Dapatkan available periods untuk branch
     */
    public static function getAvailablePeriods($branchId = null)
    {
        $query = static::selectRaw('DISTINCT year, month');

        if ($branchId) {
            $query->where('branch_id', $branchId);
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

    /**
     * Get cross-branch comparison
     */
    public static function getCrossBranchComparison($year, $month)
    {
        $branchSummaries = static::getAllBranchPeriodSummary($year, $month);
        
        return $branchSummaries->map(function($summary) {
            $usageRate = $summary->total_transfer_in > 0 ? 
                ($summary->total_usage_out / $summary->total_transfer_in) * 100 : 0;
            
            $stockTurnover = $summary->total_opening_stock > 0 ? 
                $summary->total_usage_out / $summary->total_opening_stock : 0;

            return [
                'branch' => $summary->branch,
                'summary' => $summary,
                'usage_rate' => $usageRate,
                'stock_turnover' => $stockTurnover,
                'efficiency_score' => $usageRate * 0.7 + ($stockTurnover * 30) // Weighted score
            ];
        })->sortByDesc('efficiency_score');
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
            $this->year, 
            $this->month
        );
        
        $this->update(['opening_stock' => $openingStock]);
        
        // Recalculate closing stock
        $this->recalculateClosingStock();
    }
    
    /**
     * Update movement (IN/OUT) pada balance
     * UPDATED: Add support for outlet warehouse source
     *
     * @param string $type - Type: transfer_in, usage, waste, etc.
     * @param float $quantity - Quantity (bisa + atau -)
     * @param string|null $source - Source: 'branch_warehouse' | 'outlet_warehouse' | null
     * @return bool
     */
    public function updateMovement($type, $quantity, $source = null)
    {
        try {
            DB::beginTransaction();

            switch ($type) {
                case 'transfer_in':
                    $this->transfer_in += $quantity;
                    $this->closing_stock += $quantity;
                    break;

                case 'received_from_branch_warehouse':
                    // ✅ NEW: Detailed tracking from branch warehouse
                    $this->received_from_branch_warehouse += $quantity;
                    $this->closing_stock += $quantity;
                    break;

                case 'received_from_outlet_warehouse':
                    // ✅ NEW: Detailed tracking from outlet warehouse
                    $this->received_from_outlet_warehouse += $quantity;
                    $this->closing_stock += $quantity;
                    break;

                case 'usage':
                    $this->usage += $quantity;
                    $this->closing_stock -= $quantity;
                    break;

                case 'waste':
                    $this->waste += $quantity;
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

            Log::info('Kitchen balance updated', [
                'balance_id' => $this->id,
                'type' => $type,
                'quantity' => $quantity,
                'source' => $source,
                'new_closing_stock' => $this->closing_stock,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update kitchen balance error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hitung ulang closing stock berdasarkan formula
     */
    public function recalculateClosingStock()
    {
        try {
            $calculatedClosing = $this->opening_stock
                               + $this->transfer_in
                               + $this->received_from_branch_warehouse   // ✅ ADD
                               + $this->received_from_outlet_warehouse   // ✅ ADD
                               - $this->usage
                               - $this->waste
                               - $this->transfer_out
                               + $this->adjustments;

            if ($calculatedClosing != $this->closing_stock) {
                $oldClosing = $this->closing_stock;
                $this->closing_stock = $calculatedClosing;
                $this->save();

                Log::info('Kitchen closing stock recalculated', [
                    'balance_id' => $this->id,
                    'old_closing' => $oldClosing,
                    'new_closing' => $calculatedClosing,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Recalculate kitchen closing stock error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recalculate balance from actual transactions
     */
    public function recalculateFromTransactions()
    {
        $transactions = $this->kitchenStockTransactions;

        // Reset movement columns
        $this->update([
            'transfer_in' => 0,
            'usage_out' => 0,
            'adjustments' => 0
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
     * Process individual transaction untuk detailed tracking
     */
    public function processTransaction($transaction, $save = true)
    {
        $quantity = $transaction->quantity;

        switch ($transaction->transaction_type) {
            case KitchenStockTransaction::TYPE_RECEIVE_FROM_WAREHOUSE:
            case KitchenStockTransaction::TYPE_TRANSFER_IN:
            case KitchenStockTransaction::TYPE_RETURN_FROM_PRODUCTION:
                $this->transfer_in += $quantity;
                break;

            case KitchenStockTransaction::TYPE_USAGE_PRODUCTION:
            case KitchenStockTransaction::TYPE_USAGE_COOKING:
            case KitchenStockTransaction::TYPE_USAGE_PREPARATION:
            case KitchenStockTransaction::TYPE_USAGE:
            case KitchenStockTransaction::TYPE_WASTAGE:
            case KitchenStockTransaction::TYPE_RETURN_TO_WAREHOUSE:
                $this->usage_out += $quantity;
                break;

            case KitchenStockTransaction::TYPE_ADJUSTMENT_IN:
                $this->adjustments += $quantity;
                $this->transfer_in += $quantity;
                break;

            case KitchenStockTransaction::TYPE_ADJUSTMENT_OUT:
                $this->adjustments -= $quantity;
                $this->usage_out += $quantity;
                break;

            case KitchenStockTransaction::TYPE_STOCK_TAKE:
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
     * Close this month's balance
     */
    public function closeMonth($userId = null)
    {
        if ($this->is_closed) {
            throw new \Exception("Bulan {$this->formatted_period} sudah ditutup sebelumnya");
        }

        $this->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $userId ?? auth()->id()
        ]);

        Log::info("Kitchen stock balance closed for {$this->branch->branch_name} - {$this->formatted_period}");

        return $this;
    }

    /**
     * Reopen this month's balance
     */
    public function reopenMonth($userId = null)
    {
        if (!$this->is_closed) {
            throw new \Exception("Bulan {$this->formatted_period} belum ditutup");
        }

        $this->update([
            'is_closed' => false,
            'closed_at' => null,
            'closed_by' => null
        ]);

        Log::info("Kitchen stock balance reopened for {$this->branch->branch_name} - {$this->formatted_period}");

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
            'transfer_in_change' => $this->transfer_in - $previousBalance->transfer_in,
            'usage_out_change' => $this->usage_out - $previousBalance->usage_out,
            'net_change_comparison' => $this->net_change - $previousBalance->net_change
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

    public function scopeOpenMonths($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeClosedMonths($query)
    {
        return $query->where('is_closed', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function($q) {
            $q->whereRaw('monthly_kitchen_stock_balances.closing_stock <= (items.low_stock_threshold * 0.5)')
              ->where('monthly_kitchen_stock_balances.closing_stock', '>', 0);
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('closing_stock', '<=', 0);
    }

    public function scopeHighUsage($query, $threshold = 50)
    {
        return $query->where('usage_out', '>=', $threshold);
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
        return $this->transfer_in + $this->usage_out + abs($this->adjustments);
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

    public function getCanEditAttribute()
    {
        return !$this->is_closed;
    }

    public function getStatusTextAttribute()
    {
        return $this->is_closed ? 'Ditutup' : 'Terbuka';
    }

    public function getStatusColorAttribute()
    {
        return $this->is_closed ? 'danger' : 'success';
    }

    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->branch_name : 'Unknown Branch';
    }

    public function getItemNameAttribute()
    {
        return $this->item ? $this->item->item_name : 'Unknown Item';
    }

    public function getUsageRateAttribute()
    {
        return $this->transfer_in > 0 ? ($this->usage_out / $this->transfer_in) * 100 : 0;
    }

    public function getStockTurnoverAttribute()
    {
        $avgStock = ($this->opening_stock + $this->closing_stock) / 2;
        return $avgStock > 0 ? $this->usage_out / $avgStock : 0;
    }

    public function getEfficiencyScoreAttribute()
    {
        // Weighted efficiency score based on usage rate and turnover
        return ($this->usage_rate * 0.7) + ($this->stock_turnover * 30);
    }

    public function getStockStatusAttribute()
    {
        if ($this->closing_stock <= 0) {
            return 'Habis';
        } elseif ($this->item && $this->closing_stock <= ($this->item->low_stock_threshold * 0.5)) {
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

    /**
     * Get total stock IN
     * UPDATED: Include new fields
     */
    public function getTotalStockInAttribute()
    {
        return $this->transfer_in 
             + $this->received_from_branch_warehouse    // ✅ ADD
             + $this->received_from_outlet_warehouse    // ✅ ADD
             + ($this->adjustments > 0 ? $this->adjustments : 0);
    }

    /**
     * Get stock source breakdown
     * NEW METHOD!
     * 
     * @return array
     */
    public function getStockSourceBreakdownAttribute()
    {
        return [
            'branch_warehouse' => [
                'quantity' => $this->received_from_branch_warehouse,
                'percentage' => $this->getTotalStockIn() > 0 
                    ? round(($this->received_from_branch_warehouse / $this->getTotalStockIn()) * 100, 2)
                    : 0,
                'label' => 'Branch Warehouse',
            ],
            'outlet_warehouse' => [
                'quantity' => $this->received_from_outlet_warehouse,
                'percentage' => $this->getTotalStockIn() > 0 
                    ? round(($this->received_from_outlet_warehouse / $this->getTotalStockIn()) * 100, 2)
                    : 0,
                'label' => 'Outlet Warehouse',
            ],
            'transfer' => [
                'quantity' => $this->transfer_in,
                'percentage' => $this->getTotalStockIn() > 0 
                    ? round(($this->transfer_in / $this->getTotalStockIn()) * 100, 2)
                    : 0,
                'label' => 'Transfer',
            ],
        ];
    }

    /**
     * Get primary stock source
     * NEW METHOD!
     * 
     * @return string
     */
    public function getPrimaryStockSourceAttribute()
    {
        $breakdown = $this->stock_source_breakdown;
        
        $max = 0;
        $primary = 'none';
        
        foreach ($breakdown as $source => $data) {
            if ($data['quantity'] > $max) {
                $max = $data['quantity'];
                $primary = $source;
            }
        }
        
        return $primary;
    }

    /**
     * Check if received from outlet warehouse
     * NEW METHOD!
     * 
     * @return bool
     */
    public function hasOutletWarehouseSource()
    {
        return $this->received_from_outlet_warehouse > 0;
    }

    /**
     * Check if received from branch warehouse
     * NEW METHOD!
     * 
     * @return bool
     */
    public function hasBranchWarehouseSource()
    {
        return $this->received_from_branch_warehouse > 0;
    }

    /**
     * Get stock flow summary
     * NEW METHOD!
     * 
     * @return array
     */
    public function getStockFlowSummaryAttribute()
    {
        return [
            'opening' => $this->opening_stock,
            'in' => [
                'total' => $this->total_stock_in,
                'branch_warehouse' => $this->received_from_branch_warehouse,
                'outlet_warehouse' => $this->received_from_outlet_warehouse,
                'transfer' => $this->transfer_in,
                'adjustment' => $this->adjustments > 0 ? $this->adjustments : 0,
            ],
            'out' => [
                'total' => $this->total_stock_out,
                'usage' => $this->usage,
                'waste' => $this->waste,
                'transfer' => $this->transfer_out,
                'adjustment' => $this->adjustments < 0 ? abs($this->adjustments) : 0,
            ],
            'closing' => $this->closing_stock,
        ];
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'item_id' => 'required|exists:items,id',
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'opening_stock' => 'required|numeric|min:0',
            'closing_stock' => 'required|numeric|min:0',
            'transfer_in' => 'required|numeric|min:0',
            'usage_out' => 'required|numeric|min:0',
            'adjustments' => 'nullable|numeric'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_id.required' => 'Item wajib dipilih',
            'branch_id.required' => 'Branch wajib dipilih',
            'year.required' => 'Tahun wajib diisi',
            'month.required' => 'Bulan wajib diisi',
            'opening_stock.required' => 'Opening stock wajib diisi',
            'closing_stock.required' => 'Closing stock wajib diisi',
            'transfer_in.required' => 'Transfer in wajib diisi',
            'usage_out.required' => 'Usage out wajib diisi'
        ];
    }
}