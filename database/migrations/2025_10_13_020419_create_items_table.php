<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('item_name', 150);
            
            // Buat kolom biasa dulu, foreign key ditambah terpisah
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            
            $table->string('unit', 20);
            // $table->decimal('current_stock', 10, 2)->default(0.00);
            $table->decimal('low_stock_threshold', 10, 2)->default(5.00);
            
            $table->timestamps();
        });
        
        // Tambah foreign key setelah tabel dibuat
        Schema::table('items', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
