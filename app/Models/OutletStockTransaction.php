<?php
// filepath: app/Models/OutletStockTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutletStockTransaction extends Model
{
    use HasFactory;

    protected $table = 'outlet_stock_transactions';

    protected $fillable = [
        'outlet_warehouse_id',
        'item_id',
        'user_id',
        'branch_warehouse_transaction_id',
        'outlet_to_kitchen_transaction_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'balance_before',
        'balance_after',
        'reference_no',
        'batch_no',
        'notes',
        'transaction_date',
        'year',
        'month',
        'status',
        'approved_by',
        'approved_at',
        'document_no',
        'supplier_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // ============================================================
    // CONSTANTS - Transaction Types
    // ============================================================

    const TYPE_RECEIVE_FROM_BRANCH = 'RECEIVE_FROM_BRANCH';
    const TYPE_RETURN_FROM_KITCHEN = 'RETURN_FROM_KITCHEN';
    const TYPE_DISTRIBUTE_TO_KITCHEN = 'DISTRIBUTE_TO_KITCHEN';
    const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';
    const TYPE_TRANSFER_IN = 'TRANSFER_IN';
    const TYPE_ADJUSTMENT_IN = 'ADJUSTMENT_IN';
    const TYPE_ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';
    const TYPE_STOCK_OPNAME = 'STOCK_OPNAME';
    const TYPE_OPENING_BALANCE = 'OPENING_BALANCE';
    const TYPE_CLOSING_BALANCE = 'CLOSING_BALANCE';

    // ============================================================
    // CONSTANTS - Status
    // ============================================================

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * Outlet warehouse
     */
    public function outletWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'outlet_warehouse_id');
    }

    /**
     * Item
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * User yang create transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User yang approve transaction
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Parent transaction dari branch warehouse (jika receive from branch)
     */
    public function branchWarehouseTransaction()
    {
        return $this->belongsTo(BranchStockTransaction::class, 'branch_warehouse_transaction_id');
    }

    /**
     * Linked transaction distribusi ke kitchen
     */
    public function outletToKitchenTransaction()
    {
        return $this->belongsTo(OutletWarehouseToKitchenTransaction::class, 'outlet_to_kitchen_transaction_id');
    }

    /**
     * Supplier (jika applicable)
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Monthly balance terkait
     */
    public function monthlyBalance()
    {
        return $this->hasOne(OutletWarehouseMonthlyBalance::class, 'warehouse_id', 'outlet_warehouse_id')
                    ->where('item_id', $this->item_id)
                    ->where('year', $this->year)
                    ->where('month', $this->month);
    }

    // ============================================================
    // STATIC FACTORY METHODS
    // ============================================================

    /**
     * Create RECEIVE FROM BRANCH transaction
     *
     * @param array $data
     * @return OutletStockTransaction|null
     */
    public static function createReceiveFromBranch($data)
    {
        try {
            DB::beginTransaction();

            // Validate required data
            if (!isset($data['outlet_warehouse_id'], $data['item_id'], $data['quantity'])) {
                throw new \Exception('Missing required data for receive from branch transaction');
            }

            // Generate reference number
            $referenceNo = self::generateReferenceNo('RCV-BR', $data['outlet_warehouse_id']);

            // Get current balance
            $year = $data['year'] ?? date('Y');
            $month = $data['month'] ?? date('m');
            
            $balance = OutletWarehouseMonthlyBalance::getOrCreateBalance(
                $data['item_id'],
                $data['outlet_warehouse_id'],
                $year,
                $month
            );

            $balanceBefore = $balance->closing_stock;
            $balanceAfter = $balanceBefore + $data['quantity'];

            // Create transaction
            $transaction = self::create([
                'outlet_warehouse_id' => $data['outlet_warehouse_id'],
                'item_id' => $data['item_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'branch_warehouse_transaction_id' => $data['branch_warehouse_transaction_id'] ?? null,
                'transaction_type' => self::TYPE_RECEIVE_FROM_BRANCH,
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'total_cost' => isset($data['unit_cost']) ? ($data['quantity'] * $data['unit_cost']) : null,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_no' => $referenceNo,
                'batch_no' => $data['batch_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? now(),
                'year' => $year,
                'month' => $month,
                'status' => $data['status'] ?? self::STATUS_COMPLETED,
                'document_no' => $data['document_no'] ?? null,
            ]);

            // Update monthly balance
            $balance->updateMovement('received_from_branch_warehouse', $data['quantity']);

            DB::commit();

            Log::info('Outlet stock transaction created - RECEIVE FROM BRANCH', [
                'transaction_id' => $transaction->id,
                'reference_no' => $referenceNo,
                'warehouse_id' => $data['outlet_warehouse_id'],
                'item_id' => $data['item_id'],
                'quantity' => $data['quantity'],
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create outlet receive from branch transaction error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create DISTRIBUTE TO KITCHEN transaction
     *
     * @param array $data
     * @return OutletStockTransaction|null
     */
    public static function createDistributeToKitchen($data)
    {
        try {
            DB::beginTransaction();

            // Validate required data
            if (!isset($data['outlet_warehouse_id'], $data['item_id'], $data['quantity'])) {
                throw new \Exception('Missing required data for distribute to kitchen transaction');
            }

            // Generate reference number
            $referenceNo = self::generateReferenceNo('DIST-KIT', $data['outlet_warehouse_id']);

            // Get current balance
            $year = $data['year'] ?? date('Y');
            $month = $data['month'] ?? date('m');
            
            $balance = OutletWarehouseMonthlyBalance::getOrCreateBalance(
                $data['item_id'],
                $data['outlet_warehouse_id'],
                $year,
                $month
            );

            // Check stock availability
            if ($balance->closing_stock < $data['quantity']) {
                throw new \Exception('Insufficient stock. Available: ' . $balance->closing_stock . ', Required: ' . $data['quantity']);
            }

            $balanceBefore = $balance->closing_stock;
            $balanceAfter = $balanceBefore - $data['quantity'];

            // Create transaction
            $transaction = self::create([
                'outlet_warehouse_id' => $data['outlet_warehouse_id'],
                'item_id' => $data['item_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'outlet_to_kitchen_transaction_id' => $data['outlet_to_kitchen_transaction_id'] ?? null,
                'transaction_type' => self::TYPE_DISTRIBUTE_TO_KITCHEN,
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'total_cost' => isset($data['unit_cost']) ? ($data['quantity'] * $data['unit_cost']) : null,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_no' => $referenceNo,
                'batch_no' => $data['batch_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? now(),
                'year' => $year,
                'month' => $month,
                'status' => $data['status'] ?? self::STATUS_COMPLETED,
                'document_no' => $data['document_no'] ?? null,
            ]);

            // Update monthly balance
            $balance->updateMovement('distributed_to_kitchen', $data['quantity']);

            DB::commit();

            Log::info('Outlet stock transaction created - DISTRIBUTE TO KITCHEN', [
                'transaction_id' => $transaction->id,
                'reference_no' => $referenceNo,
                'warehouse_id' => $data['outlet_warehouse_id'],
                'item_id' => $data['item_id'],
                'quantity' => $data['quantity'],
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create outlet distribute to kitchen transaction error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create ADJUSTMENT transaction
     *
     * @param array $data
     * @return OutletStockTransaction|null
     */
    public static function createAdjustment($data)
    {
        try {
            DB::beginTransaction();

            // Validate required data
            if (!isset($data['outlet_warehouse_id'], $data['item_id'], $data['quantity'])) {
                throw new \Exception('Missing required data for adjustment transaction');
            }

            $isAddition = $data['quantity'] > 0;
            $type = $isAddition ? self::TYPE_ADJUSTMENT_IN : self::TYPE_ADJUSTMENT_OUT;
            $referenceNo = self::generateReferenceNo('ADJ', $data['outlet_warehouse_id']);

            // Get current balance
            $year = $data['year'] ?? date('Y');
            $month = $data['month'] ?? date('m');
            
            $balance = OutletWarehouseMonthlyBalance::getOrCreateBalance(
                $data['item_id'],
                $data['outlet_warehouse_id'],
                $year,
                $month
            );

            $balanceBefore = $balance->closing_stock;
            $balanceAfter = $balanceBefore + $data['quantity']; // quantity bisa + atau -

            // Create transaction
            $transaction = self::create([
                'outlet_warehouse_id' => $data['outlet_warehouse_id'],
                'item_id' => $data['item_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'transaction_type' => $type,
                'quantity' => abs($data['quantity']), // Store as positive
                'unit_cost' => $data['unit_cost'] ?? null,
                'total_cost' => isset($data['unit_cost']) ? (abs($data['quantity']) * $data['unit_cost']) : null,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_no' => $referenceNo,
                'notes' => $data['notes'] ?? 'Stock adjustment',
                'transaction_date' => $data['transaction_date'] ?? now(),
                'year' => $year,
                'month' => $month,
                'status' => $data['status'] ?? self::STATUS_COMPLETED,
            ]);

            // Update monthly balance
            $balance->updateMovement('adjustments', $data['quantity']);

            DB::commit();

            Log::info('Outlet stock transaction created - ADJUSTMENT', [
                'transaction_id' => $transaction->id,
                'reference_no' => $referenceNo,
                'warehouse_id' => $data['outlet_warehouse_id'],
                'item_id' => $data['item_id'],
                'quantity' => $data['quantity'],
                'type' => $type,
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create outlet adjustment transaction error: ' . $e->getMessage());
            return null;
        }
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Generate reference number
     *
     * @param string $prefix
     * @param int $warehouseId
     * @return string
     */
    public static function generateReferenceNo($prefix, $warehouseId)
    {
        $date = date('ymd');
        $warehouse = Warehouse::find($warehouseId);
        $warehouseCode = $warehouse ? $warehouse->warehouse_code : 'OUT';
        
        $lastTransaction = self::where('reference_no', 'like', "$prefix-$warehouseCode-$date%")
                              ->orderBy('reference_no', 'desc')
                              ->first();

        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->reference_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%s-%04d', $prefix, $warehouseCode, $date, $newNumber);
    }

    /**
     * Approve transaction
     *
     * @param int $userId
     * @return bool
     */
    public function approve($userId = null)
    {
        try {
            if ($this->status === self::STATUS_APPROVED || $this->status === self::STATUS_COMPLETED) {
                return false; // Already approved/completed
            }

            $this->status = self::STATUS_APPROVED;
            $this->approved_by = $userId ?? auth()->id();
            $this->approved_at = now();
            $this->save();

            Log::info('Outlet stock transaction approved', [
                'transaction_id' => $this->id,
                'approved_by' => $this->approved_by,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Approve outlet stock transaction error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel transaction
     *
     * @param string $reason
     * @return bool
     */
    public function cancel($reason = null)
    {
        try {
            if ($this->status === self::STATUS_CANCELLED) {
                return false; // Already cancelled
            }

            if ($this->status === self::STATUS_COMPLETED) {
                return false; // Cannot cancel completed transaction
            }

            DB::beginTransaction();

            // Reverse balance if already applied
            if ($this->status === self::STATUS_APPROVED) {
                $balance = OutletWarehouseMonthlyBalance::getOrCreateBalance(
                    $this->item_id,
                    $this->outlet_warehouse_id,
                    $this->year,
                    $this->month
                );

                // Reverse the movement
                $reverseQuantity = $this->isStockIn() ? -$this->quantity : $this->quantity;
                
                $movementType = match($this->transaction_type) {
                    self::TYPE_RECEIVE_FROM_BRANCH => 'received_from_branch_warehouse',
                    self::TYPE_RETURN_FROM_KITCHEN => 'received_return_from_kitchen',
                    self::TYPE_DISTRIBUTE_TO_KITCHEN => 'distributed_to_kitchen',
                    self::TYPE_TRANSFER_OUT => 'transfer_out',
                    self::TYPE_ADJUSTMENT_IN, self::TYPE_ADJUSTMENT_OUT => 'adjustments',
                    default => null,
                };

                if ($movementType) {
                    $balance->updateMovement($movementType, $reverseQuantity);
                }
            }

            $this->status = self::STATUS_CANCELLED;
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . "CANCELLED: " . ($reason ?? 'No reason provided');
            $this->save();

            DB::commit();

            Log::info('Outlet stock transaction cancelled', [
                'transaction_id' => $this->id,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cancel outlet stock transaction error: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // QUERY SCOPES
    // ============================================================

    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('outlet_warehouse_id', $warehouseId);
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeStockIn($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_RECEIVE_FROM_BRANCH,
            self::TYPE_RETURN_FROM_KITCHEN,
            self::TYPE_TRANSFER_IN,
            self::TYPE_ADJUSTMENT_IN,
        ]);
    }

    public function scopeStockOut($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_DISTRIBUTE_TO_KITCHEN,
            self::TYPE_TRANSFER_OUT,
            self::TYPE_ADJUSTMENT_OUT,
        ]);
    }

    // ============================================================
    // ACCESSORS & HELPERS
    // ============================================================

    public function isStockIn()
    {
        return in_array($this->transaction_type, [
            self::TYPE_RECEIVE_FROM_BRANCH,
            self::TYPE_RETURN_FROM_KITCHEN,
            self::TYPE_TRANSFER_IN,
            self::TYPE_ADJUSTMENT_IN,
        ]);
    }

    public function isStockOut()
    {
        return in_array($this->transaction_type, [
            self::TYPE_DISTRIBUTE_TO_KITCHEN,
            self::TYPE_TRANSFER_OUT,
            self::TYPE_ADJUSTMENT_OUT,
        ]);
    }

    public function getFormattedQuantityAttribute()
    {
        $prefix = $this->isStockIn() ? '+' : '-';
        return $prefix . number_format($this->quantity, 3);
    }

    public function getTransactionTypeLabelAttribute()
    {
        return match($this->transaction_type) {
            self::TYPE_RECEIVE_FROM_BRANCH => 'Terima dari Branch Warehouse',
            self::TYPE_RETURN_FROM_KITCHEN => 'Retur dari Kitchen',
            self::TYPE_DISTRIBUTE_TO_KITCHEN => 'Distribusi ke Kitchen',
            self::TYPE_TRANSFER_OUT => 'Transfer Keluar',
            self::TYPE_TRANSFER_IN => 'Transfer Masuk',
            self::TYPE_ADJUSTMENT_IN => 'Penyesuaian (+)',
            self::TYPE_ADJUSTMENT_OUT => 'Penyesuaian (-)',
            self::TYPE_STOCK_OPNAME => 'Stock Opname',
            self::TYPE_OPENING_BALANCE => 'Saldo Awal',
            self::TYPE_CLOSING_BALANCE => 'Saldo Akhir',
            default => 'Unknown',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => '<span class="badge bg-secondary">Draft</span>',
            self::STATUS_PENDING => '<span class="badge bg-warning">Pending</span>',
            self::STATUS_APPROVED => '<span class="badge bg-info">Approved</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success">Completed</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}