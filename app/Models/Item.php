<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'item_name',
        'category_id',
        'supplier_id',
        'unit',
        'current_stock',
        'low_stock_threshold'
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= low_stock_threshold');
    }

    public function scopeInStock($query)
    {
        return $query->where('current_stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    // Accessors
    public function getStockStatusAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'Out of Stock';
        } elseif ($this->current_stock <= $this->low_stock_threshold) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    public function getStockStatusColorAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'danger';
        } elseif ($this->current_stock <= $this->low_stock_threshold) {
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

    // Methods
    public function addStock($quantity, $notes = null, $userId = null)
    {
        $this->increment('current_stock', $quantity);
        
        StockTransaction::create([
            'item_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'transaction_type' => 'IN',
            'quantity' => $quantity,
            'notes' => $notes ?? 'Stock added',
            'transaction_date' => now()
        ]);
    }

    public function reduceStock($quantity, $notes = null, $userId = null)
    {
        if ($this->current_stock >= $quantity) {
            $this->decrement('current_stock', $quantity);
            
            StockTransaction::create([
                'item_id' => $this->id,
                'user_id' => $userId ?? auth()->id(),
                'transaction_type' => 'OUT',
                'quantity' => $quantity,
                'notes' => $notes ?? 'Stock reduced',
                'transaction_date' => now()
            ]);
            
            return true;
        }
        
        return false; // Insufficient stock
    }

    public function adjustStock($newQuantity, $notes = null, $userId = null)
    {
        $oldQuantity = $this->current_stock;
        $difference = $newQuantity - $oldQuantity;
        
        $this->update(['current_stock' => $newQuantity]);
        
        StockTransaction::create([
            'item_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'transaction_type' => 'ADJUSTMENT',
            'quantity' => abs($difference),
            'notes' => $notes ?? "Stock adjusted from {$oldQuantity} to {$newQuantity}",
            'transaction_date' => now()
        ]);
    }
}