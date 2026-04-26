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
        Schema::table('discounts', function (Blueprint $table) {
            // Relasi ke tabel events (event_id)
            // Nullable karena diskon global (untuk semua event) event_id-nya null
            // Jika event dihapus, set null (atau cascade, tapi null lebih aman agar history transaksi tetap ada info diskonnya meski event hilang, tapi terserah. NullOnDelete ok)
            $table->foreignId('event_id')->nullable()->after('discount_id')->constrained('events', 'event_id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
