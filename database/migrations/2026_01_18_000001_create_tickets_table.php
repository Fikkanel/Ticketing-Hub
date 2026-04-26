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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 50)->unique(); // e.g., "TIKET-13-1"
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedInteger('sequence'); // 1, 2, 3... per order item
            $table->boolean('is_scanned')->default(false);
            $table->timestamp('scanned_at')->nullable();
            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('order_item_id')
                  ->references('item_id')
                  ->on('order_items')
                  ->onDelete('cascade');
                  
            $table->foreign('scanned_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
                  
            // Index for faster lookups
            $table->index(['order_item_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
