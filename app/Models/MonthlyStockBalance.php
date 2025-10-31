<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class MonthlyStockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 
        'year', 
        'month', 
        'opening_stock', 
        'closing_stock', 
        'stock_in', 
        'stock_out', 
        'adjustments'
    ];

    protected $casts = [
        'opening_stock' => 'decimal:2',
        'closing_stock' => 'decimal:2',
        'stock_in' => 'decimal:2',
        'stock_out' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
    ];

    // === RELATIONSHIPS ===
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // === STATIC METHODS ===
    
    /**
     * Dapatkan atau buat balance untuk bulan tertentu
     */
    public static function getOrCreateBalance($itemId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        $balance = static::where([
            'item_id' => $itemId,
            'year' => $year,
            'month' => $month
        ])->first();
        
        if (!$balance) {
            // Cari opening stock dari bulan sebelumnya
            $openingStock = static::getOpeningStock($itemId, $year, $month);
            
            $balance = static::create([
                'item_id' => $itemId,
                'year' => $year,
                'month' => $month,
                'opening_stock' => $openingStock,
                'closing_stock' => $openingStock, // Mulai dengan opening stock
                'stock_in' => 0,
                'stock_out' => 0,
                'adjustments' => 0
            ]);
        }
        
        return $balance;
    }

    /**
     * Hitung opening stock dari closing stock bulan sebelumnya
     */
    public static function getOpeningStock($itemId, $year, $month)
    {
        // Tentukan bulan sebelumnya
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
        }
        
        // Cari closing stock bulan sebelumnya
        $previousBalance = static::where([
            'item_id' => $itemId,
            'year' => $prevYear,
            'month' => $prevMonth
        ])->first();
        
        if ($previousBalance) {
            return $previousBalance->closing_stock;
        }
        
        // Jika tidak ada data bulan sebelumnya, gunakan current_stock dari items table
        $item = Item::find($itemId);
        return $item ? $item->current_stock : 0;
    }

    /**
     * Dapatkan balance untuk bulan tertentu (tanpa create)
     */
    public static function getBalance($itemId, $year, $month)
    {
        return static::where([
            'item_id' => $itemId,
            'year' => $year,
            'month' => $month
        ])->first();
    }

    /**
     * Dapatkan balance bulan sebelumnya
     */
    public static function getPreviousMonthBalance($itemId, $year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        // Tentukan bulan sebelumnya
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
        }

        return static::getBalance($itemId, $prevYear, $prevMonth);
    }

    /**
     * Dapatkan all balance untuk item tertentu (riwayat bulanan)
     */
    public static function getItemHistory($itemId, $limit = 12)
    {
        return static::where('item_id', $itemId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Dapatkan summary untuk periode tertentu
     */
    public static function getPeriodSummary($year, $month)
    {
        return static::where('year', $year)
            ->where('month', $month)
            ->selectRaw('
                COUNT(*) as total_items,
                SUM(opening_stock) as total_opening_stock,
                SUM(stock_in) as total_stock_in,
                SUM(stock_out) as total_stock_out,
                SUM(closing_stock) as total_closing_stock,
                SUM(adjustments) as total_adjustments
            ')
            ->first();
    }

    /**
     * Dapatkan available periods (tahun-bulan yang ada data)
     */
    public static function getAvailablePeriods()
    {
        return static::selectRaw('DISTINCT year, month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'label' => Carbon::create($item->year, $item->month, 1)->format('F Y'),
                    'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT)
                ];
            });
    }

    
    /**
     * Update opening stock dari bulan sebelumnya
     */
    public function syncOpeningStock()
    {
        $openingStock = static::getOpeningStock($this->item_id, $this->year, $this->month);
        $this->update(['opening_stock' => $openingStock]);
        
        // Recalculate closing stock
        $this->recalculateClosingStock();
    }
    
    /**
     * Hitung ulang closing stock berdasarkan formula
     */
    public function recalculateClosingStock()
    {
        $newClosingStock = $this->opening_stock + $this->stock_in - $this->stock_out + $this->adjustments;
        $this->update(['closing_stock' => $newClosingStock]);
    }
    
    /**
     * Update stock movement (IN/OUT/ADJUSTMENT)
     */
    public function updateMovement($type, $quantity)
    {
        switch (strtoupper($type)) {
            case 'IN':
                $this->increment('stock_in', $quantity);
                $this->increment('closing_stock', $quantity);
                break;
                
            case 'OUT':
                $this->increment('stock_out', $quantity);
                $this->decrement('closing_stock', $quantity);
                break;
                
            case 'ADJUSTMENT':
                // Adjustment bisa positif atau negatif
                $this->increment('adjustments', abs($quantity));
                
                if ($quantity > 0) {
                    $this->increment('closing_stock', $quantity);
                } else {
                    $this->decrement('closing_stock', abs($quantity));
                }
                break;
        }
    }

    /**
     * Get comparison with previous month
     */
    public function getPreviousMonthComparison()
    {
        $previousBalance = static::getPreviousMonthBalance($this->item_id, $this->year, $this->month);
        
        if (!$previousBalance) {
            return null;
        }

        return [
            'previous_balance' => $previousBalance,
            'opening_stock_change' => $this->opening_stock - $previousBalance->closing_stock,
            'closing_stock_change' => $this->closing_stock - $previousBalance->closing_stock,
            'stock_in_change' => $this->stock_in - $previousBalance->stock_in,
            'stock_out_change' => $this->stock_out - $previousBalance->stock_out,
            'net_change_comparison' => $this->net_change - $previousBalance->net_change
        ];
    }

    // === SCOPES ===
    
    public function scopeForMonth($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }
    
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }
    
    public function scopeCurrentMonth($query)
    {
        return $query->where('year', now()->year)->where('month', now()->month);
    }

    public function scopePreviousMonth($query)
    {
        $prevDate = now()->subMonth();
        return $query->where('year', $prevDate->year)->where('month', $prevDate->month);
    }

    public function scopeForPeriod($query, $yearMonth)
    {
        if (strpos($yearMonth, '-') !== false) {
            [$year, $month] = explode('-', $yearMonth);
            return $query->where('year', $year)->where('month', $month);
        }
        return $query;
    }

    // === ACCESSORS ===
    
    public function getNetChangeAttribute()
    {
        return $this->closing_stock - $this->opening_stock;
    }
    
    public function getMonthNameAttribute()
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
    
    public function getTotalMovementAttribute()
    {
        return $this->stock_in + $this->stock_out + abs($this->adjustments);
    }
    
    public function getFormattedPeriodAttribute()
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$this->month] . ' ' . $this->year;
    }

    public function getPeriodValueAttribute()
    {
        return $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT);
    }

    public function getIsCurrentMonthAttribute()
    {
        return $this->year == now()->year && $this->month == now()->month;
    }

    public function getIsPreviousMonthAttribute()
    {
        $prevDate = now()->subMonth();
        return $this->year == $prevDate->year && $this->month == $prevDate->month;
    }
}