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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('buyer_nik', 16)->nullable()->after('guest_token');
            $table->date('buyer_dob')->nullable()->after('buyer_nik');
            $table->char('buyer_gender', 1)->nullable()->after('buyer_dob'); // L/P
            $table->json('buyer_custom_data')->nullable()->after('buyer_gender'); // For custom form fields
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['buyer_nik', 'buyer_dob', 'buyer_gender', 'buyer_custom_data']);
        });
    }
};
