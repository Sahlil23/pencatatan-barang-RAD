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
}