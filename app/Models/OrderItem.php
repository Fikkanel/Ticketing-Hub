<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $table = 'order_items';
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'order_id', 'product_id', 'kuantitas', 'subtotal', 'nik_data', 'bundle_id'
    ];

    // Relasi: Item Detail milik satu Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // Relasi: Item Detail terikat pada satu Product
    protected $casts = [
        'is_scanned' => 'boolean',
        'scanned_at' => 'datetime',
        'nik_data' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    /**
     * Relationship: OrderItem has many individual Tickets
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'order_item_id', 'item_id');
    }

    /**
     * Relationship: OrderItem has many Seats (if product is seating category)
     */
    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'order_item_seats', 'order_item_id', 'seat_id');
    }
}
