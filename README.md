# 🍗 Chicking BJM - Sistem Inventory Management

![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Sistem Inventory Management untuk Chicking BJM adalah aplikasi web modern yang dibangun dengan Laravel untuk mengelola inventori restoran fast food. Aplikasi ini menyediakan solusi lengkap untuk manajemen stok, kategori produk, supplier, dan transaksi inventory.

## 📋 Daftar Isi

-   [Fitur Utama](#-fitur-utama)
-   [Teknologi](#-teknologi)
-   [Persyaratan Sistem](#-persyaratan-sistem)
-   [Instalasi](#-instalasi)
-   [Konfigurasi](#-konfigurasi)
-   [Penggunaan](#-penggunaan)
-   [API Documentation](#-api-documentation)
-   [Testing](#-testing)
-   [Deployment](#-deployment)
-   [Kontribusi](#-kontribusi)
-   [Lisensi](#-lisensi)
-   [Tim Pengembang](#-tim-pengembang)

## 🚀 Fitur Utama

### 📦 Manajemen Inventory

-   **Dashboard Analytics** - Overview lengkap dengan statistik real-time
-   **Manajemen Item** - CRUD lengkap dengan tracking stok
-   **Kategori Produk** - Organisasi produk yang terstruktur
-   **Supplier Management** - Database supplier dan kontak
-   **Transaksi Stok** - Track IN/OUT dengan riwayat lengkap

### 📊 Pelaporan & Analytics

-   **Real-time Dashboard** - Statistik inventory terkini
-   **Laporan Stok** - Status stok dan alert low stock
-   **Analisis Trend** - Grafik pergerakan stok
-   **Export Data** - Excel/PDF reports
-   **Alert System** - Notifikasi stok menipis

### 🔐 Sistem Autentikasi

-   **Multi-role Access** - Admin, Manager, Staff
-   **Secure Login** - Session management dengan security
-   **User Management** - Profile dan permission control
-   **Activity Logging** - Track user activities

### 📱 User Experience

-   **Responsive Design** - Mobile-friendly interface
-   **Modern UI/UX** - Clean dan intuitive design
-   **Real-time Search** - Instant search & filtering
-   **Bulk Operations** - Import/Export Excel
-   **Print Support** - Print-ready reports

## 🛠 Teknologi

### Backend

-   **Framework**: Laravel 10.x
-   **PHP**: 8.1+
-   **Database**: MySQL 8.0+
-   **Authentication**: Laravel Sanctum
-   **File Storage**: Laravel Storage

### Frontend

-   **CSS Framework**: Bootstrap 5.3
-   **JavaScript**: Vanilla JS + jQuery
-   **Icons**: Boxicons
-   **Charts**: Chart.js
-   **DataTables**: Advanced table features

### Tools & Libraries

-   **Excel Processing**: PhpSpreadsheet
-   **PDF Generation**: DomPDF
-   **Image Processing**: Intervention Image
-   **Activity Logging**: Spatie Activity Log
-   **Development**: Laravel Debugbar, Laravel IDE Helper

## 💻 Persyaratan Sistem

### Minimum Requirements

-   **PHP**: 8.1 atau lebih tinggi
-   **Composer**: 2.0+
-   **Node.js**: 16.0+ (untuk asset compilation)
-   **MySQL**: 8.0+ atau MariaDB 10.3+
-   **Web Server**: Apache 2.4+ atau Nginx 1.18+

### Recommended

-   **RAM**: 2GB minimum, 4GB recommended
-   **Storage**: 1GB free space
-   **PHP Extensions**:
    -   OpenSSL
    -   PDO
    -   Mbstring
    -   Tokenizer
    -   XML
    -   Ctype
    -   JSON
    -   BCMath
    -   Fileinfo
    -   GD atau Imagick

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/chicking-bjm.git
cd chicking-bjm
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE chicking_bjm"

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 5. Storage Setup

```bash
# Create storage symlink
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## ⚙️ Konfigurasi

### Database Configuration

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chicking_bjm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="Chicking BJM"
```

### File Upload Configuration

```env
FILESYSTEM_DISK=local
MAX_UPLOAD_SIZE=5120  # 5MB in KB
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,xlsx,xls
```

## 📖 Penggunaan

### Default Login

Setelah seeding database, gunakan kredensial berikut:

**Admin Account:**

-   Email: `admin@chickingbjm.com`
-   Password: `admin123`

**Manager Account:**

-   Email: `manager@chickingbjm.com`
-   Password: `manager123`

**Staff Account:**

-   Email: `staff@chickingbjm.com`
-   Password: `staff123`

### Quick Start Guide

#### 1. Setup Kategori

1. Login sebagai Admin
2. Navigasi ke **Kategori** → **Tambah Kategori**
3. Isi nama dan deskripsi kategori
4. Simpan dan kategori siap digunakan

#### 2. Setup Supplier

1. Navigasi ke **Supplier** → **Tambah Supplier**
2. Isi informasi supplier (nama, kontak, alamat)
3. Simpan data supplier

#### 3. Tambah Item

1. Navigasi ke **Item** → **Tambah Item**
2. Isi detail item (nama, SKU, kategori, supplier)
3. Set stok awal dan minimum stok
4. Simpan item

#### 4. Transaksi Stok

1. Navigasi ke **Transaksi Stok** → **Tambah Transaksi**
2. Pilih jenis transaksi (Masuk/Keluar)
3. Pilih item dan quantity
4. Tambahkan keterangan
5. Simpan transaksi

### Import Data Excel

#### 1. Download Template

-   Klik tombol **Import/Export** → **Download Template**
-   Template akan ter-download dengan format yang benar

#### 2. Isi Data

-   Buka template Excel
-   Isi data sesuai kolom yang tersedia
-   Hapus baris contoh sebelum import

#### 3. Upload File

-   Klik **Import/Export** → **Import Excel**
-   Pilih file yang sudah diisi
-   Klik **Import Data**

### Tips Penggunaan

-   🔍 **Search**: Gunakan search box untuk mencari data cepat
-   🏷️ **Filter**: Manfaatkan filter untuk menyaring data
-   📊 **Dashboard**: Cek dashboard secara rutin untuk monitoring
-   🔔 **Alert**: Perhatikan notifikasi stok menipis
-   📱 **Mobile**: Aplikasi responsive, bisa diakses via mobile

## 📚 API Documentation

### Authentication Endpoints

```
POST /api/login           # User login
POST /api/logout          # User logout
GET  /api/user            # Get current user
```

### Inventory Endpoints

```
GET    /api/items         # Get all items
POST   /api/items         # Create new item
GET    /api/items/{id}    # Get specific item
PUT    /api/items/{id}    # Update item
DELETE /api/items/{id}    # Delete item
```

### Stock Transaction Endpoints

```
GET  /api/transactions          # Get all transactions
POST /api/transactions          # Create new transaction
GET  /api/transactions/{id}     # Get specific transaction
```

### Reports Endpoints

```
GET /api/reports/low-stock      # Get low stock items
GET /api/reports/dashboard      # Get dashboard data
GET /api/reports/export         # Export data
```

Untuk dokumentasi lengkap API, jalankan:

```bash
php artisan route:list --path=api
```

## 🧪 Testing

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit

# Test dengan coverage
php artisan test --coverage
```

### Test Categories

-   **Authentication Tests**: Login, logout, permissions
-   **Inventory Tests**: CRUD operations, stock management
-   **Transaction Tests**: Stock in/out, validations
-   **API Tests**: Endpoint responses, data validation

## 🚀 Deployment

### Production Deployment

#### 1. Server Requirements

-   **VPS/Dedicated Server** dengan spesifikasi minimum
-   **Domain** dan **SSL Certificate**
-   **Database Server** terpisah (recommended)

#### 2. Environment Setup

```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### 3. Web Server Configuration

**Apache (.htaccess)**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Nginx**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/chicking-bjm/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### 4. Security Setup

```bash
# Set proper permissions
chmod -R 755 /var/www/chicking-bjm
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data /var/www/chicking-bjm

# Setup SSL (using Certbot)
sudo certbot --nginx -d yourdomain.com
```

### Docker Deployment

```dockerfile
# Dockerfile
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql gd xml

# Copy application
COPY . /var/www
WORKDIR /var/www

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --optimize-autoloader --no-dev

EXPOSE 9000
CMD ["php-fpm"]
```

```yaml
# docker-compose.yml
version: "3.8"
services:
    app:
        build: .
        volumes:
            - .:/var/www
        depends_on:
            - database

    nginx:
        image: nginx:alpine
        ports:
            - "80:80"
        volumes:
            - .:/var/www
            - ./nginx.conf:/etc/nginx/conf.d/default.conf
        depends_on:
            - app

    database:
        image: mysql:8.0
        environment:
            MYSQL_DATABASE: chicking_bjm
            MYSQL_USER: laravel
            MYSQL_PASSWORD: secret
            MYSQL_ROOT_PASSWORD: secret
        volumes:
            - mysql_data:/var/lib/mysql

volumes:
    mysql_data:
```

## 🤝 Kontribusi

Kami menerima kontribusi dari developer lain! Berikut cara berkontribusi:

### 1. Fork Repository

```bash
git clone https://github.com/yourusername/chicking-bjm.git
cd chicking-bjm
git remote add upstream https://github.com/original/chicking-bjm.git
```

### 2. Create Feature Branch

```bash
git checkout -b feature/amazing-feature
```

### 3. Commit Changes

```bash
git commit -m "Add: amazing new feature"
```

### 4. Push to Branch

```bash
git push origin feature/amazing-feature
```

### 5. Create Pull Request

-   Buat pull request dengan deskripsi yang jelas
-   Sertakan screenshot jika ada perubahan UI
-   Pastikan semua test passed

### Contribution Guidelines

-   **Code Style**: Ikuti PSR-12 standards
-   **Testing**: Tambahkan test untuk fitur baru
-   **Documentation**: Update dokumentasi jika diperlukan
-   **Commit Messages**: Gunakan format conventional commits

### Areas for Contribution

-   🐛 **Bug Fixes**: Perbaikan bug yang ditemukan
-   ✨ **New Features**: Fitur baru yang bermanfaat
-   📚 **Documentation**: Improvement dokumentasi
-   🎨 **UI/UX**: Enhancement tampilan dan user experience
-   ⚡ **Performance**: Optimasi performa aplikasi

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

```
MIT License

Copyright (c) 2024 Chicking BJM Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 👥 Tim Pengembang

### Core Team

-   **Project Manager**: [Nama PM](mailto:pm@chickingbjm.com)
-   **Lead Developer**: [GitHub Copilot](mailto:dev@chickingbjm.com)
-   **UI/UX Designer**: [Nama Designer](mailto:design@chickingbjm.com)
-   **QA Engineer**: [Nama QA](mailto:qa@chickingbjm.com)

### Contributors

Terima kasih kepada semua [contributors](https://github.com/username/chicking-bjm/contributors) yang telah membantu pengembangan proyek ini.

## 📞 Support & Contact

### Bantuan & Dokumentasi

-   📧 **Email**: support@chickingbjm.com
-   📱 **WhatsApp**: +62 xxx-xxxx-xxxx
-   🌐 **Website**: [www.chickingbjm.com](https://www.chickingbjm.com)
-   📚 **Wiki**: [Documentation Wiki](https://github.com/username/chicking-bjm/wiki)

### Social Media

-   📘 **Facebook**: [Chicking BJM](https://facebook.com/chickingbjm)
-   📸 **Instagram**: [@chickingbjm](https://instagram.com/chickingbjm)
-   🐦 **Twitter**: [@chickingbjm](https://twitter.com/chickingbjm)

### Issue Reporting

Jika menemukan bug atau ingin request fitur:

1. Cek [existing issues](https://github.com/username/chicking-bjm/issues)
2. Buat [new issue](https://github.com/username/chicking-bjm/issues/new) dengan template yang sesuai
3. Berikan detail yang lengkap dan screenshot jika diperlukan

---

<div align="center">

**⭐ Star this repository if you find it helpful!**

Made with ❤️ by [Chicking BJM Team](https://github.com/username/chicking-bjm)

</div>
