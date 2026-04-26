# Rencana Implementasi: Venue Map Builder Terpadu (Opsi B)

Anda telah memilih **Opsi B**, yang berarti kita akan membangun sebuah sistem *Canvas Map Builder* profesional layaknya platform tiket besar (misal: Ticketmaster/Loket). Sesuai permintaan Anda, kita juga akan mempertahankan opsi **"Default Grid"** yang sudah kita buat sebelumnya agar admin bisa memilih mana yang paling cocok untuk event mereka.

## ⚠️ User Review Required

> [!WARNING]  
> Fitur ini adalah sebuah **Super Feature** yang akan mengubah struktur database dan cara tiket ditampilkan kepada pelanggan. Mohon baca rencana di bawah dengan teliti sebelum menyetujuinya.

## 1. Arsitektur "Pilihan Konfigurasi"

Karena sebuah Peta Venue Terpadu (Canvas) akan menggabungkan *beberapa* tiket sekaligus (Standing & Seating di satu peta), maka pengaturannya tidak lagi berada di dalam menu "Edit Produk/Tiket", melainkan akan dipindah ke menu **Event**.

Nantinya, pada halaman detail/Edit Event, akan ada opsi: **Tipe Layout Tiket Event**:
1. **Per-Produk / Default Grid (Sistem Lama):** Peta tidak disatukan. Setiap tiket *Seating* akan memiliki tombol "Atur Layout Kursi" masing-masing (grid 10x10, dll). Tiket *Standing* langsung dibeli tanpa peta.
2. **Venue Map Builder / Manual Draw (Sistem Baru):** Ada satu kanvas besar untuk Event ini. Semua produk tiket untuk event ini akan digambar di atas kanvas tersebut. Tombol "Atur Layout Kursi" di masing-masing produk akan dihilangkan, diganti dengan satu tombol **"Buka Map Builder"** di level Event.

## 2. Struktur Database Baru

Untuk mendukung sistem Canvas/SVG yang Anda minta, kita perlu menambahkan tabel baru:
- `[NEW] venue_maps`
  - `id`, `event_id`
  - `canvas_background_data` (JSON/Koordinat untuk kotak besar "Area Penonton Keseluruhan")
- `[NEW] venue_zones`
  - `id`, `venue_map_id`, `product_id` (Dihubungkan ke tiket tertentu)
  - `zone_name` (misal: "Festival A", "VIP 1")
  - `type` (standing / seating)
  - `coordinates` (JSON - menyimpan x, y, width, height, warna dari kotak yang digambar)
  - `capacity` (opsional)

## 3. Fitur "Manual Draw" di Backend (Admin)

Kita akan membuat halaman khusus **Venue Map Builder** terintegrasi dengan HTML5 Canvas (menggunakan pustaka ringan).
Alur kerjanya sesuai permintaan Anda:
1. Admin membuka halaman Builder.
2. Admin menggambar **1 Kotak Besar** (Area Penonton).
3. Admin memilih menu "Tambah Area Tiket".
4. Admin menggambar **Kotak Kecil** di atas kotak besar tersebut.
5. Admin memilih kotak kecil tersebut dan mengaitkannya dengan produk tiket yang sudah dibuat (Misal: Ditautkan ke produk "Tiket Festival" - Standing).
6. Admin menggambar kotak lain dan menautkannya ke produk "Tribune VIP" - Seating.

## 4. Tampilan Publik (Pembeli)

Jika event menggunakan tipe **Manual Draw (Venue Map)**:
1. Saat pembeli membuka halaman Event, mereka akan melihat gambar Peta (gabungan kotak-kotak) yang telah admin gambar.
2. Saat pembeli mengarahkan mouse (hover) ke sebuah kotak, kotak tersebut akan menyala dan menampilkan harga.
3. Saat diklik:
   - Jika kotak itu adalah tiket **Standing**: Pembeli langsung memilih jumlah tiket dan klik "Tambah ke Keranjang".
   - Jika kotak itu adalah tiket **Seating**: *Sistem akan membuka popup Grid Kursi (Sistem Default yang sudah kita buat sebelumnya) KHUSUS untuk kotak/zona tersebut, sehingga mereka bisa memilih nomor kursi spesifik (misal A1, A2).*

## ❓ Open Questions

Sebelum saya mulai menulis struktur databasenya (migration), mohon konfirmasi satu hal terakhir terkait poin ke-4 di atas:
**Untuk tiket "Seating" di dalam Manual Draw, apakah saat pembeli mengklik kotak Seating tersebut, Anda ingin pembeli tetap harus memilih NOMOR KURSI spesifik (menggunakan Grid), atau tiket Seating di Manual Draw ini bersifat "Bebas Duduk di Area Tersebut" (tidak perlu pilih nomor kursi)?**

---
Silakan balas konfirmasi Anda (terutama pertanyaan di bagian Open Questions) agar saya dapat langsung mengeksekusi kode database dan fitur Canvas Builder-nya!
