<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Kolom untuk menyimpan token SNAP dari Midtrans
            // Dibuat nullable karena kolom ini baru terisi setelah transaksi dibuat
            $table->string('midtrans_snap_token')->nullable()->after('metode_pembayaran');
            
            // Kolom opsional: Untuk menyimpan ID transaksi dari Midtrans jika diperlukan
            // $table->string('midtrans_transaction_id')->nullable()->after('midtrans_snap_token');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Saat rollback, hapus kolom yang telah ditambahkan
            $table->dropColumn('midtrans_snap_token');
            
            // Jika Anda menambahkan midtrans_transaction_id, tambahkan baris ini juga
            // $table->dropColumn('midtrans_transaction_id');
        });
    }
};
