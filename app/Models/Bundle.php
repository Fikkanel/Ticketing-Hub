<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'stok',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Relasi ke Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    /**
     * Relasi ke BundleItem
     */
    public function items()
    {
        return $this->hasMany(BundleItem::class);
    }

    /**
     * Get all products in this bundle
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_items', 'bundle_id', 'product_id')
                    ->withPivot('quantity');
    }

    /**
     * Calculate total value of bundle (sum of product prices * qty)
     */
    public function getTotalValueAttribute()
    {
        return $this->items->sum(function ($item) {
            return ($item->product->harga ?? 0) * $item->quantity;
        });
    }

    /**
     * Calculate discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        $totalValue = $this->total_value;
        if ($totalValue <= 0) return 0;
        
        return round((1 - ($this->price / $totalValue)) * 100);
    }
}
