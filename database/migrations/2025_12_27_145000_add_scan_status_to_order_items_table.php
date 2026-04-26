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
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_scanned')->default(false)->after('subtotal');
            $table->timestamp('scanned_at')->nullable()->after('is_scanned');
            $table->unsignedBigInteger('scanned_by')->nullable()->after('scanned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_scanned', 'scanned_at', 'scanned_by']);
        });
    }
};
