<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import Library SoftDeletes

class Product extends Model
{
    use HasFactory, SoftDeletes; // 2. Aktifkan Trait SoftDeletes
    
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    // Pastikan semua kolom yang diisi dari formulir produk ada di fillable
    protected $fillable = [
        'event_id', 'nama_produk', 'deskripsi', 'harga', 'stok', 'tipe', 'kategori_tiket', 'whatsapp_link'
    ];
    
    // Tentukan kolom tanggal jika perlu (untuk deleted_at otomatis dikenali oleh trait SoftDeletes)
    protected $dates = ['deleted_at'];

    // =========================================================================
    // RELASI
    // =========================================================================

    // Relasi: Produk dimiliki oleh satu Event (optional/nullable)
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    // Relasi: Produk muncul di banyak Order Items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    // Relasi: Produk bisa memiliki satu Layout Kursi (jika kategori_tiket = seating)
    public function seatLayout()
    {
        return $this->hasOne(SeatLayout::class, 'product_id', 'product_id');
    }
}