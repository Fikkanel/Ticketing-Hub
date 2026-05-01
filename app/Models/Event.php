<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Pastikan import ini ada

class Event extends Model
{
    use HasFactory;

    // Menentukan primary key custom (karena bukan 'id' default)
    protected $primaryKey = 'event_id';

    /**
     * Atribut yang bisa diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'location_id', 
        'category_id',  // Kategori event (MUSIC, SPORT, dll)
        'judul', 
        'deskripsi', 
        'terms_conditions',
        'tgl_mulai', 
        'tgl_selesai', 
        'status', 
        'banner_image', // Kolom baru untuk Banner (Detail/Slider)
        'card_image',   // Kolom baru untuk Card (List Event)
        'ticket_type',
        'custom_email_content',
        'seminar_whatsapp_link', // Link WA untuk seminar (jika diisi, tidak kirim tiket)
        'payment_channels', // all, qris_only
        'payment_mode',
        // Unique Data Settings
        'max_tickets_per_transaction',
        'limit_one_email_per_transaction',
        'require_unique_data_per_ticket',
        'buyer_form_fields',
        'custom_form_fields',
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     */
    protected $casts = [
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
        'limit_one_email_per_transaction' => 'boolean',
        'require_unique_data_per_ticket' => 'boolean',
        'buyer_form_fields' => 'array',
        'custom_form_fields' => 'array',
    ];

    // =========================================================================
    // RELASI ANTAR TABEL
    // =========================================================================

    /**
     * Relasi ke Model Location (Many-to-One).
     * Satu event memiliki satu lokasi.
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Relasi ke Model Category (Many-to-Many).
     * Satu event bisa memiliki banyak kategori.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_event', 'event_id', 'category_id')
                    ->withTimestamps();
    }

    /**
     * Accessor untuk cek apakah event masuk kategori FREE (harga = 0).
     * FREE bukan kategori tersimpan, tapi otomatis jika semua produk gratis.
     */
    public function getIsFreeAttribute(): bool
    {
        if ($this->products->isEmpty()) {
            return false;
        }
        return $this->products->max('harga') == 0;
    }
    /**
     * Relasi ke Model Product (One-to-Many).
     * Satu event bisa memiliki banyak produk/tiket.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'event_id', 'event_id');
    }

    // =========================================================================
    // ACCESSORS (CUSTOM ATTRIBUTES)
    // =========================================================================

    /**
     * Accessor Magic untuk Banner.
     * Dipanggil di Blade dengan: {{ $event->banner_src }}
     * Otomatis cek apakah ini URL luar atau File Storage.
     */
    public function getBannerSrcAttribute()
    {
        $path = $this->banner_image;
        
        // 1. Jika data kosong, tampilkan placeholder abu-abu ukuran Banner
        if (!$path) {
            return 'https://via.placeholder.com/1200x600?text=No+Banner+Image';
        }

        // 2. Jika path dimulai dengan 'http' atau 'https', anggap sebagai URL Eksternal
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // 3. Jika bukan URL, anggap sebagai file yang diupload ke Storage Public
        // Pastikan Anda sudah menjalankan: php artisan storage:link
        return asset('storage/' . $path);
    }

    /**
     * Accessor Magic untuk Card Image.
     * Dipanggil di Blade dengan: {{ $event->card_src }}
     */
    public function getCardSrcAttribute()
    {
        $path = $this->card_image;
        
        // 1. Jika kosong, tampilkan placeholder ukuran Card
        if (!$path) {
            return 'https://via.placeholder.com/600x400?text=No+Card+Image';
        }

        // 2. Jika URL Eksternal
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // 3. Jika File Storage
        return asset('storage/' . $path);
    }

    /**
     * Accessor untuk Mendapatkan Harga Terendah (Starts From).
     * Pastikan products diload dengan eager loading untuk performa.
     */
    public function getMinPriceAttribute()
    {
        // Mengembalikan harga terendah dari koleksi produk yang diload
        return $this->products->min('harga') ?? 0;
    }

    /**
     * Accessor untuk mengecek apakah event ini GRATIS.
     * Event gratis = semua produk memiliki harga 0 atau max price = 0
     * 
     * @return bool
     */
    public function getIsFreeEventAttribute(): bool
    {
        // Jika tidak ada produk, anggap bukan gratis
        if ($this->products->isEmpty()) {
            return false;
        }
        
        // Cek apakah semua produk harganya 0
        return $this->products->max('harga') == 0;
    }

    /**
     * Relasi ke User (Organizers) via pivot table event_user.
     * Satu event bisa dikelola oleh banyak admin/organizer.
     */
    public function organizers()
    {
        return $this->belongsToMany(User::class, 'event_user', 'event_id', 'user_id');
    }

    /**
     * Helper untuk mendapatkan organizer utama (pertama).
     * Berguna untuk menampilkan di halaman event detail.
     */
    public function getPrimaryOrganizerAttribute()
    {
        return $this->organizers->first();
    }

    /**
     * Relasi ke Bundle (One-to-Many).
     * Satu event bisa memiliki banyak bundle tiket.
     */
    public function bundles()
    {
        return $this->hasMany(Bundle::class, 'event_id', 'event_id');
    }

    /**
     * Relasi ke Lineup (One-to-Many).
     * Satu event bisa memiliki banyak lineup (artis pengisi acara).
     */
    public function lineups()
    {
        return $this->hasMany(EventLineup::class, 'event_id', 'event_id');
    }

    /**
     * Relasi ke Fasilitas (One-to-Many).
     * Satu event bisa memiliki banyak fasilitas.
     */
    public function facilities()
    {
        return $this->hasMany(EventFacility::class, 'event_id', 'event_id');
    }
}