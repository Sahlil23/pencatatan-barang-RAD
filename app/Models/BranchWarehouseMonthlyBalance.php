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

    protected $table = 'branch_warehouse_monthly_balances';

    // ✅ FIXED: Use warehouse_id (sesuai dengan DB)
    protected $fillable = [
        'item_id',
        'warehouse_id',              // ✅ CHANGED from warehouse_id
        'year',
        'month',
        'opening_stock',
        'closing_stock',
        'stock_in',
        'stock_out',
        'adjustments',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'opening_stock' => 'decimal:2',
        'closing_stock' => 'decimal:2',
        'stock_in' => 'decimal:2',
        'stock_out' => 'decimal:2',
        'adjustments' => 'decimal:2',
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
     * ✅ FIXED: Relationship menggunakan warehouse_id
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    /**
     * Alias untuk backward compatibility
     */
    public function branchWarehouse()
    {
        return $this->warehouse();
    }

    /**
     * User who closed the balance
     */
    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * ✅ FIXED: Scope menggunakan warehouse_id
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
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
        return $query->where('year', now()->year)
                    ->where('month', now()->month);
    }

    /**
     * Scope open balances
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Scope closed balances
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    /**
     * Scope with stock
     */
    public function scopeWithStock($query)
    {
        return $query->where('closing_stock', '>', 0);
    }

    // ========================================
    // METHODS
    // ========================================

    public function calculateClosingStock()
    {
        return (float)$this->opening_stock 
             + (float)$this->stock_in 
             - (float)$this->stock_out 
             + (float)$this->adjustments;
    }

    public function getStockStatus()
    {
        if ($this->closing_stock <= 0) {
            return 'OUT_OF_STOCK';
        } elseif ($this->item && $this->closing_stock <= $this->item->low_stock_threshold) {
            return 'LOW_STOCK';
        }
        return 'NORMAL';
    }
}