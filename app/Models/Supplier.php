<?php
// filepath: app/Models/Supplier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'phone',
        'address',
        'supplier_type',
        'serves_branches',
        'is_active'
    ];

    protected $casts = [
        'serves_branches' => 'array',
        'is_active' => 'boolean'
    ];

    // ========================================
    // SUPPLIER TYPES
    // ========================================
    
    const TYPE_CENTRAL = 'central';
    const TYPE_BRANCH = 'branch';
    const TYPE_BOTH = 'both';

    /**
     * Get available supplier types
     */
    public static function getSupplierTypes()
    {
        return [
            self::TYPE_CENTRAL => 'Central Only',
            self::TYPE_BRANCH => 'Branch Only',
            self::TYPE_BOTH => 'Central & Branch'
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Legacy relation untuk backward compatibility
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'stock_transactions')
                    ->withPivot(['transaction_type', 'quantity', 'transaction_date', 'notes'])
                    ->withTimestamps()
                    ->distinct();
    }

    /**
     * Legacy stock transactions
     */
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    /**
     * Central stock transactions
     */
    public function centralStockTransactions()
    {
        return $this->hasMany(CentralStockTransaction::class);
    }

    /**
     * Branch stock transactions
     */
    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class);
    }

    /**
     * Kitchen stock transactions (indirect through branch)
     */
    public function kitchenStockTransactions()
    {
        return $this->hasManyThrough(
            KitchenStockTransaction::class,
            BranchStockTransaction::class,
            'supplier_id',
            'branch_warehouse_transaction_id',
            'id',
            'id'
        );
    }

    /**
     * Served branches relationship
     */
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'supplier_branches')
                    ->withTimestamps();
    }

    /**
     * Active items berdasarkan transaksi terakhir
     */
    public function activeItems()
    {
        return $this->belongsToMany(Item::class, 'stock_transactions')
                    ->withPivot(['transaction_date'])
                    ->wherePivot('transaction_date', '>=', now()->subMonths(3))
                    ->distinct();
    }

    /**
     * Central items yang di-supply
     */
    public function centralItems()
    {
        return $this->belongsToMany(Item::class, 'central_stock_transactions', 'supplier_id', 'item_id')
                    ->distinct();
    }

    /**
     * Branch items yang di-supply
     */
    public function branchItems()
    {
        return $this->belongsToMany(Item::class, 'branch_stock_transactions', 'supplier_id', 'item_id')
                    ->distinct();
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive suppliers
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for suppliers yang melayani branch tertentu
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function($q) use ($branchId) {
            $q->where('supplier_type', self::TYPE_CENTRAL)
              ->orWhere('supplier_type', self::TYPE_BOTH)
              ->orWhere(function($sq) use ($branchId) {
                  $sq->where('supplier_type', self::TYPE_BRANCH)
                     ->whereJsonContains('serves_branches', $branchId);
              });
        });
    }

    /**
     * Scope by supplier type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('supplier_type', $type);
    }

    /**
     * Scope for central suppliers
     */
    public function scopeCentral($query)
    {
        return $query->whereIn('supplier_type', [self::TYPE_CENTRAL, self::TYPE_BOTH]);
    }

    /**
     * Scope for branch suppliers
     */
    public function scopeBranch($query)
    {
        return $query->whereIn('supplier_type', [self::TYPE_BRANCH, self::TYPE_BOTH]);
    }

    /**
     * Scope suppliers dengan contact person
     */
    public function scopeWithContact($query)
    {
        return $query->whereNotNull('contact_person');
    }

    /**
     * Scope suppliers dengan transaksi dalam periode tertentu
     */
    public function scopeWithRecentTransactions($query, $days = 30)
    {
        return $query->where(function($q) use ($days) {
            $q->whereHas('stockTransactions', function($sq) use ($days) {
                $sq->where('transaction_date', '>=', now()->subDays($days));
            })
            ->orWhereHas('centralStockTransactions', function($sq) use ($days) {
                $sq->where('transaction_date', '>=', now()->subDays($days));
            })
            ->orWhereHas('branchStockTransactions', function($sq) use ($days) {
                $sq->where('transaction_date', '>=', now()->subDays($days));
            });
        });
    }

    /**
     * Scope suppliers yang aktif dalam periode tertentu
     */
    public function scopeActiveInPeriod($query, $months = 3)
    {
        return $query->where(function($q) use ($months) {
            $q->whereHas('stockTransactions', function($sq) use ($months) {
                $sq->where('transaction_date', '>=', now()->subMonths($months));
            })
            ->orWhereHas('centralStockTransactions', function($sq) use ($months) {
                $sq->where('transaction_date', '>=', now()->subMonths($months));
            })
            ->orWhereHas('branchStockTransactions', function($sq) use ($months) {
                $sq->where('transaction_date', '>=', now()->subMonths($months));
            });
        });
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Check if supplier can supply to specific branch
     */
    public function canSupplyToBranch($branchId)
    {
        if (!$this->is_active) {
            return false;
        }

        switch ($this->supplier_type) {
            case self::TYPE_CENTRAL:
            case self::TYPE_BOTH:
                return true; // Central suppliers dapat supply ke semua branch
                
            case self::TYPE_BRANCH:
                return in_array($branchId, $this->serves_branches ?? []);
                
            default:
                return false;
        }
    }

    /**
     * Get served branches dengan detail
     */
    public function getServedBranches()
    {
        switch ($this->supplier_type) {
            case self::TYPE_CENTRAL:
            case self::TYPE_BOTH:
                // Return all active branches
                return Branch::active()->get();
                
            case self::TYPE_BRANCH:
                // Return only specified branches
                if (empty($this->serves_branches)) {
                    return collect();
                }
                return Branch::whereIn('id', $this->serves_branches)->get();
                
            default:
                return collect();
        }
    }

    /**
     * Add branch to served branches
     */
    public function addBranch($branchId)
    {
        if ($this->supplier_type === self::TYPE_CENTRAL) {
            return false; // Central suppliers serve all branches automatically
        }

        $servedBranches = $this->serves_branches ?? [];
        
        if (!in_array($branchId, $servedBranches)) {
            $servedBranches[] = $branchId;
            $this->update(['serves_branches' => $servedBranches]);
        }

        return true;
    }

    /**
     * Remove branch from served branches
     */
    public function removeBranch($branchId)
    {
        if ($this->supplier_type === self::TYPE_CENTRAL) {
            return false; // Cannot remove branches from central suppliers
        }

        $servedBranches = $this->serves_branches ?? [];
        $servedBranches = array_values(array_filter($servedBranches, function($id) use ($branchId) {
            return $id != $branchId;
        }));

        $this->update(['serves_branches' => $servedBranches]);
        return true;
    }

    /**
     * Activate supplier
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    /**
     * Deactivate supplier
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Get comprehensive supplier statistics
     */
    public function getSupplierStats($startDate = null, $endDate = null)
    {
        $stats = [
            'legacy' => $this->getLegacyStats($startDate, $endDate),
            'central' => $this->getCentralStats($startDate, $endDate),
            'branch' => $this->getBranchStats($startDate, $endDate)
        ];

        // Combine stats
        return [
            'total_transactions' => $stats['legacy']['total_transactions'] + 
                                  $stats['central']['total_transactions'] + 
                                  $stats['branch']['total_transactions'],
            'total_items' => $stats['legacy']['unique_items'] + 
                           $stats['central']['unique_items'] + 
                           $stats['branch']['unique_items'],
            'total_stock_supplied' => $stats['legacy']['total_stock_in'] + 
                                    $stats['central']['total_stock_supplied'] + 
                                    $stats['branch']['total_stock_supplied'],
            'breakdown' => $stats
        ];
    }

    /**
     * Get legacy transaction stats (backward compatibility)
     */
    public function getLegacyStats($startDate = null, $endDate = null)
    {
        $query = $this->stockTransactions();
        
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        return $query->selectRaw('
                COUNT(*) as total_transactions,
                COUNT(DISTINCT item_id) as unique_items,
                SUM(CASE WHEN transaction_type = "IN" THEN quantity ELSE 0 END) as total_stock_in,
                SUM(CASE WHEN transaction_type = "OUT" THEN quantity ELSE 0 END) as total_stock_out,
                MIN(transaction_date) as first_transaction,
                MAX(transaction_date) as last_transaction
            ')
            ->first() ?? (object) [
                'total_transactions' => 0,
                'unique_items' => 0,
                'total_stock_in' => 0,
                'total_stock_out' => 0,
                'first_transaction' => null,
                'last_transaction' => null
            ];
    }

    /**
     * Get central transaction stats
     */
    public function getCentralStats($startDate = null, $endDate = null)
    {
        $query = $this->centralStockTransactions();
        
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        return $query->selectRaw('
                COUNT(*) as total_transactions,
                COUNT(DISTINCT item_id) as unique_items,
                SUM(quantity) as total_stock_supplied,
                MIN(transaction_date) as first_transaction,
                MAX(transaction_date) as last_transaction
            ')
            ->first() ?? (object) [
                'total_transactions' => 0,
                'unique_items' => 0,
                'total_stock_supplied' => 0,
                'first_transaction' => null,
                'last_transaction' => null
            ];
    }

    /**
     * Get branch transaction stats
     */
    public function getBranchStats($startDate = null, $endDate = null)
    {
        $query = $this->branchStockTransactions();
        
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        return $query->selectRaw('
                COUNT(*) as total_transactions,
                COUNT(DISTINCT item_id) as unique_items,
                COUNT(DISTINCT branch_id) as branches_served,
                SUM(quantity) as total_stock_supplied,
                MIN(transaction_date) as first_transaction,
                MAX(transaction_date) as last_transaction
            ')
            ->first() ?? (object) [
                'total_transactions' => 0,
                'unique_items' => 0,
                'branches_served' => 0,
                'total_stock_supplied' => 0,
                'first_transaction' => null,
                'last_transaction' => null
            ];
    }

    /**
     * Get supplier performance by branch
     */
    public function getBranchPerformance($startDate = null, $endDate = null)
    {
        $query = $this->branchStockTransactions();
        
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        return $query->selectRaw('
                branch_id,
                COUNT(*) as transaction_count,
                COUNT(DISTINCT item_id) as unique_items,
                SUM(quantity) as total_quantity,
                AVG(quantity) as avg_quantity,
                MAX(transaction_date) as last_transaction_date
            ')
            ->with('branch')
            ->groupBy('branch_id')
            ->orderByDesc('transaction_count')
            ->get();
    }

    /**
     * Get supplier item analysis
     */
    public function getItemAnalysis($startDate = null, $endDate = null)
    {
        // Combine data from all transaction types
        $centralItems = $this->centralStockTransactions()
            ->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->selectRaw('
                item_id,
                "central" as source_type,
                COUNT(*) as transaction_count,
                SUM(quantity) as total_quantity,
                MAX(transaction_date) as last_transaction_date
            ')
            ->groupBy('item_id');

        $branchItems = $this->branchStockTransactions()
            ->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->selectRaw('
                item_id,
                "branch" as source_type,
                COUNT(*) as transaction_count,
                SUM(quantity) as total_quantity,
                MAX(transaction_date) as last_transaction_date
            ')
            ->groupBy('item_id');

        // Union and process results
        return $centralItems->union($branchItems)
            ->with('item')
            ->orderByDesc('total_quantity')
            ->get()
            ->groupBy('item_id')
            ->map(function($transactions, $itemId) {
                $item = $transactions->first()->item;
                $centralData = $transactions->where('source_type', 'central')->first();
                $branchData = $transactions->where('source_type', 'branch')->first();

                return [
                    'item' => $item,
                    'central_transactions' => $centralData ? $centralData->transaction_count : 0,
                    'central_quantity' => $centralData ? $centralData->total_quantity : 0,
                    'branch_transactions' => $branchData ? $branchData->transaction_count : 0,
                    'branch_quantity' => $branchData ? $branchData->total_quantity : 0,
                    'total_transactions' => ($centralData ? $centralData->transaction_count : 0) + 
                                          ($branchData ? $branchData->transaction_count : 0),
                    'total_quantity' => ($centralData ? $centralData->total_quantity : 0) + 
                                      ($branchData ? $branchData->total_quantity : 0),
                    'last_transaction_date' => max(
                        $centralData ? $centralData->last_transaction_date : null,
                        $branchData ? $branchData->last_transaction_date : null
                    )
                ];
            });
    }

    // ========================================
    // LEGACY METHODS (Backward Compatibility)
    // ========================================

    /**
     * Legacy method - Get item stats
     */
    public function getItemStats()
    {
        return $this->stockTransactions()
                    ->selectRaw('
                        item_id,
                        COUNT(*) as transaction_count,
                        SUM(CASE WHEN transaction_type = "IN" THEN quantity ELSE 0 END) as total_stock_in,
                        SUM(CASE WHEN transaction_type = "OUT" THEN quantity ELSE 0 END) as total_stock_out,
                        MAX(transaction_date) as last_transaction_date
                    ')
                    ->with('item')
                    ->groupBy('item_id')
                    ->orderByDesc('transaction_count')
                    ->get();
    }

    /**
     * Legacy method - Get monthly stats
     */
    public function getMonthlyStats($year = null, $month = null)
    {
        $query = $this->stockTransactions();
        
        if ($year) {
            $query->whereYear('transaction_date', $year);
        }
        
        if ($month) {
            $query->whereMonth('transaction_date', $month);
        }
        
        return $query->selectRaw('
                transaction_type,
                COUNT(*) as transaction_count,
                SUM(quantity) as total_quantity
            ')
            ->groupBy('transaction_type')
            ->get();
    }

    /**
     * Legacy method - Get performance stats
     */
    public function getPerformanceStats($startDate = null, $endDate = null)
    {
        return $this->getLegacyStats($startDate, $endDate);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get full contact info
     */
    public function getFullContactAttribute()
    {
        return $this->contact_person . ' (' . $this->phone . ')';
    }

    /**
     * Get supplier type text
     */
    public function getSupplierTypeTextAttribute()
    {
        return self::getSupplierTypes()[$this->supplier_type] ?? $this->supplier_type;
    }

    /**
     * Get supplier type color untuk UI
     */
    public function getSupplierTypeColorAttribute()
    {
        return match($this->supplier_type) {
            self::TYPE_CENTRAL => 'primary',
            self::TYPE_BRANCH => 'success',
            self::TYPE_BOTH => 'info',
            default => 'secondary'
        };
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Check if supplier is central type
     */
    public function getIsCentralAttribute()
    {
        return in_array($this->supplier_type, [self::TYPE_CENTRAL, self::TYPE_BOTH]);
    }

    /**
     * Check if supplier is branch type
     */
    public function getIsBranchAttribute()
    {
        return in_array($this->supplier_type, [self::TYPE_BRANCH, self::TYPE_BOTH]);
    }

    /**
     * Get served branches count
     */
    public function getServedBranchesCountAttribute()
    {
        return $this->getServedBranches()->count();
    }

    /**
     * Get served branches names
     */
    public function getServedBranchesNamesAttribute()
    {
        return $this->getServedBranches()->pluck('branch_name')->join(', ');
    }

    // Legacy accessors untuk backward compatibility
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    public function getTotalTransactionsAttribute()
    {
        return $this->stockTransactions()->count() + 
               $this->centralStockTransactions()->count() + 
               $this->branchStockTransactions()->count();
    }

    public function getLastTransactionDateAttribute()
    {
        $dates = collect([
            $this->stockTransactions()->latest('transaction_date')->value('transaction_date'),
            $this->centralStockTransactions()->latest('transaction_date')->value('transaction_date'),
            $this->branchStockTransactions()->latest('transaction_date')->value('transaction_date')
        ])->filter();

        return $dates->isNotEmpty() ? $dates->max() : null;
    }

    public function getTotalStockInAttribute()
    {
        return $this->stockTransactions()->where('transaction_type', 'IN')->sum('quantity');
    }

    public function getTotalStockOutAttribute()
    {
        return $this->stockTransactions()->where('transaction_type', 'OUT')->sum('quantity');
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules($id = null)
    {
        return [
            'supplier_name' => 'required|string|max:255|unique:suppliers,supplier_name,' . $id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'supplier_type' => 'required|in:' . implode(',', array_keys(self::getSupplierTypes())),
            'serves_branches' => 'nullable|array',
            'serves_branches.*' => 'exists:branches,id',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages()
    {
        return [
            'supplier_name.required' => 'Nama supplier wajib diisi',
            'supplier_name.unique' => 'Nama supplier sudah ada',
            'supplier_type.required' => 'Tipe supplier wajib dipilih',
            'supplier_type.in' => 'Tipe supplier tidak valid',
            'serves_branches.*.exists' => 'Branch tidak valid'
        ];
    }
}