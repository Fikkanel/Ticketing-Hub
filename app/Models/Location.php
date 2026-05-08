<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    
    protected $table = 'locations';
    protected $primaryKey = 'location_id';

    protected $fillable = [
        'nama_lokasi', 'alamat', 'kota', 'map_link', 'latitude', 'longitude'
    ];

    public function events()
    {
        // Satu lokasi memiliki banyak event
        return $this->hasMany(Event::class, 'location_id', 'location_id');
    }
}
