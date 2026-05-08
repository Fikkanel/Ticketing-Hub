<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'site_title', 'value' => 'Ticketing Hub', 'type' => 'text'],
            ['key' => 'logo_path', 'value' => null, 'type' => 'image'], // Akan di-handle upload
            ['key' => 'primary_color', 'value' => '#3b82f6', 'type' => 'color'], // Default Blue
            ['key' => 'secondary_color', 'value' => '#1e3a8a', 'type' => 'color'], // Default Dark Blue
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
