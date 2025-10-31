<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_kitchen_stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('opening_stock', 10, 2)->default(0.00);
            $table->decimal('closing_stock', 10, 2)->default(0.00); // INI CURRENT STOCK
            $table->decimal('transfer_in', 10, 2)->default(0.00); // Total dari gudang
            $table->decimal('usage_out', 10, 2)->default(0.00); // Total penggunaan
            $table->decimal('adjustments', 10, 2)->default(0.00); // Total penyesuaian
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            // FIX: Unique constraint dengan nama pendek
            $table->unique(['item_id', 'year', 'month'], 'unique_kitchen_item_month');
            
            // FIX: Indexes dengan nama pendek
            $table->index(['year', 'month'], 'idx_kitchen_year_month');
            $table->index('item_id', 'idx_kitchen_balance_item');
            $table->index('is_closed', 'idx_kitchen_closed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_kitchen_stock_balances');
    }
};