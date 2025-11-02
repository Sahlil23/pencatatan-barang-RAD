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
        'address',
        'capacity_m2',
        'capacity_volume',
        'manager_name',    // ✅ Ganti dari 'pic_name'
        'phone',          // ✅ Ganti dari 'pic_phone'  
        'email',          // ✅ Tambah email
        'coverage_area',  // ✅ Tambah coverage_area
        'capacity',  
        'status',
        'settings'
    ];

    protected $casts = [
        'capacity_m2' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'settings' => 'array'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Warehouse belongs to branch (nullable untuk central warehouse)
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Central stock balances (untuk central warehouse)
     */
    public function centralStockBalances()
    {
        return $this->hasMany(CentralStockBalance::class);
    }

    /**
     * Central stock transactions (untuk central warehouse)
     */
    public function centralStockTransactions()
    {
        return $this->hasMany(CentralStockTransaction::class);
    }

    /**
     * Branch stock balances (untuk branch warehouse)
     */
    public function branchStockBalances()
    {
        return $this->hasMany(BranchMonthlyBalance::class);
    }

    /**
     * Branch stock transactions (untuk branch warehouse)
     */
    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class);
    }

    /**
     * Distribution orders dari warehouse ini
     */
    public function distributionOrdersFrom()
    {
        return $this->hasMany(DistributionOrder::class, 'from_warehouse_id');
    }

    /**
     * Distribution orders ke warehouse ini
     */
    public function distributionOrdersTo()
    {
        return $this->hasMany(DistributionOrder::class, 'to_warehouse_id');
    }

    /**
     * Users yang punya akses ke warehouse ini
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_warehouse_access');
    }

    /**
     * Stock periods untuk warehouse ini
     */
    public function stockPeriods()
    {
        return $this->hasMany(StockPeriod::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope untuk central warehouse
     */
    public function scopeCentral($query)
    {
        return $query->where('warehouse_type', 'central');
    }

    /**
     * Scope untuk branch warehouse
     */
    public function scopeBranch($query)
    {
        return $query->where('warehouse_type', 'branch');
    }

    /**
     * Scope untuk warehouse aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk search warehouse
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('warehouse_code', 'like', "%{$search}%")
              ->orWhere('warehouse_name', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%");
        });
    }

    /**
     * Scope untuk filter by branch
     */
    public function scopeForBranch($query, $branchId)
    {
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }
        return $query;
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Check apakah warehouse ini adalah central
     */
    public function isCentral()
    {
        return $this->warehouse_type === 'central';
    }

    /**
     * Check apakah warehouse ini adalah branch
     */
    public function isBranch()
    {
        return $this->warehouse_type === 'branch';
    }

    /**
     * Get current stock value untuk warehouse ini
     */
    public function getCurrentStockValue()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($this->isCentral()) {
            return $this->centralStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->with('item')
                ->get()
                ->sum(function($balance) {
                    return $balance->closing_stock * ($balance->item->unit_cost ?? 0);
                });
        } else {
            return $this->branchStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->with('item')
                ->get()
                ->sum(function($balance) {
                    return $balance->closing_stock * ($balance->item->unit_cost ?? 0);
                });
        }
    }

    /**
     * Get total items in stock
     */
    public function getTotalItemsInStock()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($this->isCentral()) {
            return $this->centralStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->where('closing_stock', '>', 0)
                ->distinct('item_id')
                ->count();
        } else {
            return $this->branchStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->where('closing_stock', '>', 0)
                ->distinct('item_id')
                ->count();
        }
    }

    /**
     * Get low stock items untuk warehouse ini
     */
    public function getLowStockItems()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($this->isCentral()) {
            return $this->centralStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->with('item')
                ->get()
                ->filter(function($balance) {
                    return $balance->closing_stock <= ($balance->item->central_min_stock ?? $balance->item->low_stock_threshold);
                });
        } else {
            return $this->branchStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->with('item')
                ->get()
                ->filter(function($balance) {
                    return $balance->closing_stock <= $balance->item->low_stock_threshold;
                });
        }
    }

    /**
     * Get monthly transactions count
     */
    public function getMonthlyTransactionsCount($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        if ($this->isCentral()) {
            return $this->centralStockTransactions()
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->count();
        } else {
            return $this->branchStockTransactions()
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->count();
        }
    }

    /**
     * Get warehouse utilization percentage
     */
    public function getUtilizationPercentage()
    {
        if (!$this->capacity_volume) {
            return null;
        }

        $currentStockVolume = $this->getCurrentStockVolume();
        return ($currentStockVolume / $this->capacity_volume) * 100;
    }

    /**
     * Get current stock volume (estimation)
     */
    public function getCurrentStockVolume()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($this->isCentral()) {
            $totalItems = $this->centralStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->sum('closing_stock');
        } else {
            $totalItems = $this->branchStockBalances()
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->sum('closing_stock');
        }

        // Estimasi volume (bisa disesuaikan dengan business logic)
        return $totalItems * 0.01; // Asumsi 1 item = 0.01 m3
    }

    /**
     * Get warehouse performance metrics
     */
    public function getPerformanceMetrics()
    {
        return [
            'type' => $this->warehouse_type,
            'branch' => $this->branch,
            'current_stock_value' => $this->getCurrentStockValue(),
            'total_items_in_stock' => $this->getTotalItemsInStock(),
            'low_stock_items_count' => $this->getLowStockItems()->count(),
            'monthly_transactions' => $this->getMonthlyTransactionsCount(),
            'utilization_percentage' => $this->getUtilizationPercentage(),
            'current_stock_volume' => $this->getCurrentStockVolume(),
            'capacity_m2' => $this->capacity_m2,
            'capacity_volume' => $this->capacity_volume,
            'status' => $this->status
        ];
    }

    /**
     * Check apakah warehouse bisa distribute
     */
    public function canDistribute()
    {
        return $this->isCentral() && $this->status === 'active';
    }

    /**
     * Check apakah warehouse bisa receive distribution
     */
    public function canReceiveDistribution()
    {
        return $this->isBranch() && $this->status === 'active';
    }

    /**
     * Get atau set warehouse setting
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
     * Get available items untuk distribution (untuk central warehouse)
     */
    public function getAvailableItemsForDistribution()
    {
        if (!$this->isCentral()) {
            return collect();
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;

        return $this->centralStockBalances()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('closing_stock', '>', 0)
            ->with('item')
            ->get();
    }

    /**
     * Generate warehouse code otomatis
     */
    public static function generateWarehouseCode($type = 'branch', $branchCode = null)
    {
        if ($type === 'central') {
            $lastCentral = self::where('warehouse_type', 'central')
                ->orderBy('id', 'desc')
                ->first();
            $nextNumber = $lastCentral ? (intval(substr($lastCentral->warehouse_code, 10)) + 1) : 1;
            return 'WH-CENTRAL' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        } else {
            return 'WH-' . $branchCode;
        }
    }

    // ========================================
    // ATTRIBUTES
    // ========================================

    /**
     * Get full address dengan branch info
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->branch) {
            $address .= ' (' . $this->branch->branch_name . ')';
        }
        return $address;
    }

    /**
     * Get status badge untuk UI
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active' => '<span class="badge bg-success">Aktif</span>',
            'inactive' => '<span class="badge bg-warning">Tidak Aktif</span>',
            'maintenance' => '<span class="badge bg-info">Maintenance</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get warehouse type badge
     */
    public function getTypeBadgeAttribute()
    {
        return match($this->warehouse_type) {
            'central' => '<span class="badge bg-primary">Central</span>',
            'branch' => '<span class="badge bg-info">Branch</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get PIC display name
     */
    public function getPicDisplayAttribute()
    {
        if ($this->pic_name) {
            $display = $this->pic_name;
            if ($this->pic_phone) {
                $display .= ' (' . $this->pic_phone . ')';
            }
            return $display;
        }
        return 'Belum ada PIC';
    }

    /**
     * Get capacity info
     */
    public function getCapacityInfoAttribute()
    {
        $info = [];
        if ($this->capacity_m2) {
            $info[] = number_format($this->capacity_m2, 0) . ' m²';
        }
        if ($this->capacity_volume) {
            $info[] = number_format($this->capacity_volume, 0) . ' m³';
        }
        return implode(' | ', $info) ?: 'Tidak ada data kapasitas';
    }

    /**
     * Get utilization status
     */
    public function getUtilizationStatusAttribute()
    {
        $percentage = $this->getUtilizationPercentage();
        
        if ($percentage === null) {
            return ['status' => 'unknown', 'color' => 'secondary', 'text' => 'Data tidak tersedia'];
        }

        if ($percentage >= 90) {
            return ['status' => 'full', 'color' => 'danger', 'text' => 'Hampir penuh (' . number_format($percentage, 1) . '%)'];
        } elseif ($percentage >= 70) {
            return ['status' => 'high', 'color' => 'warning', 'text' => 'Tinggi (' . number_format($percentage, 1) . '%)'];
        } elseif ($percentage >= 40) {
            return ['status' => 'medium', 'color' => 'info', 'text' => 'Sedang (' . number_format($percentage, 1) . '%)'];
        } else {
            return ['status' => 'low', 'color' => 'success', 'text' => 'Rendah (' . number_format($percentage, 1) . '%)'];
        }
    }

    // ========================================
    // VALIDATION RULES
    // ========================================

    public static function validationRules($id = null)
    {
        return [
            'warehouse_code' => 'required|string|max:15|unique:warehouses,warehouse_code,' . $id,
            'warehouse_name' => 'required|string|max:100',
            'warehouse_type' => 'required|in:central,branch',
            'branch_id' => 'nullable|exists:branches,id|required_if:warehouse_type,branch',
            'address' => 'required|string',
            'capacity_m2' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|numeric|min:0',
            'manager_name' => 'nullable|string|max:100',  // ✅ Updated
            'phone' => 'nullable|string|max:20',         // ✅ Updated
            'email' => 'nullable|email|max:100',         // ✅ Added
            'coverage_area' => 'nullable|string',        // ✅ Added
            'capacity' => 'nullable|numeric|min:0',      // ✅ Added
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE'  // ✅ Updated enum values
        ];
    }

    public static function validationMessages()
    {
        return [
            'warehouse_code.required' => 'Kode gudang wajib diisi',
            'warehouse_code.unique' => 'Kode gudang sudah digunakan',
            'warehouse_name.required' => 'Nama gudang wajib diisi',
            'warehouse_type.required' => 'Tipe gudang wajib dipilih',
            'warehouse_type.in' => 'Tipe gudang tidak valid',
            'branch_id.required_if' => 'Branch wajib dipilih untuk warehouse branch',
            'branch_id.exists' => 'Branch tidak valid',
            'address.required' => 'Alamat gudang wajib diisi',
            'capacity_m2.numeric' => 'Kapasitas harus berupa angka',
            'capacity_volume.numeric' => 'Volume harus berupa angka',
            'status.required' => 'Status gudang wajib dipilih'
        ];
    }
}