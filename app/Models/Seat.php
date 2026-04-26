<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'seat_layout_id',
        'row_index',
        'col_index',
        'seat_number',
        'is_active',
    ];

    public function seatLayout()
    {
        return $this->belongsTo(SeatLayout::class, 'seat_layout_id');
    }

    public function orderItems()
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_seats', 'seat_id', 'order_item_id');
    }

    /**
     * Check if seat is currently booked or sold.
     * A seat is booked if it's attached to an order item belonging to an order
     * that is NOT failed or cancelled.
     */
    public function isBooked()
    {
        return $this->orderItems()->whereHas('order', function ($query) {
            $query->whereIn('status', ['Pending', 'Paid', 'Completed']);
        })->exists();
    }
}
