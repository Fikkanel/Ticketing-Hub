# 🎯 Project Scope Document

## TixKita - Event Ticketing Platform

---

## 1. Project Overview

### 1.1 Vision
Menjadi platform ticketing event terdepan di Indonesia dengan pengalaman pembelian tiket yang mudah, aman, dan terpercaya.

### 1.2 Mission
Menyediakan solusi ticketing digital yang menghubungkan penyelenggara event dengan audience secara efisien.

---

## 2. In Scope ✅

### 2.1 Core Features

#### Event Management
- Create, Read, Update, Delete events
- Event kategorisasi (Music, Sport, Seminar, dll)
- Multi-product per event
- Bundle tickets
- Event status management

#### Ticketing
- Online ticket purchase
- Digital ticket dengan QR code
- PDF ticket generation
- Email ticket delivery
- QR code ticket validation

#### Payment
- Midtrans payment gateway
- Virtual Account (BCA, BNI, BRI, Mandiri, Permata, CIMB)
- QRIS payment
- Payment status tracking
- Invoice generation

#### User Management
- Customer registration/login
- Admin role management
- Organizer profiles
- OTP verification

#### Admin Features
- Dashboard analytics
- Order management
- Participant export (Excel)
- Discount/promo code management
- Scanner interface (PWA)

### 2.2 Platform Support
- Desktop web browsers
- Mobile web browsers (responsive)
- Scanner PWA (installable)

---

## 3. Out of Scope ❌

### 3.1 Not Included in Current Version

| Feature | Reason |
|---------|--------|
| Native Mobile App | Future phase |
| Seat Selection | Complexity, future phase |
| Live Streaming | Different product |
| Merchandise Store | Out of core scope |
| Crypto Payment | Regulatory concerns |
| Multi-currency | Indonesia focus |
| Auction/Bidding | Different business model |

### 3.2 Third-party Dependencies
- Payment processing by Midtrans (not custom)
- Email sending via SMTP provider
- Hosting infrastructure

---

## 4. Boundaries

### 4.1 Technical Boundaries

```
┌─────────────────────────────────────────┐
│           TixKita Scope                  │
│  ┌─────────────────────────────────┐    │
│  │  • Event Management             │    │
│  │  • Ticket Sales                 │    │
│  │  • Order Processing             │    │
│  │  • User Management              │    │
│  │  • Admin Dashboard              │    │
│  │  • QR Validation                │    │
│  └─────────────────────────────────┘    │
│                                          │
│  External Services:                      │
│  [Midtrans] [SMTP] [Storage]            │
└─────────────────────────────────────────┘
```

### 4.2 Business Boundaries
- Indonesia market only
- Event ticketing only (not venue booking)
- B2C model (organizer to customer)

---

## 5. Assumptions

1. **Internet connectivity** required for all operations
2. **Email access** required for customer registration
3. **Bank account** required for payment (via Midtrans)
4. **Modern browser** support only (Chrome, Firefox, Safari, Edge)
5. **Organizer** responsible for event fulfillment

---

## 6. Constraints

### 6.1 Technical Constraints
- PHP 8.1+ required
- MySQL 8.0+ required
- Server with composer support

### 6.2 Business Constraints
- Midtrans merchant account required
- Domain and SSL certificate required
- SMTP service for email

### 6.3 Regulatory Constraints
- Compliance with Indonesian e-commerce regulations
- Data privacy (customer data protection)

---

## 7. Dependencies

| Dependency | Type | Impact |
|------------|------|--------|
| Midtrans | External | Payment processing |
| SMTP Provider | External | Email delivery |
| Web Hosting | Infrastructure | Application availability |
| SSL Certificate | Security | Secure transactions |
| QR Code Library | Internal | Ticket generation |
| DomPDF | Internal | PDF generation |

---

## 8. Success Criteria

### 8.1 Technical Success
- [ ] All core features functional
- [ ] Page load < 3 seconds
- [ ] 99% uptime
- [ ] Zero payment errors

### 8.2 Business Success
- [ ] 100+ events created
- [ ] 1000+ tickets sold
- [ ] 95% customer satisfaction
- [ ] Zero security incidents

---

## 9. Change Management

Changes to scope require:
1. Written request with justification
2. Impact analysis
3. Stakeholder approval
4. Updated documentation

---

*Document Version: 1.0*  
*Last Updated: January 2026*
