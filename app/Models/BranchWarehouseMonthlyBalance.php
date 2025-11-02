<?php
// filepath: app/Models/BranchWarehouseMonthlyBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchWarehouseMonthlyBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'branch_warehouse_id',
        'year',
        'month',
        'opening_stock',
        'closing_stock',
        'stock_in',
        'stock_out',
        'adjustments',
        'total_receipts_from_central',
        'total_distributions_to_branches',
        'total_returns_from_branches',
        'total_returns_to_central',
        'avg_unit_cost',
        'total_value',
        'is_closed',
        'closed_at',
        'closed_by',
        'notes'
    ];

    protected $casts = [
        'opening_stock' => 'decimal:3',
        'closing_stock' => 'decimal:3',
        'stock_in' => 'decimal:3',
        'stock_out' => 'decimal:3',
        'adjustments' => 'decimal:3',
        'total_receipts_from_central' => 'decimal:3',
        'total_distributions_to_branches' => 'decimal:3',
        'total_returns_from_branches' => 'decimal:3',
        'total_returns_to_central' => 'decimal:3',
        'avg_unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
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
     * Balance belongs to branch warehouse
     */
    public function branchWarehouse()
    {
        return $this->belongsTo(BranchWarehouse::class);
    }

    /**
     * User who closed the balance
     */
    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Related central to branch transactions untuk period ini
     */
    public function centralToBranchTransactions()
    {
        return $this->hasMany(CentralToBranchWarehouseTransaction::class, 'branch_warehouse_id', 'branch_warehouse_id')
                    ->whereYear('transaction_date', $this->year)
                    ->whereMonth('transaction_date', $this->month);
    }

    /**
     * Related warehouse to outlet transactions untuk period ini
     */
    public function warehouseToOutletTransactions()
    {
        return $this->hasMany(BranchWarehouseToOutletTransaction::class, 'branch_warehouse_id', 'branch_warehouse_id')
                    ->whereYear('transaction_date', $this->year)
                    ->whereMonth('transaction_date', $this->month);
    }

    /**
     * Receipts from central (stock IN)
     */
    public function receiptsFromCentral()
    {
        return $this->centralToBranchTransactions()
                    ->where('item_id', $this->item_id)
                    ->stockOut(); // From central perspective, it's OUT, to warehouse it's IN
    }

    /**
     * Distributions to branches (stock OUT)
     */
    public function distributionsToBranches()
    {
        return $this->warehouseToOutletTransactions()
                    ->where('item_id', $this->item_id)
                    ->stockOut();
    }

    /**
     * Returns from branches (stock IN)
     */
    public function returnsFromBranches()
    {
        return $this->warehouseToOutletTransactions()
                    ->where('item_id', $this->item_id)
                    ->stockIn();
    }

    /**
     * Returns to central (stock OUT)
     */
    public function returnsToCentral()
    {
        return $this->centralToBranchTransactions()
                    ->where('item_id', $this->item_id)
                    ->stockIn(); // Return IN to central, OUT from warehouse
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope by warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('branch_warehouse_id', $warehouseId);
    }

    /**
     * Scope by item
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope by period
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope current period
     */
    public function scopeCurrentPeriod($query)
    {
        return $query->where('year', now()->year)->where('month', now()->month);
    }

    /**
     * Scope previous period
     */
    public function scopePreviousPeriod($query)
    {
        $previousMonth = now()->subMonth();
        return $query->where('year', $previousMonth->year)->where('month', $previousMonth->month);
    }

    /**
     * Scope closed balances
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    /**
     * Scope open balances
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Scope with stock
     */
    public function scopeWithStock($query)
    {
        return $query->where('closing_stock', '>', 0);
    }

    /**
     * Scope low stock (based on item threshold)
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('item', function($q) {
            $q->whereRaw('branch_warehouse_monthly_balances.closing_stock <= items.low_stock_threshold');
        });
    }

    /**
     * Scope out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('closing_stock', '<=', 0);
    }

    /**
     * Scope by year
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope recent periods
     */
    public function scopeRecentPeriods($query, $months = 6)
    {
        $startDate = now()->subMonths($months);
        return $query->where(function($q) use ($startDate) {
            $q->where('year', '>', $startDate->year)
              ->orWhere(function($sq) use ($startDate) {
                  $sq->where('year', $startDate->year)
                     ->where('month', '>=', $startDate->month);
              });
        });
    }

    // ========================================
    // FACTORY METHODS
    // ========================================

    /**
     * Get or create balance untuk item & warehouse pada period tertentu
     */
    public static function getOrCreateBalance($itemId, $warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $balance = static::where('item_id', $itemId)
                         ->where('branch_warehouse_id', $warehouseId)
                         ->where('year', $year)
                         ->where('month', $month)
                         ->first();

        if (!$balance) {
            // Get opening stock from previous month
            $openingStock = static::getPreviousMonthClosingStock($itemId, $warehouseId, $year, $month);

            $balance = static::create([
                'item_id' => $itemId,
                'branch_warehouse_id' => $warehouseId,
                'year' => $year,
                'month' => $month,
                'opening_stock' => $openingStock,
                'closing_stock' => $openingStock,
                'stock_in' => 0,
                'stock_out' => 0,
                'adjustments' => 0,
                'total_receipts_from_central' => 0,
                'total_distributions_to_branches' => 0,
                'total_returns_from_branches' => 0,
                'total_returns_to_central' => 0,
                'avg_unit_cost' => 0,
                'total_value' => 0,
                'is_closed' => false
            ]);

            Log::info("Created new branch warehouse balance", [
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'period' => "{$year}-{$month}",
                'opening_stock' => $openingStock
            ]);
        }

        return $balance;
    }

    /**
     * Create balances untuk semua items di warehouse untuk period tertentu
     */
    public static function createWarehousePeriodBalances($warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        DB::beginTransaction();
        try {
            // Get all items yang pernah ada di warehouse ini
            $itemIds = static::where('branch_warehouse_id', $warehouseId)
                            ->distinct('item_id')
                            ->pluck('item_id');

            // Add items dari current transactions
            $transactionItemIds = CentralToBranchWarehouseTransaction::where('branch_warehouse_id', $warehouseId)
                                                                    ->whereYear('transaction_date', $year)
                                                                    ->whereMonth('transaction_date', $month)
                                                                    ->distinct('item_id')
                                                                    ->pluck('item_id');

            $allItemIds = $itemIds->merge($transactionItemIds)->unique();

            $createdBalances = [];
            foreach ($allItemIds as $itemId) {
                $balance = static::getOrCreateBalance($itemId, $warehouseId, $year, $month);
                $createdBalances[] = $balance;
            }

            DB::commit();

            Log::info("Created warehouse period balances", [
                'warehouse_id' => $warehouseId,
                'period' => "{$year}-{$month}",
                'balances_created' => count($createdBalances)
            ]);

            return $createdBalances;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Update movement (IN atau OUT)
     */
    public function updateMovement($direction, $quantity, $unitCost = 0, $transactionType = null)
    {
        DB::beginTransaction();
        try {
            $oldClosingStock = $this->closing_stock;

            if ($direction === 'IN') {
                $this->stock_in += $quantity;
                $this->closing_stock += $quantity;

                // Update specific movement types
                if ($transactionType) {
                    switch ($transactionType) {
                        case CentralToBranchWarehouseTransaction::TYPE_DISTRIBUTION:
                        case CentralToBranchWarehouseTransaction::TYPE_TRANSFER_OUT:
                        case CentralToBranchWarehouseTransaction::TYPE_EMERGENCY_SUPPLY:
                            $this->total_receipts_from_central += $quantity;
                            break;
                        case BranchWarehouseToOutletTransaction::TYPE_RETURN_IN:
                            $this->total_returns_from_branches += $quantity;
                            break;
                    }
                }

            } elseif ($direction === 'OUT') {
                $this->stock_out += $quantity;
                $this->closing_stock -= $quantity;

                // Update specific movement types
                if ($transactionType) {
                    switch ($transactionType) {
                        case BranchWarehouseToOutletTransaction::TYPE_DISTRIBUTION:
                        case BranchWarehouseToOutletTransaction::TYPE_TRANSFER_OUT:
                        case BranchWarehouseToOutletTransaction::TYPE_EMERGENCY_SUPPLY:
                            $this->total_distributions_to_branches += $quantity;
                            break;
                        case CentralToBranchWarehouseTransaction::TYPE_RETURN_IN:
                            $this->total_returns_to_central += $quantity;
                            break;
                    }
                }
            }

            // Update average unit cost dengan weighted average
            if ($unitCost > 0 && $direction === 'IN') {
                $totalValue = ($oldClosingStock * $this->avg_unit_cost) + ($quantity * $unitCost);
                $this->avg_unit_cost = $this->closing_stock > 0 ? $totalValue / $this->closing_stock : $unitCost;
            }

            // Update total value
            $this->total_value = $this->closing_stock * $this->avg_unit_cost;

            $this->save();

            DB::commit();

            Log::debug("Updated warehouse balance movement", [
                'balance_id' => $this->id,
                'direction' => $direction,
                'quantity' => $quantity,
                'old_closing_stock' => $oldClosingStock,
                'new_closing_stock' => $this->closing_stock
            ]);

            return $this;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Add adjustment
     */
    public function addAdjustment($quantity, $reason = null)
    {
        $this->adjustments += $quantity;
        $this->closing_stock += $quantity;
        $this->total_value = $this->closing_stock * $this->avg_unit_cost;
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\nAdjustment: {$quantity} - {$reason}";
        }

        $this->save();

        Log::info("Added warehouse balance adjustment", [
            'balance_id' => $this->id,
            'adjustment' => $quantity,
            'reason' => $reason
        ]);

        return $this;
    }

    /**
     * Recalculate balance dari transactions
     */
    public function recalculateFromTransactions($recalculateOpening = false)
    {
        DB::beginTransaction();
        try {
            // Reset calculated fields
            $this->stock_in = 0;
            $this->stock_out = 0;
            $this->total_receipts_from_central = 0;
            $this->total_distributions_to_branches = 0;
            $this->total_returns_from_branches = 0;
            $this->total_returns_to_central = 0;

            // Recalculate opening stock if requested
            if ($recalculateOpening) {
                $this->opening_stock = static::getPreviousMonthClosingStock(
                    $this->item_id, 
                    $this->branch_warehouse_id, 
                    $this->year, 
                    $this->month
                );
            }

            // Calculate receipts from central
            $receiptsFromCentral = $this->receiptsFromCentral()->sum('quantity');
            $this->total_receipts_from_central = $receiptsFromCentral;
            $this->stock_in += $receiptsFromCentral;

            // Calculate returns from branches
            $returnsFromBranches = $this->returnsFromBranches()->sum('quantity');
            $this->total_returns_from_branches = $returnsFromBranches;
            $this->stock_in += $returnsFromBranches;

            // Calculate distributions to branches
            $distributionsToBranches = $this->distributionsToBranches()->sum('quantity');
            $this->total_distributions_to_branches = $distributionsToBranches;
            $this->stock_out += $distributionsToBranches;

            // Calculate returns to central
            $returnsToCentral = $this->returnsToCentral()->sum('quantity');
            $this->total_returns_to_central = $returnsToCentral;
            $this->stock_out += $returnsToCentral;

            // Calculate closing stock
            $this->closing_stock = $this->opening_stock + $this->stock_in - $this->stock_out + $this->adjustments;

            // Calculate weighted average unit cost
            $this->calculateWeightedAverageUnitCost();

            // Update total value
            $this->total_value = $this->closing_stock * $this->avg_unit_cost;

            $this->save();

            DB::commit();

            Log::info("Recalculated warehouse balance", [
                'balance_id' => $this->id,
                'opening_stock' => $this->opening_stock,
                'stock_in' => $this->stock_in,
                'stock_out' => $this->stock_out,
                'closing_stock' => $this->closing_stock
            ]);

            return $this;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Close the balance period
     */
    public function closePeriod($userId = null, $notes = null)
    {
        if ($this->is_closed) {
            throw new \Exception("Period sudah ditutup sebelumnya");
        }

        // Recalculate before closing
        $this->recalculateFromTransactions();

        $this->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $userId ?? auth()->id(),
            'notes' => $notes ? ($this->notes ?? '') . "\nClosed: {$notes}" : $this->notes
        ]);

        Log::info("Warehouse balance period closed", [
            'balance_id' => $this->id,
            'period' => "{$this->year}-{$this->month}",
            'closing_stock' => $this->closing_stock,
            'closed_by' => $this->closed_by
        ]);

        return $this;
    }

    /**
     * Reopen the balance period
     */
    public function reopenPeriod($userId = null, $reason = null)
    {
        if (!$this->is_closed) {
            throw new \Exception("Period belum ditutup");
        }

        // Check if next period exists and is closed
        $nextPeriodBalance = static::getNextPeriodBalance($this->item_id, $this->branch_warehouse_id, $this->year, $this->month);
        if ($nextPeriodBalance && $nextPeriodBalance->is_closed) {
            throw new \Exception("Tidak dapat membuka period karena period berikutnya sudah ditutup");
        }

        $this->update([
            'is_closed' => false,
            'closed_at' => null,
            'closed_by' => null,
            'notes' => $reason ? ($this->notes ?? '') . "\nReopened: {$reason}" : $this->notes
        ]);

        Log::warning("Warehouse balance period reopened", [
            'balance_id' => $this->id,
            'period' => "{$this->year}-{$this->month}",
            'reopened_by' => $userId ?? auth()->id(),
            'reason' => $reason
        ]);

        return $this;
    }

    /**
     * Get stock movement summary
     */
    public function getMovementSummary()
    {
        return [
            'opening_stock' => $this->opening_stock,
            'receipts' => [
                'from_central' => $this->total_receipts_from_central,
                'returns_from_branches' => $this->total_returns_from_branches,
                'total_in' => $this->stock_in
            ],
            'distributions' => [
                'to_branches' => $this->total_distributions_to_branches,
                'returns_to_central' => $this->total_returns_to_central,
                'total_out' => $this->stock_out
            ],
            'adjustments' => $this->adjustments,
            'closing_stock' => $this->closing_stock,
            'stock_movement' => $this->stock_in - $this->stock_out,
            'turnover_ratio' => $this->opening_stock > 0 ? $this->stock_out / $this->opening_stock : 0,
            'stock_velocity' => $this->opening_stock + $this->closing_stock > 0 ? 
                ($this->stock_out * 2) / ($this->opening_stock + $this->closing_stock) : 0
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics()
    {
        $summary = $this->getMovementSummary();
        
        // Distribution efficiency
        $distributionEfficiency = $this->total_receipts_from_central > 0 ? 
            ($this->total_distributions_to_branches / $this->total_receipts_from_central) * 100 : 0;

        // Return rate
        $returnRate = $this->total_distributions_to_branches > 0 ? 
            ($this->total_returns_from_branches / $this->total_distributions_to_branches) * 100 : 0;

        // Stock health score
        $stockHealth = $this->calculateStockHealthScore();

        // Value metrics
        $avgInventoryValue = ($this->opening_stock + $this->closing_stock) / 2 * $this->avg_unit_cost;
        $inventoryTurnover = $avgInventoryValue > 0 ? ($this->stock_out * $this->avg_unit_cost) / $avgInventoryValue : 0;

        return [
            'distribution_efficiency' => round($distributionEfficiency, 2),
            'return_rate' => round($returnRate, 2),
            'stock_health_score' => round($stockHealth, 2),
            'inventory_turnover' => round($inventoryTurnover, 2),
            'avg_inventory_value' => round($avgInventoryValue, 2),
            'total_value_distributed' => round($this->total_distributions_to_branches * $this->avg_unit_cost, 2),
            'days_of_inventory' => $this->stock_out > 0 ? round(($this->closing_stock / $this->stock_out) * 30, 1) : 0
        ];
    }

    // ========================================
    // ANALYTICS & REPORTING METHODS
    // ========================================

    /**
     * Get warehouse performance analytics untuk periode tertentu
     */
    public static function getWarehouseAnalytics($warehouseId, $startYear = null, $startMonth = null, $endYear = null, $endMonth = null)
    {
        $startYear = $startYear ?? now()->subMonths(11)->year;
        $startMonth = $startMonth ?? now()->subMonths(11)->month;
        $endYear = $endYear ?? now()->year;
        $endMonth = $endMonth ?? now()->month;

        $balances = static::forWarehouse($warehouseId)
                          ->where(function($query) use ($startYear, $startMonth, $endYear, $endMonth) {
                              $query->where('year', '>', $startYear)
                                    ->orWhere(function($q) use ($startYear, $startMonth) {
                                        $q->where('year', $startYear)->where('month', '>=', $startMonth);
                                    });
                          })
                          ->where(function($query) use ($endYear, $endMonth) {
                              $query->where('year', '<', $endYear)
                                    ->orWhere(function($q) use ($endYear, $endMonth) {
                                        $q->where('year', $endYear)->where('month', '<=', $endMonth);
                                    });
                          })
                          ->with(['item', 'branchWarehouse'])
                          ->get();

        // Group by period
        $periodData = $balances->groupBy(function($balance) {
            return $balance->year . '-' . str_pad($balance->month, 2, '0', STR_PAD_LEFT);
        })->map(function($periodBalances) {
            return [
                'total_items' => $periodBalances->count(),
                'total_opening_stock' => $periodBalances->sum('opening_stock'),
                'total_closing_stock' => $periodBalances->sum('closing_stock'),
                'total_stock_in' => $periodBalances->sum('stock_in'),
                'total_stock_out' => $periodBalances->sum('stock_out'),
                'total_receipts_from_central' => $periodBalances->sum('total_receipts_from_central'),
                'total_distributions_to_branches' => $periodBalances->sum('total_distributions_to_branches'),
                'total_value' => $periodBalances->sum('total_value'),
                'avg_turnover_ratio' => $periodBalances->avg(function($balance) {
                    return $balance->opening_stock > 0 ? $balance->stock_out / $balance->opening_stock : 0;
                })
            ];
        });

        // Calculate overall metrics
        $totalValue = $balances->sum('total_value');
        $totalStockOut = $balances->sum('stock_out');
        $totalReceiptsFromCentral = $balances->sum('total_receipts_from_central');
        $totalDistributionsToBranches = $balances->sum('total_distributions_to_branches');

        $overallDistributionEfficiency = $totalReceiptsFromCentral > 0 ? 
            ($totalDistributionsToBranches / $totalReceiptsFromCentral) * 100 : 0;

        // Top performing items
        $topItems = $balances->groupBy('item_id')->map(function($itemBalances) {
            $item = $itemBalances->first()->item;
            return [
                'item' => $item,
                'total_distributed' => $itemBalances->sum('total_distributions_to_branches'),
                'total_value_distributed' => $itemBalances->sum(function($balance) {
                    return $balance->total_distributions_to_branches * $balance->avg_unit_cost;
                }),
                'avg_turnover' => $itemBalances->avg(function($balance) {
                    return $balance->opening_stock > 0 ? $balance->stock_out / $balance->opening_stock : 0;
                })
            ];
        })->sortByDesc('total_distributed')->take(10);

        return [
            'period' => [
                'start' => "{$startYear}-{$startMonth}",
                'end' => "{$endYear}-{$endMonth}",
                'months' => $periodData->count()
            ],
            'overall_metrics' => [
                'total_value' => round($totalValue, 2),
                'total_receipts_from_central' => $totalReceiptsFromCentral,
                'total_distributions_to_branches' => $totalDistributionsToBranches,
                'distribution_efficiency' => round($overallDistributionEfficiency, 2),
                'avg_monthly_throughput' => $periodData->count() > 0 ? 
                    round($totalStockOut / $periodData->count(), 2) : 0
            ],
            'period_data' => $periodData,
            'top_items' => $topItems,
            'stock_health' => [
                'items_with_stock' => $balances->where('closing_stock', '>', 0)->count(),
                'items_out_of_stock' => $balances->where('closing_stock', '<=', 0)->count(),
                'items_low_stock' => $balances->filter(function($balance) {
                    return $balance->item && $balance->closing_stock <= $balance->item->low_stock_threshold;
                })->count(),
                'avg_stock_health_score' => round($balances->avg(function($balance) {
                    return $balance->calculateStockHealthScore();
                }), 2)
            ]
        ];
    }

    /**
     * Compare warehouse performance
     */
    public static function compareWarehousePerformance($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return static::forPeriod($year, $month)
                     ->with(['branchWarehouse', 'item'])
                     ->get()
                     ->groupBy('branch_warehouse_id')
                     ->map(function($warehouseBalances, $warehouseId) {
                         $warehouse = $warehouseBalances->first()->branchWarehouse;
                         
                         $totalValue = $warehouseBalances->sum('total_value');
                         $totalDistributed = $warehouseBalances->sum('total_distributions_to_branches');
                         $totalReceived = $warehouseBalances->sum('total_receipts_from_central');
                         
                         $distributionEfficiency = $totalReceived > 0 ? 
                             ($totalDistributed / $totalReceived) * 100 : 0;
                         
                         $avgTurnover = $warehouseBalances->avg(function($balance) {
                             return $balance->opening_stock > 0 ? $balance->stock_out / $balance->opening_stock : 0;
                         });

                         $stockHealthScore = $warehouseBalances->avg(function($balance) {
                             return $balance->calculateStockHealthScore();
                         });

                         return [
                             'warehouse' => $warehouse,
                             'metrics' => [
                                 'total_items' => $warehouseBalances->count(),
                                 'total_value' => round($totalValue, 2),
                                 'total_distributed' => $totalDistributed,
                                 'total_received' => $totalReceived,
                                 'distribution_efficiency' => round($distributionEfficiency, 2),
                                 'avg_turnover' => round($avgTurnover, 3),
                                 'stock_health_score' => round($stockHealthScore, 2)
                             ],
                             'performance_score' => round(
                                 ($distributionEfficiency * 0.4) + 
                                 (min($avgTurnover * 100, 100) * 0.3) + 
                                 ($stockHealthScore * 0.3), 2
                             )
                         ];
                     })
                     ->sortByDesc('performance_score');
    }

    /**
     * Get trend analysis
     */
    public static function getTrendAnalysis($warehouseId, $months = 12)
    {
        $endDate = now();
        $startDate = now()->subMonths($months - 1)->startOfMonth();

        $balances = static::forWarehouse($warehouseId)
                          ->where(function($query) use ($startDate, $endDate) {
                              $query->where('year', '>', $startDate->year)
                                    ->orWhere(function($q) use ($startDate) {
                                        $q->where('year', $startDate->year)
                                          ->where('month', '>=', $startDate->month);
                                    });
                          })
                          ->where(function($query) use ($endDate) {
                              $query->where('year', '<', $endDate->year)
                                    ->orWhere(function($q) use ($endDate) {
                                        $q->where('year', $endDate->year)
                                          ->where('month', '<=', $endDate->month);
                                    });
                          })
                          ->orderBy('year')
                          ->orderBy('month')
                          ->get();

        $trendData = $balances->groupBy(function($balance) {
            return $balance->year . '-' . str_pad($balance->month, 2, '0', STR_PAD_LEFT);
        })->map(function($periodBalances, $period) {
            $totalReceived = $periodBalances->sum('total_receipts_from_central');
            $totalDistributed = $periodBalances->sum('total_distributions_to_branches');
            $totalValue = $periodBalances->sum('total_value');
            
            return [
                'period' => $period,
                'total_received' => $totalReceived,
                'total_distributed' => $totalDistributed,
                'total_value' => $totalValue,
                'distribution_efficiency' => $totalReceived > 0 ? ($totalDistributed / $totalReceived) * 100 : 0,
                'items_count' => $periodBalances->count(),
                'avg_turnover' => $periodBalances->avg(function($balance) {
                    return $balance->opening_stock > 0 ? $balance->stock_out / $balance->opening_stock : 0;
                })
            ];
        });

        return [
            'warehouse_id' => $warehouseId,
            'trend_data' => $trendData,
            'summary' => [
                'total_periods' => $trendData->count(),
                'avg_monthly_received' => round($trendData->avg('total_received'), 2),
                'avg_monthly_distributed' => round($trendData->avg('total_distributed'), 2),
                'avg_distribution_efficiency' => round($trendData->avg('distribution_efficiency'), 2),
                'growth_rate' => static::calculateGrowthRate($trendData)
            ]
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get previous month closing stock
     */
    protected static function getPreviousMonthClosingStock($itemId, $warehouseId, $year, $month)
    {
        $previousDate = Carbon::createFromDate($year, $month, 1)->subMonth();
        
        $previousBalance = static::where('item_id', $itemId)
                                ->where('branch_warehouse_id', $warehouseId)
                                ->where('year', $previousDate->year)
                                ->where('month', $previousDate->month)
                                ->first();

        return $previousBalance ? $previousBalance->closing_stock : 0;
    }

    /**
     * Get next period balance
     */
    protected static function getNextPeriodBalance($itemId, $warehouseId, $year, $month)
    {
        $nextDate = Carbon::createFromDate($year, $month, 1)->addMonth();
        
        return static::where('item_id', $itemId)
                     ->where('branch_warehouse_id', $warehouseId)
                     ->where('year', $nextDate->year)
                     ->where('month', $nextDate->month)
                     ->first();
    }

    /**
     * Calculate weighted average unit cost
     */
    protected function calculateWeightedAverageUnitCost()
    {
        // Get all receipts dengan unit cost untuk period ini
        $receipts = $this->receiptsFromCentral()
                         ->selectRaw('SUM(quantity) as total_quantity, AVG(unit_cost) as avg_cost')
                         ->first();

        if ($receipts && $receipts->total_quantity > 0) {
            $this->avg_unit_cost = $receipts->avg_cost;
        } elseif ($this->avg_unit_cost <= 0) {
            // Fallback to item's default cost
            $this->avg_unit_cost = $this->item->unit_cost ?? 0;
        }
    }

    /**
     * Calculate stock health score (0-100)
     */
    protected function calculateStockHealthScore()
    {
        if (!$this->item) {
            return 50; // Neutral score if no item data
        }

        $score = 100;

        // Penalti untuk out of stock
        if ($this->closing_stock <= 0) {
            $score -= 50;
        }
        // Penalti untuk low stock
        elseif ($this->closing_stock <= $this->item->low_stock_threshold) {
            $score -= 30;
        }

        // Bonus untuk good turnover ratio (0.5 - 2.0 is ideal)
        $turnoverRatio = $this->opening_stock > 0 ? $this->stock_out / $this->opening_stock : 0;
        if ($turnoverRatio >= 0.5 && $turnoverRatio <= 2.0) {
            $score += 10;
        } elseif ($turnoverRatio > 2.0) {
            $score -= 10; // Too high turnover might indicate stockouts
        }

        // Penalti untuk excessive returns
        $returnRate = $this->total_distributions_to_branches > 0 ? 
            ($this->total_returns_from_branches / $this->total_distributions_to_branches) * 100 : 0;
        if ($returnRate > 10) {
            $score -= ($returnRate - 10); // Penalti untuk return rate > 10%
        }

        return max(0, min(100, $score));
    }

    /**
     * Calculate growth rate dari trend data
     */
    protected static function calculateGrowthRate($trendData)
    {
        if ($trendData->count() < 2) {
            return 0;
        }

        $firstPeriod = $trendData->first();
        $lastPeriod = $trendData->last();

        if ($firstPeriod['total_distributed'] <= 0) {
            return 0;
        }

        $periodsCount = $trendData->count() - 1;
        $growthRate = (($lastPeriod['total_distributed'] / $firstPeriod['total_distributed']) ** (1 / $periodsCount)) - 1;

        return round($growthRate * 100, 2);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get period text
     */
    public function getPeriodTextAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('M Y');
    }

    /**
     * Get full period text
     */
    public function getFullPeriodTextAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return $this->is_closed ? 'Closed' : 'Open';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return $this->is_closed ? 'success' : 'warning';
    }

    /**
     * Get warehouse name
     */
    public function getWarehouseNameAttribute()
    {
        return $this->branchWarehouse ? $this->branchWarehouse->warehouse_name : 'N/A';
    }

    /**
     * Get item name
     */
    public function getItemNameAttribute()
    {
        return $this->item ? $this->item->item_name : 'N/A';
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if ($this->closing_stock <= 0) {
            return 'OUT_OF_STOCK';
        } elseif ($this->item && $this->closing_stock <= $this->item->low_stock_threshold) {
            return 'LOW_STOCK';
        } else {
            return 'NORMAL';
        }
    }

    /**
     * Get stock status color
     */
    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'OUT_OF_STOCK' => 'danger',
            'LOW_STOCK' => 'warning',
            'NORMAL' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get turnover ratio
     */
    public function getTurnoverRatioAttribute()
    {
        return $this->opening_stock > 0 ? round($this->stock_out / $this->opening_stock, 3) : 0;
    }

    /**
     * Get distribution efficiency
     */
    public function getDistributionEfficiencyAttribute()
    {
        return $this->total_receipts_from_central > 0 ? 
            round(($this->total_distributions_to_branches / $this->total_receipts_from_central) * 100, 2) : 0;
    }

    /**
     * Get return rate
     */
    public function getReturnRateAttribute()
    {
        return $this->total_distributions_to_branches > 0 ? 
            round(($this->total_returns_from_branches / $this->total_distributions_to_branches) * 100, 2) : 0;
    }

    /**
     * Get stock health score
     */
    public function getStockHealthScoreAttribute()
    {
        return round($this->calculateStockHealthScore(), 2);
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Close warehouse period untuk semua items
     */
    public static function closeWarehousePeriod($warehouseId, $year, $month, $userId = null, $notes = null)
    {
        DB::beginTransaction();
        try {
            $balances = static::forWarehouse($warehouseId)
                             ->forPeriod($year, $month)
                             ->open()
                             ->get();

            $closedCount = 0;
            foreach ($balances as $balance) {
                $balance->closePeriod($userId, $notes);
                $closedCount++;
            }

            DB::commit();

            Log::info("Warehouse period closed", [
                'warehouse_id' => $warehouseId,
                'period' => "{$year}-{$month}",
                'balances_closed' => $closedCount,
                'closed_by' => $userId
            ]);

            return $closedCount;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get warehouse summary untuk dashboard
     */
    public static function getWarehouseSummary($warehouseId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $balances = static::forWarehouse($warehouseId)
                          ->forPeriod($year, $month)
                          ->with('item')
                          ->get();

        return [
            'period' => "{$year}-{$month}",
            'total_items' => $balances->count(),
            'total_value' => round($balances->sum('total_value'), 2),
            'total_closing_stock' => $balances->sum('closing_stock'),
            'items_with_stock' => $balances->where('closing_stock', '>', 0)->count(),
            'items_out_of_stock' => $balances->where('closing_stock', '<=', 0)->count(),
            'items_low_stock' => $balances->filter(function($balance) {
                return $balance->item && $balance->closing_stock <= $balance->item->low_stock_threshold;
            })->count(),
            'total_received' => $balances->sum('total_receipts_from_central'),
            'total_distributed' => $balances->sum('total_distributions_to_branches'),
            'avg_distribution_efficiency' => round($balances->avg('distribution_efficiency'), 2),
            'avg_turnover_ratio' => round($balances->avg('turnover_ratio'), 3),
            'avg_stock_health_score' => round($balances->avg('stock_health_score'), 2),
            'period_status' => $balances->where('is_closed', true)->count() === $balances->count() ? 'CLOSED' : 'OPEN'
        ];
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'item_id' => 'required|exists:items,id',
            'branch_warehouse_id' => 'required|exists:branch_warehouses,id',
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'opening_stock' => 'nullable|numeric|min:0',
            'closing_stock' => 'nullable|numeric|min:0',
            'stock_in' => 'nullable|numeric|min:0',
            'stock_out' => 'nullable|numeric|min:0',
            'adjustments' => 'nullable|numeric',
            'avg_unit_cost' => 'nullable|numeric|min:0',
            'is_closed' => 'boolean',
            'closed_by' => 'nullable|exists:users,id'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_id.required' => 'Item wajib dipilih',
            'branch_warehouse_id.required' => 'Branch warehouse wajib dipilih',
            'year.required' => 'Year wajib diisi',
            'month.required' => 'Month wajib diisi',
            'year.min' => 'Year minimal 2020',
            'year.max' => 'Year maksimal 2050',
            'month.min' => 'Month minimal 1',
            'month.max' => 'Month maksimal 12'
        ];
    }
}