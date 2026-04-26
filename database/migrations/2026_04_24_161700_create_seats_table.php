<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_layout_id')->constrained('seat_layouts')->onDelete('cascade');
            $table->integer('row_index');
            $table->integer('col_index');
            $table->string('seat_number'); // e.g., A1, A2
            $table->boolean('is_active')->default(true); // false if the cell is an empty walkway
            $table->timestamps();
            
            // Adding composite index
            $table->unique(['seat_layout_id', 'row_index', 'col_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
