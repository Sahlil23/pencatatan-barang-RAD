<?php
// filepath: app/Models/BranchWarehouse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BranchWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_name',
        'warehouse_code',
        'address',
        'phone',
        'manager_name',
        'central_warehouse_id',
        'region',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Branch warehouse belongs to central warehouse
     */
    public function centralWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'central_warehouse_id');
    }

    /**
     * Branch warehouse has many branches
     */
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Active branches only
     */
    public function activeBranches()
    {
        return $this->hasMany(Branch::class)->where('is_active', true);
    }

    /**
     * Transactions from central warehouse to this branch warehouse
     */
    public function centralToBranchWarehouseTransactions()
    {
        return $this->hasMany(CentralToBranchWarehouseTransaction::class);
    }

    /**
     * Received transactions (stock IN)
     */
    public function receivedTransactions()
    {
        return $this->hasMany(CentralToBranchWarehouseTransaction::class)
                    ->whereIn('transaction_type', ['TRANSFER_OUT', 'DISTRIBUTION']);
    }

    /**
     * Transactions from this branch warehouse to outlets
     */
    public function branchWarehouseToOutletTransactions()
    {
        return $this->hasMany(BranchWarehouseToOutletTransaction::class);
    }

    /**
     * Distributed transactions (stock OUT)
     */
    public function distributedTransactions()
    {
        return $this->hasMany(BranchWarehouseToOutletTransaction::class)
                    ->whereIn('transaction_type', ['TRANSFER_OUT', 'DISTRIBUTION']);
    }

    /**
     * Monthly balances untuk warehouse ini
     */
    public function branchWarehouseMonthlyBalances()
    {
        return $this->hasMany(BranchWarehouseMonthlyBalance::class);
    }

    /**
     * Current month balances
     */
    public function currentMonthBalances()
    {
        return $this->hasMany(BranchWarehouseMonthlyBalance::class)
                    ->where('year', now()->year)
                    ->where('month', now()->month);
    }

    /**
     * Items dengan stock di warehouse ini
     */
    public function itemsWithStock()
    {
        return $this->belongsToMany(Item::class, 'branch_warehouse_monthly_balances')
                    ->withPivot(['closing_stock', 'year', 'month'])
                    ->wherePivot('closing_stock', '>', 0)
                    ->wherePivot('year', now()->year)
                    ->wherePivot('month', now()->month);
    }

    /**
     * Suppliers yang melayani warehouse ini
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'central_to_branch_warehouse_transactions', 'branch_warehouse_id', 'supplier_id')
                    ->distinct();
    }

    /**
     * Users yang bekerja di warehouse ini
     */
    public function users()
    {
        return $this->hasMany(User::class, 'branch_warehouse_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive warehouses
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope by region
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope by central warehouse
     */
    public function scopeByCentralWarehouse($query, $centralWarehouseId)
    {
        return $query->where('central_warehouse_id', $centralWarehouseId);
    }

    /**
     * Scope warehouses dengan branches
     */
    public function scopeWithBranches($query)
    {
        return $query->whereHas('branches');
    }

    /**
     * Scope warehouses dengan active branches
     */
    public function scopeWithActiveBranches($query)
    {
        return $query->whereHas('activeBranches');
    }

    /**
     * Scope warehouses dengan stock
     */
    public function scopeWithStock($query)
    {
        return $query->whereHas('currentMonthBalances', function($q) {
            $q->where('closing_stock', '>', 0);
        });
    }

    /**
     * Scope warehouses dengan recent activity
     */
    public function scopeWithRecentActivity($query, $days = 30)
    {
        return $query->where(function($q) use ($days) {
            $q->whereHas('receivedTransactions', function($sq) use ($days) {
                $sq->where('transaction_date', '>=', now()->subDays($days));
            })
            ->orWhereHas('distributedTransactions', function($sq) use ($days) {
                $sq->where('transaction_date', '>=', now()->subDays($days));
            });
        });
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Get served branches dengan details
     */
    public function getServedBranches()
    {
        return $this->branches()
                    ->with(['branchMonthlyBalances' => function($query) {
                        $query->where('year', now()->year)
                              ->where('month', now()->month);
                    }])
                    ->get()
                    ->map(function($branch) {
                        $totalStock = $branch->branchMonthlyBalances->sum('closing_stock');
                        $totalItems = $branch->branchMonthlyBalances->count();
                        
                        return [
                            'branch' => $branch,
                            'total_stock' => $totalStock,
                            'total_items' => $totalItems,
                            'last_supply_date' => $this->getLastSupplyDate($branch->id),
                            'distance' => $this->calculateDistance($branch->address ?? '')
                        ];
                    });
    }

    /**
     * Get warehouse performance metrics
     */
    public function getWarehousePerformance($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        // Stock IN (received from central)
        $stockIn = $this->receivedTransactions()
                        ->whereBetween('transaction_date', [$startDate, $endDate])
                        ->sum('quantity');

        // Stock OUT (distributed to branches)
        $stockOut = $this->distributedTransactions()
                         ->whereBetween('transaction_date', [$startDate, $endDate])
                         ->sum('quantity');

        // Transaction counts
        $inboundTransactions = $this->receivedTransactions()
                                    ->whereBetween('transaction_date', [$startDate, $endDate])
                                    ->count();

        $outboundTransactions = $this->distributedTransactions()
                                     ->whereBetween('transaction_date', [$startDate, $endDate])
                                     ->count();

        // Unique items handled
        $uniqueItemsIn = $this->receivedTransactions()
                              ->whereBetween('transaction_date', [$startDate, $endDate])
                              ->distinct('item_id')
                              ->count('item_id');

        $uniqueItemsOut = $this->distributedTransactions()
                               ->whereBetween('transaction_date', [$startDate, $endDate])
                               ->distinct('item_id')
                               ->count('item_id');

        // Served branches count
        $servedBranches = $this->distributedTransactions()
                               ->whereBetween('transaction_date', [$startDate, $endDate])
                               ->distinct('branch_id')
                               ->count('branch_id');

        // Calculate efficiency metrics
        $throughputRate = $stockIn > 0 ? ($stockOut / $stockIn) * 100 : 0;
        $distributionEfficiency = $inboundTransactions > 0 ? ($outboundTransactions / $inboundTransactions) * 100 : 0;

        // Average response time (days between receiving and distributing)
        $avgResponseTime = $this->calculateAverageResponseTime($startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate) + 1
            ],
            'stock_movement' => [
                'stock_in' => $stockIn,
                'stock_out' => $stockOut,
                'net_change' => $stockIn - $stockOut,
                'throughput_rate' => round($throughputRate, 2)
            ],
            'transactions' => [
                'inbound_count' => $inboundTransactions,
                'outbound_count' => $outboundTransactions,
                'total_transactions' => $inboundTransactions + $outboundTransactions,
                'distribution_efficiency' => round($distributionEfficiency, 2)
            ],
            'items' => [
                'unique_items_received' => $uniqueItemsIn,
                'unique_items_distributed' => $uniqueItemsOut,
                'item_variety_score' => $uniqueItemsIn > 0 ? ($uniqueItemsOut / $uniqueItemsIn) * 100 : 0
            ],
            'branches' => [
                'total_branches_served' => $this->branches()->count(),
                'active_branches_served' => $servedBranches,
                'branch_coverage' => $this->branches()->count() > 0 ? ($servedBranches / $this->branches()->count()) * 100 : 0
            ],
            'efficiency' => [
                'avg_response_time_days' => $avgResponseTime,
                'overall_efficiency_score' => $this->calculateOverallEfficiency($throughputRate, $distributionEfficiency, $avgResponseTime)
            ]
        ];
    }

    /**
     * Get current stock summary
     */
    public function getStockSummary($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $balances = $this->branchWarehouseMonthlyBalances()
                         ->where('year', $year)
                         ->where('month', $month)
                         ->with('item')
                         ->get();

        $summary = $balances->reduce(function($carry, $balance) {
            $carry['total_items']++;
            $carry['total_closing_stock'] += $balance->closing_stock;
            $carry['total_stock_in'] += $balance->stock_in;
            $carry['total_stock_out'] += $balance->stock_out;

            if ($balance->closing_stock <= 0) {
                $carry['out_of_stock_items']++;
            } elseif ($balance->item && $balance->closing_stock <= $balance->item->low_stock_threshold) {
                $carry['low_stock_items']++;
            } else {
                $carry['normal_stock_items']++;
            }

            return $carry;
        }, [
            'total_items' => 0,
            'total_closing_stock' => 0,
            'total_stock_in' => 0,
            'total_stock_out' => 0,
            'out_of_stock_items' => 0,
            'low_stock_items' => 0,
            'normal_stock_items' => 0
        ]);

        // Calculate additional metrics
        $summary['stock_turnover'] = $summary['total_closing_stock'] > 0 ? 
            $summary['total_stock_out'] / $summary['total_closing_stock'] : 0;
        
        $summary['stock_utilization'] = $summary['total_stock_in'] > 0 ? 
            ($summary['total_stock_out'] / $summary['total_stock_in']) * 100 : 0;

        $summary['stock_health_score'] = $summary['total_items'] > 0 ? 
            (($summary['normal_stock_items'] * 100) + ($summary['low_stock_items'] * 50)) / $summary['total_items'] : 0;

        // Add category breakdown
        $summary['category_breakdown'] = $balances->groupBy('item.category_id')
                                                 ->map(function($categoryBalances) {
                                                     return [
                                                         'category' => $categoryBalances->first()->item->category ?? null,
                                                         'item_count' => $categoryBalances->count(),
                                                         'total_stock' => $categoryBalances->sum('closing_stock'),
                                                         'stock_value' => $categoryBalances->sum(function($balance) {
                                                             return $balance->closing_stock * ($balance->item->unit_cost ?? 0);
                                                         })
                                                     ];
                                                 });

        return $summary;
    }

    /**
     * Check if can receive from central warehouse
     */
    public function canReceiveFromCentral()
    {
        if (!$this->is_active) {
            return [
                'can_receive' => false,
                'reason' => 'Warehouse tidak aktif'
            ];
        }

        if (!$this->centralWarehouse) {
            return [
                'can_receive' => false,
                'reason' => 'Central warehouse tidak ditemukan'
            ];
        }

        if (!$this->centralWarehouse->is_active) {
            return [
                'can_receive' => false,
                'reason' => 'Central warehouse tidak aktif'
            ];
        }

        // Check capacity (optional - implement based on business rules)
        $currentStock = $this->currentMonthBalances()->sum('closing_stock');
        $maxCapacity = $this->max_capacity ?? 10000; // Default or from settings

        if ($currentStock >= $maxCapacity * 0.95) {
            return [
                'can_receive' => false,
                'reason' => 'Warehouse hampir penuh (95% kapasitas)'
            ];
        }

        return [
            'can_receive' => true,
            'reason' => 'Warehouse siap menerima stock',
            'available_capacity' => $maxCapacity - $currentStock
        ];
    }

    /**
     * Check if can supply to specific branch
     */
    public function canSupplyToBranch($branchId)
    {
        if (!$this->is_active) {
            return [
                'can_supply' => false,
                'reason' => 'Warehouse tidak aktif'
            ];
        }

        $branch = $this->branches()->find($branchId);
        
        if (!$branch) {
            return [
                'can_supply' => false,
                'reason' => 'Branch tidak terdaftar di warehouse ini'
            ];
        }

        if (!$branch->is_active) {
            return [
                'can_supply' => false,
                'reason' => 'Branch tidak aktif'
            ];
        }

        // Check if warehouse has stock
        $hasStock = $this->currentMonthBalances()
                         ->where('closing_stock', '>', 0)
                         ->exists();

        if (!$hasStock) {
            return [
                'can_supply' => false,
                'reason' => 'Warehouse tidak memiliki stock'
            ];
        }

        return [
            'can_supply' => true,
            'reason' => 'Warehouse dapat supply ke branch',
            'branch' => $branch,
            'available_items' => $this->getAvailableItemsForBranch($branchId)
        ];
    }

    /**
     * Activate warehouse
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
        Log::info("Branch warehouse activated: {$this->warehouse_name} ({$this->warehouse_code})");
        return $this;
    }

    /**
     * Deactivate warehouse
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
        Log::info("Branch warehouse deactivated: {$this->warehouse_name} ({$this->warehouse_code})");
        return $this;
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get last supply date to specific branch
     */
    protected function getLastSupplyDate($branchId)
    {
        return $this->distributedTransactions()
                    ->where('branch_id', $branchId)
                    ->latest('transaction_date')
                    ->value('transaction_date');
    }

    /**
     * Calculate distance to branch (placeholder - implement with actual logic)
     */
    protected function calculateDistance($branchAddress)
    {
        // Implement actual distance calculation if needed
        // For now, return placeholder
        return 'N/A';
    }

    /**
     * Calculate average response time
     */
    protected function calculateAverageResponseTime($startDate, $endDate)
    {
        $distributions = $this->distributedTransactions()
                              ->whereBetween('transaction_date', [$startDate, $endDate])
                              ->whereNotNull('central_to_branch_transaction_id')
                              ->with('centralToBranchTransaction')
                              ->get();

        if ($distributions->isEmpty()) {
            return 0;
        }

        $totalDays = $distributions->sum(function($distribution) {
            $receiveDate = Carbon::parse($distribution->centralToBranchTransaction->transaction_date);
            $distributeDate = Carbon::parse($distribution->transaction_date);
            return $receiveDate->diffInDays($distributeDate);
        });

        return round($totalDays / $distributions->count(), 2);
    }

    /**
     * Calculate overall efficiency score
     */
    protected function calculateOverallEfficiency($throughputRate, $distributionEfficiency, $avgResponseTime)
    {
        // Weighted efficiency calculation
        $throughputScore = min($throughputRate, 100) * 0.4;
        $distributionScore = min($distributionEfficiency, 100) * 0.3;
        $responseScore = $avgResponseTime > 0 ? max(0, 100 - ($avgResponseTime * 10)) * 0.3 : 100 * 0.3;

        return round($throughputScore + $distributionScore + $responseScore, 2);
    }

    /**
     * Get available items untuk specific branch
     */
    protected function getAvailableItemsForBranch($branchId)
    {
        return $this->currentMonthBalances()
                    ->where('closing_stock', '>', 0)
                    ->with('item')
                    ->get()
                    ->map(function($balance) {
                        return [
                            'item' => $balance->item,
                            'available_stock' => $balance->closing_stock,
                            'last_distributed' => $this->getLastSupplyDate($balance->item_id)
                        ];
                    });
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Get warehouse by code
     */
    public static function findByCode($code)
    {
        return static::where('warehouse_code', $code)->first();
    }

    /**
     * Get warehouses by region
     */
    public static function getByRegion($region)
    {
        return static::byRegion($region)->active()->get();
    }

    /**
     * Get warehouse performance comparison
     */
    public static function getPerformanceComparison($startDate = null, $endDate = null)
    {
        return static::active()
                     ->get()
                     ->map(function($warehouse) use ($startDate, $endDate) {
                         $performance = $warehouse->getWarehousePerformance($startDate, $endDate);
                         return [
                             'warehouse' => $warehouse,
                             'performance' => $performance,
                             'efficiency_score' => $performance['efficiency']['overall_efficiency_score']
                         ];
                     })
                     ->sortByDesc('efficiency_score');
    }

    /**
     * Get regional summary
     */
    public static function getRegionalSummary()
    {
        return static::active()
                     ->selectRaw('
                         region,
                         COUNT(*) as warehouse_count,
                         SUM((SELECT COUNT(*) FROM branches WHERE branch_warehouse_id = branch_warehouses.id)) as total_branches
                     ')
                     ->groupBy('region')
                     ->get();
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get full warehouse name dengan region
     */
    public function getFullNameAttribute()
    {
        return "{$this->warehouse_name} ({$this->region})";
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Get central warehouse name
     */
    public function getCentralWarehouseNameAttribute()
    {
        return $this->centralWarehouse ? $this->centralWarehouse->warehouse_name : 'N/A';
    }

    /**
     * Get served branches count
     */
    public function getServedBranchesCountAttribute()
    {
        return $this->branches()->count();
    }

    /**
     * Get active branches count
     */
    public function getActiveBranchesCountAttribute()
    {
        return $this->activeBranches()->count();
    }

    /**
     * Get current stock count
     */
    public function getCurrentStockCountAttribute()
    {
        return $this->currentMonthBalances()->sum('closing_stock');
    }

    /**
     * Get current items count
     */
    public function getCurrentItemsCountAttribute()
    {
        return $this->currentMonthBalances()->where('closing_stock', '>', 0)->count();
    }

    /**
     * Check if warehouse has stock
     */
    public function getHasStockAttribute()
    {
        return $this->current_stock_count > 0;
    }

    /**
     * Get warehouse efficiency rating
     */
    public function getEfficiencyRatingAttribute()
    {
        $performance = $this->getWarehousePerformance();
        $score = $performance['efficiency']['overall_efficiency_score'];

        if ($score >= 90) return 'Excellent';
        if ($score >= 80) return 'Good';
        if ($score >= 70) return 'Average';
        if ($score >= 60) return 'Below Average';
        return 'Poor';
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules($id = null)
    {
        return [
            'warehouse_name' => 'required|string|max:255',
            'warehouse_code' => 'required|string|max:10|unique:branch_warehouses,warehouse_code,' . $id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'central_warehouse_id' => 'nullable|exists:warehouses,id',
            'region' => 'required|string|max:100',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages()
    {
        return [
            'warehouse_name.required' => 'Nama warehouse wajib diisi',
            'warehouse_code.required' => 'Kode warehouse wajib diisi',
            'warehouse_code.unique' => 'Kode warehouse sudah digunakan',
            'region.required' => 'Region wajib diisi',
            'central_warehouse_id.exists' => 'Central warehouse tidak valid'
        ];
    }
}