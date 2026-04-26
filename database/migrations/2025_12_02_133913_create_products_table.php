<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ...
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            
            // PASTIKAN nullable() dipanggil di sini.
            // onnDelete('set null') agar FK diatur ke NULL jika Event dihapus.
            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events', 'event_id')
                ->onDelete('set null'); 
                
            $table->string('nama_produk', 150);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 10, 2);
            $table->integer('stok')->default(0);
            $table->enum('tipe', ['Fisik', 'Digital']);
            $table->timestamps();
        });
    }
};
