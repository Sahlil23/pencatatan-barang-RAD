<?php
// filepath: app/Models/BranchStockTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'branch_id',
        'warehouse_id',
        'user_id',
        'supplier_id',
        'central_transaction_id',
        'transaction_type',
        'quantity',
        'notes',
        'transaction_date',
        'reference_no',
        'batch_no'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'transaction_date' => 'datetime',
    ];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at'
    ];

    // ========================================
    // BRANCH TRANSACTION TYPES
    // ========================================

    /**
     * Stock IN transactions
     */
    const TYPE_RECEIVE_FROM_CENTRAL = 'RECEIVE_FROM_CENTRAL';
    const TYPE_RECEIVE_FROM_SUPPLIER = 'RECEIVE_FROM_SUPPLIER';
    const TYPE_RETURN_FROM_KITCHEN = 'RETURN_FROM_KITCHEN';
    const TYPE_ADJUSTMENT_IN = 'ADJUSTMENT_IN';

    /**
     * Stock OUT transactions
     */
    const TYPE_TRANSFER_TO_KITCHEN = 'TRANSFER_TO_KITCHEN';
    const TYPE_TRANSFER_TO_CENTRAL = 'TRANSFER_TO_CENTRAL';
    const TYPE_TRANSFER_TO_BRANCH = 'TRANSFER_TO_BRANCH';
    const TYPE_WASTAGE = 'WASTAGE';
    const TYPE_SOLD = 'SOLD';
    const TYPE_ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';

    /**
     * Special transactions
     */
    const TYPE_OPENING_BALANCE = 'OPENING_BALANCE';
    const TYPE_CLOSING_BALANCE = 'CLOSING_BALANCE';
    const TYPE_STOCK_TAKE = 'STOCK_TAKE';

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Transaction belongs to item
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Transaction belongs to branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Transaction belongs to warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Transaction belongs to user who made it
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
     * Transaction belongs to central transaction (optional)
     */
    public function centralTransaction()
    {
        return $this->belongsTo(CentralStockTransaction::class, 'central_transaction_id');
    }

    /**
     * Related kitchen transactions (jika ada transfer ke kitchen)
     */
    public function kitchenTransactions()
    {
        return $this->hasMany(KitchenStockTransaction::class, 'warehouse_transaction_id');
    }

    /**
     * Related branch monthly balance
     */
    public function branchMonthlyBalance()
    {
        return $this->belongsTo(BranchMonthlyBalance::class, 'item_id', 'item_id')
            ->where('branch_id', $this->branch_id)
            ->where('warehouse_id', $this->warehouse_id)
            ->where('year', $this->transaction_date->year)
            ->where('month', $this->transaction_date->month);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope for specific item
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope for specific transaction type
     */
    public function scopeType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    // === STOCK IN SCOPES ===

    public function scopeStockIn($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_RECEIVE_FROM_CENTRAL,
            self::TYPE_RECEIVE_FROM_SUPPLIER,
            self::TYPE_RETURN_FROM_KITCHEN,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_OPENING_BALANCE
        ]);
    }

    public function scopeReceiveFromCentral($query)
    {
        return $query->where('transaction_type', self::TYPE_RECEIVE_FROM_CENTRAL);
    }

    public function scopeReceiveFromSupplier($query)
    {
        return $query->where('transaction_type', self::TYPE_RECEIVE_FROM_SUPPLIER);
    }

    public function scopeReturnFromKitchen($query)
    {
        return $query->where('transaction_type', self::TYPE_RETURN_FROM_KITCHEN);
    }

    // === STOCK OUT SCOPES ===

    public function scopeStockOut($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_TRANSFER_TO_KITCHEN,
            self::TYPE_TRANSFER_TO_CENTRAL,
            self::TYPE_TRANSFER_TO_BRANCH,
            self::TYPE_WASTAGE,
            self::TYPE_SOLD,
            self::TYPE_ADJUSTMENT_OUT
        ]);
    }

    public function scopeTransferToKitchen($query)
    {
        return $query->where('transaction_type', self::TYPE_TRANSFER_TO_KITCHEN);
    }

    public function scopeWastage($query)
    {
        return $query->where('transaction_type', self::TYPE_WASTAGE);
    }

    public function scopeSold($query)
    {
        return $query->where('transaction_type', self::TYPE_SOLD);
    }

    // === ADJUSTMENT SCOPES ===

    public function scopeAdjustment($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_STOCK_TAKE
        ]);
    }

    // === TIME SCOPES ===

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // === SUPPLIER SCOPES ===

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeWithSupplier($query)
    {
        return $query->whereNotNull('supplier_id');
    }

    public function scopeWithoutSupplier($query)
    {
        return $query->whereNull('supplier_id');
    }

    // === REFERENCE SCOPES ===

    public function scopeByReference($query, $referenceNo)
    {
        return $query->where('reference_no', $referenceNo);
    }

    public function scopeByBatch($query, $batchNo)
    {
        return $query->where('batch_no', $batchNo);
    }

    public function scopeFromCentral($query)
    {
        return $query->whereNotNull('central_transaction_id');
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Get available transaction types
     */
    public static function getTransactionTypes()
    {
        return [
            // Stock IN
            self::TYPE_RECEIVE_FROM_CENTRAL => 'Terima dari Central',
            self::TYPE_RECEIVE_FROM_SUPPLIER => 'Terima dari Supplier',
            self::TYPE_RETURN_FROM_KITCHEN => 'Return dari Kitchen',
            self::TYPE_ADJUSTMENT_IN => 'Adjustment (+)',
            self::TYPE_OPENING_BALANCE => 'Opening Balance',
            
            // Stock OUT
            self::TYPE_TRANSFER_TO_KITCHEN => 'Transfer ke Kitchen',
            self::TYPE_TRANSFER_TO_CENTRAL => 'Transfer ke Central',
            self::TYPE_TRANSFER_TO_BRANCH => 'Transfer ke Branch Lain',
            self::TYPE_WASTAGE => 'Wastage/Rusak',
            self::TYPE_SOLD => 'Terjual',
            self::TYPE_ADJUSTMENT_OUT => 'Adjustment (-)',
            
            // Special
            self::TYPE_CLOSING_BALANCE => 'Closing Balance',
            self::TYPE_STOCK_TAKE => 'Stock Opname'
        ];
    }

    /**
     * Get stock IN transaction types
     */
    public static function getStockInTypes()
    {
        return [
            self::TYPE_RECEIVE_FROM_CENTRAL,
            self::TYPE_RECEIVE_FROM_SUPPLIER,
            self::TYPE_RETURN_FROM_KITCHEN,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_OPENING_BALANCE
        ];
    }

    /**
     * Get stock OUT transaction types
     */
    public static function getStockOutTypes()
    {
        return [
            self::TYPE_TRANSFER_TO_KITCHEN,
            self::TYPE_TRANSFER_TO_CENTRAL,
            self::TYPE_TRANSFER_TO_BRANCH,
            self::TYPE_WASTAGE,
            self::TYPE_SOLD,
            self::TYPE_ADJUSTMENT_OUT
        ];
    }

    /**
     * Check if transaction is stock IN
     */
    public function isStockIn()
    {
        return in_array($this->transaction_type, self::getStockInTypes());
    }

    /**
     * Check if transaction is stock OUT
     */
    public function isStockOut()
    {
        return in_array($this->transaction_type, self::getStockOutTypes());
    }

    /**
     * Generate reference number
     */
    public static function generateReferenceNo($branchId, $type)
    {
        $date = now()->format('ymd');
        $branchCode = sprintf('%03d', $branchId);
        
        $typeCode = match($type) {
            self::TYPE_RECEIVE_FROM_CENTRAL => 'RFC',
            self::TYPE_RECEIVE_FROM_SUPPLIER => 'RFS',
            self::TYPE_TRANSFER_TO_KITCHEN => 'TTK',
            self::TYPE_TRANSFER_TO_CENTRAL => 'TTC',
            self::TYPE_TRANSFER_TO_BRANCH => 'TTB',
            self::TYPE_WASTAGE => 'WST',
            self::TYPE_SOLD => 'SLD',
            self::TYPE_ADJUSTMENT_IN => 'ADI',
            self::TYPE_ADJUSTMENT_OUT => 'ADO',
            self::TYPE_STOCK_TAKE => 'STO',
            default => 'TXN'
        };

        $lastTransaction = self::where('branch_id', $branchId)
            ->where('transaction_type', $type)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? 
            (intval(substr($lastTransaction->reference_no, -4)) + 1) : 1;

        return "{$typeCode}-{$branchCode}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create receive from central transaction
     */
    public static function createReceiveFromCentral($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'central_transaction_id' => $data['central_transaction_id'] ?? null,
            'transaction_type' => self::TYPE_RECEIVE_FROM_CENTRAL,
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? null,
            'transaction_date' => $data['transaction_date'] ?? now(),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo($data['branch_id'], self::TYPE_RECEIVE_FROM_CENTRAL),
            'batch_no' => $data['batch_no'] ?? null
        ]);

        // Update branch monthly balance
        $balance = BranchMonthlyBalance::getOrCreateBalance(
            $data['item_id'],
            $data['branch_id'],
            $data['warehouse_id'],
            $transaction->transaction_date->year,
            $transaction->transaction_date->month
        );
        
        $balance->updateMovement('IN', $data['quantity']);

        return $transaction;
    }

    /**
     * Create transfer to kitchen transaction
     */
    public static function createTransferToKitchen($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_TRANSFER_TO_KITCHEN,
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? 'Transfer to kitchen',
            'transaction_date' => $data['transaction_date'] ?? now(),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo($data['branch_id'], self::TYPE_TRANSFER_TO_KITCHEN),
            'batch_no' => $data['batch_no'] ?? null
        ]);

        // Update branch monthly balance
        $balance = BranchMonthlyBalance::getOrCreateBalance(
            $data['item_id'],
            $data['branch_id'],
            $data['warehouse_id'],
            $transaction->transaction_date->year,
            $transaction->transaction_date->month
        );
        
        $balance->updateMovement('OUT', $data['quantity']);

        return $transaction;
    }

    /**
     * Create adjustment transaction
     */
    public static function createAdjustment($data)
    {
        $quantity = $data['quantity'];
        $type = $quantity >= 0 ? self::TYPE_ADJUSTMENT_IN : self::TYPE_ADJUSTMENT_OUT;
        $absQuantity = abs($quantity);

        $transaction = self::create([
            'item_id' => $data['item_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => $type,
            'quantity' => $absQuantity,
            'notes' => $data['notes'] ?? 'Stock adjustment',
            'transaction_date' => $data['transaction_date'] ?? now(),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo($data['branch_id'], $type),
            'batch_no' => $data['batch_no'] ?? null
        ]);

        // Update branch monthly balance
        $balance = BranchMonthlyBalance::getOrCreateBalance(
            $data['item_id'],
            $data['branch_id'],
            $data['warehouse_id'],
            $transaction->transaction_date->year,
            $transaction->transaction_date->month
        );
        
        $movementType = $quantity >= 0 ? 'IN' : 'OUT';
        $balance->updateMovement($movementType, $absQuantity);

        return $transaction;
    }

    /**
     * Create wastage transaction
     */
    public static function createWastage($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'branch_id' => $data['branch_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_WASTAGE,
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? 'Wastage/Damaged items',
            'transaction_date' => $data['transaction_date'] ?? now(),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo($data['branch_id'], self::TYPE_WASTAGE),
            'batch_no' => $data['batch_no'] ?? null
        ]);

        // Update branch monthly balance
        $balance = BranchMonthlyBalance::getOrCreateBalance(
            $data['item_id'],
            $data['branch_id'],
            $data['warehouse_id'],
            $transaction->transaction_date->year,
            $transaction->transaction_date->month
        );
        
        $balance->updateMovement('OUT', $data['quantity']);

        return $transaction;
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get transaction type display name
     */
    public function getTransactionTypeTextAttribute()
    {
        return self::getTransactionTypes()[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * Get transaction type color untuk UI
     */
    public function getTransactionTypeColorAttribute()
    {
        if ($this->isStockIn()) {
            return 'success';
        } elseif ($this->isStockOut()) {
            return 'danger';
        } else {
            return 'warning';
        }
    }

    /**
     * Get formatted quantity dengan sign
     */
    public function getFormattedQuantityAttribute()
    {
        $sign = $this->isStockIn() ? '+' : '-';
        return $sign . number_format($this->quantity, 3);
    }

    /**
     * Get supplier name
     */
    public function getSupplierNameAttribute()
    {
        return $this->supplier ? $this->supplier->supplier_name : 'Tidak ada supplier';
    }

    /**
     * Check if has supplier
     */
    public function getHasSupplierAttribute()
    {
        return !is_null($this->supplier_id);
    }

    /**
     * Get branch name
     */
    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->branch_name : 'Unknown Branch';
    }

    /**
     * Get warehouse name
     */
    public function getWarehouseNameAttribute()
    {
        return $this->warehouse ? $this->warehouse->warehouse_name : 'Unknown Warehouse';
    }

    /**
     * Get item name
     */
    public function getItemNameAttribute()
    {
        return $this->item ? $this->item->item_name : 'Unknown Item';
    }

    /**
     * Get user name
     */
    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->full_name : 'Unknown User';
    }

    /**
     * Check if linked to central transaction
     */
    public function getIsFromCentralAttribute()
    {
        return !is_null($this->central_transaction_id);
    }

    /**
     * Get transaction value (quantity × unit cost)
     */
    public function getTransactionValueAttribute()
    {
        $unitCost = $this->item ? $this->item->unit_cost : 0;
        return $this->quantity * $unitCost;
    }

    // ========================================
    // STATIC ANALYTICS METHODS
    // ========================================

    /**
     * Get branch transaction summary
     */
    public static function getBranchSummary($branchId, $startDate = null, $endDate = null)
    {
        $query = self::forBranch($branchId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->thisMonth();
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_stock_in' => $transactions->where('isStockIn', true)->sum('quantity'),
            'total_stock_out' => $transactions->where('isStockOut', true)->sum('quantity'),
            'net_movement' => $transactions->where('isStockIn', true)->sum('quantity') - 
                            $transactions->where('isStockOut', true)->sum('quantity'),
            'total_value_in' => $transactions->where('isStockIn', true)->sum('transaction_value'),
            'total_value_out' => $transactions->where('isStockOut', true)->sum('transaction_value'),
            'by_type' => $transactions->groupBy('transaction_type')->map->count(),
            'top_items' => $transactions->groupBy('item_id')->map->sum('quantity')->sortDesc()->take(10)
        ];
    }

    /**
     * Get supplier statistics untuk branch
     */
    public static function getBranchSupplierStats($branchId, $startDate = null, $endDate = null)
    {
        $query = self::forBranch($branchId)->withSupplier();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('
                supplier_id,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN transaction_type IN ("' . implode('","', self::getStockInTypes()) . '") THEN quantity ELSE 0 END) as total_stock_in,
                SUM(CASE WHEN transaction_type IN ("' . implode('","', self::getStockOutTypes()) . '") THEN quantity ELSE 0 END) as total_stock_out,
                MAX(transaction_date) as last_transaction_date
            ')
            ->with('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('transaction_count')
            ->get();
    }

    /**
     * Get warehouse movement analysis
     */
    public static function getWarehouseMovementAnalysis($warehouseId, $startDate = null, $endDate = null)
    {
        $query = self::forWarehouse($warehouseId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->thisMonth();
        }

        return $query->selectRaw('
                item_id,
                SUM(CASE WHEN transaction_type IN ("' . implode('","', self::getStockInTypes()) . '") THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN transaction_type IN ("' . implode('","', self::getStockOutTypes()) . '") THEN quantity ELSE 0 END) as total_out,
                COUNT(*) as transaction_count,
                MAX(transaction_date) as last_movement_date
            ')
            ->with('item')
            ->groupBy('item_id')
            ->orderByDesc('transaction_count')
            ->get();
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
            'user_id' => 'required|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'central_transaction_id' => 'nullable|exists:central_stock_transactions,id',
            'transaction_type' => 'required|in:' . implode(',', array_keys(self::getTransactionTypes())),
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
            'reference_no' => 'nullable|string|max:50',
            'batch_no' => 'nullable|string|max:50'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_id.required' => 'Item wajib dipilih',
            'branch_id.required' => 'Branch wajib dipilih',
            'warehouse_id.required' => 'Warehouse wajib dipilih',
            'user_id.required' => 'User wajib dipilih',
            'transaction_type.required' => 'Jenis transaksi wajib dipilih',
            'quantity.required' => 'Jumlah wajib diisi',
            'quantity.min' => 'Jumlah harus lebih dari 0',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi'
        ];
    }
}   