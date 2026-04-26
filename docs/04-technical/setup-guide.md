# 🔧 Developer Setup Guide

## TixKita - Local Development Setup

---

## 1. Requirements

### System Requirements
| Software | Version | Notes                  |
| -------- | ------- | ---------------------- |
| PHP      | 8.1+    | With extensions below  |
| Composer | 2.x     | PHP dependency manager |
| MySQL    | 8.0+    | Or MariaDB 10.5+       |
| Node.js  | 18+     | For frontend assets    |
| Git      | 2.x     | Version control        |

### PHP Extensions
```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO MySQL
- Tokenizer
- XML
- GD (untuk QR code & PDF)
- Zip
```

---

## 2. Installation Steps

### 2.1 Clone Repository
```bash
git clone https://github.com/your-org/tixkita.git
cd tixkita
```

### 2.2 Install PHP Dependencies
```bash
composer install
```

### 2.3 Install Node Dependencies
```bash
npm install
```

### 2.4 Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2.5 Configure .env
Edit file `.env`:

```env
APP_NAME=TixKita
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tixkita
DB_USERNAME=root
DB_PASSWORD=your_password

# Mail (Development)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tixkita.id"
MAIL_FROM_NAME="${APP_NAME}"

# Midtrans (Sandbox)
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

### 2.6 Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE tixkita"

# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

### 2.7 Storage Link
```bash
php artisan storage:link
```

### 2.8 Build Frontend Assets
```bash
# Development
npm run dev

# Or watch for changes
npm run watch
```

### 2.9 Start Development Server
```bash
php artisan serve
```

Aplikasi akan berjalan di: `http://localhost:8000`

---

## 3. Development Accounts

### Default Admin Account
```
Email: admin@tixkita.id
Password: password123
Role: superadmin
```

### Test Customer Account
```
Email: customer@test.com
Password: password123
```

---

## 4. Common Commands

### Artisan Commands
```bash
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (delete all, migrate fresh)
php artisan migrate:fresh --seed

# Create new controller
php artisan make:controller ControllerName

# Create new model
php artisan make:model ModelName -m

# Run queue worker
php artisan queue:work

# Run scheduled tasks
php artisan schedule:run
```

### NPM Commands
```bash
# Development build
npm run dev

# Watch for changes
npm run watch

# Production build
npm run build
```

---

## 5. Testing

### Run Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test --filter=FeatureTestName

# With coverage
php artisan test --coverage
```

### Test Database
Configure di `.env.testing`:
```env
DB_DATABASE=tixkita_test
```

---

## 6. Code Style

### PHP CS Fixer
```bash
# Check style
./vendor/bin/pint --test

# Fix style
./vendor/bin/pint
```

### ESLint (JavaScript)
```bash
npm run lint
npm run lint:fix
```

---

## 7. Debugging

### Laravel Telescope
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```
Access: `http://localhost:8000/telescope`

### Debug Bar
```bash
composer require barryvdh/laravel-debugbar --dev
```

### Log Files
```
storage/logs/laravel.log
```

---

## 8. IDE Setup

### VS Code Extensions
- Laravel Extension Pack
- PHP Intelephense
- Blade Formatter
- EditorConfig

### PhpStorm
- Laravel plugin
- PHP annotations plugin
- .editorconfig support

---

## 9. Troubleshooting

### Permission Issues (Linux/Mac)
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

### Composer Memory Limit
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### Clear Everything
```bash
php artisan optimize:clear
composer dump-autoload
npm run build
```

---

*Last Updated: January 2026*
