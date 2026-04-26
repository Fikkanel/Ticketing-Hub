<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->foreignId('location_id')->constrained('locations', 'location_id'); // Foreign Key ke tabel locations
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->dateTime('tgl_mulai');
            $table->dateTime('tgl_selesai')->nullable();
            $table->enum('status', ['Upcoming', 'Active', 'Finished'])->default('Upcoming');
            $table->string('poster_url', 255)->nullable();
            $table->timestamps();
        });
    }
};
