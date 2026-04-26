<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',        // Desktop
        'mobile_image_path', // Mobile
        'url',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Accessor untuk full URL gambar Desktop
    public function getBannerSrcAttribute()
    {
        // 1. Cek apakah path valid tidak null/kosong
        if (empty($this->image_path)) {
            return 'https://via.placeholder.com/1300x500?text=No+Desktop+Image';
        }

        // 2. Cek apakah file fisik ada (membutuhkan symlink storage:link yang benar)
        // Jika symlink rusak, file_exists akan return false -> masuk ke fallback
        if (file_exists(public_path('storage/' . $this->image_path))) {
            return asset('storage/' . $this->image_path);
        }

        // 3. Fallback jika file fisik tidak ketemu (misal storage:link belum jalan)
        // Opsional: Return placeholder atau tetap return URL dengan harapan browser bisa load (misal beda server)
        // Sesuai request user, kita return placeholder agar tidak broken image icon
        return 'https://via.placeholder.com/1300x500?text=Image+Not+Found+(Check+Storage+Link)';
    }

    // Accessor untuk full URL gambar Mobile
    public function getMobileBannerSrcAttribute()
    {
        if ($this->mobile_image_path && file_exists(public_path('storage/' . $this->mobile_image_path))) {
            return asset('storage/' . $this->mobile_image_path);
        }
        // Fallback: Jika tidak ada banner mobile, gunakan banner desktop
        return $this->getBannerSrcAttribute();
    }
}
