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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->after('phone');
            $table->date('dob')->nullable()->after('nik');
            $table->enum('gender', ['L', 'P'])->nullable()->after('dob');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['nik', 'dob', 'gender']);
        });
    }
};
