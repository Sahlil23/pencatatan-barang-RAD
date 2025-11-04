<?php
// filepath: app/Models/Warehouse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_code',
        'warehouse_name',
        'warehouse_type',
        'branch_id',
        'location',
        'address',
        'phone',
        'capacity',
        'status', // ✅ Changed from is_active to status
        'notes',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function monthlyBalances()
    {
        return $this->hasMany(OutletWarehouseMonthlyBalance::class, 'warehouse_id');
    }

    public function stockTransactions()
    {
        return $this->hasMany(OutletStockTransaction::class, 'outlet_warehouse_id');
    }

    public function kitchenDistributions()
    {
        return $this->hasMany(OutletWarehouseToKitchenTransaction::class, 'outlet_warehouse_id');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    /**
     * Scope for outlet warehouses
     */
    public function scopeOutlet($query)
    {
        return $query->where('warehouse_type', 'outlet');
    }

    /**
     * Scope for branch warehouses
     */
    public function scopeBranch($query)
    {
        return $query->where('warehouse_type', 'branch');
    }

    /**
     * Scope for main warehouses
     */
    public function scopeMain($query)
    {
        return $query->where('warehouse_type', 'main');
    }

    /**
     * ✅ FIX: Scope for active warehouses (use status column)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    // ============================================================
    // CUSTOM METHODS
    // ============================================================

    /**
     * Get current month stock summary
     */
    public function getCurrentMonthStockSummary()
    {
        $year = date('Y');
        $month = date('m');

        $balances = $this->monthlyBalances()
            ->where('year', $year)
            ->where('month', $month)
            ->with('item')
            ->get();

        return [
            'total_items' => $balances->count(),
            'total_stock_value' => $balances->sum('stock_value'),
            'low_stock_items' => $balances->where('is_low_stock', true)->count(),
            'out_of_stock_items' => $balances->where('closing_stock', '<=', 0)->count(),
            'total_received' => $balances->sum('received_from_branch_warehouse'),
            'total_distributed' => $balances->sum('distributed_to_kitchen'),
        ];
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems()
    {
        $year = date('Y');
        $month = date('m');

        return $this->monthlyBalances()
            ->where('year', $year)
            ->where('month', $month)
            ->whereHas('item', function($query) {
                $query->whereRaw('outlet_warehouse_monthly_balances.closing_stock < items.low_stock_threshold');
            })
            ->with(['item.category'])
            ->orderBy('closing_stock', 'asc')
            ->get();
    }

    /**
     * Get pending kitchen distributions
     */
    public function getPendingKitchenDistributions()
    {
        return $this->kitchenDistributions()
            ->whereIn('status', ['PENDING', 'PREPARED', 'IN_TRANSIT', 'DELIVERED'])
            ->with(['branch', 'item'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    // ============================================================
    // ATTRIBUTES
    // ============================================================

    public function getTypeLabelAttribute()
    {
        $types = [
            'main' => 'Main Warehouse',
            'branch' => 'Branch Warehouse',
            'outlet' => 'Outlet Warehouse',
        ];

        return $types[$this->warehouse_type] ?? $this->warehouse_type;
    }

    /**
     * ✅ FIX: Use status column
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'ACTIVE' => 'Active',
            'INACTIVE' => 'Inactive',
            'CLOSED' => 'Closed',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * ✅ NEW: Check if active
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'ACTIVE';
    }
}