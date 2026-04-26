# 👨‍💼 Admin Guide

## Panduan Pengelolaan TixKita untuk Admin

---

## 1. Akses Admin Panel

### 1.1 Login Admin

1. Buka URL: `https://tixkita.id/admin/login`
2. Masukkan email dan password admin
3. Klik **"Login"**

### 1.2 Role Admin

| Role           | Akses                         |
| -------------- | ----------------------------- |
| **Superadmin** | Semua fitur + settings        |
| **Admin**      | Manage events yang ditugaskan |

---

## 2. Dashboard

### Overview
Dashboard menampilkan:
- 📊 Statistik penjualan
- 📈 Grafik pendapatan
- 🎫 Pesanan terbaru
- 📅 Event mendatang

---

## 3. Manajemen Event

### 3.1 Membuat Event Baru

1. Menu: **Events** → **Tambah Event**
2. Isi informasi event:

| Field           | Keterangan                    |
| --------------- | ----------------------------- |
| Judul           | Nama event                    |
| Deskripsi       | Detail event (HTML supported) |
| Tanggal Mulai   | Kapan event dimulai           |
| Tanggal Selesai | Kapan event berakhir          |
| Lokasi          | Pilih dari database lokasi    |
| Kategori        | Pilih 1 atau lebih kategori   |
| Banner Image    | Gambar banner (1200x600)      |
| Card Image      | Gambar card (600x400)         |

3. Konfigurasi Tambahan:

| Field                  | Keterangan                   |
| ---------------------- | ---------------------------- |
| Max Tiket/Transaksi    | Batas tiket per order        |
| Unique Email per Tiket | Wajib email berbeda          |
| Custom Form Fields     | Field tambahan untuk peserta |
| Payment Mode           | All / QRIS Only              |

4. Klik **"Simpan"**

### 3.2 Edit Event

1. Menu: **Events**
2. Klik tombol **Edit** pada event
3. Ubah informasi yang diperlukan
4. Klik **"Update"**

### 3.3 Delete Event

1. Menu: **Events**
2. Klik tombol **Hapus**
3. Konfirmasi penghapusan

⚠️ **Warning:** Event dengan pesanan tidak bisa dihapus

---

## 4. Manajemen Produk/Tiket

### 4.1 Tambah Produk

1. Buka halaman edit event
2. Scroll ke bagian **Produk**
3. Klik **"Tambah Produk"**
4. Isi detail:

| Field       | Keterangan                |
| ----------- | ------------------------- |
| Nama Produk | Contoh: "VIP Ticket"      |
| Harga       | Dalam Rupiah              |
| Stok        | Jumlah tersedia           |
| Deskripsi   | Keterangan produk         |
| Tipe        | Fisik / Digital / Seminar |

5. Klik **"Simpan"**

### 4.2 Tipe Produk

| Tipe                  | Deskripsi                         |
| --------------------- | --------------------------------- |
| **Fisik**             | Tiket dengan QR code              |
| **Digital (Voucher)** | Voucher digital                   |
| **Digital (Seminar)** | Tiket seminar (tanpa tiket email) |

### 4.3 Produk Seminar

Untuk tipe Seminar:
- Isi **WhatsApp Link** (grup atau admin)
- Email tidak akan kirim tiket
- Customer diarahkan ke WhatsApp

---

## 5. Manajemen Bundle

### 5.1 Membuat Bundle

1. Menu: **Events** → pilih event → **Bundles**
2. Klik **"Buat Bundle"**
3. Isi detail:

| Field       | Keterangan                     |
| ----------- | ------------------------------ |
| Nama Bundle | Contoh: "Paket Hemat"          |
| Harga       | Harga bundle (biasanya diskon) |
| Stok        | Jumlah bundle tersedia         |
| Produk      | Pilih produk yang termasuk     |

4. Klik **"Simpan"**

### 5.2 Bundle Items

- Pilih produk + jumlah untuk setiap item
- Stok produk akan berkurang sesuai quantity bundle

---

## 6. Manajemen Order

### 6.1 Melihat Pesanan

1. Menu: **Orders**
2. Filter pesanan:
   - Berdasarkan status (Pending/Paid/Cancelled)
   - Berdasarkan event
   - Berdasarkan tanggal

