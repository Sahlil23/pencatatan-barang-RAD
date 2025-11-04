<?php
// filepath: app/Models/KitchenStockTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KitchenStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'item_id',
        'user_id',
        'branch_warehouse_transaction_id',
        'outlet_warehouse_transaction_id',  // ✅ NEW FIELD
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
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'transaction_date' => 'datetime'
    ];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at'
    ];

    // ========================================
    // KITCHEN TRANSACTION TYPES
    // ========================================

    /**
     * Stock IN transactions
     */
    const TYPE_RECEIVE_FROM_WAREHOUSE = 'RECEIVE_FROM_WAREHOUSE';
    const TYPE_TRANSFER_IN = 'TRANSFER_IN'; // Legacy compatibility
    const TYPE_RETURN_FROM_PRODUCTION = 'RETURN_FROM_PRODUCTION';
    const TYPE_ADJUSTMENT_IN = 'ADJUSTMENT_IN';

    /**
     * Stock OUT transactions
     */
    const TYPE_USAGE_PRODUCTION = 'USAGE_PRODUCTION';
    const TYPE_USAGE_COOKING = 'USAGE_COOKING';
    const TYPE_USAGE_PREPARATION = 'USAGE_PREPARATION';
    const TYPE_WASTAGE = 'WASTAGE';
    const TYPE_RETURN_TO_WAREHOUSE = 'RETURN_TO_WAREHOUSE';
    const TYPE_ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';

    /**
     * Special transactions
     */
    const TYPE_OPENING_BALANCE = 'OPENING_BALANCE';
    const TYPE_CLOSING_BALANCE = 'CLOSING_BALANCE';
    const TYPE_STOCK_TAKE = 'STOCK_TAKE';

    // Legacy type for backward compatibility
    const TYPE_USAGE = 'USAGE';
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    // ============================================================
    // CONSTANTS - Transaction Types (ADD NEW TYPE)
    // ============================================================

    const TYPE_RECEIVE_FROM_BRANCH_WAREHOUSE = 'RECEIVE_FROM_BRANCH_WAREHOUSE';
    const TYPE_RECEIVE_FROM_OUTLET_WAREHOUSE = 'RECEIVE_FROM_OUTLET_WAREHOUSE';  // ✅ NEW
    // const TYPE_USAGE = 'USAGE';
    const TYPE_WASTE = 'WASTE';
    // const TYPE_ADJUSTMENT_IN = 'ADJUSTMENT_IN';
    // const TYPE_ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';
    // const TYPE_RETURN_TO_WAREHOUSE = 'RETURN_TO_WAREHOUSE';
    // const TYPE_TRANSFER_IN = 'TRANSFER_IN';
    // const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';
    // const TYPE_OPENING_BALANCE = 'OPENING_BALANCE';
    // const TYPE_CLOSING_BALANCE = 'CLOSING_BALANCE';

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
     * Transaction belongs to user who made it
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Related warehouse transaction (legacy)
     */
    public function warehouseTransaction()
    {
        return $this->belongsTo(StockTransaction::class, 'warehouse_transaction_id');
    }

    /**
     * Parent transaction dari branch warehouse (existing)
     */
    public function branchWarehouseTransaction()
    {
        return $this->belongsTo(BranchWarehouseToOutletTransaction::class, 'branch_warehouse_transaction_id');
    }

    /**
     * Parent transaction dari outlet warehouse (NEW!)
     */
    public function outletWarehouseTransaction()
    {
        return $this->belongsTo(OutletWarehouseToKitchenTransaction::class, 'outlet_warehouse_transaction_id');
    }

    /**
     * Related monthly kitchen balance
     */
    public function monthlyKitchenBalance()
    {
        return $this->belongsTo(MonthlyKitchenStockBalance::class, 'item_id', 'item_id')
            ->where('branch_id', $this->branch_id)
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
            self::TYPE_RECEIVE_FROM_WAREHOUSE,
            self::TYPE_TRANSFER_IN,
            self::TYPE_RETURN_FROM_PRODUCTION,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_OPENING_BALANCE
        ]);
    }

    public function scopeReceiveFromWarehouse($query)
    {
        return $query->where('transaction_type', self::TYPE_RECEIVE_FROM_WAREHOUSE);
    }

    public function scopeTransferIn($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_RECEIVE_FROM_WAREHOUSE,
            self::TYPE_TRANSFER_IN
        ]);
    }

    public function scopeReturnFromProduction($query)
    {
        return $query->where('transaction_type', self::TYPE_RETURN_FROM_PRODUCTION);
    }

    // === STOCK OUT SCOPES ===

    public function scopeStockOut($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_USAGE_PRODUCTION,
            self::TYPE_USAGE_COOKING,
            self::TYPE_USAGE_PREPARATION,
            self::TYPE_WASTAGE,
            self::TYPE_RETURN_TO_WAREHOUSE,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_USAGE // Legacy
        ]);
    }

    public function scopeUsage($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_USAGE_PRODUCTION,
            self::TYPE_USAGE_COOKING,
            self::TYPE_USAGE_PREPARATION,
            self::TYPE_USAGE // Legacy
        ]);
    }

    public function scopeUsageProduction($query)
    {
        return $query->where('transaction_type', self::TYPE_USAGE_PRODUCTION);
    }

    public function scopeUsageCooking($query)
    {
        return $query->where('transaction_type', self::TYPE_USAGE_COOKING);
    }

    public function scopeUsagePreparation($query)
    {
        return $query->where('transaction_type', self::TYPE_USAGE_PREPARATION);
    }

    public function scopeWastage($query)
    {
        return $query->where('transaction_type', self::TYPE_WASTAGE);
    }

    public function scopeReturnToWarehouse($query)
    {
        return $query->where('transaction_type', self::TYPE_RETURN_TO_WAREHOUSE);
    }

    // === ADJUSTMENT SCOPES ===

    public function scopeAdjustment($query)
    {
        return $query->whereIn('transaction_type', [
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_STOCK_TAKE,
            self::TYPE_ADJUSTMENT // Legacy
        ]);
    }

    public function scopeAdjustmentIn($query)
    {
        return $query->where('transaction_type', self::TYPE_ADJUSTMENT_IN);
    }

    public function scopeAdjustmentOut($query)
    {
        return $query->where('transaction_type', self::TYPE_ADJUSTMENT_OUT);
    }

    public function scopeStockTake($query)
    {
        return $query->where('transaction_type', self::TYPE_STOCK_TAKE);
    }

    // === TIME SCOPES ===

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('transaction_date', $year)
                     ->whereMonth('transaction_date', $month);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('transaction_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
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

    // === LINKED TRANSACTION SCOPES ===

    public function scopeFromWarehouse($query)
    {
        return $query->whereNotNull('branch_warehouse_transaction_id');
    }

    public function scopeWithoutWarehouseLink($query)
    {
        return $query->whereNull('branch_warehouse_transaction_id');
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
            self::TYPE_RECEIVE_FROM_WAREHOUSE => 'Terima dari Gudang',
            self::TYPE_TRANSFER_IN => 'Transfer Masuk',
            self::TYPE_RETURN_FROM_PRODUCTION => 'Return dari Produksi',
            self::TYPE_ADJUSTMENT_IN => 'Adjustment (+)',
            self::TYPE_OPENING_BALANCE => 'Opening Balance',
            
            // Stock OUT
            self::TYPE_USAGE_PRODUCTION => 'Pakai untuk Produksi',
            self::TYPE_USAGE_COOKING => 'Pakai untuk Memasak',
            self::TYPE_USAGE_PREPARATION => 'Pakai untuk Persiapan',
            self::TYPE_WASTAGE => 'Terbuang/Rusak',
            self::TYPE_RETURN_TO_WAREHOUSE => 'Return ke Gudang',
            self::TYPE_ADJUSTMENT_OUT => 'Adjustment (-)',
            
            // Special
            self::TYPE_CLOSING_BALANCE => 'Closing Balance',
            self::TYPE_STOCK_TAKE => 'Stock Opname',
            
            // Legacy
            self::TYPE_USAGE => 'Penggunaan (Legacy)',
            self::TYPE_ADJUSTMENT => 'Penyesuaian (Legacy)'
        ];
    }

    /**
     * Get stock IN transaction types
     */
    public static function getStockInTypes()
    {
        return [
            self::TYPE_RECEIVE_FROM_WAREHOUSE,
            self::TYPE_TRANSFER_IN,
            self::TYPE_RETURN_FROM_PRODUCTION,
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
            self::TYPE_USAGE_PRODUCTION,
            self::TYPE_USAGE_COOKING,
            self::TYPE_USAGE_PREPARATION,
            self::TYPE_WASTAGE,
            self::TYPE_RETURN_TO_WAREHOUSE,
            self::TYPE_ADJUSTMENT_OUT,
            self::TYPE_USAGE // Legacy
        ];
    }

    /**
     * Check if transaction is stock IN
     */
    public function isStockIn()
    {
        return in_array($this->transaction_type, [
            self::TYPE_RECEIVE_FROM_BRANCH_WAREHOUSE,
            self::TYPE_RECEIVE_FROM_OUTLET_WAREHOUSE,  // ✅ ADD
            self::TYPE_TRANSFER_IN,
            self::TYPE_ADJUSTMENT_IN,
        ]);
    }

    /**
     * Check if transaction is stock OUT
     */
    public function isStockOut()
    {
        return in_array($this->transaction_type, self::getStockOutTypes());
    }

    /**
     * Check if transaction is usage type
     */
    public function isUsage()
    {
        return in_array($this->transaction_type, [
            self::TYPE_USAGE_PRODUCTION,
            self::TYPE_USAGE_COOKING,
            self::TYPE_USAGE_PREPARATION,
            self::TYPE_USAGE
        ]);
    }

    /**
     * Generate reference number
     */
    public static function generateReferenceNo($branchId, $type)
    {
        $date = now()->format('ymd');
        $branchCode = sprintf('%03d', $branchId);
        
        $typeCode = match($type) {
            self::TYPE_RECEIVE_FROM_WAREHOUSE => 'KRW',
            self::TYPE_USAGE_PRODUCTION => 'KUP',
            self::TYPE_USAGE_COOKING => 'KUC',
            self::TYPE_USAGE_PREPARATION => 'KUR',
            self::TYPE_WASTAGE => 'KWS',
            self::TYPE_RETURN_TO_WAREHOUSE => 'KRH',
            self::TYPE_ADJUSTMENT_IN => 'KAI',
            self::TYPE_ADJUSTMENT_OUT => 'KAO',
            self::TYPE_STOCK_TAKE => 'KST',
            default => 'KTX'
        };

        $lastTransaction = self::where('branch_id', $branchId)
            ->where('transaction_type', $type)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? 
            (intval(substr($lastTransaction->reference_no ?? '', -4)) + 1) : 1;

        return "{$typeCode}-{$branchCode}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // ========================================
    // STATIC FACTORY METHODS
    // ========================================

    /**
     * Create receive from warehouse transaction
     */
    public static function createReceiveFromWarehouse($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'branch_id' => $data['branch_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_RECEIVE_FROM_WAREHOUSE,
            'quantity' => $data['quantity'],
            'branch_warehouse_transaction_id' => $data['branch_warehouse_transaction_id'] ?? null,
            'warehouse_transaction_id' => $data['warehouse_transaction_id'] ?? null, // Legacy
            'notes' => $data['notes'] ?? 'Terima dari gudang',
            'transaction_date' => $data['transaction_date'] ?? now()
        ]);

        // Update kitchen monthly balance
        $balance = MonthlyKitchenStockBalance::getOrCreateBalance(
            $data['item_id'],
            $data['branch_id'],
            $transaction->transaction_date->year,
            $transaction->transaction_date->month
        );
        
        $balance->updateMovement('IN', $data['quantity']);

        return $transaction;
    }

    /**
     * Create transfer in transaction (legacy compatibility)
     */
    public static function createTransferIn($itemId, $quantity, $warehouseTransactionId, $notes = null, $userId = null, $branchId = null)
    {
        return self::createReceiveFromWarehouse([
            'item_id' => $itemId,
            'branch_id' => $branchId ?? auth()->user()?->branch_id,
            'user_id' => $userId ?? auth()->id(),
            'quantity' => $quantity,
            'warehouse_transaction_id' => $warehouseTransactionId,
            'notes' => $notes ?? 'Transfer dari gudang'
        ]);
    }

    /**
     * Create usage production transaction
     */
    public static function createUsageProduction($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'branch_id' => $data['branch_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_USAGE_PRODUCTION,
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? 'Pakai untuk produksi',
            'transaction_date' => $data['transaction_date'] ?? now()
        ]);

        // Update kitchen monthly balance
        $balance = MonthlyKitchenStockBalance::getOrCreateBalance(
            $data['item_id'],
            $data['branch_id'],
            $transaction->transaction_date->year,
            $transaction->transaction_date->month
        );
        
        $balance->updateMovement('OUT', $data['quantity']);

        return $transaction;
    }

    /**
     * Create usage cooking transaction
     */
    public static function createUsageCooking($data)
    {
        $data['transaction_type'] = self::TYPE_USAGE_COOKING;
        $data['notes'] = $data['notes'] ?? 'Pakai untuk memasak';
        
        return self::createUsageProduction($data);
    }

    /**
     * Create usage preparation transaction
     */
    public static function createUsagePreparation($data)
    {
        $data['transaction_type'] = self::TYPE_USAGE_PREPARATION;
        $data['notes'] = $data['notes'] ?? 'Pakai untuk persiapan';
        
        return self::createUsageProduction($data);
    }

    /**
     * Create usage transaction (legacy compatibility)
     */
    public static function createUsage($itemId, $quantity, $notes = null, $userId = null, $branchId = null)
    {
        return self::createUsageProduction([
            'item_id' => $itemId,
            'branch_id' => $branchId ?? auth()->user()?->branch_id,
            'user_id' => $userId ?? auth()->id(),
            'quantity' => $quantity,
            'notes' => $notes ?? 'Penggunaan dapur'
        ]);
    }

    /**
     * Create adjustment transaction
     */
    public static function createAdjustment($itemId, $quantity, $notes = null, $userId = null, $branchId = null)
    {
        $type = $quantity >= 0 ? self::TYPE_ADJUSTMENT_IN : self::TYPE_ADJUSTMENT_OUT;
        $absQuantity = abs($quantity);

        $transaction = self::create([
            'item_id' => $itemId,
            'branch_id' => $branchId ?? auth()->user()?->branch_id,
            'user_id' => $userId ?? auth()->id(),
            'transaction_type' => $type,
            'quantity' => $absQuantity,
            'notes' => $notes ?? 'Penyesuaian stock dapur',
            'transaction_date' => now()
        ]);

        // Update kitchen monthly balance
        $balance = MonthlyKitchenStockBalance::getOrCreateBalance(
            $itemId,
            $branchId ?? auth()->user()?->branch_id,
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
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_WASTAGE,
            'quantity' => $data['quantity'],
            'notes' => $data['notes'] ?? 'Barang terbuang/rusak',
            'transaction_date' => $data['transaction_date'] ?? now()
        ]);

        // Update kitchen monthly balance
        $balance = MonthlyKitchenStockBalance::getOrCreateBalance(
            $data['item_id'],
            $data['branch_id'],
            $transaction->transaction_date->year,
            $transaction->transaction_date->month
        );
        
        $balance->updateMovement('OUT', $data['quantity']);

        return $transaction;
    }

    /**
     * Create RECEIVE FROM OUTLET WAREHOUSE transaction
     * NEW METHOD!
     *
     * @param array $data
     * @return KitchenStockTransaction|null
     */
    public static function createReceiveFromOutletWarehouse($data)
    {
        try {
            DB::beginTransaction();

            // Validate required data
            if (!isset($data['branch_id'], $data['item_id'], $data['quantity'])) {
                throw new \Exception('Missing required data for receive from outlet warehouse transaction');
            }

            // Generate reference number
            $referenceNo = self::generateReferenceNo('RCV-OUT', $data['branch_id']);

            // Get current balance
            $year = $data['year'] ?? date('Y');
            $month = $data['month'] ?? date('m');
            
            $balance = MonthlyKitchenStockBalance::getOrCreateBalance(
                $data['item_id'],
                $data['branch_id'],
                $year,
                $month
            );

            $balanceBefore = $balance->closing_stock;
            $balanceAfter = $balanceBefore + $data['quantity'];

            // Create transaction
            $transaction = self::create([
                'branch_id' => $data['branch_id'],
                'item_id' => $data['item_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'outlet_warehouse_transaction_id' => $data['outlet_warehouse_transaction_id'] ?? null,
                'transaction_type' => self::TYPE_RECEIVE_FROM_OUTLET_WAREHOUSE,
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'total_cost' => isset($data['unit_cost']) ? ($data['quantity'] * $data['unit_cost']) : null,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_no' => $referenceNo,
                'batch_no' => $data['batch_no'] ?? null,
                'notes' => $data['notes'] ?? 'Terima dari outlet warehouse',
                'transaction_date' => $data['transaction_date'] ?? now(),
                'year' => $year,
                'month' => $month,
                'status' => $data['status'] ?? self::STATUS_COMPLETED,
            ]);

            // Update monthly balance
            $balance->updateMovement('received_from_outlet_warehouse', $data['quantity']);

            DB::commit();

            Log::info('Kitchen stock transaction created - RECEIVE FROM OUTLET WAREHOUSE', [
                'transaction_id' => $transaction->id,
                'reference_no' => $referenceNo,
                'branch_id' => $data['branch_id'],
                'item_id' => $data['item_id'],
                'quantity' => $data['quantity'],
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create kitchen receive from outlet warehouse transaction error: ' . $e->getMessage());
            return null;
        }
    }

    // ========================================
    // ANALYTICS METHODS
    // ========================================

    /**
     * Get branch kitchen transaction summary
     */
    public static function getBranchKitchenSummary($branchId, $startDate = null, $endDate = null)
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
            'total_usage' => $transactions->where('isUsage', true)->sum('quantity'),
            'usage_breakdown' => [
                'production' => $transactions->where('transaction_type', self::TYPE_USAGE_PRODUCTION)->sum('quantity'),
                'cooking' => $transactions->where('transaction_type', self::TYPE_USAGE_COOKING)->sum('quantity'),
                'preparation' => $transactions->where('transaction_type', self::TYPE_USAGE_PREPARATION)->sum('quantity'),
                'legacy' => $transactions->where('transaction_type', self::TYPE_USAGE)->sum('quantity')
            ],
            'receive_from_warehouse' => $transactions->where('transaction_type', self::TYPE_RECEIVE_FROM_WAREHOUSE)->sum('quantity'),
            'wastage' => $transactions->where('transaction_type', self::TYPE_WASTAGE)->sum('quantity'),
            'by_type' => $transactions->groupBy('transaction_type')->map->count(),
            'top_items' => $transactions->groupBy('item_id')->map->sum('quantity')->sortDesc()->take(10)
        ];
    }

    /**
     * Get kitchen efficiency metrics
     */
    public static function getKitchenEfficiencyMetrics($branchId, $startDate = null, $endDate = null)
    {
        $query = self::forBranch($branchId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->thisMonth();
        }

        $transactions = $query->get();
        $totalUsage = $transactions->where('isUsage', true)->sum('quantity');
        $totalWastage = $transactions->where('transaction_type', self::TYPE_WASTAGE)->sum('quantity');
        $totalReceived = $transactions->where('isStockIn', true)->sum('quantity');

        return [
            'usage_efficiency' => $totalReceived > 0 ? ($totalUsage / $totalReceived) * 100 : 0,
            'wastage_rate' => $totalReceived > 0 ? ($totalWastage / $totalReceived) * 100 : 0,
            'utilization_rate' => $totalReceived > 0 ? (($totalUsage + $totalWastage) / $totalReceived) * 100 : 0,
            'total_received' => $totalReceived,
            'total_usage' => $totalUsage,
            'total_wastage' => $totalWastage
        ];
    }

    /**
     * Get popular items in kitchen
     */
    public static function getPopularKitchenItems($branchId, $limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::forBranch($branchId)->usage();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->thisMonth();
        }

        return $query->selectRaw('
                item_id,
                SUM(quantity) as total_usage,
                COUNT(*) as usage_frequency,
                AVG(quantity) as avg_usage_per_transaction,
                MAX(transaction_date) as last_usage_date
            ')
            ->with('item')
            ->groupBy('item_id')
            ->orderByDesc('total_usage')
            ->limit($limit)
            ->get();
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get formatted quantity dengan sign
     */
    public function getFormattedQuantityAttribute()
    {
        $sign = $this->isStockIn() ? '+' : '-';
        return $sign . number_format($this->quantity, 3);
    }

    /**
     * Get transaction type color untuk UI
     */
    public function getTransactionTypeColorAttribute()
    {
        if ($this->isStockIn()) {
            return 'success';
        } elseif ($this->isUsage()) {
            return 'primary';
        } elseif ($this->transaction_type === self::TYPE_WASTAGE) {
            return 'danger';
        } elseif ($this->isStockOut()) {
            return 'warning';
        } else {
            return 'secondary';
        }
    }

    /**
     * Get transaction type display text
     */
    public function getTransactionTypeTextAttribute()
    {
        return self::getTransactionTypes()[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * Get branch name
     */
    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->branch_name : 'Unknown Branch';
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
     * Check if linked to warehouse transaction
     */
    public function getIsFromWarehouseAttribute()
    {
        return !is_null($this->branch_warehouse_transaction_id) || !is_null($this->warehouse_transaction_id);
    }

    /**
     * Get warehouse reference
     */
    public function getWarehouseReferenceAttribute()
    {
        if ($this->branchWarehouseTransaction) {
            return $this->branchWarehouseTransaction->reference_no;
        }
        
        if ($this->warehouseTransaction) {
            return $this->warehouseTransaction->reference_no ?? 'Legacy';
        }

        return null;
    }

    /**
     * Get transaction value (quantity × unit cost)
     */
    public function getTransactionValueAttribute()
    {
        $unitCost = $this->item ? $this->item->unit_cost : 0;
        return $this->quantity * $unitCost;
    }

    /**
     * Get transaction type label
     * UPDATED: Add new outlet warehouse type
     */
    public function getTransactionTypeLabelAttribute()
    {
        return match($this->transaction_type) {
            self::TYPE_RECEIVE_FROM_BRANCH_WAREHOUSE => 'Terima dari Branch Warehouse',
            self::TYPE_RECEIVE_FROM_OUTLET_WAREHOUSE => 'Terima dari Outlet Warehouse',  // ✅ NEW
            self::TYPE_USAGE => 'Pemakaian',
            self::TYPE_WASTE => 'Waste/Rusak',
            self::TYPE_ADJUSTMENT_IN => 'Penyesuaian (+)',
            self::TYPE_ADJUSTMENT_OUT => 'Penyesuaian (-)',
            self::TYPE_RETURN_TO_WAREHOUSE => 'Retur ke Warehouse',
            self::TYPE_TRANSFER_IN => 'Transfer Masuk',
            self::TYPE_TRANSFER_OUT => 'Transfer Keluar',
            self::TYPE_OPENING_BALANCE => 'Saldo Awal',
            self::TYPE_CLOSING_BALANCE => 'Saldo Akhir',
            default => 'Unknown',
        };
    }

    /**
     * Get stock source (where stock came from)
     * NEW METHOD!
     */
    public function getStockSourceAttribute()
    {
        if ($this->branch_warehouse_transaction_id) {
            return [
                'type' => 'branch_warehouse',
                'label' => 'Branch Warehouse',
                'transaction' => $this->branchWarehouseTransaction,
            ];
        } elseif ($this->outlet_warehouse_transaction_id) {
            return [
                'type' => 'outlet_warehouse',
                'label' => 'Outlet Warehouse',
                'transaction' => $this->outletWarehouseTransaction,
            ];
        }

        return [
            'type' => 'direct',
            'label' => 'Direct Entry',
            'transaction' => null,
        ];
    }

    /**
     * Get source warehouse info
     * NEW METHOD!
     */
    public function getSourceWarehouseInfoAttribute()
    {
        if ($this->branch_warehouse_transaction_id && $this->branchWarehouseTransaction) {
            $warehouse = $this->branchWarehouseTransaction->warehouse;
            return [
                'type' => 'branch',
                'warehouse_code' => $warehouse->warehouse_code ?? '-',
                'warehouse_name' => $warehouse->warehouse_name ?? '-',
                'transaction_ref' => $this->branchWarehouseTransaction->reference_no,
            ];
        } elseif ($this->outlet_warehouse_transaction_id && $this->outletWarehouseTransaction) {
            $warehouse = $this->outletWarehouseTransaction->outletWarehouse;
            return [
                'type' => 'outlet',
                'warehouse_code' => $warehouse->warehouse_code ?? '-',
                'warehouse_name' => $warehouse->warehouse_name ?? '-',
                'transaction_ref' => $this->outletWarehouseTransaction->reference_no,
            ];
        }

        return null;
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'item_id' => 'required|exists:items,id',
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'transaction_type' => 'required|in:' . implode(',', array_keys(self::getTransactionTypes())),
            'quantity' => 'required|numeric|min:0.001',
            'warehouse_transaction_id' => 'nullable|exists:stock_transactions,id',
            'branch_warehouse_transaction_id' => 'nullable|exists:branch_stock_transactions,id',
            'notes' => 'nullable|string|max:500',
            'transaction_date' => 'required|date'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_id.required' => 'Item wajib dipilih',
            'branch_id.required' => 'Branch wajib dipilih',
            'user_id.required' => 'User wajib dipilih',
            'transaction_type.required' => 'Jenis transaksi wajib dipilih',
            'quantity.required' => 'Jumlah wajib diisi',
            'quantity.min' => 'Jumlah harus lebih dari 0',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi'
        ];
    }
}