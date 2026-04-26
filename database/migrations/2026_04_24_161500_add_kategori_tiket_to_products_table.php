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
        // Because of the existing ENUM ('Fisik', 'Digital', 'Seminar'), altering tables with ENUMs in Laravel
        // can sometimes be tricky with DBAL. It's safer to use an independent string or just add a new column.
        Schema::table('products', function (Blueprint $table) {
            $table->enum('kategori_tiket', ['standing', 'seating'])->nullable()->after('tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('kategori_tiket');
        });
    }
};
