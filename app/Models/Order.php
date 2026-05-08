<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;
    
    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $fillable = [
        'order_id', 
        'customer_id', 
        'total_harga', 
        'status', 
        'metode_pembayaran',
        'diskon_code',
        'diskon_amount',
        'payment_url',
        'external_id',
        'payment_status',
        'payment_status',
        'fee_admin',
        'fee_service',
        'fee_tax',
        'guest_token',
        'buyer_nik',
        'buyer_dob',
        'buyer_gender',
        'buyer_custom_data',
    ];
    
    protected $casts = [
        'buyer_custom_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_id)) {
                $code = null;
                do {
                    $code = strtoupper(Str::random(8));
                } while (Order::where('order_id', $code)->exists());
                $order->order_id = $code;
            }
        });
    }
    
    /**
     * Relasi ke Customer.
     * PERBAIKAN: Menentukan 'customer_id' secara eksplisit sebagai foreign key dan owner key.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
}
