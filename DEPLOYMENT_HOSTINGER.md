# KISAN ENTERPRISES ERP & WEBSITE — HOSTINGER DEPLOYMENT GUIDE

This guide details the exact steps for deploying the Kisan Enterprises ERP and Website to Hostinger Shared / Cloud Hosting or any standard cPanel / DirectAdmin environment.

---

## 1. System Requirements

Ensure the following PHP extensions are enabled in **Hostinger hPanel &rarr; Advanced &rarr; PHP Configuration**:
- **PHP Version**: 8.2 or 8.3
- **Extensions**:
  - `bcmath` (Mandatory for high-precision decimal calculations)
  - `pdo_mysql` & `mysqli`
  - `gd` or `imagick` (For image processing and barcode rendering)
  - `mbstring`
  - `fileinfo`
  - `zip`
  - `xml` / `dom` (For DomPDF receipt and invoice printing)

---

## 2. File Upload Structure

### Recommended Setup (Subfolder or Custom Root)
Upload the entire codebase to `public_html/` or a dedicated subdirectory (e.g. `domains/yourdomain.com/`).

### Web Routing Options:
1. **Option A (Recommended if Hostinger allows changing Document Root):**
   - Point Document Root to: `/public_html/public`
2. **Option B (Standard Shared Hosting with root `.htaccess`):**
   - Keep files in `public_html/`. The included root `.htaccess` file automatically routes all traffic into `public/` and forbids direct access to `.env`, `composer.json`, and `storage/`.

---

## 3. Environment Configuration (`.env`)

In the project root, copy or edit `.env`:
```ini
APP_NAME="Kisan Enterprises"
APP_ENV=production
APP_KEY=base64:... (Run php artisan key:generate if needed)
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_kisan_erp
DB_USERNAME=u123456789_kisan_user
DB_PASSWORD=your_secure_mysql_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## 4. Database Setup & Seeding

1. Open **Hostinger hPanel &rarr; Databases &rarr; Management** and create your MySQL Database and User.
2. Open SSH terminal or Hostinger Browser Terminal in project root:
   ```bash
   # Run all database schema migrations
   php artisan migrate --force

   # Seed system roles, default permissions, crops, and warehouses
   php artisan db:seed --class=RolePermissionSeeder --force
   php artisan db:seed --class=MasterDataSeeder --force

   # Optional (for demonstration testing only):
   # php artisan db:seed --class=DevelopmentSeeder --force
   ```

---

## 5. Storage Symlink & File Permissions

Set write permissions for runtime directories:
```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

---

## 6. Automated Nightly Cron Jobs

In **Hostinger hPanel &rarr; Advanced &rarr; Cron Jobs**:

### Job 1: Scheduled Daily Database Backup (2:00 AM)
- **Schedule**: `0 2 * * *`
- **Command**:
  ```bash
  mysqldump -u DB_USERNAME -p'DB_PASSWORD' DB_NAME > /home/u123456/backups/kisan_$(date +\%F).sql
  ```

### Job 2: Laravel Scheduler (Every Minute)
- **Schedule**: `* * * * *`
- **Command**:
  ```bash
  cd /home/u123456/domains/yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
  ```

---

## 7. Initial Staff Logins

When seeded with `DevelopmentSeeder`:

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@kisan.local` | `Kisan@1234` |
| **Purchase & Sales Manager** | `purchase@kisan.local` | `Kisan@1234` |
| **Accounts & Operations Manager** | `accounts@kisan.local` | `Kisan@1234` |

> **Important:** Upon production handover, the Admin must log in, update passwords, and input verified business GSTIN, PAN, and address in **ERP Settings**.
