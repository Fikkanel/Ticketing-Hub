<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $primaryKey = 'discount_id';

    protected $fillable = [
        'event_id',
        'code',
        'percentage',
        'max_uses',
        'used_count',
        'is_active',
        'expires_at',
    ];

    /**
     * Relasi ke Event (Optional).
     * Jika null, berarti diskon berlaku global (atau logika lain).
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
