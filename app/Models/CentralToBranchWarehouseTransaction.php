<?php
// filepath: app/Models/CentralToBranchWarehouseTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CentralToBranchWarehouseTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'central_warehouse_id',
        'branch_warehouse_id',
        'item_id',
        'user_id',
        'transaction_type',
        'quantity',
        'reference_no',
        'notes',
        'transaction_date'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'transaction_date' => 'datetime',
        'expiry_date' => 'date',
    ];

    // ========================================
    // TRANSACTION TYPES
    // ========================================
    
    const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';
    const TYPE_DISTRIBUTION = 'DISTRIBUTION';
    const TYPE_ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';
    const TYPE_RETURN_IN = 'RETURN_IN';
    const TYPE_EMERGENCY_SUPPLY = 'EMERGENCY_SUPPLY';

    // ========================================
    // STATUS TYPES
    // ========================================
    
    const STATUS_PENDING = 'PENDING';
    const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    const STATUS_DELIVERED = 'DELIVERED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_RETURNED = 'RETURNED';

    /**
     * Get available transaction types
     */
    public static function getTransactionTypes()
    {
        return [
            self::TYPE_TRANSFER_OUT => 'Transfer Out',
            self::TYPE_DISTRIBUTION => 'Distribution',
            self::TYPE_ADJUSTMENT_OUT => 'Adjustment Out',
            self::TYPE_RETURN_IN => 'Return In',
            self::TYPE_EMERGENCY_SUPPLY => 'Emergency Supply'
        ];
    }

    /**
     * Get available status types
     */
    public static function getStatusTypes()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RETURNED => 'Returned'
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Transaction belongs to central warehouse
     */
    public function centralWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'central_warehouse_id');
    }

    /**
     * Transaction belongs to branch warehouse
     */
    public function branchWarehouse()
    {
        return $this->belongsTo(BranchWarehouse::class);
    }

    /**
     * Transaction belongs to item
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Transaction belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaction belongs to supplier (optional)
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Related central stock transaction (if any)
     */
    public function centralStockTransaction()
    {
        return $this->belongsTo(CentralStockTransaction::class, 'central_transaction_id');
    }

    /**
     * Related branch warehouse to outlet transactions
     */
    public function branchWarehouseToOutletTransactions()
    {
        return $this->hasMany(BranchWarehouseToOutletTransaction::class, 'central_to_branch_transaction_id');
    }

    /**
     * Distribution chain - track where this stock went
     */
    public function distributionChain()
    {
        return $this->branchWarehouseToOutletTransactions()
                    ->with(['branch', 'branchStockTransaction']);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope by central warehouse
     */
    public function scopeFromCentralWarehouse($query, $warehouseId)
    {
        return $query->where('central_warehouse_id', $warehouseId);
    }

    /**
     * Scope by branch warehouse
     */
    public function scopeToBranchWarehouse($query, $warehouseId)
    {
        return $query->where('branch_warehouse_id', $warehouseId);
    }

    /**
     * Scope by transaction type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for outbound transactions (reduces central stock)
     */
    public function scopeStockOut($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_TRANSFER_OUT,
            self::TYPE_DISTRIBUTION,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_EMERGENCY_SUPPLY
        ]);
    }

    /**
     * Scope for inbound transactions (increases central stock)
     */
    public function scopeStockIn($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_RETURN_IN
        ]);
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for delivered transactions
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope for transactions dalam periode tertentu
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('transaction_date', now()->year)
                     ->whereMonth('transaction_date', now()->month);
    }

    /**
     * Scope by reference number
     */
    public function scopeByReference($query, $referenceNo)
    {
        return $query->where('reference_no', $referenceNo);
    }

    /**
     * Scope emergency supplies
     */
    public function scopeEmergencySupply($query)
    {
        return $query->where('transaction_type', self::TYPE_EMERGENCY_SUPPLY);
    }

    // ========================================
    // FACTORY METHODS
    // ========================================

    /**
     * Create distribution transaction
     */
    public static function createDistribution($centralWarehouseId, $branchWarehouseId, $itemId, $quantity, $options = [])
    {
        DB::beginTransaction();
        try {
            // Validate stock availability
            $centralBalance = CentralStockBalance::getOrCreateBalance($itemId, $centralWarehouseId);
            
            if ($centralBalance->closing_stock < $quantity) {
                throw new \Exception("Stock tidak mencukupi. Tersedia: {$centralBalance->closing_stock}, Diminta: {$quantity}");
            }

            // Generate reference number
            $referenceNo = static::generateReferenceNo('DIST');

            // ✅ FIXED: Create transaction with only existing columns
            $transaction = static::create([
                'central_warehouse_id' => $centralWarehouseId,
                'branch_warehouse_id' => $branchWarehouseId,
                'item_id' => $itemId,
                'user_id' => $options['user_id'] ?? auth()->id(),
                'transaction_type' => 'DISTRIBUTION',
                'quantity' => $quantity,
                'reference_no' => $referenceNo,
                'notes' => $options['notes'] ?? "Distribution from central to branch warehouse",
                'transaction_date' => $options['transaction_date'] ?? now()
                // ❌ Removed: 'unit_cost', 'total_cost', 'status', 'supplier_id', 'batch_no', 'expiry_date'
            ]);

            // Update central stock balance
            $centralBalance->updateMovement('OUT', $quantity);

            DB::commit();
            
            Log::info("Distribution created: {$referenceNo}", [
                'transaction_id' => $transaction->id,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'from' => $centralWarehouseId,
                'to' => $branchWarehouseId
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to create distribution: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create transfer out transaction
     */
    public static function createTransferOut($centralWarehouseId, $branchWarehouseId, $itemId, $quantity, $options = [])
    {
        $options['transaction_type'] = self::TYPE_TRANSFER_OUT;
        return static::createDistribution($centralWarehouseId, $branchWarehouseId, $itemId, $quantity, $options);
    }

    /**
     * Create emergency supply transaction
     */
    public static function createEmergencySupply($centralWarehouseId, $branchWarehouseId, $itemId, $quantity, $options = [])
    {
        DB::beginTransaction();
        try {
            $options['transaction_type'] = self::TYPE_EMERGENCY_SUPPLY;
            $options['notes'] = $options['notes'] ?? "Emergency supply - urgent distribution";
            $referenceNo = static::generateReferenceNo('EMRG');
            $options['reference_no'] = $referenceNo;

            $transaction = static::createDistribution($centralWarehouseId, $branchWarehouseId, $itemId, $quantity, $options);
            
            // Mark as urgent/priority
            $transaction->update(['status' => self::STATUS_IN_TRANSIT]);

            DB::commit();
            
            Log::warning("Emergency supply created: {$referenceNo}", [
                'transaction_id' => $transaction->id,
                'item_id' => $itemId,
                'quantity' => $quantity
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create return transaction (from branch warehouse back to central)
     */
    public static function createReturn($branchWarehouseId, $centralWarehouseId, $itemId, $quantity, $options = [])
    {
        DB::beginTransaction();
        try {
            // Validate branch warehouse stock
            $branchBalance = BranchWarehouseMonthlyBalance::getOrCreateBalance($itemId, $branchWarehouseId);
            
            if ($branchBalance->closing_stock < $quantity) {
                throw new \Exception("Stock branch warehouse tidak mencukupi. Tersedia: {$branchBalance->closing_stock}, Dikembalikan: {$quantity}");
            }

            $referenceNo = static::generateReferenceNo('RET');

            $transaction = static::create([
                'central_warehouse_id' => $centralWarehouseId,
                'branch_warehouse_id' => $branchWarehouseId,
                'item_id' => $itemId,
                'user_id' => $options['user_id'] ?? auth()->id(),
                'transaction_type' => self::TYPE_RETURN_IN,
                'quantity' => $quantity,
                'unit_cost' => $options['unit_cost'] ?? 0,
                'total_cost' => ($options['unit_cost'] ?? 0) * $quantity,
                'reference_no' => $referenceNo,
                'notes' => $options['notes'] ?? "Return from branch warehouse to central",
                'transaction_date' => $options['transaction_date'] ?? now(),
                'status' => self::STATUS_PENDING
            ]);

            // Update balances
            $branchBalance->updateMovement('OUT', $quantity);
            
            $centralBalance = CentralStockBalance::getOrCreateBalance($itemId, $centralWarehouseId);
            $centralBalance->updateMovement('IN', $quantity);

            DB::commit();
            
            Log::info("Return transaction created: {$referenceNo}");
            return $transaction;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create adjustment transaction
     */
    public static function createAdjustment($centralWarehouseId, $branchWarehouseId, $itemId, $quantity, $options = [])
    {
        $referenceNo = static::generateReferenceNo('ADJ');
        $options['reference_no'] = $referenceNo;
        $options['transaction_type'] = self::TYPE_ADJUSTMENT_OUT;
        $options['notes'] = $options['notes'] ?? "Stock adjustment - central to branch warehouse";

        return static::createDistribution($centralWarehouseId, $branchWarehouseId, $itemId, $quantity, $options);
    }

    /**
     * Bulk distribution untuk multiple items
     */
    public static function createBulkDistribution($centralWarehouseId, $branchWarehouseId, $items, $options = [])
    {
        DB::beginTransaction();
        try {
            $transactions = [];
            $batchNo = $options['batch_no'] ?? 'BULK-' . date('YmdHis');

            foreach ($items as $itemData) {
                $itemOptions = array_merge($options, [
                    'batch_no' => $batchNo,
                    'unit_cost' => $itemData['unit_cost'] ?? 0
                ]);

                $transaction = static::createDistribution(
                    $centralWarehouseId,
                    $branchWarehouseId,
                    $itemData['item_id'],
                    $itemData['quantity'],
                    $itemOptions
                );

                $transactions[] = $transaction;
            }

            DB::commit();
            
            Log::info("Bulk distribution created", [
                'batch_no' => $batchNo,
                'transaction_count' => count($transactions),
                'total_items' => array_sum(array_column($items, 'quantity'))
            ]);

            return $transactions;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Update transaction status
     */
    public function updateStatus($status, $notes = null)
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $status,
            'notes' => $notes ? $this->notes . "\n" . $notes : $this->notes
        ]);

        Log::info("Transaction status updated: {$this->reference_no}", [
            'old_status' => $oldStatus,
            'new_status' => $status,
            'transaction_id' => $this->id
        ]);

        return $this;
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered($deliveryNotes = null)
    {
        return $this->updateStatus(self::STATUS_DELIVERED, $deliveryNotes);
    }

    /**
     * Mark as cancelled
     */
    public function markAsCancelled($cancellationReason = null)
    {
        DB::beginTransaction();
        try {
            // Reverse stock movements
            if ($this->isStockOut()) {
                // Return stock to central warehouse
                $centralBalance = CentralStockBalance::getOrCreateBalance($this->item_id, $this->central_warehouse_id);
                $centralBalance->updateMovement('IN', $this->quantity);

                // Remove stock from branch warehouse
                $branchBalance = BranchWarehouseMonthlyBalance::getOrCreateBalance(
                    $this->item_id, 
                    $this->branch_warehouse_id,
                    $this->transaction_date->year,
                    $this->transaction_date->month
                );
                $branchBalance->updateMovement('OUT', $this->quantity);
            }

            $this->updateStatus(self::STATUS_CANCELLED, $cancellationReason);
            
            DB::commit();
            return $this;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get distribution chain summary
     */
    public function getDistributionChainSummary()
    {
        $distributions = $this->distributionChain;
        
        return [
            'original_quantity' => $this->quantity,
            'distributed_quantity' => $distributions->sum('quantity'),
            'remaining_quantity' => $this->quantity - $distributions->sum('quantity'),
            'branches_served' => $distributions->pluck('branch.branch_name')->unique()->values(),
            'distribution_count' => $distributions->count(),
            'distribution_efficiency' => $this->quantity > 0 ? ($distributions->sum('quantity') / $this->quantity) * 100 : 0
        ];
    }

    /**
     * Check if transaction reduces stock
     */
    public function isStockOut()
    {
        return in_array($this->transaction_type, [
            self::TYPE_TRANSFER_OUT,
            self::TYPE_DISTRIBUTION,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_EMERGENCY_SUPPLY
        ]);
    }

    /**
     * Check if transaction increases stock
     */
    public function isStockIn()
    {
        return in_array($this->transaction_type, [
            self::TYPE_RETURN_IN
        ]);
    }

    // ========================================
    // ANALYTICS & REPORTING METHODS
    // ========================================

    /**
     * Get transaction analytics untuk periode tertentu
     */
    public static function getAnalytics($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        $query = static::whereBetween('transaction_date', [$startDate, $endDate]);

        // Apply filters
        if (isset($filters['central_warehouse_id'])) {
            $query->where('central_warehouse_id', $filters['central_warehouse_id']);
        }
        
        if (isset($filters['branch_warehouse_id'])) {
            $query->where('branch_warehouse_id', $filters['branch_warehouse_id']);
        }
        
        if (isset($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        $transactions = $query->with(['item', 'centralWarehouse', 'branchWarehouse'])->get();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate) + 1
            ],
            'summary' => [
                'total_transactions' => $transactions->count(),
                'total_quantity' => $transactions->sum('quantity'),
                'total_value' => $transactions->sum('total_cost'),
                'unique_items' => $transactions->pluck('item_id')->unique()->count(),
                'unique_warehouses' => $transactions->pluck('branch_warehouse_id')->unique()->count()
            ],
            'by_type' => $transactions->groupBy('transaction_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'value' => $group->sum('total_cost')
                ];
            }),
            'by_status' => $transactions->groupBy('status')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'percentage' => 0 // Will be calculated
                ];
            }),
            'by_warehouse' => $transactions->groupBy('branch_warehouse_id')->map(function($group) {
                $warehouse = $group->first()->branchWarehouse;
                return [
                    'warehouse' => $warehouse,
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'value' => $group->sum('total_cost')
                ];
            }),
            'top_items' => $transactions->groupBy('item_id')
                                      ->map(function($group) {
                                          return [
                                              'item' => $group->first()->item,
                                              'quantity' => $group->sum('quantity'),
                                              'transaction_count' => $group->count()
                                          ];
                                      })
                                      ->sortByDesc('quantity')
                                      ->take(10),
            'daily_trend' => $transactions->groupBy(function($transaction) {
                                return $transaction->transaction_date->format('Y-m-d');
                            })
                            ->map(function($group) {
                                return [
                                    'count' => $group->count(),
                                    'quantity' => $group->sum('quantity'),
                                    'value' => $group->sum('total_cost')
                                ];
                            })
                            ->sortKeys()
        ];

        // Calculate percentages for status
        $totalCount = $transactions->count();
        if ($totalCount > 0) {
            foreach ($analytics['by_status'] as &$statusData) {
                $statusData['percentage'] = ($statusData['count'] / $totalCount) * 100;
            }
        }

        return $analytics;
    }

    /**
     * Get warehouse performance comparison
     */
    public static function getWarehousePerformanceComparison($startDate = null, $endDate = null)
    {
        $analytics = static::getAnalytics($startDate, $endDate);
        
        return collect($analytics['by_warehouse'])->map(function($data, $warehouseId) use ($startDate, $endDate) {
            $warehouse = BranchWarehouse::find($warehouseId);
            $deliveryRate = static::getDeliveryRate($warehouseId, $startDate, $endDate);
            $avgDeliveryTime = static::getAverageDeliveryTime($warehouseId, $startDate, $endDate);

            return [
                'warehouse' => $warehouse,
                'statistics' => $data,
                'delivery_rate' => $deliveryRate,
                'avg_delivery_time' => $avgDeliveryTime,
                'efficiency_score' => $deliveryRate * 0.6 + (100 - min($avgDeliveryTime * 10, 100)) * 0.4
            ];
        })->sortByDesc('efficiency_score');
    }

    /**
     * Get delivery rate untuk warehouse
     */
    public static function getDeliveryRate($warehouseId, $startDate = null, $endDate = null)
    {
        $query = static::toBranchWarehouse($warehouseId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $total = $query->count();
        $delivered = $query->where('status', self::STATUS_DELIVERED)->count();

        return $total > 0 ? ($delivered / $total) * 100 : 0;
    }

    /**
     * Get average delivery time dalam hari
     */
    public static function getAverageDeliveryTime($warehouseId, $startDate = null, $endDate = null)
    {
        $query = static::toBranchWarehouse($warehouseId)
                       ->where('status', self::STATUS_DELIVERED);
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $transactions = $query->get();
        
        if ($transactions->isEmpty()) {
            return 0;
        }

        $totalDays = $transactions->sum(function($transaction) {
            // Assuming delivery time is from transaction_date to updated_at when status changed to delivered
            return $transaction->transaction_date->diffInDays($transaction->updated_at);
        });

        return round($totalDays / $transactions->count(), 2);
    }

    /**
     * Get emergency supply statistics
     */
    public static function getEmergencySupplyStats($startDate = null, $endDate = null)
    {
        $query = static::emergencySupply();
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $emergencySupplies = $query->with(['item', 'branchWarehouse'])->get();

        return [
            'total_emergency_supplies' => $emergencySupplies->count(),
            'total_quantity' => $emergencySupplies->sum('quantity'),
            'total_value' => $emergencySupplies->sum('total_cost'),
            'by_warehouse' => $emergencySupplies->groupBy('branch_warehouse_id')->map(function($group) {
                return [
                    'warehouse' => $group->first()->branchWarehouse,
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity')
                ];
            }),
            'by_item' => $emergencySupplies->groupBy('item_id')->map(function($group) {
                return [
                    'item' => $group->first()->item,
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity')
                ];
            })->sortByDesc('count'),
            'frequency_trend' => $emergencySupplies->groupBy(function($transaction) {
                return $transaction->transaction_date->format('Y-m');
            })->map(function($group) {
                return $group->count();
            })
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Generate reference number
     */
    public static function generateReferenceNo($prefix = 'CTBW')
    {
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get transaction type text
     */
    public function getTransactionTypeTextAttribute()
    {
        return self::getTransactionTypes()[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return self::getStatusTypes()[$this->status] ?? $this->status;
    }

    /**
     * Get status color untuk UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_TRANSIT => 'info',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_RETURNED => 'secondary',
            default => 'light'
        };
    }

    /**
     * Get transaction type color
     */
    public function getTransactionTypeColorAttribute()
    {
        return match($this->transaction_type) {
            self::TYPE_DISTRIBUTION => 'primary',
            self::TYPE_TRANSFER_OUT => 'info',
            self::TYPE_EMERGENCY_SUPPLY => 'danger',
            self::TYPE_RETURN_IN => 'warning',
            self::TYPE_ADJUSTMENT_OUT => 'secondary',
            default => 'light'
        };
    }

    /**
     * Get central warehouse name
     */
    public function getCentralWarehouseNameAttribute()
    {
        return $this->centralWarehouse ? $this->centralWarehouse->warehouse_name : 'N/A';
    }

    /**
     * Get branch warehouse name
     */
    public function getBranchWarehouseNameAttribute()
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
     * Get formatted transaction date
     */
    public function getFormattedTransactionDateAttribute()
    {
        return $this->transaction_date ? $this->transaction_date->format('d M Y H:i') : 'N/A';
    }

    /**
     * Check if transaction is pending
     */
    public function getIsPendingAttribute()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is delivered
     */
    public function getIsDeliveredAttribute()
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if transaction can be cancelled
     */
    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_TRANSIT]);
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'central_warehouse_id' => 'required|exists:warehouses,id',
            'branch_warehouse_id' => 'required|exists:warehouses,id', // ✅ Changed from branch_warehouses
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'transaction_type' => 'required|in:TRANSFER_OUT,DISTRIBUTION,ADJUSTMENT_OUT',
            'quantity' => 'required|numeric|min:0.001',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date'
        ];
    }

    public static function validationMessages()
    {
        return [
            'central_warehouse_id.required' => 'Central warehouse wajib dipilih',
            'branch_warehouse_id.required' => 'Branch warehouse wajib dipilih',
            'item_id.required' => 'Item wajib dipilih',
            'quantity.required' => 'Quantity wajib diisi',
            'quantity.min' => 'Quantity harus lebih dari 0',
            'transaction_type.required' => 'Tipe transaksi wajib dipilih',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi'
        ];
    }
}