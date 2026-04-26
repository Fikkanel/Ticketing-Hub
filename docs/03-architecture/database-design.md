# 🗄️ Database Design

## TixKita - Database Schema

---

## 1. Entity Relationship Diagram

```
                                    ┌──────────────┐
                                    │    users     │
                                    │ (Admin/Org)  │
                                    └──────┬───────┘
                                           │
                         ┌─────────────────┼─────────────────┐
                         │                 │                 │
                         ▼                 ▼                 ▼
                  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐
                  │event_user   │  │   events    │──│   categories    │
                  │(pivot)      │  │             │  │                 │
                  └─────────────┘  └──────┬──────┘  └─────────────────┘
                                          │
              ┌───────────────────────────┼───────────────────────────┐
              │                           │                           │
              ▼                           ▼                           ▼
       ┌─────────────┐             ┌─────────────┐             ┌─────────────┐
       │  products   │             │  bundles    │             │  locations  │
       └──────┬──────┘             └──────┬──────┘             └─────────────┘
              │                           │
              │              ┌────────────┼────────────┐
              │              ▼                         ▼
              │       ┌─────────────┐          ┌─────────────┐
              │       │bundle_items │          │   orders    │
              │       └─────────────┘          └──────┬──────┘
              │                                       │
              └───────────────────┬───────────────────┤
                                  │                   │
                                  ▼                   ▼
                           ┌─────────────┐     ┌─────────────┐
                           │ order_items │     │   tickets   │
                           └─────────────┘     └─────────────┘
                                                      │
                                                      ▼
                                               ┌─────────────┐
                                               │  customers  │
                                               └─────────────┘
```

---

## 2. Table Definitions

### 2.1 Core Tables

#### users (Admin/Organizer)
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Organizer Profile Fields
    organizer_name VARCHAR(255) NULL,
    organizer_slug VARCHAR(255) UNIQUE NULL,
    organizer_bio TEXT NULL,
    organizer_logo VARCHAR(255) NULL,
    organizer_banner VARCHAR(255) NULL,
    organizer_website VARCHAR(255) NULL,
    organizer_instagram VARCHAR(255) NULL,
    organizer_twitter VARCHAR(255) NULL,
    
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### customers
```sql
CREATE TABLE customers (
    customer_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    unix_id VARCHAR(50) UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### events
```sql
CREATE TABLE events (
    event_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    location_id BIGINT UNSIGNED,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    tgl_mulai DATETIME NOT NULL,
    tgl_selesai DATETIME NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    banner_image VARCHAR(255),
    card_image VARCHAR(255),
    ticket_type VARCHAR(50),
    custom_email_content TEXT,
    seminar_whatsapp_link VARCHAR(255),
    payment_channels VARCHAR(50) DEFAULT 'all',
    payment_mode VARCHAR(50),
    max_tickets_per_transaction INT DEFAULT 10,
    limit_one_email_per_transaction BOOLEAN DEFAULT FALSE,
    require_unique_data_per_ticket BOOLEAN DEFAULT FALSE,
    buyer_form_fields JSON,
    custom_form_fields JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (location_id) REFERENCES locations(id)
);
```

#### products
```sql
CREATE TABLE products (
    product_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT UNSIGNED NOT NULL,
    nama_produk VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(12,2) DEFAULT 0,
    stok INT DEFAULT 0,
    tipe_produk ENUM('fisik', 'digital_voucher', 'digital_seminar') DEFAULT 'fisik',
    whatsapp_link VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES events(event_id)
);
```

#### orders
```sql
CREATE TABLE orders (
    order_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED,
    unix_id VARCHAR(50) UNIQUE,
    nama_pembeli VARCHAR(255) NOT NULL,
    email_pembeli VARCHAR(255) NOT NULL,
    phone_pembeli VARCHAR(20),
    subtotal DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    status ENUM('pending', 'paid', 'cancelled', 'expired') DEFAULT 'pending',
    payment_method VARCHAR(50),
    midtrans_order_id VARCHAR(100),
    midtrans_snap_token VARCHAR(255),
    midtrans_transaction_id VARCHAR(100),
    midtrans_va_number VARCHAR(50),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);
```

#### tickets
```sql
CREATE TABLE tickets (
    ticket_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED,
    bundle_id BIGINT UNSIGNED,
    kode_tiket VARCHAR(50) UNIQUE NOT NULL,
    qr_code_path VARCHAR(255),
    nama_peserta VARCHAR(255),
    email_peserta VARCHAR(255),
    phone_peserta VARCHAR(20),
    custom_data JSON,
    status ENUM('valid', 'used', 'cancelled') DEFAULT 'valid',
    scanned_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (bundle_id) REFERENCES bundles(id)
);
```

### 2.2 Supporting Tables

#### categories
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    icon VARCHAR(50),
    color VARCHAR(20),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### locations
```sql
CREATE TABLE locations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    alamat TEXT,
    kota VARCHAR(100),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### bundles
```sql
CREATE TABLE bundles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(12,2),
    stok INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES events(event_id)
);
```

#### bundle_items
```sql
CREATE TABLE bundle_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    bundle_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (bundle_id) REFERENCES bundles(id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);
```

#### discounts
```sql
CREATE TABLE discounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL,
    max_uses INT,
    used_count INT DEFAULT 0,
    valid_from DATE,
    valid_until DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2.3 Pivot Tables

#### category_event
```sql
CREATE TABLE category_event (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    event_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (event_id) REFERENCES events(event_id)
);
```

#### event_user
```sql
CREATE TABLE event_user (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 3. Indexes

```sql
-- Performance indexes
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_events_dates ON events(tgl_mulai, tgl_selesai);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_email ON orders(email_pembeli);
CREATE INDEX idx_tickets_kode ON tickets(kode_tiket);
CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_discounts_code ON discounts(code);
```

---

## 4. Data Flow

### Order Creation
1. Customer adds products to cart (session)
2. Checkout creates `orders` record (status: pending)
3. Creates `order_items` for each cart item
4. Midtrans payment initiated
5. On payment success → status: paid
6. Generate `tickets` for each order item
7. Send email with PDF ticket

### Ticket Validation
1. Scanner reads QR code → `kode_tiket`
2. Query `tickets` table
3. Check status = 'valid'
4. Update status = 'used', set `scanned_at`

---

*Last Updated: January 2026*
