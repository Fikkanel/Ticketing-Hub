<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Music',
                'slug' => 'music',
                'icon' => 'fa-music',
            ],
            [
                'name' => 'Sport',
                'slug' => 'sport',
                'icon' => 'fa-futbol',
            ],
            [
                'name' => 'Seminar',
                'slug' => 'seminar',
                'icon' => 'fa-chalkboard-teacher',
            ],
            [
                'name' => 'Volunteer',
                'slug' => 'volunteer',
                'icon' => 'fa-hands-helping',
            ],
            [
                'name' => 'Free',
                'slug' => 'free',
                'icon' => 'fa-gift',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
