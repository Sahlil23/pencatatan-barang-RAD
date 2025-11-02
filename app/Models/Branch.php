<?php
// filepath: app/Models/Branch.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_code',
        'branch_name',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'manager_name',
        'manager_phone',
        'opening_date',
        'status',
        'settings'
    ];

    protected $casts = [
        'opening_date' => 'date',
        'settings' => 'array'
    ];

    protected $dates = [
        'opening_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Branch memiliki banyak warehouses
     */
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class)->where('status', 'active');
    }

    /**
     * Branch memiliki banyak users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Branch memiliki banyak branch stock balances
     */
    public function branchStockBalances()
    {
        return $this->hasMany(BranchMonthlyBalance::class);
    }

    /**
     * Branch memiliki banyak kitchen stock balances
     */
    public function kitchenStockBalances()
    {
        return $this->hasMany(MonthlyKitchenStockBalance::class);
    }

    /**
     * Branch memiliki banyak branch stock transactions
     */
    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class);
    }

    /**
     * Branch memiliki banyak kitchen stock transactions
     */
    public function kitchenStockTransactions()
    {
        return $this->hasMany(KitchenStockTransaction::class);
    }

    /**
     * Branch sebagai tujuan distribution orders
     */
    public function distributionOrders()
    {
        return $this->hasMany(DistributionOrder::class, 'to_branch_id');
    }

    /**
     * Branch manager (user dengan role branch_manager)
     */
    public function manager()
    {
        return $this->hasOne(User::class)->where('role', 'branch_manager');
    }

    /**
     * Main warehouse untuk branch ini
     */
    public function mainWarehouse()
    {
        return $this->hasOne(Warehouse::class)->where('warehouse_type', 'branch');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope untuk branch yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk branch yang tidak aktif
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope untuk search branch
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('branch_code', 'like', "%{$search}%")
              ->orWhere('branch_name', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%");
        });
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Get current month stock value untuk branch ini
     */
    public function getCurrentStockValue()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $branchStockValue = $this->branchStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->with('item')
            ->get()
            ->sum(function($balance) {
                return $balance->closing_stock * $balance->item->unit_cost;
            });

        $kitchenStockValue = $this->kitchenStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->with('item')
            ->get()
            ->sum(function($balance) {
                return $balance->closing_stock * $balance->item->unit_cost;
            });

        return $branchStockValue + $kitchenStockValue;
    }

    /**
     * Get total items in stock untuk branch ini
     */
    public function getTotalItemsInStock()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $branchItems = $this->branchStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('closing_stock', '>', 0)
            ->distinct('item_id')
            ->count();

        $kitchenItems = $this->kitchenStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('closing_stock', '>', 0)
            ->distinct('item_id')
            ->count();

        // Count unique items across both warehouse and kitchen
        $totalUniqueItems = $this->branchStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('closing_stock', '>', 0)
            ->pluck('item_id')
            ->merge(
                $this->kitchenStockBalances()
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth)
                    ->where('closing_stock', '>', 0)
                    ->pluck('item_id')
            )
            ->unique()
            ->count();

        return $totalUniqueItems;
    }

    /**
     * Get low stock items untuk branch ini
     */
    public function getLowStockItems()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $lowStockItems = collect();

        // Check branch warehouse low stock
        $branchLowStock = $this->branchStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->with('item')
            ->get()
            ->filter(function($balance) {
                return $balance->closing_stock <= $balance->item->low_stock_threshold;
            });

        // Check kitchen low stock  
        $kitchenLowStock = $this->kitchenStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->with('item')
            ->get()
            ->filter(function($balance) {
                return $balance->closing_stock <= 5; // Default kitchen threshold
            });

        return [
            'warehouse' => $branchLowStock,
            'kitchen' => $kitchenLowStock,
            'total_count' => $branchLowStock->count() + $kitchenLowStock->count()
        ];
    }

    /**
     * Get monthly transactions count
     */
    public function getMonthlyTransactionsCount($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $branchTransactions = $this->branchStockTransactions()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->count();

        $kitchenTransactions = $this->kitchenStockTransactions()
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->count();

        return $branchTransactions + $kitchenTransactions;
    }

    /**
     * Get total kitchen usage untuk bulan ini
     */
    public function getMonthlyKitchenUsage($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $this->kitchenStockTransactions()
            ->where('transaction_type', 'USAGE')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('quantity');
    }

    /**
     * Get branch performance metrics
     */
    public function getPerformanceMetrics()
    {
        return [
            'total_warehouses' => $this->warehouses()->count(),
            'total_users' => $this->users()->count(),
            'current_stock_value' => $this->getCurrentStockValue(),
            'total_items_in_stock' => $this->getTotalItemsInStock(),
            'low_stock_items' => $this->getLowStockItems(),
            'monthly_transactions' => $this->getMonthlyTransactionsCount(),
            'monthly_kitchen_usage' => $this->getMonthlyKitchenUsage(),
            'status' => $this->status
        ];
    }

    /**
     * Check if branch dapat menerima distribution
     */
    public function canReceiveDistribution()
    {
        return $this->status === 'active' && $this->mainWarehouse()->exists();
    }

    /**
     * Get atau set branch setting
     */
    public function getSetting($key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Generate branch code otomatis
     */
    public static function generateBranchCode()
    {
        $lastBranch = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastBranch ? (intval(substr($lastBranch->branch_code, 3)) + 1) : 1;
        return 'CBG' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // ========================================
    // ATTRIBUTES
    // ========================================

    /**
     * Get full address attribute
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) {
            $address .= ', ' . $this->city;
        }
        if ($this->province) {
            $address .= ', ' . $this->province;
        }
        if ($this->postal_code) {
            $address .= ' ' . $this->postal_code;
        }
        return $address;
    }

    /**
     * Get status badge attribute untuk UI
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active' => '<span class="badge bg-success">Aktif</span>',
            'inactive' => '<span class="badge bg-warning">Tidak Aktif</span>',
            'closed' => '<span class="badge bg-danger">Tutup</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get manager name attribute
     */
    public function getManagerDisplayAttribute()
    {
        return $this->manager_name ?? $this->manager?->name ?? 'Belum ada manager';
    }

    // ========================================
    // VALIDATION RULES
    // ========================================

    public static function validationRules($id = null)
    {
        return [
            'branch_code' => 'required|string|max:10|unique:branches,branch_code,' . $id,
            'branch_name' => 'required|string|max:100',
            'address' => 'required|string',
            'city' => 'required|string|max:50',
            'province' => 'required|string|max:50',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|string|max:100',
            'manager_phone' => 'nullable|string|max:20',
            'opening_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,closed'
        ];
    }
}