# 📊 Monitoring Guide

## TixKita - System Monitoring & Alerting

---

## 1. Application Monitoring

### 1.1 Laravel Log Files

**Location:**
```
/var/www/tixkita.id/storage/logs/laravel.log
```

**View Latest Logs:**
```bash
# Last 100 lines
tail -100 storage/logs/laravel.log

# Real-time monitoring
tail -f storage/logs/laravel.log

# Search for errors
grep -i "error" storage/logs/laravel.log
```

**Log Rotation:**
```bash
# Configure logrotate
sudo nano /etc/logrotate.d/laravel

/var/www/tixkita.id/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

### 1.2 Log Levels

| Level       | Description             |
| ----------- | ----------------------- |
| `emergency` | System unusable         |
| `alert`     | Immediate action needed |
| `critical`  | Critical conditions     |
| `error`     | Error conditions        |
| `warning`   | Warning conditions      |
| `notice`    | Normal but significant  |
| `info`      | Informational           |
| `debug`     | Debug information       |

---

## 2. Server Monitoring

### 2.1 System Resources

```bash
# CPU usage
top
htop

# Memory usage
free -h

# Disk usage
df -h

# Active connections
netstat -an | grep :80 | wc -l
```

### 2.2 Process Monitoring

```bash
# Check PHP-FPM
sudo systemctl status php8.1-fpm

# Check Nginx
sudo systemctl status nginx

# Check MySQL
sudo systemctl status mysql
```

### 2.3 Automated Monitoring Script

```bash
#!/bin/bash
# /home/scripts/monitor.sh

LOG="/var/log/tixkita-monitor.log"
ALERT_EMAIL="admin@tixkita.id"

# Check services
check_service() {
    if ! systemctl is-active --quiet $1; then
        echo "$(date): $1 is DOWN!" >> $LOG
        echo "$1 is down on $(hostname)" | mail -s "ALERT: $1 DOWN" $ALERT_EMAIL
        systemctl restart $1
    fi
}

check_service nginx
check_service php8.1-fpm
check_service mysql

# Check disk space
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$(date): Disk usage at $DISK_USAGE%" >> $LOG
    echo "Disk usage is $DISK_USAGE% on $(hostname)" | mail -s "ALERT: High Disk Usage" $ALERT_EMAIL
fi

# Check website
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://tixkita.id)
if [ $HTTP_CODE -ne 200 ]; then
    echo "$(date): Website returned $HTTP_CODE" >> $LOG
    echo "Website returned $HTTP_CODE" | mail -s "ALERT: Website Down" $ALERT_EMAIL
fi
```

**Crontab:**
```bash
# Run every 5 minutes
*/5 * * * * /home/scripts/monitor.sh
```

---

## 3. Database Monitoring

### 3.1 MySQL Status

```bash
# Connection status
mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"

# Slow queries
mysql -u root -p -e "SHOW GLOBAL STATUS LIKE 'Slow_queries';"

# Table status
mysql -u root -p tixkita -e "SHOW TABLE STATUS;"
```

### 3.2 Enable Slow Query Log

```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 3.3 Database Size Check

```sql
SELECT 
    table_name AS 'Table',
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'tixkita_prod'
ORDER BY (data_length + index_length) DESC;
```

---

## 4. Error Tracking

### 4.1 Laravel Error Handling

**Custom Error Handler:**
```php
// app/Exceptions/Handler.php
public function register()
{
    $this->reportable(function (Throwable $e) {
        // Send to external service
        if (app()->environment('production')) {
            // Log to file or send email
            Log::channel('slack')->error($e->getMessage());
        }
    });
}
```

### 4.2 Error Notification

**Email on Error:**
```php
// config/logging.php
'channels' => [
    'mail' => [
        'driver' => 'monolog',
        'handler' => \Monolog\Handler\SwiftMailerHandler::class,
        'formatter' => \Monolog\Formatter\HtmlFormatter::class,
        'with' => [
            'mailer' => fn() => app('mailer'),
            'message' => fn() => (new \Swift_Message('TixKita Error'))
                ->setFrom('noreply@tixkita.id')
                ->setTo('admin@tixkita.id'),
        ],
    ],
],
```

---

## 5. Performance Monitoring

### 5.1 Key Metrics

| Metric        | Threshold | Action             |
| ------------- | --------- | ------------------ |
| Response Time | > 3s      | Optimize queries   |
| Error Rate    | > 1%      | Investigate errors |
| CPU Usage     | > 80%     | Scale resources    |
| Memory Usage  | > 85%     | Increase RAM       |
| Disk Usage    | > 80%     | Cleanup/expand     |

### 5.2 Laravel Telescope (Development)

```bash
# Install
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access: `https://tixkita.id/telescope` (protect in production)

### 5.3 Laravel Debugbar (Development)

```bash
composer require barryvdh/laravel-debugbar --dev
```

---

## 6. Business Metrics

### 6.1 Key Performance Indicators (KPIs)

| KPI             | Query                                                                                |
| --------------- | ------------------------------------------------------------------------------------ |
| Daily Orders    | `SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()`                     |
| Revenue Today   | `SELECT SUM(total) FROM orders WHERE status='paid' AND DATE(created_at) = CURDATE()` |
| Conversion Rate | Orders / Visitors                                                                    |
| Active Events   | `SELECT COUNT(*) FROM events WHERE status='active'`                                  |

### 6.2 Dashboard Queries

```sql
-- Hourly orders today
SELECT HOUR(created_at) as hour, COUNT(*) as orders
FROM orders
WHERE DATE(created_at) = CURDATE()
GROUP BY HOUR(created_at);

-- Top selling events
SELECT e.judul, COUNT(o.order_id) as sales
FROM events e
JOIN order_items oi ON oi.product_id IN (SELECT product_id FROM products WHERE event_id = e.event_id)
JOIN orders o ON o.order_id = oi.order_id
WHERE o.status = 'paid'
GROUP BY e.event_id
ORDER BY sales DESC
LIMIT 10;
```

---

## 7. Alerting Rules

### 7.1 Priority Levels

| Level         | Response Time | Examples                       |
| ------------- | ------------- | ------------------------------ |
| P1 - Critical | < 15 min      | Website down, payment failure  |
| P2 - High     | < 1 hour      | High error rate, slow response |
| P3 - Medium   | < 4 hours     | Disk warning, SSL expiring     |
| P4 - Low      | < 24 hours    | Minor issues, optimizations    |

### 7.2 Alert Channels

| Channel       | Use For                   |
| ------------- | ------------------------- |
| Email         | All alerts, detailed logs |
| SMS/WA        | P1 & P2 alerts            |
| Slack/Discord | Team notifications        |

---

## 8. Health Check Endpoint

### Create Health Check Route

```php
// routes/api.php
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => now()->toIso8601String()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ], 500);
    }
});
```

**Monitor with:**
```bash
curl -s https://tixkita.id/api/health | jq .
```

---

*Last Updated: January 2026*
