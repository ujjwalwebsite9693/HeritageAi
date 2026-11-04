# Deployment and Production Setup Guide

## System Requirements
- **PHP:** 8.1 or higher (PHP 8.2+ recommended). Required extensions: `pdo_mysql`, `curl`, `gd`, `mbstring`, `fileinfo`, `openssl`.
- **MySQL:** 8.0 or higher (supports InnoDB, `utf8mb4_unicode_ci`, and FULLTEXT indexing).
- **Web Server:** Apache (with `mod_rewrite`), Nginx, or PHP 8 CLI server.

---

## 1. Installation Steps

### Step 1: Database Setup
1. Open MySQL and verify credentials.
2. Run the database migration script:
```bash
php database/migrate.php
```
This automatically creates the `manuscript_archive` database, executes `database/schema.sql`, and seeds initial data via `database/seed.sql`.

### Step 2: Environment Configuration
Copy or update `.env` in the project root:
```ini
APP_NAME="Manuscript Intelligence Archive"
APP_ENV=production
APP_DEBUG=false
APP_URL="http://localhost:8000"
APP_SECRET="your_secure_random_key"

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=manuscript_archive
DB_USER=root
DB_PASS=root
DB_CHARSET=utf8mb4

STORAGE_PATH="storage"
UPLOAD_MAX_BYTES=26214400

# Optional: Add Gemini API key for external real-time AI translations
GEMINI_API_KEY=""
```

### Step 3: Generating Facsimile Assets
To generate initial sample facsimile assets and portraits:
```bash
php database/generate_assets.php
```

---

## 2. Web Server Configuration

### Option A: Local Development (PHP Built-in Server)
```bash
php -S localhost:8000 -t public public/index.php
```
Visit `http://localhost:8000` in your browser.

### Option B: Apache Configuration
Point your Apache `DocumentRoot` to `D:/New folder/public`:
```apache
<VirtualHost *:80>
    ServerName manuscript.archive
    DocumentRoot "D:/New folder/public"

    <Directory "D:/New folder/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Ensure `public/.htaccess` exists with URL rewriting enabled:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

### Option C: Nginx Configuration
```nginx
server {
    listen 80;
    server_name manuscript.archive;
    root /path/to/manuscript-archive/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 3. Background Job Automation (Cron)
Configure a crontab entry or scheduled task to process background OCR and Link Discovery jobs every minute:
```bash
* * * * * php /path/to/manuscript-archive/cron/worker.php > /dev/null 2>&1
0 2 * * * php /path/to/manuscript-archive/cron/link_scan.php > /dev/null 2>&1
```

---

## 4. Default Seeded Accounts
| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@archive.org` | `admin123` |
| **Researcher** | `dr.anand@nalanda.edu` | `researcher123` |
| **Researcher** | `farooqi@oriental.ac.in` | `researcher123` |
| **User** | `priya.sharma@delhiuniv.ac.in` | `user123` |

