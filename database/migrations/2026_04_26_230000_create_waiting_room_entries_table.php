<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiting_room_entries', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->unsignedBigInteger('event_id')->index();
            $table->string('token', 64)->unique();
            $table->enum('status', ['waiting', 'active', 'expired'])->default('waiting');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            // Composite index for fast lookups
            $table->index(['event_id', 'status']);
            $table->index(['session_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_room_entries');
    }
};
