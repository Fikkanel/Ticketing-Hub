<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Raw SQL untuk mengubah kolom ENUM
        DB::statement("ALTER TABLE products MODIFY COLUMN tipe ENUM('Fisik', 'Digital', 'Seminar') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke definisi sebelumnya (jika perlu rollback, pastikan data 'Seminar' sudah aman/dihapus dulu)
        DB::statement("ALTER TABLE products MODIFY COLUMN tipe ENUM('Fisik', 'Digital') NOT NULL");
    }
};
