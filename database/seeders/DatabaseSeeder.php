<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    // database/seeders/DatabaseSeeder.php

    public function run(): void
    {
        $this->call([
            // Panggil LocationSeeder pertama karena Events bergantung padanya
            LocationSeeder::class,
            EventSeeder::class,
            SuperAdminSeeder::class,
            SettingsSeeder::class,
            ConcertSeeder::class,
            // Tambahkan ProductSeeder, UserSeeder, dll. nanti
        ]);
    }
}
