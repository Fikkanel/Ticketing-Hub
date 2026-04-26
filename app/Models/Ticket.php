<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    
    protected $table = 'tickets';

    protected $fillable = [
        'ticket_code',
        'order_item_id', 
        'sequence',
        'is_scanned',
        'scanned_at',
        'scanned_by',
    ];

    protected $casts = [
        'is_scanned' => 'boolean',
        'scanned_at' => 'datetime',
        'sequence' => 'integer',
    ];

    /**
     * Relationship: Ticket belongs to an OrderItem
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'item_id');
    }

    /**
     * Relationship: Ticket was scanned by a User
     */
    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    /**
     * Get the order through the order item
     */
    public function order()
    {
        return $this->hasOneThrough(
            Order::class,
            OrderItem::class,
            'item_id',      // Foreign key on order_items
            'order_id',     // Foreign key on orders
            'order_item_id', // Local key on tickets
            'order_id'      // Local key on order_items
        );
    }

    /**
     * Get the product through the order item
     */
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            OrderItem::class,
            'item_id',      // Foreign key on order_items
            'product_id',   // Foreign key on products
            'order_item_id', // Local key on tickets
            'product_id'    // Local key on order_items
        );
    }

    /**
     * Generate ticket code from order item ID and sequence
     */
    public static function generateCode($orderItemId, $sequence)
    {
        return "TIKET-{$orderItemId}-{$sequence}";
    }

    /**
     * Parse ticket code to extract order item ID and sequence
     * Returns array ['item_id' => int, 'sequence' => int] or null if invalid
     */
    public static function parseCode($code)
    {
        // Support both formats: "TIKET-13-1" and legacy "TIKET-13"
        if (preg_match('/^TIKET-(\d+)-(\d+)$/', $code, $matches)) {
            return [
                'item_id' => (int) $matches[1],
                'sequence' => (int) $matches[2],
            ];
        }
        
        // Legacy format (backward compatibility): "TIKET-13"
        if (preg_match('/^TIKET-(\d+)$/', $code, $matches)) {
            return [
                'item_id' => (int) $matches[1],
                'sequence' => null, // Legacy format
            ];
        }
        
        return null;
    }

    /**
     * Create tickets for an order item based on quantity
     */
    public static function createForOrderItem(OrderItem $orderItem)
    {
        $tickets = [];
        
        for ($i = 1; $i <= $orderItem->kuantitas; $i++) {
            $tickets[] = self::create([
                'ticket_code' => self::generateCode($orderItem->item_id, $i),
                'order_item_id' => $orderItem->item_id,
                'sequence' => $i,
            ]);
        }
        
        return $tickets;
    }
}
