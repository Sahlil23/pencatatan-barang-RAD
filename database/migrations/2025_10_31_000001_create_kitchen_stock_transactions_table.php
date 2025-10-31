<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->enum('transaction_type', ['TRANSFER_IN', 'USAGE', 'ADJUSTMENT']);
            $table->decimal('quantity', 10, 2); // Bisa negatif untuk adjustment
            $table->foreignId('warehouse_transaction_id')->nullable()->constrained('stock_transactions')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // FIX: Indexes dengan nama yang lebih pendek
            $table->index(['item_id', 'transaction_date'], 'idx_kitchen_item_date');
            $table->index(['transaction_type', 'transaction_date'], 'idx_kitchen_type_date');
            $table->index('user_id', 'idx_kitchen_user');
            $table->index('warehouse_transaction_id', 'idx_kitchen_warehouse');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_stock_transactions');
    }
};