<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KitchenStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'transaction_type',
        'quantity',
        'warehouse_transaction_id',
        'notes',
        'transaction_date',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // === RELATIONSHIPS ===
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouseTransaction()
    {
        return $this->belongsTo(StockTransaction::class, 'warehouse_transaction_id');
    }

    // === SCOPES ===
    
    public function scopeTransferIn($query)
    {
        return $query->where('transaction_type', 'TRANSFER_IN');
    }

    public function scopeUsage($query)
    {
        return $query->where('transaction_type', 'USAGE');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('transaction_type', 'ADJUSTMENT');
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('transaction_date', $year)
                     ->whereMonth('transaction_date', $month);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('transaction_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // === ACCESSORS ===
    
    public function getFormattedQuantityAttribute()
    {
        $prefix = $this->quantity > 0 ? '+' : '';
        return $prefix . number_format($this->quantity, 1);
    }

    public function getTransactionTypeColorAttribute()
    {
        return match($this->transaction_type) {
            'TRANSFER_IN' => 'success',
            'USAGE' => 'danger', 
            'ADJUSTMENT' => 'warning',
            default => 'secondary'
        };
    }

    public function getTransactionTypeTextAttribute()
    {
        return match($this->transaction_type) {
            'TRANSFER_IN' => 'Transfer Masuk',
            'USAGE' => 'Penggunaan',
            'ADJUSTMENT' => 'Penyesuaian',
            default => 'Unknown'
        };
    }

    // === STATIC METHODS ===
    
    public static function createTransferIn($itemId, $quantity, $warehouseTransactionId, $notes = null, $userId = null)
    {
        return static::create([
            'item_id' => $itemId,
            'transaction_type' => 'TRANSFER_IN',
            'quantity' => $quantity,
            'warehouse_transaction_id' => $warehouseTransactionId,
            'notes' => $notes ?: 'Transfer dari gudang',
            'transaction_date' => now()->toDateString(),
            'user_id' => $userId ?: auth()->id(),
        ]);
    }

    public static function createUsage($itemId, $quantity, $notes = null, $userId = null)
    {
        return static::create([
            'item_id' => $itemId,
            'transaction_type' => 'USAGE',
            'quantity' => $quantity,
            'warehouse_transaction_id' => null,
            'notes' => $notes ?: 'Penggunaan dapur',
            'transaction_date' => now()->toDateString(),
            'user_id' => $userId ?: auth()->id(),
        ]);
    }

    public static function createAdjustment($itemId, $quantity, $notes = null, $userId = null)
    {
        return static::create([
            'item_id' => $itemId,
            'transaction_type' => 'ADJUSTMENT',
            'quantity' => $quantity,
            'warehouse_transaction_id' => null,
            'notes' => $notes ?: 'Penyesuaian stock dapur',
            'transaction_date' => now()->toDateString(),
            'user_id' => $userId ?: auth()->id(),
        ]);
    }
}