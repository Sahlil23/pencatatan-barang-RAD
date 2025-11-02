<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CentralStockTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'user_id',
        'supplier_id',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_no',
        'target_branch_id',
        'notes',
        'transaction_date'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'transaction_date' => 'datetime'
    ];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Transaction Types
    const TYPE_PURCHASE = 'PURCHASE';
    const TYPE_PURCHASE_RETURN = 'PURCHASE_RETURN';
    const TYPE_DISTRIBUTE_OUT = 'DISTRIBUTE_OUT';
    const TYPE_BRANCH_RETURN = 'BRANCH_RETURN';
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    const TYPE_WASTE = 'WASTE';

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
     * Transaction belongs to warehouse (central warehouse)
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Transaction belongs to user (who performed the transaction)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaction belongs to supplier (untuk purchase transactions)
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Transaction belongs to target branch (untuk distribute transactions)
     */
    public function targetBranch()
    {
        return $this->belongsTo(Branch::class, 'target_branch_id');
    }

    /**
     * Related branch stock transactions (untuk tracking distribution)
     */
    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class, 'central_transaction_id');
    }

    /**
     * Related distribution order detail (jika dari distribution order)
     */
    public function distributionOrderDetail()
    {
        return $this->hasOne(DistributionOrderDetail::class, 'central_transaction_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope untuk transaction type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope untuk purchase transactions
     */
    public function scopePurchase($query)
    {
        return $query->where('transaction_type', self::TYPE_PURCHASE);
    }

    /**
     * Scope untuk distribution transactions
     */
    public function scopeDistribution($query)
    {
        return $query->where('transaction_type', self::TYPE_DISTRIBUTE_OUT);
    }

    /**
     * Scope untuk adjustment transactions
     */
    public function scopeAdjustment($query)
    {
        return $query->where('transaction_type', self::TYPE_ADJUSTMENT);
    }

    /**
     * Scope untuk date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    /**
     * Scope untuk today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    /**
     * Scope untuk specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope untuk specific item
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope untuk search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('reference_no', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%")
              ->orWhereHas('item', function($itemQuery) use ($search) {
                  $itemQuery->where('item_name', 'like', "%{$search}%")
                           ->orWhere('sku', 'like', "%{$search}%");
              });
        });
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Process transaction dan update stock balance
     */
    public function processTransaction()
    {
        // Get atau create balance untuk period ini
        $balance = CentralStockBalance::getOrCreateBalance(
            $this->item_id,
            $this->warehouse_id,
            $this->transaction_date->year,
            $this->transaction_date->month
        );

        // Update balance berdasarkan transaction
        $balance->updateFromTransaction($this);

        // Update item's central stock level
        $this->updateItemCentralStock();

        return $this;
    }

    /**
     * Update central stock level di item
     */
    protected function updateItemCentralStock()
    {
        $currentBalance = CentralStockBalance::where([
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'year' => now()->year,
            'month' => now()->month
        ])->first();

        if ($currentBalance) {
            $this->item->update([
                'central_stock_level' => $currentBalance->closing_stock
            ]);
        }
    }

    /**
     * Generate reference number otomatis
     */
    public static function generateReferenceNo($type, $warehouseCode = 'CENTRAL')
    {
        $prefix = match($type) {
            self::TYPE_PURCHASE => 'PUR',
            self::TYPE_PURCHASE_RETURN => 'PRN',
            self::TYPE_DISTRIBUTE_OUT => 'DST',
            self::TYPE_BRANCH_RETURN => 'BRN',
            self::TYPE_ADJUSTMENT => 'ADJ',
            self::TYPE_WASTE => 'WST',
            default => 'TRX'
        };

        $date = now()->format('ymd');
        $lastTransaction = self::where('transaction_type', $type)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? 
            (intval(substr($lastTransaction->reference_no, -4)) + 1) : 1;

        return "{$prefix}-{$warehouseCode}-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create purchase transaction
     */
    public static function createPurchase($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'supplier_id' => $data['supplier_id'],
            'transaction_type' => self::TYPE_PURCHASE,
            'quantity' => $data['quantity'],
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => ($data['quantity'] * ($data['unit_cost'] ?? 0)),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo(self::TYPE_PURCHASE),
            'notes' => $data['notes'] ?? null,
            'transaction_date' => $data['transaction_date'] ?? now()
        ]);

        $transaction->processTransaction();
        return $transaction;
    }

    /**
     * Create distribution transaction
     */
    public static function createDistribution($data)
    {
        // Validate stock availability
        $currentStock = self::getCurrentStock($data['item_id'], $data['warehouse_id']);
        if ($currentStock < $data['quantity']) {
            throw new \Exception('Insufficient stock for distribution. Available: ' . $currentStock);
        }

        $transaction = self::create([
            'item_id' => $data['item_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_DISTRIBUTE_OUT,
            'quantity' => $data['quantity'],
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => ($data['quantity'] * ($data['unit_cost'] ?? 0)),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo(self::TYPE_DISTRIBUTE_OUT),
            'target_branch_id' => $data['target_branch_id'],
            'notes' => $data['notes'] ?? null,
            'transaction_date' => $data['transaction_date'] ?? now()
        ]);

        $transaction->processTransaction();
        return $transaction;
    }

    /**
     * Create adjustment transaction
     */
    public static function createAdjustment($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_ADJUSTMENT,
            'quantity' => $data['quantity'], // bisa positif atau negatif
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => ($data['quantity'] * ($data['unit_cost'] ?? 0)),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo(self::TYPE_ADJUSTMENT),
            'notes' => $data['notes'] ?? 'Stock adjustment',
            'transaction_date' => $data['transaction_date'] ?? now()
        ]);

        $transaction->processTransaction();
        return $transaction;
    }

    /**
     * Create waste transaction
     */
    public static function createWaste($data)
    {
        $transaction = self::create([
            'item_id' => $data['item_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => $data['user_id'],
            'transaction_type' => self::TYPE_WASTE,
            'quantity' => $data['quantity'],
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => ($data['quantity'] * ($data['unit_cost'] ?? 0)),
            'reference_no' => $data['reference_no'] ?? self::generateReferenceNo(self::TYPE_WASTE),
            'notes' => $data['notes'] ?? 'Waste/damaged items',
            'transaction_date' => $data['transaction_date'] ?? now()
        ]);

        $transaction->processTransaction();
        return $transaction;
    }

    /**
     * Get current stock untuk item di warehouse
     */
    public static function getCurrentStock($itemId, $warehouseId)
    {
        $balance = CentralStockBalance::where([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'year' => now()->year,
            'month' => now()->month
        ])->first();

        return $balance ? $balance->closing_stock : 0;
    }

    /**
     * Reverse transaction (create opposite transaction)
     */
    public function reverse($reason = null)
    {
        $reverseType = match($this->transaction_type) {
            self::TYPE_PURCHASE => self::TYPE_PURCHASE_RETURN,
            self::TYPE_DISTRIBUTE_OUT => self::TYPE_BRANCH_RETURN,
            self::TYPE_ADJUSTMENT => self::TYPE_ADJUSTMENT,
            default => throw new \Exception('Transaction type cannot be reversed')
        };

        $reverseQuantity = $this->transaction_type === self::TYPE_ADJUSTMENT ? 
            -$this->quantity : $this->quantity;

        $reverseTransaction = self::create([
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'user_id' => auth()->id(),
            'supplier_id' => $this->supplier_id,
            'transaction_type' => $reverseType,
            'quantity' => $reverseQuantity,
            'unit_cost' => $this->unit_cost,
            'total_cost' => $reverseQuantity * $this->unit_cost,
            'reference_no' => self::generateReferenceNo($reverseType),
            'target_branch_id' => $this->target_branch_id,
            'notes' => "Reverse of {$this->reference_no}. Reason: " . ($reason ?? 'Manual reversal'),
            'transaction_date' => now()
        ]);

        $reverseTransaction->processTransaction();
        return $reverseTransaction;
    }

    /**
     * Check if transaction dapat di-reverse
     */
    public function canBeReversed()
    {
        return in_array($this->transaction_type, [
            self::TYPE_PURCHASE,
            self::TYPE_DISTRIBUTE_OUT,
            self::TYPE_ADJUSTMENT
        ]) && $this->created_at->isToday();
    }

    // ========================================
    // STATIC ANALYTICS METHODS
    // ========================================

    /**
     * Get purchase summary untuk period
     */
    public static function getPurchaseSummary($warehouseId, $startDate = null, $endDate = null)
    {
        $query = self::forWarehouse($warehouseId)
            ->purchase()
            ->with('item', 'supplier');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->currentMonth();
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_quantity' => $transactions->sum('quantity'),
            'total_cost' => $transactions->sum('total_cost'),
            'avg_unit_cost' => $transactions->avg('unit_cost'),
            'unique_items' => $transactions->unique('item_id')->count(),
            'unique_suppliers' => $transactions->unique('supplier_id')->count(),
            'transactions' => $transactions
        ];
    }

    /**
     * Get distribution summary untuk period
     */
    public static function getDistributionSummary($warehouseId, $startDate = null, $endDate = null)
    {
        $query = self::forWarehouse($warehouseId)
            ->distribution()
            ->with('item', 'targetBranch');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->currentMonth();
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_quantity' => $transactions->sum('quantity'),
            'total_value' => $transactions->sum('total_cost'),
            'unique_items' => $transactions->unique('item_id')->count(),
            'unique_branches' => $transactions->unique('target_branch_id')->count(),
            'by_branch' => $transactions->groupBy('target_branch_id'),
            'transactions' => $transactions
        ];
    }

    /**
     * Get top items by quantity/value
     */
    public static function getTopItems($warehouseId, $type = 'quantity', $limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::forWarehouse($warehouseId)
            ->with('item');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->currentMonth();
        }

        $aggregateField = $type === 'value' ? 'total_cost' : 'quantity';

        return $query->selectRaw("item_id, SUM({$aggregateField}) as total")
            ->groupBy('item_id')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
    }

    // ========================================
    // ATTRIBUTES
    // ========================================

    /**
     * Get transaction type display name
     */
    public function getTypeDisplayAttribute()
    {
        return match($this->transaction_type) {
            self::TYPE_PURCHASE => 'Pembelian',
            self::TYPE_PURCHASE_RETURN => 'Return Pembelian',
            self::TYPE_DISTRIBUTE_OUT => 'Distribusi Keluar',
            self::TYPE_BRANCH_RETURN => 'Return dari Cabang',
            self::TYPE_ADJUSTMENT => 'Penyesuaian',
            self::TYPE_WASTE => 'Barang Rusak',
            default => 'Unknown'
        };
    }

    /**
     * Get transaction type badge untuk UI
     */
    public function getTypeBadgeAttribute()
    {
        return match($this->transaction_type) {
            self::TYPE_PURCHASE => '<span class="badge bg-success">Pembelian</span>',
            self::TYPE_PURCHASE_RETURN => '<span class="badge bg-warning">Return Pembelian</span>',
            self::TYPE_DISTRIBUTE_OUT => '<span class="badge bg-primary">Distribusi</span>',
            self::TYPE_BRANCH_RETURN => '<span class="badge bg-info">Return Cabang</span>',
            self::TYPE_ADJUSTMENT => '<span class="badge bg-secondary">Penyesuaian</span>',
            self::TYPE_WASTE => '<span class="badge bg-danger">Barang Rusak</span>',
            default => '<span class="badge bg-dark">Unknown</span>'
        };
    }

    /**
     * Get formatted quantity dengan unit
     */
    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity, 2) . ' ' . ($this->item->unit ?? '');
    }

    /**
     * Get formatted total cost
     */
    public function getFormattedTotalCostAttribute()
    {
        return 'Rp ' . number_format($this->total_cost, 0, ',', '.');
    }

    /**
     * Check if transaction is incoming (menambah stock)
     */
    public function getIsIncomingAttribute()
    {
        return in_array($this->transaction_type, [
            self::TYPE_PURCHASE,
            self::TYPE_BRANCH_RETURN
        ]) || ($this->transaction_type === self::TYPE_ADJUSTMENT && $this->quantity > 0);
    }

    /**
     * Check if transaction is outgoing (mengurangi stock)
     */
    public function getIsOutgoingAttribute()
    {
        return in_array($this->transaction_type, [
            self::TYPE_PURCHASE_RETURN,
            self::TYPE_DISTRIBUTE_OUT,
            self::TYPE_WASTE
        ]) || ($this->transaction_type === self::TYPE_ADJUSTMENT && $this->quantity < 0);
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'user_id' => 'required|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'transaction_type' => 'required|in:' . implode(',', [
                self::TYPE_PURCHASE,
                self::TYPE_PURCHASE_RETURN,
                self::TYPE_DISTRIBUTE_OUT,
                self::TYPE_BRANCH_RETURN,
                self::TYPE_ADJUSTMENT,
                self::TYPE_WASTE
            ]),
            'quantity' => 'required|numeric|not_in:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'reference_no' => 'nullable|string|max:50',
            'target_branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string|max:500',
            'transaction_date' => 'required|date'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_id.required' => 'Item wajib dipilih',
            'warehouse_id.required' => 'Warehouse wajib dipilih',
            'transaction_type.required' => 'Tipe transaksi wajib dipilih',
            'quantity.required' => 'Quantity wajib diisi',
            'quantity.not_in' => 'Quantity tidak boleh 0',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi'
        ];
    }
}