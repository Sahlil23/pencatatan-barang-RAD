<?php
// filepath: app/Models/StockPeriod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StockPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'year',
        'month',
        'status',
        'closed_by',
        'closed_at',
        'opening_balance_confirmed',
        'closing_balance_confirmed',
        'total_opening_value',
        'total_closing_value',
        'total_transactions',
        'notes'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'closed_at' => 'datetime',
        'opening_balance_confirmed' => 'boolean',
        'closing_balance_confirmed' => 'boolean',
        'total_opening_value' => 'decimal:2',
        'total_closing_value' => 'decimal:2',
        'total_transactions' => 'integer'
    ];

    protected $dates = [
        'closed_at',
        'created_at',
        'updated_at'
    ];

    // Status Constants
    const STATUS_OPEN = 'open';
    const STATUS_CLOSING = 'closing';
    const STATUS_CLOSED = 'closed';

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Period belongs to branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Period belongs to warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * User who closed the period
     */
    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Related stock balances (central or branch)
     */
    public function centralStockBalances()
    {
        return $this->hasMany(CentralStockBalance::class, 'warehouse_id', 'warehouse_id')
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    public function branchStockBalances()
    {
        return $this->hasMany(BranchMonthlyBalance::class, 'warehouse_id', 'warehouse_id')
            ->where('branch_id', $this->branch_id)
            ->where('year', $this->year)
            ->where('month', $this->month);
    }

    /**
     * Related stock transactions for this period
     */
    public function centralStockTransactions()
    {
        return $this->hasMany(CentralStockTransaction::class, 'warehouse_id', 'warehouse_id')
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month);
    }

    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class, 'warehouse_id', 'warehouse_id')
            ->where('branch_id', $this->branch_id)
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month);
    }

    public function kitchenStockTransactions()
    {
        return $this->hasMany(KitchenStockTransaction::class, 'branch_id', 'branch_id')
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope untuk specific status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk open periods
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope untuk closed periods
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /**
     * Scope untuk current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('year', now()->year)
                    ->where('month', now()->month);
    }

    /**
     * Scope untuk specific period
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope untuk specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope untuk specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Get atau create period untuk branch/warehouse/period tertentu
     */
    public static function getOrCreate($branchId, $warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return self::firstOrCreate(
            [
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'year' => $year,
                'month' => $month
            ],
            [
                'status' => self::STATUS_OPEN,
                'opening_balance_confirmed' => false,
                'closing_balance_confirmed' => false,
                'total_opening_value' => 0,
                'total_closing_value' => 0,
                'total_transactions' => 0
            ]
        );
    }

    /**
     * Check if period is open for transactions
     */
    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if period is closed
     */
    public function isClosed()
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Check if period can be closed
     */
    public function canBeClosed()
    {
        // Period hanya bisa di-close jika:
        // 1. Status masih open atau closing
        // 2. Semua balances sudah confirmed
        // 3. Tidak ada pending transactions
        
        if ($this->status === self::STATUS_CLOSED) {
            return false;
        }

        // Check if all balances confirmed
        if (!$this->opening_balance_confirmed || !$this->closing_balance_confirmed) {
            return false;
        }

        // Check if ada pending transactions untuk period ini
        $pendingTransactions = $this->getPendingTransactionsCount();
        
        return $pendingTransactions === 0;
    }

    /**
     * Get pending transactions count
     */
    public function getPendingTransactionsCount()
    {
        $count = 0;

        if ($this->warehouse->isCentral()) {
            $count += $this->centralStockTransactions()
                ->whereDate('created_at', '>', $this->getEndOfPeriod())
                ->count();
        } else {
            $count += $this->branchStockTransactions()
                ->whereDate('created_at', '>', $this->getEndOfPeriod())
                ->count();
                
            $count += $this->kitchenStockTransactions()
                ->whereDate('created_at', '>', $this->getEndOfPeriod())
                ->count();
        }

        return $count;
    }

    /**
     * Confirm opening balances
     */
    public function confirmOpeningBalances($userId = null)
    {
        if ($this->opening_balance_confirmed) {
            throw new \Exception('Opening balances already confirmed');
        }

        // Calculate total opening value
        $totalOpeningValue = $this->calculateOpeningValue();

        $this->update([
            'opening_balance_confirmed' => true,
            'total_opening_value' => $totalOpeningValue
        ]);

        return $this;
    }

    /**
     * Confirm closing balances
     */
    public function confirmClosingBalances($userId = null)
    {
        if ($this->closing_balance_confirmed) {
            throw new \Exception('Closing balances already confirmed');
        }

        // Recalculate all balances dari transactions
        $this->recalculateBalances();

        // Calculate total closing value dan transaction count
        $totalClosingValue = $this->calculateClosingValue();
        $totalTransactions = $this->calculateTotalTransactions();

        $this->update([
            'closing_balance_confirmed' => true,
            'total_closing_value' => $totalClosingValue,
            'total_transactions' => $totalTransactions
        ]);

        return $this;
    }

    /**
     * Close period
     */
    public function closePeriod($userId, $notes = null)
    {
        if (!$this->canBeClosed()) {
            throw new \Exception('Period cannot be closed. Check opening/closing balance confirmation and pending transactions.');
        }

        $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_by' => $userId,
            'closed_at' => now(),
            'notes' => $notes
        ]);

        // Create next period opening balances
        $this->createNextPeriodOpeningBalances();

        return $this;
    }

    /**
     * Reopen period (only if next period belum ada transactions)
     */
    public function reopenPeriod($userId, $reason = null)
    {
        if ($this->status !== self::STATUS_CLOSED) {
            throw new \Exception('Only closed periods can be reopened');
        }

        // Check if next period sudah ada transactions
        $nextPeriod = $this->getNextPeriod();
        if ($nextPeriod && $nextPeriod->total_transactions > 0) {
            throw new \Exception('Cannot reopen period. Next period already has transactions.');
        }

        $this->update([
            'status' => self::STATUS_OPEN,
            'closed_by' => null,
            'closed_at' => null,
            'notes' => $reason
        ]);

        return $this;
    }

    /**
     * Recalculate all balances untuk period ini
     */
    public function recalculateBalances()
    {
        if ($this->warehouse->isCentral()) {
            // Recalculate central stock balances
            $balances = $this->centralStockBalances;
            foreach ($balances as $balance) {
                $balance->recalculateFromTransactions();
            }
        } else {
            // Recalculate branch stock balances
            $balances = $this->branchStockBalances;
            foreach ($balances as $balance) {
                $balance->recalculateFromTransactions();
            }

            // Recalculate kitchen balances
            $kitchenBalances = MonthlyKitchenStockBalance::where([
                'branch_id' => $this->branch_id,
                'year' => $this->year,
                'month' => $this->month
            ])->get();

            foreach ($kitchenBalances as $balance) {
                $balance->recalculateFromTransactions();
            }
        }

        return $this;
    }

    /**
     * Calculate opening value
     */
    protected function calculateOpeningValue()
    {
        $totalValue = 0;

        if ($this->warehouse->isCentral()) {
            $balances = $this->centralStockBalances()->with('item')->get();
            foreach ($balances as $balance) {
                $totalValue += $balance->opening_stock * ($balance->item->unit_cost ?? 0);
            }
        } else {
            $balances = $this->branchStockBalances()->with('item')->get();
            foreach ($balances as $balance) {
                $totalValue += $balance->opening_stock * ($balance->item->unit_cost ?? 0);
            }
        }

        return $totalValue;
    }

    /**
     * Calculate closing value
     */
    protected function calculateClosingValue()
    {
        $totalValue = 0;

        if ($this->warehouse->isCentral()) {
            $balances = $this->centralStockBalances()->with('item')->get();
            foreach ($balances as $balance) {
                $totalValue += $balance->closing_stock * ($balance->item->unit_cost ?? 0);
            }
        } else {
            $balances = $this->branchStockBalances()->with('item')->get();
            foreach ($balances as $balance) {
                $totalValue += $balance->closing_stock * ($balance->item->unit_cost ?? 0);
            }

            // Add kitchen stock value
            $kitchenBalances = MonthlyKitchenStockBalance::where([
                'branch_id' => $this->branch_id,
                'year' => $this->year,
                'month' => $this->month
            ])->with('item')->get();

            foreach ($kitchenBalances as $balance) {
                $totalValue += $balance->closing_stock * ($balance->item->unit_cost ?? 0);
            }
        }

        return $totalValue;
    }

    /**
     * Calculate total transactions
     */
    protected function calculateTotalTransactions()
    {
        $count = 0;

        if ($this->warehouse->isCentral()) {
            $count += $this->centralStockTransactions()->count();
        } else {
            $count += $this->branchStockTransactions()->count();
            $count += $this->kitchenStockTransactions()->count();
        }

        return $count;
    }

    /**
     * Create opening balances untuk period selanjutnya
     */
    protected function createNextPeriodOpeningBalances()
    {
        $nextDate = Carbon::create($this->year, $this->month, 1)->addMonth();
        
        // Create next period
        $nextPeriod = self::getOrCreate(
            $this->branch_id,
            $this->warehouse_id,
            $nextDate->year,
            $nextDate->month
        );

        // Copy closing balances sebagai opening balances
        if ($this->warehouse->isCentral()) {
            $currentBalances = $this->centralStockBalances;
            foreach ($currentBalances as $balance) {
                CentralStockBalance::getOrCreateBalance(
                    $balance->item_id,
                    $balance->warehouse_id,
                    $nextDate->year,
                    $nextDate->month
                );
            }
        } else {
            $currentBalances = $this->branchStockBalances;
            foreach ($currentBalances as $balance) {
                BranchMonthlyBalance::getOrCreateBalance(
                    $balance->item_id,
                    $balance->branch_id,
                    $balance->warehouse_id,
                    $nextDate->year,
                    $nextDate->month
                );
            }

            // Kitchen balances
            $kitchenBalances = MonthlyKitchenStockBalance::where([
                'branch_id' => $this->branch_id,
                'year' => $this->year,
                'month' => $this->month
            ])->get();

            foreach ($kitchenBalances as $balance) {
                MonthlyKitchenStockBalance::getOrCreateBalance(
                    $balance->item_id,
                    $balance->branch_id,
                    $nextDate->year,
                    $nextDate->month
                );
            }
        }

        return $nextPeriod;
    }

    /**
     * Get next period
     */
    public function getNextPeriod()
    {
        $nextDate = Carbon::create($this->year, $this->month, 1)->addMonth();
        
        return self::where([
            'branch_id' => $this->branch_id,
            'warehouse_id' => $this->warehouse_id,
            'year' => $nextDate->year,
            'month' => $nextDate->month
        ])->first();
    }

    /**
     * Get previous period
     */
    public function getPreviousPeriod()
    {
        $prevDate = Carbon::create($this->year, $this->month, 1)->subMonth();
        
        return self::where([
            'branch_id' => $this->branch_id,
            'warehouse_id' => $this->warehouse_id,
            'year' => $prevDate->year,
            'month' => $prevDate->month
        ])->first();
    }

    /**
     * Get period performance metrics
     */
    public function getPerformanceMetrics()
    {
        $stockTurnover = $this->total_opening_value > 0 ? 
            ($this->total_closing_value - $this->total_opening_value) / $this->total_opening_value * 100 : 0;

        return [
            'total_opening_value' => $this->total_opening_value,
            'total_closing_value' => $this->total_closing_value,
            'stock_movement_value' => $this->total_closing_value - $this->total_opening_value,
            'stock_turnover_percentage' => $stockTurnover,
            'total_transactions' => $this->total_transactions,
            'avg_transaction_value' => $this->total_transactions > 0 ? 
                abs($this->total_closing_value - $this->total_opening_value) / $this->total_transactions : 0,
            'period_status' => $this->status,
            'days_in_period' => Carbon::create($this->year, $this->month, 1)->daysInMonth,
            'transactions_per_day' => $this->total_transactions / Carbon::create($this->year, $this->month, 1)->daysInMonth
        ];
    }

    /**
     * Get start dan end of period
     */
    public function getStartOfPeriod()
    {
        return Carbon::create($this->year, $this->month, 1)->startOfDay();
    }

    public function getEndOfPeriod()
    {
        return Carbon::create($this->year, $this->month, 1)->endOfMonth()->endOfDay();
    }

    // ========================================
    // STATIC ANALYTICS METHODS
    // ========================================

    /**
     * Get periods summary untuk branch/warehouse
     */
    public static function getPeriodsSummary($branchId = null, $warehouseId = null, $limit = 12)
    {
        $query = self::query();

        if ($branchId) {
            $query->forBranch($branchId);
        }

        if ($warehouseId) {
            $query->forWarehouse($warehouseId);
        }

        return $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($period) {
                return array_merge(
                    $period->toArray(),
                    $period->getPerformanceMetrics()
                );
            });
    }

    /**
     * Get open periods yang perlu di-close
     */
    public static function getPeriodsNeedingClosure()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get periods yang masih open dan bukan current month
        return self::open()
            ->where(function($query) use ($currentYear, $currentMonth) {
                $query->where('year', '<', $currentYear)
                      ->orWhere(function($q) use ($currentYear, $currentMonth) {
                          $q->where('year', $currentYear)
                            ->where('month', '<', $currentMonth);
                      });
            })
            ->with('branch', 'warehouse')
            ->get();
    }

    // ========================================
    // ATTRIBUTES
    // ========================================

    /**
     * Get period display name
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
     * Get status badge untuk UI
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_OPEN => '<span class="badge bg-success">Open</span>',
            self::STATUS_CLOSING => '<span class="badge bg-warning">Closing</span>',
            self::STATUS_CLOSED => '<span class="badge bg-danger">Closed</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get confirmation status
     */
    public function getConfirmationStatusAttribute()
    {
        $opening = $this->opening_balance_confirmed ? '✓' : '✗';
        $closing = $this->closing_balance_confirmed ? '✓' : '✗';
        
        return "Opening: {$opening} | Closing: {$closing}";
    }

    /**
     * Get progress percentage untuk closing
     */
    public function getClosingProgressAttribute()
    {
        $progress = 0;
        
        if ($this->opening_balance_confirmed) {
            $progress += 50;
        }
        
        if ($this->closing_balance_confirmed) {
            $progress += 50;
        }
        
        return $progress;
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'status' => 'required|in:' . implode(',', [self::STATUS_OPEN, self::STATUS_CLOSING, self::STATUS_CLOSED]),
            'notes' => 'nullable|string|max:500'
        ];
    }

    public static function validationMessages()
    {
        return [
            'branch_id.required' => 'Branch wajib dipilih',
            'warehouse_id.required' => 'Warehouse wajib dipilih',
            'year.required' => 'Tahun wajib diisi',
            'month.required' => 'Bulan wajib diisi',
            'status.required' => 'Status wajib dipilih'
        ];
    }
}