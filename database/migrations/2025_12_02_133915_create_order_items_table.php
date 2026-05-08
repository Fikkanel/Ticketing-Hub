<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('item_id');
            
            // 1. Kolom order_id harus sama tipe dan panjangnya dengan di tabel orders
            $table->string('order_id', 10);
            
            // 2. Relasi ke tabel products
            $table->foreignId('product_id')->constrained('products', 'product_id'); 
                
            $table->integer('kuantitas');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            // 3. Definisi Foreign Key manual untuk string order_id
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');

            $table->unique(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
