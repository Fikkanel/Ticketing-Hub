<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah kolom untuk menyimpan data QRIS dari Core API
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_snap_token');
            $table->text('qris_url')->nullable()->after('midtrans_transaction_id');
            $table->text('qris_string')->nullable()->after('qris_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['midtrans_transaction_id', 'qris_url', 'qris_string']);
        });
    }
};
