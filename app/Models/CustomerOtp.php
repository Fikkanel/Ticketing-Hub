<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Generate OTP baru untuk email tertentu.
     * Menghapus OTP lama yang belum dipakai.
     * 
     * @param string $email
     * @param int $length
     * @return self
     */
    public static function generateFor(string $email, int $length = 6): self
    {
        // Hapus OTP lama yang belum dipakai untuk email ini
        self::where('email', $email)->where('is_used', false)->delete();

        // Generate OTP numerik
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }

        // Simpan OTP baru (valid 10 menit)
        return self::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
        ]);
    }

    /**
     * Verifikasi OTP untuk email tertentu.
     * 
     * @param string $email
     * @param string $otp
     * @return bool
     */
    public static function verify(string $email, string $otp): bool
    {
        $record = self::where('email', $email)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return false;
        }

        // Tandai sebagai sudah digunakan
        $record->update(['is_used' => true]);

        return true;
    }

    /**
     * Check if OTP is still valid (for display purposes).
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }
}
