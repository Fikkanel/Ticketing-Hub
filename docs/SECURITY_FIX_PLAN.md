# Security Vulnerabilities Fix Plan

**Tanggal Scan:** 2026-01-31  
**Target:** tixkita.id  
**Risk Score:** 155 (CRITICAL)

---

## Ringkasan Temuan

| Severity   | Jumlah | Temuan                                      |
| ---------- | ------ | ------------------------------------------- |
| 🔴 Critical | 2      | `.git` dan `.env` terekspos                 |
| 🟠 High     | 2      | Missing HSTS, CSP headers                   |
| 🟡 Medium   | 3      | Admin panel terekspos, security headers 40% |
| 🟢 Low      | 1      | -                                           |

---

## Proposed Fixes

### 🔴 Critical Fixes

#### 1. Block `.git` dan `.env` Access

**File:** `public/.htaccess`

Tambahkan rules berikut:

```apache
# ================================
# SECURITY: Block sensitive files
# ================================

# Block .git directory
<IfModule mod_rewrite.c>
    RewriteRule ^\.git - [F,L]
    RewriteRule ^\.env - [F,L]
    RewriteRule ^composer\.(json|lock)$ - [F,L]
</IfModule>

# Block hidden files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

### 🟠 High Priority Fixes

#### 2. Add Security Headers Middleware

**File baru:** `app/Http/Middleware/SecurityHeadersMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Strict Transport Security (HSTS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Content Security Policy
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://app.sandbox.midtrans.com https://app.midtrans.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self' https://api.sandbox.midtrans.com https://api.midtrans.com; frame-src 'self' https://app.sandbox.midtrans.com https://app.midtrans.com;");

        // XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
```

#### 3. Register Middleware

**File:** `app/Http/Kernel.php`

Tambahkan ke `$middleware` array:

```php
protected $middleware = [
    // ... existing middleware ...
    \App\Http\Middleware\SecurityHeadersMiddleware::class,  // ADD THIS
];
```

---

### 🟡 Medium Priority

#### 4. Root Directory Protection

**File baru:** `.htaccess` (di root folder, bukan public)

```apache
# Redirect all to public folder
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Block sensitive file extensions
<FilesMatch "\.(env|git|sql|bak|log|lock)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## Verification Steps

Setelah implementasi, jalankan command berikut:

```bash
# Test .env blocked (expect 403 Forbidden)
curl -I https://tixkita.id/.env

# Test .git blocked (expect 403 Forbidden)
curl -I https://tixkita.id/.git

# Check security headers
curl -I https://tixkita.id
```

**Online tools:**
- https://securityheaders.com/?q=tixkita.id
- https://observatory.mozilla.org/analyze/tixkita.id

---

## Expected Results

| Sebelum               | Sesudah                |
| --------------------- | ---------------------- |
| Security Headers: 40% | Security Headers: 80%+ |
| `.git` exposed        | `.git` blocked (403)   |
| `.env` exposed        | `.env` blocked (403)   |

---

## Notes

- Perubahan `.htaccess` berlaku langsung tanpa restart
- Jika menggunakan **Nginx**, konfigurasi berbeda
- Backup konfigurasi sebelum menerapkan perubahan
- Test di staging/development dulu sebelum production

---

**Prepared by:** Security Scanner  
**Status:** 🟡 Pending Review
