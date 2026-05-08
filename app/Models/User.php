<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id']; 

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', 
    ];
    
    // Relasi orders() DIHAPUS karena admin tidak melakukan order.
    // Jika admin butuh melihat semua order, lakukan via Order::all() di Controller.

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    public function isScanner()
    {
        return $this->role === 'scanner';
    }

    /**
     * Relasi ke Events (Many-to-Many).
     * Satu user admin bisa mengelola BANYAK event.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_user', 'user_id', 'event_id');
    }

    /**
     * Helper untuk cek akses ke event tertentu.
     * Superadmin punya akses ke semua.
     */
    public function hasAccessToEvent($eventId)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        // Cek apakah event_id ada di koleksi events milik user ini
        return $this->events->contains('event_id', $eventId);
    }

    // =========================================================================
    // ORGANIZER PROFILE METHODS
    // =========================================================================

    /**
     * Accessor untuk URL logo organizer.
     * Dipanggil dengan: $user->organizer_logo_src
     */
    public function getOrganizerLogoSrcAttribute()
    {
        $path = $this->organizer_logo;
        
        // Jika kosong, tampilkan placeholder
        if (!$path) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->organizer_name ?? $this->name) . '&background=6366f1&color=fff&size=128';
        }

        // Jika URL eksternal
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Jika file storage
        return asset('storage/' . $path);
    }

    /**
     * Generate slug dari organizer_name.
     * Dipanggil sebelum save jika organizer_name berubah.
     */
    public function generateOrganizerSlug()
    {
        $baseName = $this->organizer_name ?? $this->name;
        $slug = Str::slug($baseName);
        
        // Pastikan unique
        $count = 1;
        $originalSlug = $slug;
        while (static::where('organizer_slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        $this->organizer_slug = $slug;
    }

    /**
     * Accessor untuk tanggal bergabung yang terformat.
     */
    public function getMemberSinceAttribute()
    {
        return $this->created_at ? $this->created_at->format('F Y') : 'Unknown';
    }

    /**
     * Cek apakah user memiliki profil organizer yang lengkap.
     */
    public function hasOrganizerProfile()
    {
        return !empty($this->organizer_name) && !empty($this->organizer_slug);
    }
}
