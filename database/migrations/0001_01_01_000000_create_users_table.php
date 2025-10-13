<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Ini setara dengan user_id INT AUTO_INCREMENT PRIMARY KEY
            $table->string('username', 50)->unique();
            $table->string('password'); // Laravel otomatis menggunakan ini untuk password_hash
            $table->string('full_name', 100);
            $table->enum('role', ['Admin', 'Staf'])->default('Staf');
            $table->timestamps(); // Membuat kolom created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
