<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatLayout extends Model
{
    protected $fillable = [
        'product_id',
        'rows',
        'columns',
        'stage_position',
        'stage_shape',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function seats()
    {
        return $this->hasMany(Seat::class, 'seat_layout_id');
    }
}
