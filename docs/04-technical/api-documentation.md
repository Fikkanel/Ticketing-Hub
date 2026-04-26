# 📡 API Documentation

## TixKita - REST API Reference

---

## 1. Overview

### Base URL
```
Production: https://tixkita.id/api
Development: http://localhost:8000/api
```

### Authentication
Most API endpoints are public. Protected endpoints require:
- Laravel Sanctum token (Bearer token)
- Session authentication

### Response Format
```json
{
    "success": true,
    "data": { ... },
    "message": "Success message"
}
```

### Error Response
```json
{
    "success": false,
    "error": "Error message",
    "code": 400
}
```

---

## 2. Public Endpoints

### 2.1 Midtrans Callback

**POST** `/api/midtrans/callback`

Endpoint untuk menerima notifikasi pembayaran dari Midtrans.

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
    "transaction_time": "2026-01-22 12:00:00",
    "transaction_status": "settlement",
    "transaction_id": "abc123-def456",
    "status_message": "midtrans payment notification",
    "status_code": "200",
    "signature_key": "xxx",
    "payment_type": "bank_transfer",
    "order_id": "TXK-123456",
    "merchant_id": "G123456789",
    "gross_amount": "150000.00",
    "fraud_status": "accept",
    "currency": "IDR"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Notification processed"
}
```

---

### 2.2 Check Bundle Stock

**GET** `/api/check-bundle-stock/{bundleId}`

Mengecek ketersediaan stok bundle.

**Parameters:**
| Name     | Type    | Description |
| -------- | ------- | ----------- |
| bundleId | integer | ID bundle   |

**Response (Available):**
```json
{
    "available": true,
    "stock": 50
}
```

**Response (Unavailable):**
```json
{
    "available": false,
    "error": "Stok bundle habis"
}
```

**Response (Insufficient Product Stock):**
```json
{
    "available": false,
    "error": "Stok produk tidak mencukupi: VIP Ticket (tersedia: 5, butuh: 10)"
}
```

---

### 2.3 Get Cart Limits

**POST** `/api/get-cart-limits`

Mendapatkan batasan pembelian untuk item di cart.

**Request Body:**
```json
{
    "product_ids": [1, 2, 3],
    "bundle_ids": [1, 2]
}
```

**Response:**
```json
{
    "limits": {
        "1": {
            "event_id": 1,
            "max_tickets": 5,
            "stock": 100
        },
        "2": {
            "event_id": 1,
            "max_tickets": 5,
            "stock": 50
        },
        "bundle_1": {
            "event_id": 2,
            "max_tickets": 3,
            "stock": 20
        }
    },
    "events": {
        "1": {
            "max_tickets": 5,
            "limit_one_email": true,
            "products": [1, 2]
        },
        "2": {
            "max_tickets": 3,
            "limit_one_email": false,
            "products": []
        }
    }
}
```

---

### 2.4 Validate Discount Code

**POST** `/api/validate-discount`

Memvalidasi kode diskon.

**Request Body:**
```json
{
    "code": "PROMO50",
    "subtotal": 200000
}
```

**Response (Valid):**
```json
{
    "valid": true,
    "discount": {
        "code": "PROMO50",
        "type": "percentage",
        "value": 50,
        "discount_amount": 100000
    }
}
```

**Response (Invalid):**
```json
{
    "valid": false,
    "message": "Kode diskon tidak valid atau sudah kadaluarsa"
}
```

---

### 2.5 Check Tax Status

**POST** `/api/validate-tax-status`

Mengecek status pajak untuk event.

**Request Body:**
```json
{
    "event_id": 1
}
```

**Response:**
```json
{
    "has_tax": true,
    "tax_percentage": 10
}
```

---

## 3. Authenticated Endpoints

### 3.1 Get Current User

**GET** `/api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin",
    "created_at": "2026-01-01T00:00:00.000000Z"
}
```

---

## 4. Web Routes (Non-API)

Beberapa endpoint penting yang tersedia via web routes:

### Public Routes

| Method | Route           | Description         |
| ------ | --------------- | ------------------- |
| GET    | `/`             | Homepage            |
| GET    | `/events/{id}`  | Event detail        |
| GET    | `/cart`         | Shopping cart       |
| GET    | `/checkout`     | Checkout form       |
| POST   | `/checkout`     | Process checkout    |
| GET    | `/invoice/{id}` | View invoice        |
| GET    | `/history`      | Guest order history |

### Customer Routes

| Method | Route         | Description        |
| ------ | ------------- | ------------------ |
| GET    | `/login`      | Login form         |
| POST   | `/login`      | Process login      |
| GET    | `/register`   | Register form      |
| POST   | `/register`   | Request OTP        |
| POST   | `/verify-otp` | Verify OTP         |
| GET    | `/dashboard`  | Customer dashboard |
| POST   | `/logout`     | Logout             |

### Admin Routes (Authenticated)

| Method | Route                       | Description     |
| ------ | --------------------------- | --------------- |
| GET    | `/admin/dashboard`          | Admin dashboard |
| GET    | `/admin/events`             | Event list      |
| POST   | `/admin/events`             | Create event    |
| PUT    | `/admin/events/{id}`        | Update event    |
| DELETE | `/admin/events/{id}`        | Delete event    |
| GET    | `/admin/orders`             | Order list      |
| PUT    | `/admin/orders/{id}/status` | Update status   |
| GET    | `/admin/scanner`            | QR scanner      |
| POST   | `/admin/scanner/process`    | Process scan    |

---

## 5. Error Codes

| Code | Description      |
| ---- | ---------------- |
| 200  | Success          |
| 201  | Created          |
| 400  | Bad Request      |
| 401  | Unauthorized     |
| 403  | Forbidden        |
| 404  | Not Found        |
| 422  | Validation Error |
| 500  | Server Error     |

---

## 6. Rate Limiting

| Endpoint         | Limit               |
| ---------------- | ------------------- |
| Public API       | 60 requests/minute  |
| Payment Callback | No limit            |
| Authenticated    | 120 requests/minute |

---

## 7. Webhook Events

### Payment Notification Events

| Event        | Description        |
| ------------ | ------------------ |
| `settlement` | Payment successful |
| `pending`    | Payment pending    |
| `deny`       | Payment denied     |
| `cancel`     | Payment cancelled  |
| `expire`     | Payment expired    |

---

*Last Updated: January 2026*
