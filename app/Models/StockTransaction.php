<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'supplier_id',  // TAMBAH INI
        'transaction_type',
        'quantity',
        'notes',
        'transaction_date'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // TAMBAH RELASI SUPPLIER
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Scopes
    public function scopeStockIn($query)
    {
        return $query->where('transaction_type', 'IN');
    }

    public function scopeStockOut($query)
    {
        return $query->where('transaction_type', 'OUT');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('transaction_type', 'ADJUSTMENT');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    // TAMBAH SCOPE UNTUK SUPPLIER
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeWithSupplier($query)
    {
        return $query->whereNotNull('supplier_id');
    }

    public function scopeWithoutSupplier($query)
    {
        return $query->whereNull('supplier_id');
    }

    // Accessors
    public function getTransactionTypeTextAttribute()
    {
        return match($this->transaction_type) {
            'IN' => 'Stock In',
            'OUT' => 'Stock Out',
            'ADJUSTMENT' => 'Adjustment',
            default => $this->transaction_type
        };
    }

    public function getTransactionTypeColorAttribute()
    {
        return match($this->transaction_type) {
            'IN' => 'success',
            'OUT' => 'danger',
            'ADJUSTMENT' => 'warning',
            default => 'secondary'
        };
    }

    public function getFormattedQuantityAttribute()
    {
        $sign = match($this->transaction_type) {
            'IN' => '+',
            'OUT' => '-',
            'ADJUSTMENT' => '±',
            default => ''
        };
        return $sign . number_format($this->quantity, 2);
    }

    // TAMBAH ACCESSOR UNTUK SUPPLIER
    public function getSupplierNameAttribute()
    {
        return $this->supplier ? $this->supplier->supplier_name : 'Tidak ada supplier';
    }

    public function getHasSupplierAttribute()
    {
        return !is_null($this->supplier_id);
    }

    // STATIC METHODS UNTUK SUPPLIER STATISTICS
    public static function getSupplierStats($startDate = null, $endDate = null)
    {
        $query = self::whereNotNull('supplier_id');
        
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        return $query->selectRaw('
                supplier_id,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN transaction_type = "IN" THEN quantity ELSE 0 END) as total_stock_in,
                SUM(CASE WHEN transaction_type = "OUT" THEN quantity ELSE 0 END) as total_stock_out,
                MAX(transaction_date) as last_transaction_date
            ')
            ->with('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('transaction_count')
            ->get();
    }

    public static function getTopSuppliers($limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::whereNotNull('supplier_id');
        
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        
        return $query->selectRaw('
                supplier_id,
                COUNT(*) as transaction_count,
                SUM(quantity) as total_quantity
            ')
            ->with('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('total_quantity')
            ->take($limit)
            ->get();
    }
}