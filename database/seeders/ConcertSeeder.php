<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Product;
use App\Models\SeatLayout;
use App\Models\Seat;
use App\Models\Location;
use Carbon\Carbon;

class ConcertSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada lokasi
        $location = Location::first();
        if (!$location) {
            $location = Location::create([
                'nama_lokasi' => 'Stadion Gelora Bung Karno',
                'alamat' => 'Jl. Pintu Satu Senayan, Gelora, Tanah Abang, Jakarta Pusat',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
                'kapasitas' => 77000
            ]);
        }

        // 1. Buat Event Konser
        $concert = Event::create([
            'location_id' => $location->id ?? 1,
            'judul' => 'Coldplay: Music of the Spheres World Tour',
            'deskripsi' => 'Konser spektakuler Coldplay di Jakarta.',
            'tgl_mulai' => Carbon::now()->addDays(60),
            'tgl_selesai' => Carbon::now()->addDays(60)->addHours(4),
            'status' => 'Active',
            'payment_mode' => 'regular',
        ]);

        // 2. Buat Produk: Festival (Standing)
        Product::create([
            'event_id' => $concert->event_id,
            'nama_produk' => 'Festival (Free Standing)',
            'deskripsi' => 'Area berdiri bebas di lapangan utama. Paling dekat dengan panggung.',
            'harga' => 3500000,
            'stok' => 500,
            'tipe' => 'Digital',
            'kategori_tiket' => 'standing',
        ]);

        // 3. Buat Produk: VIP (Seating)
        $vipProduct = Product::create([
            'event_id' => $concert->event_id,
            'nama_produk' => 'VIP Tribune (Seating)',
            'deskripsi' => 'Kursi tribun eksklusif dengan pandangan terbaik ke panggung.',
            'harga' => 5000000,
            'stok' => 50, // Akan disesuaikan oleh jumlah kursi aktif
            'tipe' => 'Digital',
            'kategori_tiket' => 'seating',
        ]);

        // 4. Buat Seat Layout untuk VIP
        $seatLayout = SeatLayout::create([
            'product_id' => $vipProduct->product_id,
            'rows' => 5,
            'columns' => 10,
            'stage_position' => 'top',
        ]);

        // 5. Buat Seats
        $seatsToInsert = [];
        $now = now();
        $activeCount = 0;

        for ($r = 0; $r < 5; $r++) {
            for ($c = 0; $c < 10; $c++) {
                // Buat formasi U atau ada jalan di tengah (kolom 4 dan 5 mati)
                $isActive = !($c == 4 || $c == 5);
                
                // Hitung label A1, A2, dst
                $letter = chr(65 + $r); // A, B, C, D, E
                $label = $letter . ($c + 1);

                $seatsToInsert[] = [
                    'seat_layout_id' => $seatLayout->id,
                    'row_index' => $r,
                    'col_index' => $c,
                    'seat_number' => $label,
                    'is_active' => $isActive,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($isActive) $activeCount++;
            }
        }

        Seat::insert($seatsToInsert);
        $vipProduct->update(['stok' => $activeCount]);

        // 6. Buat Event Seminar (Tanpa seating)
        $seminar = Event::create([
            'location_id' => 1,
            'judul' => 'Digital Marketing Masterclass',
            'deskripsi' => 'Seminar intensif tentang digital marketing di era AI.',
            'tgl_mulai' => Carbon::now()->addDays(15),
            'tgl_selesai' => Carbon::now()->addDays(15)->addHours(8),
            'status' => 'Active',
            'payment_mode' => 'regular',
        ]);

        // 7. Buat Produk Seminar
        Product::create([
            'event_id' => $seminar->event_id,
            'nama_produk' => 'Tiket Masuk Seminar',
            'deskripsi' => 'Tiket sudah termasuk lunch box dan sertifikat digital.',
            'harga' => 250000,
            'stok' => 100,
            'tipe' => 'Seminar',
            'kategori_tiket' => null, // Tidak perlu pilih kursi
        ]);
        
        $this->command->info('Concert, Seminar, and Seat Layouts seeded successfully!');
    }
}
