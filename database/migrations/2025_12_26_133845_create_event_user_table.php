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
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Gunakan 'event_id' sebagai referensi sesuai primary key tabel events
            $table->unsignedBigInteger('event_id');
            // constraint manual karena PK event bukan standard id
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'event_id']); // Prevent duplicates
        });

        // Migrasi Data Lama: Pindahkan event_id dari users ke pivot table
        $usersWithEvent = DB::table('users')->whereNotNull('event_id')->get();
        foreach ($usersWithEvent as $user) {
            DB::table('event_user')->insert([
                'user_id' => $user->id,
                'event_id' => $user->event_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Hapus kolom lama
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable();
        });

        // Restore data (jika mungkin, ambil yang pertama)
        $pivots = DB::table('event_user')->get();
        foreach ($pivots as $pivot) {
           DB::table('users')->where('id', $pivot->user_id)->update(['event_id' => $pivot->event_id]);
        }

        Schema::dropIfExists('event_user');
    }
};
