<?php
// filepath: app/Models/OutletWarehouseToKitchenTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutletWarehouseToKitchenTransaction extends Model
{
    use HasFactory;

    protected $table = 'outlet_warehouse_to_kitchen_transactions';

    protected $fillable = [
        'outlet_warehouse_id',
        'branch_id',
        'item_id',
        'user_id',
        'branch_to_outlet_transaction_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_no',
        'batch_no',
        'notes',
        'transaction_date',
        'status',
        'prepared_at',
        'prepared_by',
        'delivered_at',
        'delivered_by',
        'received_at',
        'received_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'transaction_date' => 'date',
        'prepared_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    // ============================================================
    // CONSTANTS
    // ============================================================

    const TYPE_DISTRIBUTION = 'DISTRIBUTION';
    const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';
    const TYPE_RETURN_IN = 'RETURN_IN';
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    const STATUS_PENDING = 'PENDING';
    const STATUS_PREPARED = 'PREPARED';
    const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    const STATUS_DELIVERED = 'DELIVERED';
    const STATUS_RECEIVED = 'RECEIVED';
    const STATUS_CANCELLED = 'CANCELLED';

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function outletWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'outlet_warehouse_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parentTransaction()
    {
        return $this->belongsTo(BranchWarehouseToOutletTransaction::class, 'branch_to_outlet_transaction_id');
    }

    public function preparedByUser()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function deliveredByUser()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Outlet stock transaction yang terkait (stock OUT dari outlet warehouse)
     */
    public function outletStockTransaction()
    {
        return $this->hasOne(OutletStockTransaction::class, 'outlet_to_kitchen_transaction_id');
    }

    /**
     * Kitchen stock transaction yang terkait (stock IN ke kitchen)
     */
    public function kitchenStockTransaction()
    {
        return $this->hasOne(KitchenStockTransaction::class, 'outlet_warehouse_transaction_id');
    }

    // ============================================================
    // STATIC FACTORY METHODS
    // ============================================================

    /**
     * Create distribution dari outlet warehouse ke kitchen
     *
     * @param array $data
     * @return OutletWarehouseToKitchenTransaction|null
     */
    public static function createDistribution($data)
    {
        try {
            DB::beginTransaction();

            // Validate
            if (!isset($data['outlet_warehouse_id'], $data['branch_id'], $data['item_id'], $data['quantity'])) {
                throw new \Exception('Missing required data for distribution');
            }

            // Generate reference number
            $referenceNo = self::generateReferenceNo($data['outlet_warehouse_id']);

            // Calculate cost
            $item = Item::find($data['item_id']);
            $unitCost = $data['unit_cost'] ?? ($item ? $item->unit_cost : 0);
            $totalCost = $data['quantity'] * $unitCost;

            // Create transaction
            $transaction = self::create([
                'outlet_warehouse_id' => $data['outlet_warehouse_id'],
                'branch_id' => $data['branch_id'],
                'item_id' => $data['item_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'branch_to_outlet_transaction_id' => $data['branch_to_outlet_transaction_id'] ?? null,
                'transaction_type' => self::TYPE_DISTRIBUTION,
                'quantity' => $data['quantity'],
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'reference_no' => $referenceNo,
                'batch_no' => $data['batch_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? now(),
                'status' => self::STATUS_PENDING,
            ]);

            // Create outlet stock transaction (stock OUT)
            $outletStockData = [
                'outlet_warehouse_id' => $data['outlet_warehouse_id'],
                'item_id' => $data['item_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'outlet_to_kitchen_transaction_id' => $transaction->id,
                'quantity' => $data['quantity'],
                'unit_cost' => $unitCost,
                'batch_no' => $data['batch_no'] ?? null,
                'notes' => "Distribusi ke kitchen: {$transaction->branch->branch_name}",
                'transaction_date' => $data['transaction_date'] ?? now(),
            ];

            $outletStockTransaction = OutletStockTransaction::createDistributeToKitchen($outletStockData);

            if (!$outletStockTransaction) {
                throw new \Exception('Failed to create outlet stock transaction');
            }

            DB::commit();

            Log::info('Outlet to kitchen distribution created', [
                'transaction_id' => $transaction->id,
                'reference_no' => $referenceNo,
                'outlet_warehouse_id' => $data['outlet_warehouse_id'],
                'branch_id' => $data['branch_id'],
                'item_id' => $data['item_id'],
                'quantity' => $data['quantity'],
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create outlet to kitchen distribution error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Bulk create distributions
     */
    public static function createBulkDistribution($outletWarehouseId, $branchId, $items, $options = [])
    {
        try {
            DB::beginTransaction();

            $transactions = [];

            foreach ($items as $itemData) {
                $data = array_merge([
                    'outlet_warehouse_id' => $outletWarehouseId,
                    'branch_id' => $branchId,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'batch_no' => $itemData['batch_no'] ?? null,
                ], $options);

                $transaction = self::createDistribution($data);
                
                if ($transaction) {
                    $transactions[] = $transaction;
                }
            }

            DB::commit();

            Log::info('Bulk outlet to kitchen distribution created', [
                'outlet_warehouse_id' => $outletWarehouseId,
                'branch_id' => $branchId,
                'total_items' => count($transactions),
            ]);

            return $transactions;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create bulk outlet to kitchen distribution error: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // INSTANCE METHODS
    // ============================================================

    /**
     * Mark as received di kitchen
     */
    public function markAsReceived($receivedBy = null, $notes = null)
    {
        try {
            if ($this->status === self::STATUS_RECEIVED) {
                return false; // Already received
            }

            DB::beginTransaction();

            // Update status
            $this->status = self::STATUS_RECEIVED;
            $this->received_at = now();
            $this->received_by = $receivedBy ?? auth()->id();
            
            if ($notes) {
                $this->notes = ($this->notes ? $this->notes . ' | ' : '') . $notes;
            }
            
            $this->save();

            // Create kitchen stock transaction (stock IN)
            $kitchenStockData = [
                'branch_id' => $this->branch_id,
                'item_id' => $this->item_id,
                'user_id' => $this->received_by,
                'outlet_warehouse_transaction_id' => $this->id,
                'quantity' => $this->quantity,
                'unit_cost' => $this->unit_cost,
                'batch_no' => $this->batch_no,
                'notes' => "Terima dari outlet warehouse: {$this->outletWarehouse->warehouse_name}",
                'transaction_date' => now(),
            ];

            $kitchenTransaction = KitchenStockTransaction::createReceiveFromOutletWarehouse($kitchenStockData);

            if (!$kitchenTransaction) {
                throw new \Exception('Failed to create kitchen stock transaction');
            }

            DB::commit();

            Log::info('Outlet to kitchen transaction received', [
                'transaction_id' => $this->id,
                'kitchen_transaction_id' => $kitchenTransaction->id,
                'received_by' => $this->received_by,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark as received error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update status
     */
    public function updateStatus($status, $notes = null)
    {
        try {
            $this->status = $status;
            
            if ($notes) {
                $this->notes = ($this->notes ? $this->notes . ' | ' : '') . $notes;
            }

            switch ($status) {
                case self::STATUS_PREPARED:
                    $this->prepared_at = now();
                    $this->prepared_by = auth()->id();
                    break;
                    
                case self::STATUS_DELIVERED:
                    $this->delivered_at = now();
                    $this->delivered_by = auth()->id();
                    break;
            }

            $this->save();

            Log::info('Outlet to kitchen transaction status updated', [
                'transaction_id' => $this->id,
                'status' => $status,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Update status error: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    public static function generateReferenceNo($warehouseId)
    {
        $date = date('ymd');
        $warehouse = Warehouse::find($warehouseId);
        $warehouseCode = $warehouse ? str_replace('WH-OUT-', '', $warehouse->warehouse_code) : 'OUT';
        
        $lastTransaction = self::where('reference_no', 'like', "OUT-KIT-$warehouseCode-$date%")
                              ->orderBy('reference_no', 'desc')
                              ->first();

        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->reference_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('OUT-KIT-%s-%s-%04d', $warehouseCode, $date, $newNumber);
    }

    // ============================================================
    // QUERY SCOPES
    // ============================================================

    public function scopeForOutletWarehouse($query, $warehouseId)
    {
        return $query->where('outlet_warehouse_id', $warehouseId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PREPARED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_DELIVERED,
        ]);
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => '<span class="badge bg-warning">Pending</span>',
            self::STATUS_PREPARED => '<span class="badge bg-info">Prepared</span>',
            self::STATUS_IN_TRANSIT => '<span class="badge bg-primary">In Transit</span>',
            self::STATUS_DELIVERED => '<span class="badge bg-success">Delivered</span>',
            self::STATUS_RECEIVED => '<span class="badge bg-success">Received</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getStatusProgressAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 20,
            self::STATUS_PREPARED => 40,
            self::STATUS_IN_TRANSIT => 60,
            self::STATUS_DELIVERED => 80,
            self::STATUS_RECEIVED => 100,
            self::STATUS_CANCELLED => 0,
            default => 0,
        };
    }
}