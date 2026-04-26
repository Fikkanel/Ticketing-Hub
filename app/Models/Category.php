<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'requires_nik',
    ];

    /**
     * Relasi ke Model Event (Many-to-Many).
     * Satu kategori bisa memiliki banyak events.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'category_event', 'category_id', 'event_id')
                    ->withTimestamps();
    }

    /**
     * Daftar icon yang tersedia untuk dropdown
     */
    public static function availableIcons(): array
    {
        return [
            'fa-music' => 'Musik',
            'fa-futbol' => 'Olahraga',
            'fa-chalkboard-teacher' => 'Seminar',
            'fa-hands-helping' => 'Volunteer',
            'fa-gift' => 'Gratis',
            'fa-theater-masks' => 'Seni & Budaya',
            'fa-gamepad' => 'Gaming',
            'fa-utensils' => 'Kuliner',
            'fa-graduation-cap' => 'Pendidikan',
            'fa-briefcase' => 'Bisnis',
            'fa-calendar-alt' => 'Event Umum',
        ];
    }
}
