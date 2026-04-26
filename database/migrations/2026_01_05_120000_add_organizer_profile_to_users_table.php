<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom profil organizer ke tabel users
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('organizer_name', 100)->nullable()->after('telepon');
            $table->string('organizer_slug', 100)->nullable()->unique()->after('organizer_name');
            $table->string('organizer_logo', 255)->nullable()->after('organizer_slug');
            $table->text('organizer_description')->nullable()->after('organizer_logo');
            $table->string('organizer_phone', 20)->nullable()->after('organizer_description');
            $table->string('organizer_email', 100)->nullable()->after('organizer_phone');
            $table->string('organizer_city', 50)->nullable()->after('organizer_email');
            $table->string('organizer_instagram', 100)->nullable()->after('organizer_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'organizer_name',
                'organizer_slug',
                'organizer_logo',
                'organizer_description',
                'organizer_phone',
                'organizer_email',
                'organizer_city',
                'organizer_instagram',
            ]);
        });
    }
};
