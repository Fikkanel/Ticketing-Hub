<?php

namespace Database\Seeders;

// database/seeders/ProductSeeder.php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Asumsi event ID 1, 2, 3 ada dari EventSeeder
        DB::table('products')->insert([
            // Produk Fisik (Stok Terbatas)
            [
                'event_id' => 1, 
                'nama_produk' => 'Travel Kit Eksklusif (Fisik)',
                'deskripsi' => 'Kit berisi tumbler, masker, dan tote bag edisi Tech Expo.',
                'harga' => 150000.00,
                'stok' => 50, // Stok harus diuji
                'tipe' => 'Fisik',
            ],
            // Produk Digital (Stok Tak Terbatas / Voucher)
            [
                'event_id' => 2,
                'nama_produk' => 'Voucher Kopi Rp 50.000 (Digital)',
                'deskripsi' => 'Voucher diskon untuk semua tenant kopi di festival.',
                'harga' => 45000.00,
                'stok' => 999,
                'tipe' => 'Digital',
            ],
            // Produk Umum (Tidak terikat Event)
            [
                'event_id' => null,
                'nama_produk' => 'Aksesoris Universal Experience',
                'deskripsi' => 'Aksesoris umum yang dijual di luar konteks event spesifik.',
                'harga' => 75000.00,
                'stok' => 100,
                'tipe' => 'Fisik',
            ],
        ]);
    }
}
