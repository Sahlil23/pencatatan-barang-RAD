<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id(); // transaction_id
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('transaction_type', ['IN', 'OUT', 'ADJUSTMENT']);
            $table->decimal('quantity', 10, 2);
            $table->string('notes')->nullable();
            $table->timestamp('transaction_date')->useCurrent(); // Menggunakan 
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
