<?php
// filepath: app/Models/Item.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MonthlyKitchenStockBalance;
use App\Models\KitchenStockTransaction;
use App\Models\MonthlyStockBalance;
use App\Models\StockTransaction;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'item_name',
        'category_id',
        'unit',
        'low_stock_threshold'
    ];

    protected $casts = [
        'low_stock_threshold' => 'decimal:2',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // TAMBAH RELASI SUPPLIER MELALUI STOCK TRANSACTIONS
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'stock_transactions')
                    ->withPivot(['transaction_type', 'quantity', 'transaction_date', 'notes'])
                    ->withTimestamps()
                    ->distinct();
    }

    // FIX: Relasi untuk mendapatkan supplier terakhir yang digunakan
    public function latestSupplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            StockTransaction::class,
            'item_id',         // Foreign key on stock_transactions table
            'id',              // Foreign key on suppliers table
            'id',              // Local key on items table
            'supplier_id'      // Local key on stock_transactions table
        )->latest('stock_transactions.created_at');
    }

    // FIX: Relasi untuk mendapatkan supplier yang paling sering digunakan
    public function primarySupplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            StockTransaction::class,
            'item_id',
            'id', 
            'id',
            'supplier_id'
        )->selectRaw('suppliers.*, COUNT(stock_transactions.id) as transaction_count')
         ->groupBy('suppliers.id')
         ->orderByDesc('transaction_count');
    }

    // FIX: Method untuk mendapatkan supplier terakhir secara manual jika hasOneThrough bermasalah
    public function getLatestSupplierAttribute()
    {
        $latestTransaction = $this->stockTransactions()
                                  ->whereNotNull('supplier_id')
                                  ->with('supplier')
                                  ->latest('created_at')
                                  ->first();
        
        return $latestTransaction ? $latestTransaction->supplier : null;
    }

    // FIX: Method untuk mendapatkan primary supplier secara manual
    public function getPrimarySupplierAttribute()
    {
        $supplierStats = $this->stockTransactions()
                             ->whereNotNull('supplier_id')
                             ->selectRaw('supplier_id, COUNT(*) as transaction_count')
                             ->groupBy('supplier_id')
                             ->orderByDesc('transaction_count')
                             ->first();
        
        if ($supplierStats) {
            return Supplier::find($supplierStats->supplier_id);
        }
        
        return null;
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function monthlyBalances()
    {
        return $this->hasMany(MonthlyStockBalance::class);
    }
    
    public function currentBalance()
    {
        return $this->hasOne(MonthlyStockBalance::class)
            ->where('year', now()->year)
            ->where('month', now()->month);
    }

    // Relationship untuk periode tertentu
    public function balanceForPeriod($year, $month)
    {
        return $this->hasOne(MonthlyStockBalance::class)
            ->where('year', $year)
            ->where('month', $month);
    }

    // Scopes - Updated untuk menggunakan monthly balance
    public function scopeLowStock($query)
    {
        return $query->whereHas('currentBalance', function($q) {
            $q->whereRaw('closing_stock <= (SELECT low_stock_threshold FROM items WHERE items.id = monthly_stock_balances.item_id)')
              ->where('closing_stock', '>', 0);
        });
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('currentBalance', function($q) {
            $q->whereRaw('closing_stock > (SELECT low_stock_threshold FROM items WHERE items.id = monthly_stock_balances.item_id)');
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereHas('currentBalance', function($q) {
            $q->where('closing_stock', '<=', 0);
        });
    }

    // Monthly Balance Scopes - TAMBAH INI
    public function scopeLowStockMonthly($query)
    {
        return $query->whereHas('currentBalance', function($q) {
            $q->whereRaw('closing_stock <= (SELECT low_stock_threshold FROM items WHERE items.id = monthly_stock_balances.item_id)')
              ->where('closing_stock', '>', 0);
        });
    }

    public function scopeInStockMonthly($query)
    {
        return $query->whereHas('currentBalance', function($q) {
            $q->whereRaw('closing_stock > (SELECT low_stock_threshold FROM items WHERE items.id = monthly_stock_balances.item_id)');
        });
    }

    public function scopeOutOfStockMonthly($query)
    {
        return $query->whereHas('currentBalance', function($q) {
            $q->where('closing_stock', '<=', 0);
        });
    }

    // Scope untuk periode tertentu
    public function scopeLowStockForPeriod($query, $year, $month)
    {
        return $query->whereHas('monthlyBalances', function($q) use ($year, $month) {
            $q->where('year', $year)
              ->where('month', $month)
              ->whereRaw('closing_stock <= (SELECT low_stock_threshold FROM items WHERE items.id = monthly_stock_balances.item_id)')
              ->where('closing_stock', '>', 0);
        });
    }

    public function scopeOutOfStockForPeriod($query, $year, $month)
    {
        return $query->whereHas('monthlyBalances', function($q) use ($year, $month) {
            $q->where('year', $year)
              ->where('month', $month)
              ->where('closing_stock', '<=', 0);
        });
    }

    public function scopeInStockForPeriod($query, $year, $month)
    {
        return $query->whereHas('monthlyBalances', function($q) use ($year, $month) {
            $q->where('year', $year)
              ->where('month', $month)
              ->whereRaw('closing_stock > (SELECT low_stock_threshold FROM items WHERE items.id = monthly_stock_balances.item_id)');
        });
    }

    // Accessors - Current Stock dari Monthly Balance
    public function getCurrentStockAttribute()
    {
        $balance = $this->currentBalance;
        return $balance ? $balance->closing_stock : 0;
    }

    public function getStockStatusAttribute()
    {
        $currentStock = $this->current_stock;
        
        if ($currentStock <= 0) {
            return 'Habis';
        } elseif ($currentStock <= $this->low_stock_threshold) {
            return 'Menipis';
        } else {
            return 'Tersedia';
        }
    }

    public function getStockStatusColorAttribute()
    {
        $currentStock = $this->current_stock;
        
        if ($currentStock <= 0) {
            return 'danger';
        } elseif ($currentStock <= $this->low_stock_threshold) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    // Method untuk mendapatkan stock untuk periode tertentu
    public function getStockForPeriod($year, $month)
    {
        $balance = MonthlyStockBalance::getBalance($this->id, $year, $month);
        return $balance ? $balance->closing_stock : 0;
    }

    public function getStockStatusForPeriod($year, $month)
    {
        $stock = $this->getStockForPeriod($year, $month);
        
        if ($stock <= 0) {
            return 'Habis';
        } elseif ($stock <= $this->low_stock_threshold) {
            return 'Menipis';
        } else {
            return 'Tersedia';
        }
    }

    public function getStockStatusColorForPeriod($year, $month)
    {
        $stock = $this->getStockForPeriod($year, $month);
        
        if ($stock <= 0) {
            return 'danger';
        } elseif ($stock <= $this->low_stock_threshold) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    // Mutators
    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = strtoupper($value);
    }

    // Methods - Updated untuk menggunakan monthly balance
    public function addStock($quantity, $notes = null, $userId = null, $supplierId = null)
    {
        // Dapatkan atau buat monthly balance
        $balance = MonthlyStockBalance::getOrCreateBalance($this->id);
        
        // Update movement
        $balance->updateMovement('IN', $quantity);
        
        // Catat transaksi
        StockTransaction::create([
            'item_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'supplier_id' => $supplierId, // TAMBAH SUPPLIER
            'transaction_type' => 'IN',
            'quantity' => $quantity,
            'notes' => $notes ?? 'Stock added',
            'transaction_date' => now()
        ]);
        
        return $balance;
    }

    public function reduceStock($quantity, $notes = null, $userId = null, $supplierId = null)
    {
        $balance = MonthlyStockBalance::getOrCreateBalance($this->id);
        
        // Cek stok cukup
        if ($balance->closing_stock < $quantity) {
            return false;
        }
        
        // Update movement
        $balance->updateMovement('OUT', $quantity);
        
        // Catat transaksi
        StockTransaction::create([
            'item_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'supplier_id' => $supplierId, // TAMBAH SUPPLIER
            'transaction_type' => 'OUT',
            'quantity' => $quantity,
            'notes' => $notes ?? 'Stock reduced',
            'transaction_date' => now()
        ]);
        
        return $balance;
    }

    public function adjustStock($newQuantity, $notes = null, $userId = null, $supplierId = null)
    {
        $balance = MonthlyStockBalance::getOrCreateBalance($this->id);
        $oldQuantity = $balance->closing_stock;
        $difference = $newQuantity - $oldQuantity;
        
        // Update movement
        $balance->updateMovement('ADJUSTMENT', $difference);
        
        // Catat transaksi
        StockTransaction::create([
            'item_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'supplier_id' => $supplierId, // TAMBAH SUPPLIER
            'transaction_type' => 'ADJUSTMENT',
            'quantity' => $difference,
            'notes' => $notes ?? "Stock adjusted from {$oldQuantity} to {$newQuantity}",
            'transaction_date' => now()
        ]);
        
        return $balance;
    }

    // Helper methods
    public function hasStock($quantity = 1)
    {
        return $this->current_stock >= $quantity;
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->low_stock_threshold && $this->current_stock > 0;
    }

    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    public function getStockPercentage()
    {
        if ($this->low_stock_threshold <= 0) {
            return 100;
        }
        
        return min(100, ($this->current_stock / $this->low_stock_threshold) * 100);
    }

    // Method untuk mendapatkan history stock
    public function getStockHistory($limit = 12)
    {
        return MonthlyStockBalance::getItemHistory($this->id, $limit);
    }

    // Method untuk comparison dengan bulan sebelumnya
    public function getPreviousMonthComparison()
    {
        $currentBalance = $this->currentBalance;
        if (!$currentBalance) {
            return null;
        }
        
        return $currentBalance->getPreviousMonthComparison();
    }

    // FIX: NEW ACCESSORS FOR SUPPLIER INFO
    public function getSupplierNameAttribute()
    {
        $latestSupplier = $this->latest_supplier; // Gunakan accessor
        return $latestSupplier ? $latestSupplier->supplier_name : 'Tidak ada supplier';
    }

    public function getPrimarySupplierNameAttribute()
    {
        $primarySupplier = $this->primary_supplier; // Gunakan accessor
        return $primarySupplier ? $primarySupplier->supplier_name : 'Tidak ada supplier';
    }

    public function getSupplierCountAttribute()
    {
        return $this->suppliers()->count();
    }

    // METHOD UNTUK MENDAPATKAN SUPPLIER BERDASARKAN PERIODE
    public function getSuppliersForPeriod($startDate, $endDate)
    {
        return $this->suppliers()
                    ->wherePivot('transaction_date', '>=', $startDate)
                    ->wherePivot('transaction_date', '<=', $endDate)
                    ->get();
    }

    public function getSupplierStats()
    {
        return $this->stockTransactions()
                    ->whereNotNull('supplier_id')
                    ->selectRaw('
                        supplier_id,
                        COUNT(*) as transaction_count,
                        SUM(CASE WHEN transaction_type = "IN" THEN quantity ELSE 0 END) as total_stock_in,
                        MAX(transaction_date) as last_transaction_date
                    ')
                    ->with('supplier')
                    ->groupBy('supplier_id')
                    ->orderByDesc('transaction_count')
                    ->get();
    }

    // FIX: Method untuk mendapatkan supplier info dengan safe handling
    public function getSupplierInfo()
    {
        $latestSupplier = $this->latest_supplier;
        $primarySupplier = $this->primary_supplier;
        $supplierCount = $this->supplier_count;
        
        return [
            'latest' => $latestSupplier,
            'primary' => $primarySupplier,
            'count' => $supplierCount,
            'latest_name' => $latestSupplier ? $latestSupplier->supplier_name : 'Tidak ada',
            'primary_name' => $primarySupplier ? $primarySupplier->supplier_name : 'Tidak ada'
        ];
    }

    // FIX: Method alternatif untuk mengambil supplier dengan query builder
    public function getLatestSupplierViaQuery()
    {
        $latestTransaction = $this->stockTransactions()
                                  ->whereNotNull('supplier_id')
                                  ->latest('created_at')
                                  ->first();
        
        return $latestTransaction ? $latestTransaction->supplier : null;
    }

    public function getPrimarySupplierViaQuery()
    {
        $supplierStats = $this->stockTransactions()
                             ->whereNotNull('supplier_id')
                             ->selectRaw('supplier_id, COUNT(*) as transaction_count')
                             ->groupBy('supplier_id')
                             ->orderByDesc('transaction_count')
                             ->first();
        
        if ($supplierStats) {
            return Supplier::find($supplierStats->supplier_id);
        }
        
        return null;
    }

    // Static Methods untuk Statistics
    public static function getStockStatistics()
    {
        $stats = [
            'total_items' => self::count(),
            'low_stock_count' => 0,
            'out_of_stock_count' => 0,
            'in_stock_count' => 0,
        ];

        // Prioritas monthly balance
        $lowStockMonthly = self::lowStockMonthly()->count();
        $outOfStockMonthly = self::outOfStockMonthly()->count();
        $inStockMonthly = self::inStockMonthly()->count();

        if ($lowStockMonthly > 0 || $outOfStockMonthly > 0 || $inStockMonthly > 0) {
            // Gunakan monthly balance jika ada data
            $stats['low_stock_count'] = $lowStockMonthly;
            $stats['out_of_stock_count'] = $outOfStockMonthly;
            $stats['in_stock_count'] = $inStockMonthly;
        } else {
            // Fallback ke current stock
            $stats['low_stock_count'] = self::lowStock()->count();
            $stats['out_of_stock_count'] = self::outOfStock()->count();
            $stats['in_stock_count'] = self::inStock()->count();
        }

        return $stats;
    }

    // === KITCHEN STOCK RELATIONSHIPS ===
    
    public function kitchenStockTransactions()
    {
        return $this->hasMany(KitchenStockTransaction::class);
    }

    public function monthlyKitchenBalances()
    {
        return $this->hasMany(MonthlyKitchenStockBalance::class);
    }

    public function currentKitchenBalance()
    {
        return $this->hasOne(MonthlyKitchenStockBalance::class)
            ->where('year', now()->year)
            ->where('month', now()->month);
    }

    // === KITCHEN STOCK ACCESSORS ===
    
    public function getCurrentKitchenStockAttribute()
    {
        $balance = $this->currentKitchenBalance;
        return $balance ? $balance->closing_stock : 0;
    }

    public function getKitchenStockStatusAttribute()
    {
        $kitchenStock = $this->current_kitchen_stock;
        
        if ($kitchenStock <= 0) {
            return 'Habis';
        } elseif ($kitchenStock <= 5) { // Default threshold 5, nanti bisa dari settings
            return 'Menipis';
        } else {
            return 'Tersedia';
        }
    }

    public function getKitchenStockStatusColorAttribute()
    {
        return match($this->kitchen_stock_status) {
            'Habis' => 'danger',
            'Menipis' => 'warning',
            'Tersedia' => 'success',
            default => 'secondary'
        };
    }

    // === KITCHEN STOCK HELPER METHODS ===
    
    public function hasKitchenStock()
    {
        return $this->monthlyKitchenBalances()->exists();
    }

    public function getKitchenStockForPeriod($year, $month)
    {
        $balance = MonthlyKitchenStockBalance::where([
            'item_id' => $this->id,
            'year' => $year,
            'month' => $month
        ])->first();
        
        return $balance ? $balance->closing_stock : 0;
    }

    // === KITCHEN STOCK OPERATIONS ===
    
    public function transferToKitchen($quantity, $notes = null, $userId = null)
    {
        // Validasi warehouse stock
        $warehouseBalance = MonthlyStockBalance::getOrCreateBalance($this->id);
        if ($warehouseBalance->closing_stock < $quantity) {
            throw new \Exception('Stock gudang tidak mencukupi');
        }

        \DB::beginTransaction();
        try {
            // 1. Kurangi stock gudang
            $warehouseBalance->updateMovement('OUT', $quantity);
            
            // 2. Catat transaksi gudang
            $warehouseTransaction = StockTransaction::create([
                'item_id' => $this->id,
                'user_id' => $userId ?: auth()->id(),
                'transaction_type' => 'OUT',
                'quantity' => $quantity,
                'notes' => 'Naik barang ke dapur',
                'transaction_date' => now(),
                'supplier_id' => null
            ]);

            // 3. Tambah stock dapur
            $kitchenBalance = MonthlyKitchenStockBalance::getOrCreateBalance($this->id);
            $kitchenBalance->updateMovement('TRANSFER_IN', $quantity);
            
            // 4. Catat transaksi dapur
            $kitchenTransaction = KitchenStockTransaction::createTransferIn(
                $this->id, 
                $quantity, 
                $warehouseTransaction->id, 
                $notes, 
                $userId
            );

            \DB::commit();
            
            return [
                'warehouse_transaction' => $warehouseTransaction,
                'kitchen_transaction' => $kitchenTransaction,
                'warehouse_balance' => $warehouseBalance,
                'kitchen_balance' => $kitchenBalance
            ];

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function useKitchenStock($quantity, $notes = null, $userId = null)
    {
        $kitchenBalance = MonthlyKitchenStockBalance::getOrCreateBalance($this->id);
        
        if ($kitchenBalance->closing_stock < $quantity) {
            throw new \Exception('Stock dapur tidak mencukupi');
        }

        // Update kitchen balance
        $kitchenBalance->updateMovement('USAGE', $quantity);
        
        // Catat transaksi
        $transaction = KitchenStockTransaction::createUsage($this->id, $quantity, $notes, $userId);
        
        return [
            'transaction' => $transaction,
            'balance' => $kitchenBalance
        ];
    }

    public function adjustKitchenStock($newQuantity, $notes = null, $userId = null)
    {
        $kitchenBalance = MonthlyKitchenStockBalance::getOrCreateBalance($this->id);
        $oldQuantity = $kitchenBalance->closing_stock;
        $difference = $newQuantity - $oldQuantity;
        
        // Update kitchen balance
        $kitchenBalance->updateMovement('ADJUSTMENT', $difference);
        
        // Catat transaksi
        $transaction = KitchenStockTransaction::createAdjustment($this->id, $difference, $notes, $userId);
        
        return [
            'transaction' => $transaction,
            'balance' => $kitchenBalance,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => $difference
        ];
    }

    // === KITCHEN STOCK SCOPES ===
    
    public function scopeKitchenLowStock($query)
    {
        return $query->whereHas('currentKitchenBalance', function($q) {
            $q->where('closing_stock', '<=', 5)
              ->where('closing_stock', '>', 0);
        });
    }

    public function scopeKitchenInStock($query)
    {
        return $query->whereHas('currentKitchenBalance', function($q) {
            $q->where('closing_stock', '>', 5);
        });
    }

    public function scopeKitchenOutOfStock($query)
    {
        return $query->whereHas('currentKitchenBalance', function($q) {
            $q->where('closing_stock', '<=', 0);
        });
    }
}