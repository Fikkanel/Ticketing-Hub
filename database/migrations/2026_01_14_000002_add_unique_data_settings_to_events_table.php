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
        Schema::table('events', function (Blueprint $table) {
            // Pengaturan Pembelian
            $table->unsignedTinyInteger('max_tickets_per_transaction')->default(10)->after('payment_mode');
            $table->boolean('limit_one_email_per_transaction')->default(false)->after('max_tickets_per_transaction');
            $table->boolean('require_unique_data_per_ticket')->default(false)->after('limit_one_email_per_transaction');
            
            // Formulir Data Pemesan (standard fields)
            // Format: [{"field": "name", "enabled": true, "required": true}, ...]
            $table->json('buyer_form_fields')->nullable()->after('require_unique_data_per_ticket');
            
            // Custom Form Fields (admin-defined)
            // Format: [{"name": "ukuran_baju", "label": "Ukuran Baju", "type": "select", "options": ["S","M","L","XL"], "required": true}, ...]
            $table->json('custom_form_fields')->nullable()->after('buyer_form_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'max_tickets_per_transaction',
                'limit_one_email_per_transaction',
                'require_unique_data_per_ticket',
                'buyer_form_fields',
                'custom_form_fields'
            ]);
        });
    }
};
