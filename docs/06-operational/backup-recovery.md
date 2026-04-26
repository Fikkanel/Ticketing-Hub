# 💾 Backup & Recovery

## TixKita - Backup Strategy & Disaster Recovery

---

## 1. Backup Strategy

### 1.1 Backup Types

| Type              | Frequency | Retention |
| ----------------- | --------- | --------- |
| Full Database     | Daily     | 30 days   |
| Incremental       | Hourly    | 7 days    |
| Application Files | Weekly    | 4 weeks   |
| User Uploads      | Daily     | 30 days   |

### 1.2 What to Backup

- ✅ MySQL database
- ✅ .env file
- ✅ storage/app/public (uploaded files)
- ✅ storage/logs (optional)
- ❌ vendor/ (reinstall via composer)
- ❌ node_modules/ (reinstall via npm)

---

## 2. Database Backup

### 2.1 Manual Backup

```bash
# Full database backup
mysqldump -u tixkita -p tixkita_prod > backup_$(date +%Y%m%d_%H%M%S).sql

# With compression
mysqldump -u tixkita -p tixkita_prod | gzip > backup_$(date +%Y%m%d).sql.gz

# Specific tables only
mysqldump -u tixkita -p tixkita_prod orders tickets > orders_backup.sql
```

### 2.2 Automated Backup Script

```bash
#!/bin/bash
# /home/scripts/backup-db.sh

# Configuration
DB_USER="tixkita"
DB_PASS="your_password"
DB_NAME="tixkita_prod"
BACKUP_DIR="/home/backups/database"
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Create backup
BACKUP_FILE="$BACKUP_DIR/db_$(date +%Y%m%d_%H%M%S).sql.gz"
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_FILE

# Log
echo "$(date): Database backup created: $BACKUP_FILE" >> /var/log/backup.log

# Delete old backups
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
echo "$(date): Old backups cleaned" >> /var/log/backup.log
```

**Crontab:**
```bash
# Daily at 2 AM
0 2 * * * /home/scripts/backup-db.sh
```

---

## 3. File Backup

### 3.1 Backup Upload Files

```bash
#!/bin/bash
# /home/scripts/backup-files.sh

SOURCE="/var/www/tixkita.id/storage/app/public"
BACKUP_DIR="/home/backups/files"
RETENTION_DAYS=30

mkdir -p $BACKUP_DIR

# Create compressed backup
tar -czf $BACKUP_DIR/files_$(date +%Y%m%d).tar.gz -C $SOURCE .

# Cleanup old backups
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete
```

### 3.2 Backup .env File

```bash
# Copy to secure location
cp /var/www/tixkita.id/.env /home/backups/config/env_$(date +%Y%m%d)

# Encrypt for storage
gpg -c /home/backups/config/env_$(date +%Y%m%d)
```

---

## 4. Remote Backup

### 4.1 Backup to Remote Server

```bash
#!/bin/bash
# Sync to remote backup server

REMOTE="backup@backup-server.com"
REMOTE_DIR="/backups/tixkita"

# Sync database backups
rsync -avz /home/backups/database/ $REMOTE:$REMOTE_DIR/database/

# Sync file backups
rsync -avz /home/backups/files/ $REMOTE:$REMOTE_DIR/files/
```

### 4.2 Backup to Cloud (S3/GCS)

```bash
# Using AWS CLI
aws s3 sync /home/backups s3://your-bucket/tixkita-backups/

# Using rclone (supports multiple providers)
rclone sync /home/backups remote:tixkita-backups/
```

---

## 5. Recovery Procedures

### 5.1 Database Recovery

```bash
# Stop application (maintenance mode)
cd /var/www/tixkita.id
php artisan down

# Restore from backup
gunzip < /home/backups/database/db_20260122.sql.gz | mysql -u tixkita -p tixkita_prod

# Or restore uncompressed
mysql -u tixkita -p tixkita_prod < backup.sql

# Verify data
mysql -u tixkita -p tixkita_prod -e "SELECT COUNT(*) FROM orders;"

# Resume application
php artisan up
```

### 5.2 File Recovery

