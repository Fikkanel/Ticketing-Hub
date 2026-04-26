# 🚀 Deployment Guide

## TixKita - Production Deployment

---

## 1. Server Requirements

### Minimum Specifications
| Component  | Requirement                |
| ---------- | -------------------------- |
| OS         | Ubuntu 20.04+ / Debian 11+ |
| CPU        | 2 vCPU                     |
| RAM        | 4 GB                       |
| Storage    | 40 GB SSD                  |
| PHP        | 8.1+                       |
| MySQL      | 8.0+                       |
| Web Server | Nginx / Apache             |

### PHP Extensions
```bash
apt install php8.1-{fpm,mysql,mbstring,xml,curl,zip,gd,bcmath}
```

---

## 2. Pre-Deployment Checklist

- [ ] Server provisioned
- [ ] Domain configured (DNS A record)
- [ ] SSL certificate ready
- [ ] Midtrans production keys obtained
- [ ] SMTP credentials ready
- [ ] Backup strategy planned

---

## 3. Deployment Steps

### 3.1 Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server php8.1-fpm \
    php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl \
    php8.1-zip php8.1-gd php8.1-bcmath composer nodejs npm

# Secure MySQL
sudo mysql_secure_installation
```

### 3.2 Database Setup

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE tixkita_prod;
CREATE USER 'tixkita'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON tixkita_prod.* TO 'tixkita'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3.3 Application Deployment

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-org/tixkita.git tixkita.id
cd tixkita.id

# Set ownership
sudo chown -R www-data:www-data /var/www/tixkita.id
sudo chmod -R 755 /var/www/tixkita.id
sudo chmod -R 775 /var/www/tixkita.id/storage
sudo chmod -R 775 /var/www/tixkita.id/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### 3.4 Environment Configuration

```bash
# Copy and edit environment
cp .env.example .env
nano .env
```

**Production .env:**
```env
APP_NAME=TixKita
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tixkita.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tixkita_prod
DB_USERNAME=tixkita
DB_PASSWORD=your_secure_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tixkita.id"
MAIL_FROM_NAME="${APP_NAME}"

MIDTRANS_SERVER_KEY=Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

### 3.5 Application Setup

```bash
# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3.6 Nginx Configuration

```nginx
# /etc/nginx/sites-available/tixkita.id

server {
    listen 80;
    server_name tixkita.id www.tixkita.id;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tixkita.id www.tixkita.id;

    root /var/www/tixkita.id/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/tixkita.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tixkita.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/tixkita.id /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 3.7 SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d tixkita.id -d www.tixkita.id

# Auto-renewal
sudo certbot renew --dry-run
```

---

## 4. Post-Deployment

### 4.1 Create Admin Account

```bash
php artisan tinker
```

```php
use App\Models\User;

User::create([
    'name' => 'Admin',
    'email' => 'admin@tixkita.id',
    'password' => bcrypt('SecurePassword123!'),
    'role' => 'superadmin',
    'is_active' => true,
]);
```

### 4.2 Configure Midtrans Callback

Di Midtrans Dashboard:
1. Settings → Configuration
2. Payment Notification URL: `https://tixkita.id/api/midtrans/callback`
3. Finish/Error Redirect: `https://tixkita.id`

### 4.3 Test All Functions

- [ ] Homepage loads
- [ ] Event detail works
- [ ] Cart and checkout
- [ ] Payment (use sandbox first)
- [ ] Email delivery
- [ ] Admin login
- [ ] Scanner works

---

## 5. Updates & Maintenance

### Deploy Updates

```bash
cd /var/www/tixkita.id

# Enable maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Disable maintenance mode
php artisan up
```

### Rollback

```bash
# If migration fails
php artisan migrate:rollback

# If code fails
git checkout [previous-commit-hash]
```

---

## 6. Docker Deployment (Alternative)

### docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      APP_ENV: production
      
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: tixkita_prod
      MYSQL_USER: tixkita
      MYSQL_PASSWORD: password
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

---

## 7. Security Hardening

### Firewall

```bash
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### File Permissions

```bash
# Secure directories
find /var/www/tixkita.id -type d -exec chmod 755 {} \;
find /var/www/tixkita.id -type f -exec chmod 644 {} \;

# Writable directories
chmod -R 775 /var/www/tixkita.id/storage
chmod -R 775 /var/www/tixkita.id/bootstrap/cache
```

### Hide Sensitive Files

```nginx
# In nginx config
location ~ /\.env {
    deny all;
}
```

---

*Last Updated: January 2026*
