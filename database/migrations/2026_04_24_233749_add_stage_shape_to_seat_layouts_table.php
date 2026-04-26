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
        Schema::table('seat_layouts', function (Blueprint $table) {
            $table->enum('stage_shape', ['normal', 'convex', 'concave'])->default('normal')->after('stage_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seat_layouts', function (Blueprint $table) {
            $table->dropColumn('stage_shape');
        });
    }
};
