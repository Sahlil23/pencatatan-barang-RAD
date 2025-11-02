<?php
// filepath: app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'description',
        'branch_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Category belongs to branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Category has many items
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get active items in this category
     */
    public function activeItems()
    {
        return $this->hasMany(Item::class)->where('is_active', true);
    }

    /**
     * Get items dengan stock di branch ini
     */
    public function itemsWithStock()
    {
        return $this->hasMany(Item::class)
            ->whereHas('branchMonthlyBalances', function($query) {
                $query->where('branch_id', $this->branch_id)
                      ->where('closing_stock', '>', 0);
            });
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
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive categories
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope categories yang memiliki items
     */
    public function scopeWithItems($query)
    {
        return $query->whereHas('items');
    }

    /**
     * Scope categories yang memiliki active items
     */
    public function scopeWithActiveItems($query)
    {
        return $query->whereHas('activeItems');
    }

    /**
     * Scope categories dengan stock tersedia
     */
    public function scopeWithStock($query, $branchId = null)
    {
        return $query->whereHas('items.branchMonthlyBalances', function($q) use ($branchId) {
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $q->where('closing_stock', '>', 0);
        });
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Get or create category untuk branch
     */
    public static function getOrCreateCategory($categoryName, $branchId, $description = null)
    {
        return static::firstOrCreate(
            [
                'category_name' => $categoryName,
                'branch_id' => $branchId
            ],
            [
                'description' => $description,
                'is_active' => true
            ]
        );
    }

    /**
     * Get categories untuk branch dengan item count
     */
    public static function getBranchCategoriesWithCount($branchId)
    {
        return static::forBranch($branchId)
            ->withCount(['items', 'activeItems'])
            ->orderBy('category_name')
            ->get();
    }

    /**
     * Get popular categories berdasarkan transaction volume
     */
    public static function getPopularCategoriesByTransactions($branchId, $startDate = null, $endDate = null)
    {
        $query = static::forBranch($branchId)
            ->withCount(['items as transaction_count' => function($q) use ($startDate, $endDate) {
                $q->whereHas('branchStockTransactions', function($sq) use ($startDate, $endDate) {
                    if ($startDate && $endDate) {
                        $sq->whereBetween('transaction_date', [$startDate, $endDate]);
                    } else {
                        $sq->whereMonth('transaction_date', now()->month)
                          ->whereYear('transaction_date', now()->year);
                    }
                });
            }]);

        return $query->orderByDesc('transaction_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get category performance untuk branch
     */
    public static function getCategoryPerformance($branchId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return static::forBranch($branchId)
            ->select('categories.*')
            ->selectRaw('
                COUNT(DISTINCT items.id) as item_count,
                COALESCE(SUM(bmb.stock_in), 0) as total_stock_in,
                COALESCE(SUM(bmb.stock_out), 0) as total_stock_out,
                COALESCE(SUM(bmb.closing_stock), 0) as total_closing_stock,
                COALESCE(AVG(bmb.closing_stock), 0) as avg_closing_stock
            ')
            ->leftJoin('items', 'categories.id', '=', 'items.category_id')
            ->leftJoin('branch_monthly_balances as bmb', function($join) use ($branchId, $year, $month) {
                $join->on('items.id', '=', 'bmb.item_id')
                     ->where('bmb.branch_id', $branchId)
                     ->where('bmb.year', $year)
                     ->where('bmb.month', $month);
            })
            ->groupBy('categories.id', 'categories.category_name', 'categories.description', 'categories.branch_id', 'categories.is_active', 'categories.created_at', 'categories.updated_at')
            ->orderByDesc('total_stock_out')
            ->get();
    }

    /**
     * Get category stock summary
     */
    public static function getCategoryStockSummary($branchId, $categoryId = null)
    {
        $query = static::forBranch($branchId);

        if ($categoryId) {
            $query->where('id', $categoryId);
        }

        return $query->select('categories.*')
            ->selectRaw('
                COUNT(DISTINCT items.id) as total_items,
                COUNT(DISTINCT CASE WHEN bmb.closing_stock > 0 THEN items.id END) as items_in_stock,
                COUNT(DISTINCT CASE WHEN bmb.closing_stock <= 0 THEN items.id END) as items_out_of_stock,
                COALESCE(SUM(bmb.closing_stock), 0) as total_stock_value
            ')
            ->leftJoin('items', 'categories.id', '=', 'items.category_id')
            ->leftJoin('branch_monthly_balances as bmb', function($join) use ($branchId) {
                $join->on('items.id', '=', 'bmb.item_id')
                     ->where('bmb.branch_id', $branchId)
                     ->where('bmb.year', now()->year)
                     ->where('bmb.month', now()->month);
            })
            ->groupBy('categories.id', 'categories.category_name', 'categories.description', 'categories.branch_id', 'categories.is_active', 'categories.created_at', 'categories.updated_at')
            ->get();
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Get item count untuk category ini
     */
    public function getItemCount()
    {
        return $this->items()->count();
    }

    /**
     * Get active item count
     */
    public function getActiveItemCount()
    {
        return $this->activeItems()->count();
    }

    /**
     * Get total stock value untuk category ini
     */
    public function getTotalStockValue($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return $this->items()
            ->join('branch_monthly_balances as bmb', function($join) use ($year, $month) {
                $join->on('items.id', '=', 'bmb.item_id')
                     ->where('bmb.branch_id', $this->branch_id)
                     ->where('bmb.year', $year)
                     ->where('bmb.month', $month);
            })
            ->sum('bmb.closing_stock');
    }

    /**
     * Get category transaction summary untuk periode tertentu
     */
    public function getTransactionSummary($startDate = null, $endDate = null)
    {
        $query = BranchStockTransaction::whereHas('item', function($q) {
                $q->where('category_id', $this->id);
            })
            ->where('branch_id', $this->branch_id);

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        } else {
            $query->whereMonth('transaction_date', now()->month)
                  ->whereYear('transaction_date', now()->year);
        }

        return [
            'total_transactions' => $query->count(),
            'total_stock_in' => $query->stockIn()->sum('quantity'),
            'total_stock_out' => $query->stockOut()->sum('quantity'),
            'by_type' => $query->selectRaw('transaction_type, COUNT(*) as count, SUM(quantity) as total_quantity')
                              ->groupBy('transaction_type')
                              ->get()
                              ->keyBy('transaction_type')
        ];
    }

    /**
     * Get low stock items dalam category ini
     */
    public function getLowStockItems()
    {
        return $this->items()
            ->whereHas('branchMonthlyBalances', function($query) {
                $query->where('branch_id', $this->branch_id)
                      ->whereRaw('closing_stock <= items.low_stock_threshold')
                      ->where('closing_stock', '>', 0);
            })
            ->with(['branchMonthlyBalances' => function($query) {
                $query->where('branch_id', $this->branch_id)
                      ->where('year', now()->year)
                      ->where('month', now()->month);
            }])
            ->get();
    }

    /**
     * Get out of stock items dalam category ini
     */
    public function getOutOfStockItems()
    {
        return $this->items()
            ->whereHas('branchMonthlyBalances', function($query) {
                $query->where('branch_id', $this->branch_id)
                      ->where('closing_stock', '<=', 0);
            })
            ->with(['branchMonthlyBalances' => function($query) {
                $query->where('branch_id', $this->branch_id)
                      ->where('year', now()->year)
                      ->where('month', now()->month);
            }])
            ->get();
    }

    /**
     * Activate category
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    /**
     * Deactivate category
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Get status text
     */
    public function getStatusAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get status color untuk UI
     */
    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Get branch name
     */
    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->branch_name : 'Unknown Branch';
    }

    /**
     * Get item count (cached)
     */
    public function getItemCountAttribute()
    {
        return $this->items_count ?? $this->getItemCount();
    }

    /**
     * Get active item count (cached)
     */
    public function getActiveItemCountAttribute()
    {
        return $this->active_items_count ?? $this->getActiveItemCount();
    }

    /**
     * Check if category has items
     */
    public function getHasItemsAttribute()
    {
        return $this->item_count > 0;
    }

    /**
     * Check if category has active items
     */
    public function getHasActiveItemsAttribute()
    {
        return $this->active_item_count > 0;
    }

    /**
     * Get category display name dengan branch info
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->category_name} ({$this->branch_name})";
    }

    /**
     * Get current month stock summary
     */
    public function getCurrentStockSummaryAttribute()
    {
        $summary = $this->items()
            ->join('branch_monthly_balances as bmb', function($join) {
                $join->on('items.id', '=', 'bmb.item_id')
                     ->where('bmb.branch_id', $this->branch_id)
                     ->where('bmb.year', now()->year)
                     ->where('bmb.month', now()->month);
            })
            ->selectRaw('
                COUNT(*) as total_items,
                COUNT(CASE WHEN bmb.closing_stock > 0 THEN 1 END) as items_in_stock,
                COUNT(CASE WHEN bmb.closing_stock <= 0 THEN 1 END) as items_out_of_stock,
                SUM(bmb.closing_stock) as total_stock,
                AVG(bmb.closing_stock) as avg_stock
            ')
            ->first();

        return $summary ? $summary->toArray() : [
            'total_items' => 0,
            'items_in_stock' => 0,
            'items_out_of_stock' => 0,
            'total_stock' => 0,
            'avg_stock' => 0
        ];
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules($id = null)
    {
        $unique = $id ? "unique:categories,category_name,{$id},id,branch_id," : "unique:categories,category_name,NULL,id,branch_id,";
        
        return [
            'category_name' => 'required|string|max:100|' . $unique . request('branch_id'),
            'description' => 'nullable|string|max:500',
            'branch_id' => 'required|exists:branches,id',
            'is_active' => 'boolean'
        ];
    }

    public static function validationMessages()
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi',
            'category_name.unique' => 'Nama kategori sudah ada di branch ini',
            'branch_id.required' => 'Branch wajib dipilih',
            'branch_id.exists' => 'Branch tidak valid'
        ];
    }
}