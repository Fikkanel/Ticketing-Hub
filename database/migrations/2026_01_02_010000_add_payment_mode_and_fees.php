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
            $table->string('payment_mode')->default('regular')->after('payment_channels'); // 'regular', 'sponsorship'
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('fee_admin', 10, 2)->default(0)->after('total_harga');
            $table->decimal('fee_service', 10, 2)->default(0)->after('fee_admin');
            $table->decimal('fee_tax', 10, 2)->default(0)->after('fee_service'); // PPN
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['fee_admin', 'fee_service', 'fee_tax']);
        });
    }
};
