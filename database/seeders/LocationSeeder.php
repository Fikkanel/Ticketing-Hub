<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('locations')->insert([
            [
                'nama_lokasi' => 'Jakarta Convention Center',
                'alamat' => 'Jl. Jend. Gatot Subroto No.1, Jakarta',
                'kota' => 'Jakarta Pusat',
            ],
            [
                'nama_lokasi' => 'Bandung Creative Hub',
                'alamat' => 'Jl. Laswi No.7, Bandung',
                'kota' => 'Bandung',
            ],
            [
                'nama_lokasi' => 'Bali Nusa Dua Convention Center',
                'alamat' => 'Kawasan Pariwisata Nusa Dua Lot 1, Bali',
                'kota' => 'Denpasar',
            ],
        ]);
    }
}
