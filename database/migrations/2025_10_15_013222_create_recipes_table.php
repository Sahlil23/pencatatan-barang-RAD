<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama resep
            $table->string('slug')->unique(); // Slug untuk URL
            $table->text('description')->nullable(); // Deskripsi singkat
            $table->string('image')->nullable(); // Gambar makanan
            $table->integer('prep_time')->default(0); // Waktu persiapan (menit)
            $table->integer('cook_time')->default(0); // Waktu memasak (menit)
            $table->integer('servings')->default(1); // Porsi
            $table->enum('difficulty', ['mudah', 'sedang', 'sulit'])->default('mudah');
            $table->json('ingredients'); // Bahan-bahan (JSON)
            $table->json('instructions'); // Cara membuat (JSON)
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recipes');
    }
};