<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id('location_id'); // Menggunakan 'id' sebagai alias untuk AUTO_INCREMENT PRIMARY KEY
            $table->string('nama_lokasi', 150);
            $table->text('alamat');
            $table->string('kota', 50)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }
    // ...
};
