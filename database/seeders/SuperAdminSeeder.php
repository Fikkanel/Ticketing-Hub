<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'admin@tixkita.id',
            'password' => \Illuminate\Support\Facades\Hash::make('@TixKita2025'), // Ganti dengan password yang aman
            'role' => 'superadmin',
        ]);
    }
}
