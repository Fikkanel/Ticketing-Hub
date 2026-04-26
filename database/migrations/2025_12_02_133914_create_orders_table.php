<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_id', 10)->primary(); 
            
            // Menggunakan customer_id karena Anda memiliki tabel customers
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            $table->timestamp('tgl_order')->useCurrent();
            $table->decimal('total_harga', 10, 2);
            $table->enum('status', ['Pending', 'Paid', 'Shipped', 'Cancelled'])->default('Pending');
            $table->string('metode_pembayaran', 50)->nullable();
            
            $table->string('diskon_code', 50)->nullable();
            $table->decimal('diskon_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};