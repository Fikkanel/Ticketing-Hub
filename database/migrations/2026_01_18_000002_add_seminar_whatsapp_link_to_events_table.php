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
            // Add WhatsApp link for seminar-type events
            // If this field is filled, the event is treated as a "seminar" type:
            // - No ticket email attachment
            // - Invoice shows WhatsApp join link instead
            $table->string('seminar_whatsapp_link', 500)->nullable()->after('custom_email_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('seminar_whatsapp_link');
        });
    }
};
