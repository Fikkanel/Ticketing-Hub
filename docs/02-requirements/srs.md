# 📋 Software Requirements Specification (SRS)

## TixKita - Event Ticketing Platform

**Version**: 1.5  
**Date**: Januari 2026  
**Status**: Active Development

---

## 1. Introduction

### 1.1 Purpose
Dokumen ini menjelaskan spesifikasi kebutuhan perangkat lunak untuk TixKita, sebuah platform ticketing event berbasis web.

### 1.2 Scope
TixKita adalah platform yang memungkinkan:
- Organizer membuat dan mengelola event
- Customer membeli tiket secara online
- Admin mengelola seluruh operasional platform

### 1.3 Definitions

| Term | Definition |
|------|------------|
| Event | Acara yang dijual tiketnya |
| Product | Jenis tiket dalam sebuah event |
| Bundle | Paket berisi beberapa produk |
| Order | Transaksi pembelian tiket |
| Ticket | Tiket digital dengan QR code |
| Customer | Pengguna yang membeli tiket |
| Admin | Pengelola sistem |
| Organizer | Penyelenggara event |

---

## 2. Overall Description

### 2.1 Product Perspective

```
┌─────────────────────────────────────────────────────────────┐
│                      TixKita Platform                        │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Customer │  │  Admin   │  │ Scanner  │  │Organizer │   │
│  │   Web    │  │  Panel   │  │   PWA    │  │  Profile │   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘   │
│       │             │             │             │          │
│       └─────────────┴─────────────┴─────────────┘          │
│                          │                                  │
│              ┌───────────┴───────────┐                     │
│              │    Laravel Backend    │                     │
│              └───────────┬───────────┘                     │
│       ┌──────────────────┼──────────────────┐              │
│       │                  │                  │              │
│  ┌────┴────┐      ┌──────┴─────┐     ┌─────┴─────┐       │
│  │  MySQL  │      │  Midtrans  │     │   Email   │       │
│  │Database │      │  Payment   │     │  Service  │       │
│  └─────────┘      └────────────┘     └───────────┘       │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 Product Functions

1. **Event Management** - CRUD event dengan berbagai konfigurasi
2. **Ticket Sales** - Penjualan tiket online dengan berbagai metode pembayaran
3. **Customer Management** - Registrasi dan autentikasi customer
4. **Order Processing** - Pemrosesan pesanan dan pembayaran
5. **Ticket Delivery** - Pengiriman tiket digital via email/PDF
6. **Ticket Validation** - Validasi tiket via QR scanner

### 2.3 User Classes

| User Class | Description | Access Level |
|------------|-------------|--------------|
| Guest | Pengunjung tanpa login | View events, add to cart |
| Customer | User terdaftar | Purchase, view history, dashboard |
| Admin | Pengelola event | Manage assigned events |
| Superadmin | Administrator sistem | Full system access |

### 2.4 Operating Environment

- **Web Server**: Apache/Nginx
- **PHP**: 8.1+
- **Database**: MySQL 8.0+
- **Framework**: Laravel 10.x
- **Browser**: Chrome, Firefox, Safari, Edge (modern versions)

---

## 3. Functional Requirements

### 3.1 Event Management

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-01 | Sistem harus memungkinkan admin membuat event baru | High |
| FR-02 | Event harus memiliki judul, deskripsi, tanggal, lokasi | High |
| FR-03 | Event harus bisa dikategorikan (Music, Seminar, dll) | Medium |
| FR-04 | Event harus memiliki banner dan card image | Medium |
| FR-05 | Admin dapat mengatur status event (active/inactive) | High |

### 3.2 Product/Ticket Management

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-10 | Setiap event dapat memiliki multiple products | High |
| FR-11 | Product memiliki nama, harga, stok | High |
| FR-12 | Support untuk product gratis (harga 0) | Medium |
| FR-13 | Product dapat dibundle | Medium |
| FR-14 | Tipe product: Fisik, Digital (Voucher), Digital (Seminar) | Medium |

### 3.3 Customer Management

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-20 | Customer dapat registrasi dengan email | High |
| FR-21 | Verifikasi OTP via email | High |
| FR-22 | Customer dapat login/logout | High |
| FR-23 | Customer dapat reset password | Medium |
| FR-24 | Customer memiliki dashboard untuk lihat order history | Medium |

### 3.4 Order & Payment

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-30 | Customer dapat menambahkan tiket ke cart | High |
| FR-31 | Sistem checkout dengan form data pembeli | High |
| FR-32 | Integrasi Midtrans untuk pembayaran | High |
| FR-33 | Support Virtual Account (BCA, BNI, BRI, Mandiri, dll) | High |
| FR-34 | Support QRIS | High |
| FR-35 | Notifikasi pembayaran otomatis | High |
| FR-36 | Generate invoice setelah order | High |

### 3.5 Ticketing

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-40 | Generate tiket dengan QR code unik | High |
| FR-41 | Kirim tiket PDF via email | High |
| FR-42 | QR scanner untuk validasi tiket | High |
| FR-43 | Tiket hanya bisa digunakan sekali | High |

### 3.6 Discount System

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-50 | Admin dapat membuat kode diskon | Medium |
| FR-51 | Diskon persentase atau fixed amount | Medium |
| FR-52 | Generate kode diskon bulk | Low |
| FR-53 | Limit penggunaan kode diskon | Medium |

---

## 4. Non-Functional Requirements

### 4.1 Performance

| ID | Requirement |
|----|-------------|
| NFR-01 | Halaman utama load < 3 detik |
| NFR-02 | Checkout process < 5 detik |
| NFR-03 | Support 100+ concurrent users |

### 4.2 Security

| ID | Requirement |
|----|-------------|
| NFR-10 | Password hashing dengan bcrypt |
| NFR-11 | CSRF protection pada semua form |
| NFR-12 | Rate limiting pada API endpoint |
| NFR-13 | Secure payment via Midtrans |

### 4.3 Reliability

| ID | Requirement |
|----|-------------|
| NFR-20 | Uptime 99.5% |
| NFR-21 | Daily database backup |
| NFR-22 | Error logging untuk debugging |

### 4.4 Usability

| ID | Requirement |
|----|-------------|
| NFR-30 | Responsive design (mobile-first) |
| NFR-31 | Dark mode support |
| NFR-32 | Multi-language ready |

---

## 5. External Interface Requirements

### 5.1 User Interfaces
- Public website (customer-facing)
- Admin panel (management)
- Scanner PWA (ticket validation)

### 5.2 Software Interfaces

| Interface | Purpose |
|-----------|---------|
| Midtrans API | Payment processing |
| SMTP (Gmail/Mailtrap) | Email delivery |
| Storage API | File upload/download |

### 5.3 Communication Interfaces
- HTTPS untuk semua komunikasi
- RESTful API design

---

## 6. Appendix

### 6.1 Revision History

| Version | Date | Description |
|---------|------|-------------|
| 1.0 | Dec 2025 | Initial release |
| 1.5 | Jan 2026 | Added seminar features, dark mode |

---

*Document maintained by TixKita Development Team*
