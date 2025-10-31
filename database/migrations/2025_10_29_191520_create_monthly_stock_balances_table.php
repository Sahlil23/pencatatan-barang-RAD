<?php
// filepath: database/migrations/2025_10_30_000001_create_monthly_stock_balances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->year('year'); // 2024, 2025, etc
            $table->tinyInteger('month'); // 1-12
            $table->decimal('opening_stock', 10, 2)->default(0.00); // Stok awal bulan
            $table->decimal('closing_stock', 10, 2)->default(0.00); // Stok akhir bulan
            $table->decimal('stock_in', 10, 2)->default(0.00); // Total masuk dalam bulan
            $table->decimal('stock_out', 10, 2)->default(0.00); // Total keluar dalam bulan
            $table->decimal('adjustments', 10, 2)->default(0.00); // Total penyesuaian
            $table->timestamps();
            
            // Unique constraint: satu item hanya bisa punya satu balance per bulan
            $table->unique(['item_id', 'year', 'month'], 'unique_item_month');
            
            // Index untuk performance
            $table->index(['year', 'month'], 'idx_year_month');
            $table->index('item_id', 'idx_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_stock_balances');
    }
};