<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Extend Authenticatable agar bisa login
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    // Tentukan guard default untuk model ini (opsional, tapi good practice)
    protected $guard = 'customer'; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',    // Sesuai tabel customers
        'address',  // Sesuai tabel customers
        'email_verified_at',
        'unix_id',
        'nik',
        'dob',
        'gender',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Boot method untuk auto-generate unix_id saat customer dibuat
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->unix_id)) {
                $customer->unix_id = self::generateUnixId();
            }
        });
    }

    /**
     * Generate unique unix_id format: TIX + 12 digit random
     * Example: TIX835770912526
     */
    public static function generateUnixId(): string
    {
        do {
            // Format: TIX + 12 digit random (kombinasi timestamp + random)
            $timestamp = substr((string) round(microtime(true) * 1000), -9);
            $random = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $unixId = 'TIX' . $timestamp . $random;
        } while (self::where('unix_id', $unixId)->exists());

        return $unixId;
    }

    /**
     * Relasi: Satu Customer bisa memiliki banyak Order
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
}
