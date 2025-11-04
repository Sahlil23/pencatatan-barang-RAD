<?php
// filepath: app/Models/Item.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name', 
        'category_id',
        'unit',
        'low_stock_threshold',
        'unit_cost',
        'status'
    ];

    protected $casts = [
        'low_stock_threshold' => 'decimal:3',
        'unit_cost' => 'decimal:2'
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'stock_transactions')
                    ->withPivot(['transaction_type', 'quantity', 'transaction_date', 'notes'])
                    ->withTimestamps()
                    ->distinct();
    }

    // === CENTRAL STOCK RELATIONSHIPS ===
    
    public function centralStockBalances()
    {
        return $this->hasMany(CentralStockBalance::class);
    }

    public function centralStockTransactions()
    {
        return $this->hasMany(CentralStockTransaction::class);
    }

    public function currentCentralBalance()
    {
        return $this->hasOne(CentralStockBalance::class)
            ->where('year', now()->year)
            ->where('month', now()->month);
    }

    // === BRANCH RELATIONSHIPS ===
    
    public function branchMonthlyBalances()
    {
        return $this->hasMany(BranchMonthlyBalance::class);
    }

    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class);
    }

    public function branchWarehouseBalances()
    {
        return $this->hasMany(BranchWarehouseMonthlyBalance::class);
    }

    // === DISTRIBUTION RELATIONSHIPS ===
    
    public function centralToBranchTransactions()
    {
        return $this->hasMany(CentralToBranchWarehouseTransaction::class);
    }

    // ========================================
    // CENTRAL STOCK METHODS
    // ========================================

    /**
     * Get current central stock level untuk specific warehouse
     */
    public function getCentralStock($warehouseId = null)
    {
        if ($warehouseId) {
            $balance = $this->centralStockBalances()
                ->where('warehouse_id', $warehouseId)
                ->where('year', now()->year)
                ->where('month', now()->month)
                ->first();
                
            return $balance ? $balance->closing_stock : 0;
        }

        // Return total central stock dari semua central warehouses
        return $this->centralStockBalances()
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->sum('closing_stock');
    }

    /**
     * Get total branch stock
     */
    public function getTotalBranchStock()
    {
        return $this->branchWarehouseBalances()
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->sum('closing_stock');
    }

    /**
     * Get branch stock untuk specific branch warehouse
     */
    public function getBranchStock($branchWarehouseId)
    {
        $balance = $this->branchWarehouseBalances()
            ->where('warehouse_id', $branchWarehouseId)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        return $balance ? $balance->closing_stock : 0;
    }

    /**
     * Get total system stock (central + branch)
     */
    public function getTotalSystemStock()
    {
        return $this->getCentralStock() + $this->getTotalBranchStock();
    }

    /**
     * Check if item is low stock in central
     */
    public function isLowStockInCentral($warehouseId = null)
    {
        $centralStock = $this->getCentralStock($warehouseId);
        $threshold = $this->low_stock_threshold ?? 0;
        
        return $centralStock <= $threshold && $centralStock > 0;
    }

    /**
     * Check if item is out of stock in central
     */
    public function isOutOfStockInCentral($warehouseId = null)
    {
        return $this->getCentralStock($warehouseId) <= 0;
    }

    /**
     * Check if item can be distributed
     */
    public function canDistribute($quantity, $warehouseId = null)
    {
        $centralStock = $this->getCentralStock($warehouseId);
        return $centralStock >= $quantity;
    }

    /**
     * Get available quantity untuk distribution
     */
    public function getAvailableForDistribution($warehouseId = null)
    {
        return max(0, $this->getCentralStock($warehouseId));
    }

    /**
     * Get stock distribution breakdown
     */
    public function getStockDistribution()
    {
        return [
            'central' => $this->getCentralStock(),
            'total_branch' => $this->getTotalBranchStock(),
            'total_system' => $this->getTotalSystemStock(),
            'central_percentage' => $this->getCentralStockPercentage()
        ];
    }

    /**
     * Get central stock percentage
     */
    public function getCentralStockPercentage()
    {
        $totalStock = $this->getTotalSystemStock();
        
        if ($totalStock <= 0) {
            return 0;
        }

        return ($this->getCentralStock() / $totalStock) * 100;
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }


    // === MISSING SCOPES untuk beranda.php ===

    /**
     * Scope untuk low stock items (backward compatibility)
     */
    public function scopeLowStock($query)
    {
        // For backward compatibility, check both central and branch low stock
        return $query->where(function($q) {
            // Check central low stock
            $q->whereHas('centralStockBalances', function($centralQuery) {
                $centralQuery->where('year', now()->year)
                             ->where('month', now()->month)
                             ->whereRaw('closing_stock <= (SELECT low_stock_threshold FROM items WHERE items.id = central_stock_balances.item_id)')
                             ->where('closing_stock', '>', 0);
            })
            // OR check if has current_stock column (legacy)
            ->orWhereRaw('current_stock <= low_stock_threshold AND current_stock > 0');
        });
    }

    /**
     * Scope untuk out of stock items (backward compatibility)
     */
    public function scopeOutOfStock($query)
    {
        return $query->where(function($q) {
            // Check central out of stock
            $q->whereHas('centralStockBalances', function($centralQuery) {
                $centralQuery->where('year', now()->year)
                             ->where('month', now()->month)
                             ->where('closing_stock', '<=', 0);
            })
            // OR check if has current_stock column (legacy)
            ->orWhere('current_stock', '<=', 0);
        });
    }

    /**
     * Scope untuk stock in transactions
     */
    public function scopeStockIn($query)
    {
        return $query->where('transaction_type', 'IN')
                     ->orWhere('transaction_type', 'PURCHASE')
                     ->orWhere('transaction_type', 'ADJUSTMENT')
                     ->where('quantity', '>', 0);
    }

    /**
     * Scope untuk stock out transactions  
     */
    public function scopeStockOut($query)
    {
        return $query->where('transaction_type', 'OUT')
                     ->orWhere('transaction_type', 'DISTRIBUTE_OUT')
                     ->orWhere('transaction_type', 'USAGE')
                     ->orWhere('quantity', '<', 0);
    }

    // === CENTRAL STOCK SCOPES ===

    public function scopeCentralLowStock($query, $warehouseId = null)
    {
        return $query->whereHas('centralStockBalances', function($q) use ($warehouseId) {
            $q->where('year', now()->year)
              ->where('month', now()->month)
              ->whereRaw('closing_stock <= (SELECT low_stock_threshold FROM items WHERE items.id = central_stock_balances.item_id)')
              ->where('closing_stock', '>', 0);
              
            if ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }
        });
    }

    public function scopeCentralOutOfStock($query, $warehouseId = null)
    {
        return $query->whereHas('centralStockBalances', function($q) use ($warehouseId) {
            $q->where('year', now()->year)
              ->where('month', now()->month)
              ->where('closing_stock', '<=', 0);
              
            if ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }
        });
    }

    public function scopeHasCentralStock($query, $warehouseId = null)
    {
        return $query->whereHas('centralStockBalances', function($q) use ($warehouseId) {
            $q->where('year', now()->year)
              ->where('month', now()->month)
              ->where('closing_stock', '>', 0);
              
            if ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }
        });
    }

    // ========================================
    // ACCESSORS
    // ========================================

    public function getCurrentStockAttribute()
    {
        return $this->getCentralStock();
    }

    public function getStockStatusAttribute()
    {
        $currentStock = $this->current_stock;
        
        if ($currentStock <= 0) {
            return 'OUT_OF_STOCK';
        } elseif ($currentStock <= $this->low_stock_threshold) {
            return 'LOW_STOCK';
        } else {
            return 'NORMAL';
        }
    }

    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'OUT_OF_STOCK' => 'danger',
            'LOW_STOCK' => 'warning',
            'NORMAL' => 'success',
            default => 'secondary'
        };
    }

    public function getStockStatusLabelAttribute()
    {
        return match($this->stock_status) {
            'OUT_OF_STOCK' => 'Empty',
            'LOW_STOCK' => 'Low Stock',
            'NORMAL' => 'Normal',
            default => 'Unknown'
        };
    }

    public function getStockValueAttribute()
    {
        return $this->current_stock * ($this->unit_cost ?? 0);
    }

    public function getCentralStockValueAttribute()
    {
        return $this->getCentralStock() * ($this->unit_cost ?? 0);
    }

    public function getTotalStockValueAttribute()
    {
        return $this->getTotalSystemStock() * ($this->unit_cost ?? 0);
    }

    // ========================================
    // SUPPLIER ACCESSORS
    // ========================================

    public function getLatestSupplierAttribute()
    {
        $latestTransaction = $this->centralStockTransactions()
                                  ->whereNotNull('supplier_id')
                                  ->with('supplier')
                                  ->latest('created_at')
                                  ->first();
        
        return $latestTransaction ? $latestTransaction->supplier : null;
    }

    public function getSupplierNameAttribute()
    {
        $latestSupplier = $this->latest_supplier;
        return $latestSupplier ? $latestSupplier->supplier_name : 'No supplier';
    }

    public function getSupplierCountAttribute()
    {
        return $this->suppliers()->count();
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    public function hasStock($quantity = 1)
    {
        return $this->current_stock >= $quantity;
    }

    public function hasCentralStock($quantity = 1, $warehouseId = null)
    {
        return $this->getCentralStock($warehouseId) >= $quantity;
    }

    public function isLowStock()
    {
        return $this->stock_status === 'LOW_STOCK';
    }

    public function isOutOfStock()
    {
        return $this->stock_status === 'OUT_OF_STOCK';
    }

    public function getStockPercentage()
    {
        if ($this->low_stock_threshold <= 0) return 100;
        
        return min(100, ($this->current_stock / $this->low_stock_threshold) * 100);
    }

    /**
     * Get latest stock transaction untuk audit trail
     */
    public function getLatestTransaction()
    {
        return $this->centralStockTransactions()
                    ->with(['user', 'supplier'])
                    ->latest('created_at')
                    ->first();
    }

    /**
     * Get stock movement summary untuk current month
     */
    public function getMonthlyMovement()
    {
        $movements = $this->centralStockTransactions()
                          ->whereYear('transaction_date', now()->year)
                          ->whereMonth('transaction_date', now()->month)
                          ->selectRaw('
                              transaction_type,
                              SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as total_in,
                              SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as total_out,
                              COUNT(*) as transaction_count
                          ')
                          ->groupBy('transaction_type')
                          ->get();

        return [
            'total_in' => $movements->where('transaction_type', 'PURCHASE')->sum('total_in'),
            'total_out' => $movements->where('transaction_type', 'DISTRIBUTE_OUT')->sum('total_out'),
            'adjustments' => $movements->where('transaction_type', 'ADJUSTMENT')->sum('total_in') - 
                           $movements->where('transaction_type', 'ADJUSTMENT')->sum('total_out'),
            'net_movement' => $movements->sum('total_in') - $movements->sum('total_out')
        ];
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    public static function getStockStatistics()
    {
        return [
            'total_items' => self::active()->count(),
            'central_low_stock' => self::centralLowStock()->count(),
            'central_out_of_stock' => self::centralOutOfStock()->count(),
            'has_stock' => self::hasCentralStock()->count(),
            'total_stock_value' => self::active()->get()->sum('central_stock_value')
        ];
    }

    /**
     * Get top items by stock value
     */
    public static function getTopStockValue($limit = 10)
    {
        return self::active()
                   ->whereNotNull('unit_cost')
                   ->get()
                   ->map(function($item) {
                       $item->central_stock_value = $item->central_stock_value;
                       return $item;
                   })
                   ->sortByDesc('central_stock_value')
                   ->take($limit);
    }

    /**
     * Get items that need attention (low stock + out of stock)
     */
    public static function getNeedsAttention()
    {
        return self::active()
                   ->where(function($query) {
                       $query->centralLowStock()
                             ->orWhere(function($q) {
                                 $q->centralOutOfStock();
                             });
                   })
                   ->with(['category', 'centralStockBalances'])
                   ->get();
    }

    // ========================================
    // MUTATORS
    // ========================================

    public function setItemCodeAttribute($value)
    {
        $this->attributes['item_code'] = strtoupper($value);
    }

    public function setItemNameAttribute($value)
    {
        $this->attributes['item_name'] = ucfirst(strtolower($value));
    }

    // ========================================
    // VALIDATION RULES
    // ========================================

    public static function validationRules($id = null)
    {
        return [
            'item_code' => 'required|string|max:50|unique:items,item_code,' . $id,
            'item_name' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|string|max:20',
            'low_stock_threshold' => 'required|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE'
        ];
    }

    public static function validationMessages()
    {
        return [
            'item_code.required' => 'Item code is required',
            'item_code.unique' => 'Item code already exists',
            'item_name.required' => 'Item name is required',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category does not exist',
            'unit.required' => 'Unit is required',
            'low_stock_threshold.required' => 'Low stock threshold is required',
            'low_stock_threshold.min' => 'Low stock threshold cannot be negative',
            'unit_cost.min' => 'Unit cost cannot be negative',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be ACTIVE or INACTIVE'
        ];
    }
}