```bash
# Stop application
cd /var/www/tixkita.id
php artisan down

# Extract backup
tar -xzf /home/backups/files/files_20260122.tar.gz -C /var/www/tixkita.id/storage/app/public

# Fix permissions
chown -R www-data:www-data /var/www/tixkita.id/storage

# Resume application
php artisan up
```

### 5.3 Full System Recovery

**Step 1: Prepare New Server**
```bash
# Install requirements
apt update && apt install nginx mysql-server php8.1-fpm ...
```

**Step 2: Deploy Application**
```bash
cd /var/www
git clone https://github.com/your-org/tixkita.git tixkita.id
cd tixkita.id
composer install --no-dev
```

**Step 3: Restore .env**
```bash
gpg -d /home/backups/config/env_20260122.gpg > .env
```

**Step 4: Restore Database**
```bash
mysql -u root -p -e "CREATE DATABASE tixkita_prod"
gunzip < /path/to/backup.sql.gz | mysql -u root -p tixkita_prod
```

**Step 5: Restore Files**
```bash
tar -xzf /path/to/files_backup.tar.gz -C storage/app/public
php artisan storage:link
```

**Step 6: Configure & Test**
```bash
php artisan config:cache
php artisan route:cache
php artisan up
```

---

## 6. Verification

### 6.1 Backup Verification Script

```bash
#!/bin/bash
# /home/scripts/verify-backup.sh

# Check backup file exists
LATEST_DB=$(ls -t /home/backups/database/*.sql.gz | head -1)
if [ -f "$LATEST_DB" ]; then
    echo "✅ Latest DB backup: $LATEST_DB"
    echo "   Size: $(du -h $LATEST_DB | cut -f1)"
    echo "   Date: $(stat -c %y $LATEST_DB)"
else
    echo "❌ No database backup found!"
    exit 1
fi

# Check file backup
LATEST_FILES=$(ls -t /home/backups/files/*.tar.gz | head -1)
if [ -f "$LATEST_FILES" ]; then
    echo "✅ Latest files backup: $LATEST_FILES"
else
    echo "❌ No files backup found!"
fi

# Test restore (to temp database)
mysql -u root -p -e "CREATE DATABASE test_restore"
gunzip < $LATEST_DB | mysql -u root -p test_restore
COUNT=$(mysql -u root -p test_restore -N -e "SELECT COUNT(*) FROM orders")
echo "✅ Restored $COUNT orders to test database"
mysql -u root -p -e "DROP DATABASE test_restore"
```

### 6.2 Monthly Restore Test

Setiap bulan, lakukan:
1. Restore backup ke server staging
2. Verify data integrity
3. Test fungsi kritical (login, payment, ticket)
4. Document hasil test

---

## 7. Disaster Recovery Plan

### 7.1 Recovery Time Objectives

| Scenario            | RTO      | RPO      |
| ------------------- | -------- | -------- |
| Database corruption | 1 hour   | 1 hour   |
| Server failure      | 4 hours  | 24 hours |
| Data center down    | 24 hours | 24 hours |

**RTO**: Recovery Time Objective (max downtime)  
**RPO**: Recovery Point Objective (max data loss)

### 7.2 Emergency Contacts

| Role         | Contact | Responsibility  |
| ------------ | ------- | --------------- |
| System Admin | +62-xxx | Server & infra  |
| Developer    | +62-xxx | Application     |
| DBA          | +62-xxx | Database        |
| Manager      | +62-xxx | Decision making |

### 7.3 Escalation Path

1. **First 15 min**: On-call engineer attempts fix
2. **15-30 min**: Escalate to team lead
3. **30-60 min**: Activate disaster recovery
4. **1+ hour**: Management notification

---

## 8. Backup Checklist

### Daily
- [ ] Database backup completed
- [ ] Backup transferred to remote
- [ ] No backup errors in log

### Weekly
- [ ] File backup completed
- [ ] Check backup storage space
- [ ] Review backup logs

### Monthly
- [ ] Test restore procedure
- [ ] Verify backup integrity
- [ ] Update documentation
- [ ] Review retention policy

---

*Last Updated: January 2026*
