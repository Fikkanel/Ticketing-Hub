<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Asumsi ID lokasi dari LocationSeeder adalah 1, 2, dan 3
        DB::table('events')->insert([
            [
                'location_id' => 1,
                'judul' => 'Ticketing Hub Tech Expo 2026',
                'deskripsi' => 'Pameran teknologi terbesar di Indonesia, fokus pada AI dan IoT.',
                'tgl_mulai' => Carbon::now()->addDays(30),
                'tgl_selesai' => Carbon::now()->addDays(32),
                'status' => 'Upcoming',
            ],
            [
                'location_id' => 2,
                'judul' => 'Bandung Coffee & Arts Festival',
                'deskripsi' => 'Menghadirkan kopi lokal terbaik dan pameran seni kontemporer.',
                'tgl_mulai' => Carbon::now()->addDays(5),
                'tgl_selesai' => Carbon::now()->addDays(7),
                'status' => 'Active',
            ],
            [
                'location_id' => 3,
                'judul' => 'Global Tourism Summit Bali',
                'deskripsi' => 'Konferensi pariwisata internasional di Bali.',
                'tgl_mulai' => Carbon::now()->subDays(10), // Sudah lewat
                'tgl_selesai' => Carbon::now()->subDays(8),
                'status' => 'Finished',
            ],
        ]);
    }
}
