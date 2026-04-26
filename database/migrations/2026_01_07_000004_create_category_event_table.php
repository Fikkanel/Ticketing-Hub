<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pivot table untuk relasi many-to-many antara Event dan Category.
     */
    public function up(): void
    {
        Schema::create('category_event', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            
            // Unique constraint untuk mencegah duplikat
            $table->unique(['event_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_event');
    }
};
