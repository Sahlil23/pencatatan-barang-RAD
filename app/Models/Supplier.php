<?php

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
        'address'
    ];

    // UBAH RELASI ITEMS - MELALUI STOCK TRANSACTIONS
    public function items()
    {
        return $this->belongsToMany(Item::class, 'stock_transactions')
                    ->withPivot(['transaction_type', 'quantity', 'transaction_date', 'notes'])
                    ->withTimestamps()
                    ->distinct();
    }

    // TAMBAH RELASI STOCK TRANSACTIONS
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    // TAMBAH RELASI UNTUK ITEM YANG AKTIF (BERDASARKAN TRANSAKSI TERAKHIR)
    public function activeItems()
    {
        return $this->belongsToMany(Item::class, 'stock_transactions')
                    ->withPivot(['transaction_date'])
                    ->wherePivot('transaction_date', '>=', now()->subMonths(3)) // 3 bulan terakhir
                    ->distinct();
    }

    // Accessors
    public function getFullContactAttribute()
    {
        return $this->contact_person . ' (' . $this->phone . ')';
    }

    // TAMBAH ACCESSORS BARU
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    public function getTotalTransactionsAttribute()
    {
        return $this->stockTransactions()->count();
    }

    public function getLastTransactionDateAttribute()
    {
        $lastTransaction = $this->stockTransactions()->latest('transaction_date')->first();
        return $lastTransaction ? $lastTransaction->transaction_date : null;
    }

    public function getTotalStockInAttribute()
    {
        return $this->stockTransactions()->where('transaction_type', 'IN')->sum('quantity');
    }

    public function getTotalStockOutAttribute()
    {
        return $this->stockTransactions()->where('transaction_type', 'OUT')->sum('quantity');
    }

    // Scopes
    public function scopeWithContact($query)
    {
        return $query->whereNotNull('contact_person');
    }

    // TAMBAH SCOPES BARU
    public function scopeActive($query, $months = 3)
    {
        return $query->whereHas('stockTransactions', function($q) use ($months) {
            $q->where('transaction_date', '>=', now()->subMonths($months));
        });
    }

    public function scopeWithRecentTransactions($query, $days = 30)
    {
        return $query->whereHas('stockTransactions', function($q) use ($days) {
            $q->where('transaction_date', '>=', now()->subDays($days));
        });
    }

    // METHODS UNTUK SUPPLIER STATISTICS
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

    public function getPerformanceStats($startDate = null, $endDate = null)
    {
        $query = $this->stockTransactions();
        
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        $stats = $query->selectRaw('
                COUNT(*) as total_transactions,
                COUNT(DISTINCT item_id) as unique_items,
                SUM(CASE WHEN transaction_type = "IN" THEN quantity ELSE 0 END) as total_stock_in,
                SUM(CASE WHEN transaction_type = "OUT" THEN quantity ELSE 0 END) as total_stock_out,
                AVG(quantity) as average_quantity,
                MIN(transaction_date) as first_transaction,
                MAX(transaction_date) as last_transaction
            ')
            ->first();
            
        return $stats;
    }
}