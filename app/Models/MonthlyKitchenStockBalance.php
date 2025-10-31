<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MonthlyKitchenStockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'year',
        'month',
        'opening_stock',
        'closing_stock',
        'transfer_in',
        'usage_out',
        'adjustments',
        'is_closed',
        'closed_at'
    ];

    protected $casts = [
        'opening_stock' => 'decimal:2',
        'closing_stock' => 'decimal:2',
        'transfer_in' => 'decimal:2',
        'usage_out' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
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
                'closing_stock' => $openingStock,
                'transfer_in' => 0,
                'usage_out' => 0,
                'adjustments' => 0,
                'is_closed' => false,
                'closed_at' => null
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
        
        // Jika tidak ada data bulan sebelumnya, mulai dari 0 (kitchen stock awalnya kosong)
        return 0;
    }

    /**
     * Update stock movement (TRANSFER_IN/USAGE/ADJUSTMENT)
     */
    public function updateMovement($type, $quantity)
    {
        // Cek apakah bulan ini sudah ditutup
        if ($this->is_closed) {
            throw new \Exception("Tidak dapat melakukan transaksi pada bulan yang sudah ditutup ({$this->formatted_period})");
        }

        switch (strtoupper($type)) {
            case 'TRANSFER_IN':
                $this->increment('transfer_in', $quantity);
                $this->increment('closing_stock', $quantity);
                break;
                
            case 'USAGE':
                $this->increment('usage_out', $quantity);
                $this->decrement('closing_stock', $quantity);
                break;
                
            case 'ADJUSTMENT':
                // Adjustment bisa positif atau negatif
                if ($quantity >= 0) {
                    $this->increment('adjustments', $quantity);
                    $this->increment('closing_stock', $quantity);
                } else {
                    $this->increment('adjustments', abs($quantity));
                    $this->decrement('closing_stock', abs($quantity));
                }
                break;
        }
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
                SUM(transfer_in) as total_transfer_in,
                SUM(usage_out) as total_usage_out,
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

    public function scopeOpenMonths($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeClosedMonths($query)
    {
        return $query->where('is_closed', true);
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
        return $this->transfer_in + $this->usage_out + abs($this->adjustments);
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

    public function getCanEditAttribute()
    {
        return !$this->is_closed;
    }

    public function getStatusTextAttribute()
    {
        return $this->is_closed ? 'Ditutup' : 'Terbuka';
    }

    public function getStatusColorAttribute()
    {
        return $this->is_closed ? 'danger' : 'success';
    }
}