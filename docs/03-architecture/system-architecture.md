# 🏗️ System Architecture

## TixKita - Architecture Overview

---

## 1. High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              PRESENTATION LAYER                              │
├──────────────────┬──────────────────┬──────────────────┬───────────────────┤
│   Public Web     │   Admin Panel    │   Scanner PWA    │  Customer Portal  │
│   (Blade Views)  │  (Blade Views)   │    (Blade/JS)    │   (Blade Views)   │
└────────┬─────────┴────────┬─────────┴────────┬─────────┴─────────┬─────────┘
         │                  │                  │                   │
         └──────────────────┴──────────────────┴───────────────────┘
                                    │
┌───────────────────────────────────┴───────────────────────────────────────┐
│                            APPLICATION LAYER                               │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │                         Laravel Controllers                          │  │
│  ├──────────────┬──────────────┬───────────────┬──────────────────────┤  │
│  │ PublicCtrl   │ AdminCtrl    │ CustomerAuth  │ PaymentCallback      │  │
│  │ ScannerCtrl  │ ProductCtrl  │ BundleCtrl    │ DiscountCtrl         │  │
│  └──────────────┴──────────────┴───────────────┴──────────────────────┘  │
│                                                                            │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │                          Service Layer                               │  │
│  ├─────────────────┬─────────────────┬─────────────────────────────────┤  │
│  │ TicketService   │ QRCodeService   │ MidtransService                 │  │
│  │ MailService     │ PDFService      │ ExcelExportService              │  │
│  └─────────────────┴─────────────────┴─────────────────────────────────┘  │
└───────────────────────────────────────────────────────────────────────────┘
                                    │
┌───────────────────────────────────┴───────────────────────────────────────┐
│                              DATA LAYER                                    │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │                       Eloquent ORM Models                            │  │
│  ├─────────┬─────────┬─────────┬──────────┬─────────┬─────────────────┤  │
│  │  Event  │ Product │  Order  │  Ticket  │ Customer│  Bundle         │  │
│  │  User   │ Category│ Location│ Discount │ Setting │  OrderItem      │  │
│  └─────────┴─────────┴─────────┴──────────┴─────────┴─────────────────┘  │
│                                    │                                       │
│                          ┌─────────┴─────────┐                            │
│                          │    MySQL 8.0+     │                            │
│                          └───────────────────┘                            │
└───────────────────────────────────────────────────────────────────────────┘
                                    │
┌───────────────────────────────────┴───────────────────────────────────────┐
│                          EXTERNAL SERVICES                                 │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────────┐   │
│  │    Midtrans     │  │   SMTP Server   │  │    Local Storage        │   │
│  │  Payment API    │  │  (Email Send)   │  │   (Files/Images)        │   │
│  └─────────────────┘  └─────────────────┘  └─────────────────────────┘   │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Directory Structure

```
tixkita.id/
├── app/
│   ├── Console/           # Artisan commands
│   ├── Exceptions/        # Exception handlers
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/     # Admin-specific controllers
│   │   │   ├── AdminController.php
│   │   │   ├── CustomerAuthController.php
│   │   │   ├── PaymentCallbackController.php
│   │   │   ├── PublicController.php
│   │   │   └── ScannerController.php
│   │   └── Middleware/
│   ├── Mail/              # Mailable classes
│   ├── Models/            # Eloquent models
│   ├── Providers/         # Service providers
│   └── Services/          # Business logic services
├── config/                # Configuration files
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── public/                # Public assets (entry point)
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── views/
│       ├── admin/         # Admin panel views
│       ├── auth/          # Auth views
│       ├── customer/      # Customer portal views
│       ├── emails/        # Email templates
│       ├── pdf/           # PDF templates
│       ├── public/        # Public site views
│       └── scanner/       # Scanner PWA views
├── routes/
│   ├── api.php            # API routes
│   └── web.php            # Web routes
├── storage/               # File storage
└── docs/                  # Documentation (this folder)
```

---

## 3. Request Flow

### 3.1 Ticket Purchase Flow

```
┌──────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ Customer │───>│   Homepage   │───>│ Event Detail │───>│   Add Cart   │
└──────────┘    └──────────────┘    └──────────────┘    └──────┬───────┘
                                                               │
                                                               ▼
┌──────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│  Ticket  │<───│Email + PDF   │<───│   Payment    │<───│   Checkout   │
│ Received │    │   Sent       │    │  Confirmed   │    │    Form      │
└──────────┘    └──────────────┘    └──────────────┘    └──────────────┘
```

### 3.2 Payment Notification Flow

```
┌──────────────┐     ┌─────────────────┐     ┌───────────────┐
│   Midtrans   │────>│ /api/midtrans/  │────>│ PaymentCallback│
│   Server     │     │    callback     │     │  Controller    │
└──────────────┘     └─────────────────┘     └───────┬───────┘
                                                     │
                    ┌────────────────────────────────┴────┐
                    ▼                                     ▼
          ┌─────────────────┐                  ┌──────────────────┐
          │ Update Order    │                  │ Generate Tickets │
          │ Status to PAID  │                  │ & Send Email     │
          └─────────────────┘                  └──────────────────┘
```

---

## 4. Technology Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML, CSS, JavaScript, Bootstrap 5 |
| Backend | PHP 8.1+, Laravel 10.x |
| Database | MySQL 8.0+ |
| Payment | Midtrans (Snap) |
| PDF | DomPDF |
| QR Code | bacon/bacon-qr-code |
| Email | Laravel Mail (SMTP) |
| File Storage | Laravel Storage (Local/S3) |

---

## 5. Security Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Security Layers                          │
├─────────────────────────────────────────────────────────────┤
│  HTTPS/SSL        │ All traffic encrypted                   │
├───────────────────┼─────────────────────────────────────────┤
│  CSRF Protection  │ Token on all forms                      │
├───────────────────┼─────────────────────────────────────────┤
│  Authentication   │ Laravel Guards (web, customer)          │
├───────────────────┼─────────────────────────────────────────┤
│  Authorization    │ Role-based (superadmin, admin)          │
├───────────────────┼─────────────────────────────────────────┤
│  Input Validation │ Form Request validation                 │
├───────────────────┼─────────────────────────────────────────┤
│  Password Storage │ Bcrypt hashing                          │
├───────────────────┼─────────────────────────────────────────┤
│  Payment Security │ Midtrans signature verification         │
└───────────────────┴─────────────────────────────────────────┘
```

---

## 6. Scalability Considerations

### Current State
- Single server deployment
- Local file storage
- Session-based authentication

### Future Improvements
- [ ] Load balancer for horizontal scaling
- [ ] Redis for session/cache
- [ ] S3/Cloud storage for files
- [ ] Database read replicas
- [ ] CDN for static assets

---

*Last Updated: January 2026*
