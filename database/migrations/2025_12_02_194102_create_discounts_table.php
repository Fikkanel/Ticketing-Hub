<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id('discount_id');
            $table->string('code')->unique(); // Kode unik (misal: FREE2025)
            $table->integer('percentage')->default(0); // Diskon dalam %
            $table->integer('max_uses')->default(1); // Batas penggunaan
            $table->integer('used_count')->default(0); // Jumlah yang sudah digunakan
            $table->boolean('is_active')->default(true);
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};