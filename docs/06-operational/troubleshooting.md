# 🔧 Troubleshooting Guide

## TixKita - Common Issues & Solutions

---

## 1. Application Issues

### 1.1 500 Internal Server Error

**Possible Causes:**
- PHP syntax error
- Missing .env file
- Permission issues
- Missing dependencies

**Solutions:**

```bash
# Check Laravel logs
tail -100 storage/logs/laravel.log

# Check PHP error log
tail -100 /var/log/php8.1-fpm.log

# Verify .env exists
ls -la .env

# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### 1.2 Page Loads Slowly

**Check:**
```bash
# Check server load
top
htop

# Check MySQL slow queries
tail -100 /var/log/mysql/slow.log

# Check PHP-FPM status
sudo systemctl status php8.1-fpm
```

**Solutions:**
- Enable query caching
- Optimize database indexes
- Enable Laravel cache
- Check for N+1 queries

### 1.3 White/Blank Page

**Solutions:**
```bash
# Check PHP errors
php artisan serve 2>&1

# Enable debug temporarily
# In .env: APP_DEBUG=true

# Check storage permissions
ls -la storage/
chmod -R 775 storage
```

---

## 2. Payment Issues

### 2.1 Midtrans Callback Not Received

**Check:**
1. Verify callback URL in Midtrans Dashboard
2. Check server firewall allows Midtrans IPs
3. Check Laravel logs for callback attempts

**Solutions:**
```bash
# Check if callback route works
curl -X POST https://tixkita.id/api/midtrans/callback \
  -H "Content-Type: application/json" \
  -d '{"test": true}'

# Check Midtrans notification log in dashboard

# Verify route is registered
php artisan route:list | grep midtrans
```

### 2.2 Payment Status Stuck at Pending

**Check:**
```bash
# Check payment status in database
mysql -u tixkita -p tixkita_prod -e \
"SELECT order_id, status, midtrans_order_id FROM orders WHERE status='pending' ORDER BY created_at DESC LIMIT 10;"
```

**Manual Verification:**
1. Login to Midtrans Dashboard
2. Check transaction status
3. Manually update if payment confirmed

```bash
php artisan tinker
>>> $order = App\Models\Order::find(123);
>>> $order->status = 'paid';
>>> $order->paid_at = now();
>>> $order->save();
```

### 2.3 gross_amount Mismatch Error

**Cause:** Total di aplikasi tidak sama dengan Midtrans

**Solution:**
- Pastikan kalkulasi subtotal, tax, discount konsisten
- Cek apakah ada rounding issues
- Verify item details matches

---

## 3. Email Issues

### 3.1 Emails Not Sending

**Check:**
```bash
# Test mail configuration
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

**Solutions:**
```bash
# Check mail config
php artisan config:show mail

# Verify SMTP credentials in .env
cat .env | grep MAIL

# Try different mail driver
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=app-password
MAIL_ENCRYPTION=tls
```

### 3.2 Emails Going to Spam

**Solutions:**
- Setup SPF record in DNS
- Setup DKIM
- Use dedicated email service (SendGrid, Mailgun)
- Avoid spam trigger words

---

## 4. File Upload Issues

### 4.1 Upload Fails

**Check:**
```bash
# Check PHP upload limits
php -i | grep -E "upload_max|post_max|memory"

# Check storage permissions
ls -la storage/app/public
```

**Solutions:**
```ini
# php.ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

### 4.2 Images Not Displaying

**Check:**
```bash
# Verify storage link
ls -la public/storage

# If missing, recreate
php artisan storage:link
```

---

## 5. Authentication Issues

### 5.1 Cannot Login

**Check:**
```bash
# Verify user exists
php artisan tinker
>>> App\Models\User::where('email', 'admin@tixkita.id')->first();
```

**Reset Password:**
```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'admin@tixkita.id')->first();
>>> $user->password = bcrypt('newpassword');
>>> $user->save();
```

### 5.2 Session Expired Quickly

**Solutions:**
```php
// config/session.php
'lifetime' => 120, // minutes
'expire_on_close' => false,
```

```bash
# Clear session files
rm -rf storage/framework/sessions/*
```

---

## 6. Database Issues

### 6.1 Migration Errors

**Common Errors:**

```bash
# Table already exists
php artisan migrate:status
php artisan migrate:rollback

# Column doesn't exist
# Check migration file for correct column name

# Foreign key error
# Ensure related tables exist first
```

### 6.2 Connection Refused

**Check:**
```bash
# MySQL status
sudo systemctl status mysql

# Test connection
mysql -u tixkita -p tixkita_prod

# Check .env
DB_HOST=127.0.0.1
DB_PORT=3306
```

### 6.3 Too Many Connections

**Solutions:**
```sql
-- Check current connections
SHOW PROCESSLIST;

-- Kill idle connections
-- (identify IDs from PROCESSLIST)
KILL <process_id>;
```

```ini
# my.cnf
max_connections = 200
```

---

## 7. QR Code / Ticket Issues

### 7.1 QR Code Not Generating

**Check:**
```bash
# Verify GD extension
php -m | grep gd

# If missing
sudo apt install php8.1-gd
sudo systemctl restart php8.1-fpm
```

### 7.2 PDF Generation Fails

**Solutions:**
```bash
# Check DomPDF cache is writable
chmod -R 775 storage/fonts

# Clear DomPDF cache
rm -rf storage/fonts/*
```

### 7.3 Scanner Not Working

**Check:**
- HTTPS required for camera access
- Check browser camera permissions
- Verify QR code format

---

## 8. Performance Issues

### 8.1 High Memory Usage

```bash
# Check memory
free -m

# Check PHP-FPM processes
ps aux | grep php-fpm

# Limit PHP-FPM workers
# /etc/php/8.1/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 2
pm.max_spare_servers = 5
```

### 8.2 Slow Database Queries

```bash
# Enable slow query log
# /etc/mysql/mysql.conf.d/mysqld.cnf
slow_query_log = 1
long_query_time = 2

# Analyze slow queries
pt-query-digest /var/log/mysql/slow.log

# Add indexes
CREATE INDEX idx_orders_status ON orders(status);
```

---

## 9. Quick Fixes

### Clear All Cache
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload
```

### Restart Services
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo systemctl restart mysql
```

### Check Everything
```bash
# System status
systemctl status nginx php8.1-fpm mysql

# Disk space
df -h

# Memory
free -m

# Laravel logs
tail -50 storage/logs/laravel.log

# Nginx logs
tail -50 /var/log/nginx/error.log
```

---

## 10. Emergency Contacts

| Issue Type        | Contact          |
| ----------------- | ---------------- |
| Server Down       | System Admin     |
| Payment Issues    | Midtrans Support |
| Application Bugs  | Development Team |
| Security Incident | Security Team    |

---

*Last Updated: January 2026*
