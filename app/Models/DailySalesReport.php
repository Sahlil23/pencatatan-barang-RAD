<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySalesReport extends Model
{
    use HasFactory;

    // Nama tabel (opsional jika nama tabel sesuai standar plural)
    protected $table = 'daily_sales_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 1. Identitas
        'outlet_warehouse_id',
        'created_by_user_id',
        'report_date',

        // 2. Sales & Target (Kolom Biasa)
        'target_sales',
        'sales_shift_opening',
        'sales_shift_closing',
        'sales_shift_midnight',
        'total_sales',

        // 3. Cash & Void (Kolom Biasa)
        'payment_cash',
        'payment_void_refund',

        // 4. Agregat (Kolom Biasa)
        'mtd_sales',
        'guest_count_today',
        'mtd_guest_count',

        // 5. Kolom JSON (Data Terkelompok)
        'payment_details_digital', // EDC, QR, Transfer
        'delivery_platforms',      // GoFood, GrabFood
        'staff_on_duty',           // Opening, Closing
        'notes',                   // Fast moving, Kejadian penting
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'report_date' => 'date',
        
        // Casting JSON sangat PENTING.
        // Ini membuat Anda bisa mengakses $report->staff_on_duty['opening']
        // tanpa perlu json_decode() manual.
        'payment_details_digital' => 'array',
        'delivery_platforms' => 'array',
        'staff_on_duty' => 'array',
        'notes' => 'array',

        // Casting Decimal untuk memastikan format angka benar
        'target_sales' => 'decimal:2',
        'sales_shift_opening' => 'decimal:2',
        'sales_shift_closing' => 'decimal:2',
        'sales_shift_midnight' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'payment_cash' => 'decimal:2',
        'payment_void_refund' => 'decimal:2',
        'mtd_sales' => 'decimal:2',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Relasi ke Outlet Warehouse
     */
    public function outletWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'outlet_warehouse_id');
    }

    /**
     * Relasi ke User yang membuat laporan
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ========================================
    // HELPER ACCESSORS (Opsional)
    // ========================================

    /**
     * Contoh: Mengambil total pembayaran digital dari JSON
     * Cara pakai: $report->total_digital_payment
     */
    public function getTotalDigitalPaymentAttribute()
    {
        // Mengambil array dari kolom JSON
        $details = $this->payment_details_digital ?? [];
        
        // Menjumlahkan semua nilai di dalam array
        return collect($details)->sum();
    }

    /**
     * Contoh: Mengambil total sales delivery (GoFood + GrabFood + dll)
     * Cara pakai: $report->total_delivery_sales
     */
    public function getTotalDeliverySalesAttribute()
    {
        $platforms = $this->delivery_platforms ?? [];
        
        // Sum 'sales' key from each platform
        return collect($platforms)->sum('sales');
    }
}