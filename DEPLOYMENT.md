# Production Deployment Guide

## Prerequisites

- PHP 7.4+ with extensions: mysqli, curl, json
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) with URL rewriting
- SSL certificate (recommended)

## Quick Deployment Steps

### 1. Server Setup

#### For cPanel/Shared Hosting:
```bash
# Upload files via File Manager or FTP
# Extract in public_html or domain folder
# Skip to step 3
```

#### For VPS/Cloud (Ubuntu):
```bash
# Install LAMP stack
sudo apt update
sudo apt install apache2 mysql-server php php-mysqli php-curl

# Clone repository
git clone https://github.com/kalpitkawar/kalpesh-patel.git /var/www/html/ipo-pulse
cd /var/www/html/ipo-pulse

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
```

### 2. Database Setup

```sql
-- Create database
CREATE DATABASE ipo_pulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace with strong password)
CREATE USER 'ipo_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON ipo_pulse.* TO 'ipo_user'@'localhost';
FLUSH PRIVILEGES;

-- Import schema
mysql -u ipo_user -p ipo_pulse < db_schema.sql
mysql -u ipo_user -p ipo_pulse < db_indexes.sql
```

### 3. Environment Configuration

```bash
# Set environment variables (Linux)
export DB_HOST=localhost
export DB_USER=ipo_user
export DB_PASS=your_strong_password_here
export DB_NAME=ipo_pulse

# Or create .env file (if using vlucas/phpdotenv)
echo "DB_HOST=localhost" > .env
echo "DB_USER=ipo_user" >> .env
echo "DB_PASS=your_strong_password_here" >> .env
echo "DB_NAME=ipo_pulse" >> .env
```

### 4. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

# Compress files
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name yourdomain.com;
    root /var/www/html/ipo-pulse;
    index index.html index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static files
    location ~* \.(css|js|png|svg)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }
}
```

### 5. Security Hardening

```bash
# Remove sensitive files from web root
rm -f db_schema.sql db_indexes.sql
rm -f .git -rf

# Set secure file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 config.php
```

### 6. Performance Optimization

#### Enable OPcache (php.ini):
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

#### Database Tuning (my.cnf):
```ini
[mysqld]
innodb_buffer_pool_size=256M
query_cache_size=64M
query_cache_type=1
max_connections=200
```

### 7. Monitoring & Backup

#### Setup Cron Jobs:
```bash
# Daily database backup
0 2 * * * mysqldump -u ipo_user -p'password' ipo_pulse > /backups/ipo_pulse_$(date +\%Y\%m\%d).sql

# Weekly API sync (if using external APIs)
0 0 * * 0 php /var/www/html/ipo-pulse/sync_api_ipos.php

# Log rotation
0 0 * * * find /var/log/apache2/ -name "*.log" -mtime +30 -delete
```

#### Error Monitoring:
```bash
# Check application logs
tail -f /var/log/apache2/error.log

# Check PHP errors
tail -f /var/log/php_errors.log
```

## Platform-Specific Deployment

### Hostinger
```bash
# 1. Upload files via File Manager
# 2. Import database via phpMyAdmin
# 3. Update config.php with Hostinger database credentials
# 4. Set file permissions via File Manager
```

### DigitalOcean
```bash
# Use LAMP droplet
# Follow VPS steps above
# Configure firewall:
sudo ufw allow 'Apache Full'
sudo ufw enable
```

### AWS EC2
```bash
# Launch Ubuntu instance
# Follow VPS steps
# Configure Security Groups for HTTP/HTTPS
# Setup Elastic IP for static address
```

### Heroku
```bash
# Add Heroku buildpacks
heroku buildpacks:add heroku/php

# Configure environment variables
heroku config:set DB_HOST=your_db_host
heroku config:set DB_USER=your_db_user
heroku config:set DB_PASS=your_db_pass
heroku config:set DB_NAME=your_db_name

# Deploy
git push heroku main
```

## Post-Deployment Checklist

- [ ] Database connection working
- [ ] All API endpoints responding
- [ ] Admin login functioning
- [ ] Mobile responsiveness verified
- [ ] SSL certificate installed
- [ ] Performance monitoring setup
- [ ] Backup system configured
- [ ] Error logging enabled
- [ ] Cache headers configured
- [ ] Security headers set

## Troubleshooting

### Common Issues

**Database Connection Failed:**
```bash
# Check credentials
php -r "require 'config.php'; $conn = get_db_connection(); echo 'OK';"

# Check MySQL service
sudo systemctl status mysql
```

**Permission Denied:**
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/ipo-pulse
sudo chmod -R 755 /var/www/html/ipo-pulse
```

**API Not Working:**
```bash
# Check PHP extensions
php -m | grep mysqli
php -m | grep curl

# Test endpoint
curl -I http://yourdomain.com/get_ipos.php
```

## Support

For deployment issues:
- Check application logs first
- Verify environment variables
- Test database connectivity
- Contact your hosting provider if needed

## Security Notes

- Never commit database credentials to version control
- Use strong passwords for database users
- Keep PHP and MySQL updated
- Enable fail2ban for brute force protection
- Regular security audits recommended