<?php
// filepath: app/Models/BranchWarehouseToOutletTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchWarehouseToOutletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_warehouse_id',
        'branch_id',
        'item_id',
        'user_id',
        'central_to_branch_transaction_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_no',
        'batch_no',
        'expiry_date',
        'notes',
        'transaction_date',
        'status',
        'delivery_date',
        'received_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'transaction_date' => 'datetime',
        'delivery_date' => 'datetime',
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
    const TYPE_SCHEDULED_DELIVERY = 'SCHEDULED_DELIVERY';

    // ========================================
    // STATUS TYPES
    // ========================================
    
    const STATUS_PENDING = 'PENDING';
    const STATUS_PREPARED = 'PREPARED';
    const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    const STATUS_DELIVERED = 'DELIVERED';
    const STATUS_RECEIVED = 'RECEIVED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_REJECTED = 'REJECTED';

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
            self::TYPE_EMERGENCY_SUPPLY => 'Emergency Supply',
            self::TYPE_SCHEDULED_DELIVERY => 'Scheduled Delivery'
        ];
    }

    /**
     * Get available status types
     */
    public static function getStatusTypes()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PREPARED => 'Prepared',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REJECTED => 'Rejected'
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Transaction belongs to branch warehouse
     */
    public function branchWarehouse()
    {
        return $this->belongsTo(BranchWarehouse::class);
    }

    /**
     * Transaction belongs to branch (outlet)
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
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
     * Reference to central to branch warehouse transaction
     */
    public function centralToBranchTransaction()
    {
        return $this->belongsTo(CentralToBranchWarehouseTransaction::class, 'central_to_branch_transaction_id');
    }

    /**
     * Related branch stock transaction (created when received)
     */
    public function branchStockTransaction()
    {
        return $this->hasOne(BranchStockTransaction::class, 'branch_warehouse_transaction_id');
    }

    /**
     * Related kitchen stock transactions (if any)
     */
    public function kitchenStockTransactions()
    {
        return $this->hasMany(KitchenStockTransaction::class, 'branch_warehouse_transaction_id');
    }

    /**
     * User who received the transaction
     */
    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope by branch warehouse
     */
    public function scopeFromBranchWarehouse($query, $warehouseId)
    {
        return $query->where('branch_warehouse_id', $warehouseId);
    }

    /**
     * Scope by branch (outlet)
     */
    public function scopeToBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
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
     * Scope for outbound transactions (reduces warehouse stock)
     */
    public function scopeStockOut($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_TRANSFER_OUT,
            self::TYPE_DISTRIBUTION,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_EMERGENCY_SUPPLY,
            self::TYPE_SCHEDULED_DELIVERY
        ]);
    }

    /**
     * Scope for inbound transactions (increases warehouse stock)
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
     * Scope for received transactions
     */
    public function scopeReceived($query)
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    /**
     * Scope for delivered but not received
     */
    public function scopeDeliveredNotReceived($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope emergency supplies
     */
    public function scopeEmergencySupply($query)
    {
        return $query->where('transaction_type', self::TYPE_EMERGENCY_SUPPLY);
    }

    /**
     * Scope scheduled deliveries
     */
    public function scopeScheduledDelivery($query)
    {
        return $query->where('transaction_type', self::TYPE_SCHEDULED_DELIVERY);
    }

    /**
     * Scope transactions dalam periode tertentu
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope current month
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
     * Scope transactions that need follow-up
     */
    public function scopeNeedFollowUp($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PREPARED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_DELIVERED
        ])->where('transaction_date', '<', now()->subDays(2));
    }

    // ========================================
    // FACTORY METHODS
    // ========================================

    /**
     * Create outlet distribution transaction
     */
    public static function createOutletDistribution($branchWarehouseId, $branchId, $itemId, $quantity, $options = [])
    {
        DB::beginTransaction();
        try {
            // Validate branch warehouse stock
            $warehouseBalance = BranchWarehouseMonthlyBalance::getOrCreateBalance(
                $itemId, 
                $branchWarehouseId,
                $options['year'] ?? now()->year,
                $options['month'] ?? now()->month
            );
            
            if ($warehouseBalance->closing_stock < $quantity) {
                throw new \Exception("Stock warehouse tidak mencukupi. Tersedia: {$warehouseBalance->closing_stock}, Diminta: {$quantity}");
            }

            // Validate branch is served by this warehouse
            $branch = Branch::find($branchId);
            if (!$branch || $branch->branch_warehouse_id !== $branchWarehouseId) {
                throw new \Exception("Branch tidak dilayani oleh warehouse ini");
            }

            // Generate reference number
            $referenceNo = static::generateReferenceNo('BWOT');

            // Create transaction
            $transaction = static::create([
                'branch_warehouse_id' => $branchWarehouseId,
                'branch_id' => $branchId,
                'item_id' => $itemId,
                'user_id' => $options['user_id'] ?? auth()->id(),
                'central_to_branch_transaction_id' => $options['central_to_branch_transaction_id'] ?? null,
                'transaction_type' => $options['transaction_type'] ?? self::TYPE_DISTRIBUTION,
                'quantity' => $quantity,
                'unit_cost' => $options['unit_cost'] ?? 0,
                'total_cost' => ($options['unit_cost'] ?? 0) * $quantity,
                'reference_no' => $referenceNo,
                'batch_no' => $options['batch_no'] ?? null,
                'expiry_date' => $options['expiry_date'] ?? null,
                'notes' => $options['notes'] ?? "Distribution to outlet {$branch->branch_name}",
                'transaction_date' => $options['transaction_date'] ?? now(),
                'status' => self::STATUS_PENDING,
                'delivery_date' => $options['delivery_date'] ?? null
            ]);

            // Update warehouse balance
            $warehouseBalance->updateMovement('OUT', $quantity);

            DB::commit();
            
            Log::info("Outlet distribution created: {$referenceNo}", [
                'transaction_id' => $transaction->id,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'from_warehouse' => $branchWarehouseId,
                'to_branch' => $branchId
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to create outlet distribution: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create emergency supply to outlet
     */
    public static function createEmergencySupply($branchWarehouseId, $branchId, $itemId, $quantity, $options = [])
    {
        $options['transaction_type'] = self::TYPE_EMERGENCY_SUPPLY;
        $options['notes'] = $options['notes'] ?? "Emergency supply to outlet - urgent distribution";
        
        $transaction = static::createOutletDistribution($branchWarehouseId, $branchId, $itemId, $quantity, $options);
        
        // Mark as prepared immediately for urgent processing
        $transaction->updateStatus(self::STATUS_PREPARED, 'Emergency supply - prepared immediately');
        
        Log::warning("Emergency supply to outlet created: {$transaction->reference_no}");
        return $transaction;
    }

    /**
     * Create scheduled delivery
     */
    public static function createScheduledDelivery($branchWarehouseId, $branchId, $itemId, $quantity, $deliveryDate, $options = [])
    {
        $options['transaction_type'] = self::TYPE_SCHEDULED_DELIVERY;
        $options['delivery_date'] = $deliveryDate;
        $options['notes'] = $options['notes'] ?? "Scheduled delivery for " . Carbon::parse($deliveryDate)->format('d M Y');
        
        return static::createOutletDistribution($branchWarehouseId, $branchId, $itemId, $quantity, $options);
    }

    /**
     * Create return from outlet to warehouse
     */
    public static function createReturn($branchId, $branchWarehouseId, $itemId, $quantity, $options = [])
    {
        DB::beginTransaction();
        try {
            // Validate branch stock
            $branchBalance = BranchMonthlyBalance::getOrCreateBalance(
                $itemId, 
                $branchId,
                $options['year'] ?? now()->year,
                $options['month'] ?? now()->month
            );
            
            if ($branchBalance->closing_stock < $quantity) {
                throw new \Exception("Stock branch tidak mencukupi untuk return. Tersedia: {$branchBalance->closing_stock}, Dikembalikan: {$quantity}");
            }

            $referenceNo = static::generateReferenceNo('RET');

            $transaction = static::create([
                'branch_warehouse_id' => $branchWarehouseId,
                'branch_id' => $branchId,
                'item_id' => $itemId,
                'user_id' => $options['user_id'] ?? auth()->id(),
                'transaction_type' => self::TYPE_RETURN_IN,
                'quantity' => $quantity,
                'unit_cost' => $options['unit_cost'] ?? 0,
                'total_cost' => ($options['unit_cost'] ?? 0) * $quantity,
                'reference_no' => $referenceNo,
                'notes' => $options['notes'] ?? "Return from outlet to warehouse",
                'transaction_date' => $options['transaction_date'] ?? now(),
                'status' => self::STATUS_PENDING
            ]);

            // Update branch balance (reduce stock)
            $branchBalance->updateMovement('OUT', $quantity);
            
            // Update warehouse balance (increase stock)
            $warehouseBalance = BranchWarehouseMonthlyBalance::getOrCreateBalance(
                $itemId,
                $branchWarehouseId,
                $transaction->transaction_date->year,
                $transaction->transaction_date->month
            );
            $warehouseBalance->updateMovement('IN', $quantity);

            DB::commit();
            
            Log::info("Return transaction created: {$referenceNo}");
            return $transaction;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create bulk distribution untuk multiple items
     */
    public static function createBulkDistribution($branchWarehouseId, $branchId, $items, $options = [])
    {
        DB::beginTransaction();
        try {
            $transactions = [];
            $batchNo = $options['batch_no'] ?? 'BULK-' . date('YmdHis');

            foreach ($items as $itemData) {
                $itemOptions = array_merge($options, [
                    'batch_no' => $batchNo,
                    'unit_cost' => $itemData['unit_cost'] ?? 0,
                    'central_to_branch_transaction_id' => $itemData['central_to_branch_transaction_id'] ?? null
                ]);

                $transaction = static::createOutletDistribution(
                    $branchWarehouseId,
                    $branchId,
                    $itemData['item_id'],
                    $itemData['quantity'],
                    $itemOptions
                );

                $transactions[] = $transaction;
            }

            DB::commit();
            
            Log::info("Bulk outlet distribution created", [
                'batch_no' => $batchNo,
                'transaction_count' => count($transactions),
                'total_items' => array_sum(array_column($items, 'quantity')),
                'branch_id' => $branchId
            ]);

            return $transactions;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create distribution from central transaction chain
     */
    public static function createFromCentralTransaction($centralToBranchTransaction, $branchId, $distributionQuantity, $options = [])
    {
        // Ensure distribution quantity doesn't exceed available
        $availableQuantity = $centralToBranchTransaction->quantity - 
                           $centralToBranchTransaction->branchWarehouseToOutletTransactions()->sum('quantity');

        if ($distributionQuantity > $availableQuantity) {
            throw new \Exception("Distribution quantity ({$distributionQuantity}) melebihi available quantity ({$availableQuantity})");
        }

        $options['central_to_branch_transaction_id'] = $centralToBranchTransaction->id;
        $options['batch_no'] = $centralToBranchTransaction->batch_no;
        $options['unit_cost'] = $centralToBranchTransaction->unit_cost;
        $options['notes'] = $options['notes'] ?? "Distribution from central transaction {$centralToBranchTransaction->reference_no}";

        return static::createOutletDistribution(
            $centralToBranchTransaction->branch_warehouse_id,
            $branchId,
            $centralToBranchTransaction->item_id,
            $distributionQuantity,
            $options
        );
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Update transaction status
     */
    public function updateStatus($status, $notes = null, $userId = null)
    {
        $oldStatus = $this->status;
        $updateData = ['status' => $status];
        
        if ($notes) {
            $updateData['notes'] = $this->notes . "\n" . now()->format('Y-m-d H:i') . " - " . $notes;
        }

        if ($status === self::STATUS_RECEIVED) {
            $updateData['received_by'] = $userId ?? auth()->id();
            $updateData['delivery_date'] = now();
        }

        $this->update($updateData);

        Log::info("Transaction status updated: {$this->reference_no}", [
            'old_status' => $oldStatus,
            'new_status' => $status,
            'transaction_id' => $this->id
        ]);

        return $this;
    }

    /**
     * Mark as prepared
     */
    public function markAsPrepared($preparedBy = null)
    {
        return $this->updateStatus(self::STATUS_PREPARED, "Prepared by " . ($preparedBy ?? auth()->user()->name ?? 'system'));
    }

    /**
     * Mark as in transit
     */
    public function markAsInTransit($transportInfo = null)
    {
        $notes = "In transit" . ($transportInfo ? " - {$transportInfo}" : "");
        return $this->updateStatus(self::STATUS_IN_TRANSIT, $notes);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered($deliveryNotes = null)
    {
        return $this->updateStatus(self::STATUS_DELIVERED, $deliveryNotes);
    }

    /**
     * Mark as received and create branch stock transaction
     */
    public function markAsReceived($receivedBy = null, $notes = null)
    {
        DB::beginTransaction();
        try {
            // Update status
            $this->updateStatus(self::STATUS_RECEIVED, $notes, $receivedBy);

            // Create corresponding BranchStockTransaction
            $branchStockTransaction = BranchStockTransaction::createReceiveFromWarehouse(
                $this->branch_id,
                $this->item_id,
                $this->quantity,
                [
                    'warehouse_transaction_id' => null, // Legacy field
                    'branch_warehouse_transaction_id' => $this->id,
                    'user_id' => $receivedBy ?? auth()->id(),
                    'reference_no' => $this->reference_no,
                    'unit_cost' => $this->unit_cost,
                    'notes' => "Received from warehouse: {$this->branchWarehouse->warehouse_name}",
                    'transaction_date' => now()
                ]
            );

            DB::commit();

            Log::info("Transaction received and branch stock updated: {$this->reference_no}", [
                'branch_stock_transaction_id' => $branchStockTransaction->id
            ]);

            return $this;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Mark as cancelled
     */
    public function markAsCancelled($cancellationReason = null)
    {
        DB::beginTransaction();
        try {
            // Reverse stock movement if transaction was stock out
            if ($this->isStockOut() && in_array($this->status, [self::STATUS_PENDING, self::STATUS_PREPARED])) {
                $warehouseBalance = BranchWarehouseMonthlyBalance::getOrCreateBalance(
                    $this->item_id,
                    $this->branch_warehouse_id,
                    $this->transaction_date->year,
                    $this->transaction_date->month
                );
                $warehouseBalance->updateMovement('IN', $this->quantity); // Return stock to warehouse
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
     * Mark as rejected
     */
    public function markAsRejected($rejectionReason = null, $rejectedBy = null)
    {
        DB::beginTransaction();
        try {
            // Return stock to warehouse
            if ($this->isStockOut()) {
                $warehouseBalance = BranchWarehouseMonthlyBalance::getOrCreateBalance(
                    $this->item_id,
                    $this->branch_warehouse_id,
                    $this->transaction_date->year,
                    $this->transaction_date->month
                );
                $warehouseBalance->updateMovement('IN', $this->quantity);
            }

            $notes = "Rejected" . ($rejectedBy ? " by {$rejectedBy}" : "");
            if ($rejectionReason) {
                $notes .= " - Reason: {$rejectionReason}";
            }

            $this->updateStatus(self::STATUS_REJECTED, $notes);
            
            DB::commit();
            return $this;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get distribution tracking info
     */
    public function getTrackingInfo()
    {
        $tracking = [
            'reference_no' => $this->reference_no,
            'current_status' => $this->status_text,
            'from_warehouse' => $this->branchWarehouse->warehouse_name ?? 'N/A',
            'to_branch' => $this->branch->branch_name ?? 'N/A',
            'item' => $this->item->item_name ?? 'N/A',
            'quantity' => $this->quantity,
            'transaction_date' => $this->transaction_date,
            'delivery_date' => $this->delivery_date,
            'received_by' => $this->receivedByUser->name ?? null,
            'central_reference' => $this->centralToBranchTransaction->reference_no ?? null,
            'branch_stock_created' => $this->branchStockTransaction ? true : false
        ];

        // Add status history from notes
        $tracking['status_history'] = $this->parseStatusHistory();

        return $tracking;
    }

    /**
     * Parse status history from notes
     */
    protected function parseStatusHistory()
    {
        $notes = explode("\n", $this->notes ?? '');
        $history = [];

        foreach ($notes as $note) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) - (.+)$/', $note, $matches)) {
                $history[] = [
                    'timestamp' => $matches[1],
                    'note' => $matches[2]
                ];
            }
        }

        return $history;
    }

    /**
     * Check if transaction reduces warehouse stock
     */
    public function isStockOut()
    {
        return in_array($this->transaction_type, [
            self::TYPE_TRANSFER_OUT,
            self::TYPE_DISTRIBUTION,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_EMERGENCY_SUPPLY,
            self::TYPE_SCHEDULED_DELIVERY
        ]);
    }

    /**
     * Check if transaction increases warehouse stock
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
     * Get analytics untuk branch warehouse outlet distributions
     */
    public static function getAnalytics($startDate = null, $endDate = null, $filters = [])
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        $query = static::whereBetween('transaction_date', [$startDate, $endDate]);

        // Apply filters
        if (isset($filters['branch_warehouse_id'])) {
            $query->where('branch_warehouse_id', $filters['branch_warehouse_id']);
        }
        
        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        
        if (isset($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        $transactions = $query->with(['item', 'branchWarehouse', 'branch'])->get();

        // Calculate delivery performance
        $deliveredTransactions = $transactions->where('status', self::STATUS_DELIVERED);
        $receivedTransactions = $transactions->where('status', self::STATUS_RECEIVED);

        $avgDeliveryTime = $deliveredTransactions->isNotEmpty() ? 
            $deliveredTransactions->avg(function($transaction) {
                return $transaction->delivery_date ? 
                    $transaction->transaction_date->diffInHours($transaction->delivery_date) : 0;
            }) : 0;

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
                'unique_branches' => $transactions->pluck('branch_id')->unique()->count()
            ],
            'performance' => [
                'delivery_rate' => $transactions->count() > 0 ? 
                    ($deliveredTransactions->count() / $transactions->count()) * 100 : 0,
                'receive_rate' => $transactions->count() > 0 ? 
                    ($receivedTransactions->count() / $transactions->count()) * 100 : 0,
                'avg_delivery_time_hours' => round($avgDeliveryTime, 2),
                'emergency_supplies' => $transactions->where('transaction_type', self::TYPE_EMERGENCY_SUPPLY)->count()
            ],
            'by_type' => $transactions->groupBy('transaction_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'value' => $group->sum('total_cost')
                ];
            }),
            'by_status' => $transactions->groupBy('status')->map(function($group, $status) use ($transactions) {
                return [
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'percentage' => $transactions->count() > 0 ? 
                        ($group->count() / $transactions->count()) * 100 : 0
                ];
            }),
            'by_warehouse' => $transactions->groupBy('branch_warehouse_id')->map(function($group) {
                $warehouse = $group->first()->branchWarehouse;
                return [
                    'warehouse' => $warehouse,
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'value' => $group->sum('total_cost'),
                    'branches_served' => $group->pluck('branch_id')->unique()->count()
                ];
            }),
            'by_branch' => $transactions->groupBy('branch_id')->map(function($group) {
                $branch = $group->first()->branch;
                return [
                    'branch' => $branch,
                    'count' => $group->count(),
                    'quantity' => $group->sum('quantity'),
                    'value' => $group->sum('total_cost'),
                    'receive_rate' => $group->count() > 0 ? 
                        ($group->where('status', self::STATUS_RECEIVED)->count() / $group->count()) * 100 : 0
                ];
            }),
            'top_items' => $transactions->groupBy('item_id')
                                      ->map(function($group) {
                                          return [
                                              'item' => $group->first()->item,
                                              'quantity' => $group->sum('quantity'),
                                              'transaction_count' => $group->count(),
                                              'branches_count' => $group->pluck('branch_id')->unique()->count()
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
    }

    /**
     * Get branch performance comparison
     */
    public static function getBranchPerformanceComparison($startDate = null, $endDate = null, $branchWarehouseId = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }
        
        if ($branchWarehouseId) {
            $query->where('branch_warehouse_id', $branchWarehouseId);
        }

        return $query->selectRaw('
                branch_id,
                COUNT(*) as total_transactions,
                COUNT(CASE WHEN status = ? THEN 1 END) as received_transactions,
                SUM(quantity) as total_quantity,
                AVG(quantity) as avg_quantity_per_transaction,
                COUNT(CASE WHEN transaction_type = ? THEN 1 END) as emergency_supplies,
                AVG(CASE WHEN delivery_date IS NOT NULL THEN 
                    TIMESTAMPDIFF(HOUR, transaction_date, delivery_date) 
                END) as avg_delivery_time_hours
            ', [self::STATUS_RECEIVED, self::TYPE_EMERGENCY_SUPPLY])
            ->with('branch')
            ->groupBy('branch_id')
            ->get()
            ->map(function($stat) {
                $receiveRate = $stat->total_transactions > 0 ? 
                    ($stat->received_transactions / $stat->total_transactions) * 100 : 0;
                
                $emergencyRate = $stat->total_transactions > 0 ? 
                    ($stat->emergency_supplies / $stat->total_transactions) * 100 : 0;

                // Calculate efficiency score
                $efficiencyScore = ($receiveRate * 0.6) + 
                                 (max(0, 100 - $emergencyRate) * 0.2) + 
                                 (max(0, 100 - min($stat->avg_delivery_time_hours ?? 0, 100)) * 0.2);

                return [
                    'branch' => $stat->branch,
                    'statistics' => $stat,
                    'receive_rate' => round($receiveRate, 2),
                    'emergency_rate' => round($emergencyRate, 2),
                    'efficiency_score' => round($efficiencyScore, 2)
                ];
            })
            ->sortByDesc('efficiency_score');
    }

    /**
     * Get delivery performance metrics
     */
    public static function getDeliveryPerformanceMetrics($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        $transactions = $query->get();
        $totalTransactions = $transactions->count();

        if ($totalTransactions === 0) {
            return [
                'total_transactions' => 0,
                'delivery_metrics' => [],
                'status_distribution' => []
            ];
        }

        // Status distribution
        $statusDistribution = $transactions->groupBy('status')->map(function($group) use ($totalTransactions) {
            return [
                'count' => $group->count(),
                'percentage' => ($group->count() / $totalTransactions) * 100
            ];
        });

        // Delivery time analysis
        $deliveredTransactions = $transactions->whereNotNull('delivery_date');
        $avgDeliveryTime = $deliveredTransactions->isNotEmpty() ? 
            $deliveredTransactions->avg(function($transaction) {
                return $transaction->transaction_date->diffInHours($transaction->delivery_date);
            }) : 0;

        // On-time delivery (within 24 hours)
        $onTimeDeliveries = $deliveredTransactions->filter(function($transaction) {
            return $transaction->transaction_date->diffInHours($transaction->delivery_date) <= 24;
        });

        return [
            'total_transactions' => $totalTransactions,
            'delivery_metrics' => [
                'total_delivered' => $deliveredTransactions->count(),
                'delivery_rate' => ($deliveredTransactions->count() / $totalTransactions) * 100,
                'avg_delivery_time_hours' => round($avgDeliveryTime, 2),
                'on_time_deliveries' => $onTimeDeliveries->count(),
                'on_time_rate' => $deliveredTransactions->count() > 0 ? 
                    ($onTimeDeliveries->count() / $deliveredTransactions->count()) * 100 : 0
            ],
            'status_distribution' => $statusDistribution,
            'emergency_supply_rate' => ($transactions->where('transaction_type', self::TYPE_EMERGENCY_SUPPLY)->count() / $totalTransactions) * 100
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Generate reference number
     */
    public static function generateReferenceNo($prefix = 'BWOT')
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
            self::STATUS_PREPARED => 'info',
            self::STATUS_IN_TRANSIT => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_RECEIVED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get transaction type color
     */
    public function getTransactionTypeColorAttribute()
    {
        return match($this->transaction_type) {
            self::TYPE_DISTRIBUTION => 'primary',
            self::TYPE_SCHEDULED_DELIVERY => 'info',
            self::TYPE_EMERGENCY_SUPPLY => 'danger',
            self::TYPE_RETURN_IN => 'warning',
            self::TYPE_ADJUSTMENT_OUT => 'secondary',
            default => 'light'
        };
    }

    /**
     * Get warehouse name
     */
    public function getWarehouseNameAttribute()
    {
        return $this->branchWarehouse ? $this->branchWarehouse->warehouse_name : 'N/A';
    }

    /**
     * Get branch name
     */
    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->branch_name : 'N/A';
    }

    /**
     * Get item name
     */
    public function getItemNameAttribute()
    {
        return $this->item ? $this->item->item_name : 'N/A';
    }

    /**
     * Check if transaction is pending
     */
    public function getIsPendingAttribute()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is received
     */
    public function getIsReceivedAttribute()
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    /**
     * Check if transaction can be cancelled
     */
    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, [
            self::STATUS_PENDING, 
            self::STATUS_PREPARED
        ]);
    }

    /**
     * Check if transaction is overdue
     */
    public function getIsOverdueAttribute()
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PREPARED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_DELIVERED
        ]) && $this->transaction_date->addDays(2)->isPast();
    }

    /**
     * Get central transaction reference
     */
    public function getCentralReferenceAttribute()
    {
        return $this->centralToBranchTransaction ? 
            $this->centralToBranchTransaction->reference_no : null;
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'branch_warehouse_id' => 'required|exists:branch_warehouses,id',
            'branch_id' => 'required|exists:branches,id',
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'central_to_branch_transaction_id' => 'nullable|exists:central_to_branch_warehouse_transactions,id',
            'transaction_type' => 'required|in:' . implode(',', array_keys(self::getTransactionTypes())),
            'quantity' => 'required|numeric|min:0.001',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference_no' => 'nullable|string|max:50',
            'batch_no' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(self::getStatusTypes())),
            'received_by' => 'nullable|exists:users,id'
        ];
    }

    public static function validationMessages()
    {
        return [
            'branch_warehouse_id.required' => 'Branch warehouse wajib dipilih',
            'branch_id.required' => 'Branch outlet wajib dipilih',
            'item_id.required' => 'Item wajib dipilih',
            'quantity.required' => 'Quantity wajib diisi',
            'quantity.min' => 'Quantity harus lebih dari 0',
            'transaction_type.required' => 'Tipe transaksi wajib dipilih',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi'
        ];
    }
}