### 6.2 Detail Pesanan

Klik order untuk melihat:
- Informasi pembeli
- Item yang dibeli
- Status pembayaran
- Data tiket

### 6.3 Update Status

1. Buka detail order
2. Pilih status baru:
   - **Pending** - Menunggu pembayaran
   - **Paid** - Sudah dibayar
   - **Cancelled** - Dibatalkan
3. Klik **"Update"**

### 6.4 Resend Tiket

Jika customer tidak menerima tiket:
1. Buka detail order
2. Klik **"Kirim Ulang Tiket"**
3. Email akan dikirim ulang

---

## 7. Export Data Peserta

### 7.1 Export ke Excel

1. Menu: **Events**
2. Klik tombol **Export** pada event
3. File Excel akan terdownload

### 7.2 Data yang Tereksport

- Order ID
- Nama Pembeli
- Email
- Nama Peserta
- Produk yang dibeli
- Status tiket
- Waktu check-in

---

## 8. Scanner QR (PWA)

### 8.1 Akses Scanner

1. Menu: **Scanner**
2. Install sebagai PWA untuk akses cepat

### 8.2 Cara Scan

1. Izinkan akses kamera
2. Arahkan ke QR code tiket
3. Sistem akan otomatis membaca

### 8.3 Hasil Scan

| Status         | Aksi                         |
| -------------- | ---------------------------- |
| ✅ Valid        | Tiket berhasil divalidasi    |
| ❌ Already Used | Tiket sudah pernah digunakan |
| ⚠️ Not Found    | Kode tidak ditemukan         |

---

## 9. Pengaturan (Superadmin)

### 9.1 Website Settings

Menu: **Settings**

| Field         | Keterangan            |
| ------------- | --------------------- |
| Nama Website  | Ditampilkan di header |
| Logo          | Upload logo website   |
| Email Support | Email untuk customer  |
| Kontak        | Nomor telepon         |
| Social Media  | Link sosmed           |

### 9.2 Transaction Settings

Menu: **Transaction Settings**

| Field               | Keterangan              |
| ------------------- | ----------------------- |
| Midtrans Server Key | Dari dashboard Midtrans |
| Midtrans Client Key | Dari dashboard Midtrans |
| Production Mode     | Sandbox / Production    |

### 9.3 User Management

Menu: **Users**

- Tambah admin baru
- Edit role admin
- Aktifkan/nonaktifkan akun

---

## 10. Kode Diskon

### 10.1 Membuat Kode

Menu: **Discounts** → **Tambah**

| Field          | Keterangan         |
| -------------- | ------------------ |
| Kode           | Contoh: "PROMO50"  |
| Tipe           | Percentage / Fixed |
| Nilai          | 50% atau Rp 50.000 |
| Max Penggunaan | Batas pemakaian    |
| Periode        | Tanggal berlaku    |

### 10.2 Generate Bulk

1. Klik **"Generate Bulk"**
2. Tentukan prefix, jumlah, dan nilai
3. Sistem akan generate otomatis

---

## 11. Organizer Profile

### 11.1 Setup Profil

Menu: **Organizer Profile**

| Field          | Keterangan            |
| -------------- | --------------------- |
| Nama Organizer | Nama bisnis/brand     |
| Slug           | URL slug (unik)       |
| Bio            | Deskripsi singkat     |
| Logo           | Logo organizer        |
| Banner         | Banner halaman profil |
| Website        | URL website           |
| Instagram      | Username IG           |

### 11.2 Halaman Publik

Setelah diisi, profil bisa diakses di:
`https://tixkita.id/organizer/[slug]`

---

## 12. Tips & Best Practice

### Event
- ✅ Upload gambar berkualitas tinggi
- ✅ Deskripsi jelas dan informatif
- ✅ Set stok dengan buffer

### Order
- ✅ Monitor pesanan pending
- ✅ Respond email customer cepat
- ✅ Export data sebelum event

### Scanner
- ✅ Test scanner sebelum hari H
- ✅ Siapkan backup device
- ✅ Pastikan koneksi stabil

---

*Hubungi tim teknis jika ada kendala!*
