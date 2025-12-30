# Access Control - Laravel Application

Aplikasi **Access Control & Attendance Management System** berbasis Laravel 10 yang terintegrasi dengan perangkat keras Soyal access control devices.

## 🚀 Fitur Utama

- **Manajemen User/Karyawan** - Profil, kartu akses, fingerprint
- **Manajemen Device** - Integrasi Soyal access control
- **Attendance/Absensi** - Shift pattern, time tracking
- **Leave Management** - Pengajuan dan approval cuti
- **Personal Trainer Scheduling** - Fitur gym/fitness

---

## 📋 Requirements

Sebelum menginstall, pastikan sistem Anda memiliki:

- **PHP** >= 8.1
- **Composer** >= 2.0
- **MySQL** >= 5.7 atau **MariaDB** >= 10.3
- **Node.js** >= 16.x (opsional, untuk asset compilation)
- **Git**

### PHP Extensions yang Diperlukan:
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD (untuk image processing)

---

## 📥 Cara Clone & Install

### 1. Clone Repository

```bash
# Via HTTPS (recommended)
git clone https://github.com/fariz7172/accesscontrol.git

# Via SSH (jika sudah setup SSH key)
git clone git@github.com:fariz7172/accesscontrol.git

# Masuk ke folder project
cd accesscontrol
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (opsional)
npm install
```

### 3. Setup Environment

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan dengan database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=accesscontrol
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Setup Database

```bash
# Buat database baru di MySQL
mysql -u root -p
CREATE DATABASE accesscontrol;
exit;

# Jalankan migration
php artisan migrate

# (Opsional) Import sample data
php artisan db:seed
```

**Atau import langsung file SQL:**
```bash
mysql -u root -p accesscontrol < accesscontrol.sql
```

### 6. (Opsional) Build Assets

```bash
npm run build
# atau untuk development
npm run dev
```

### 7. Jalankan Aplikasi

```bash
php artisan serve
```

Aplikasi akan berjalan di: **http://localhost:8000**

---

## 🔐 Default Login

Setelah setup, gunakan kredensial berikut untuk login:

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |

> ⚠️ **Penting:** Segera ubah password default setelah login pertama kali!

---

## 📁 Struktur Folder

```
accesscontrol/
├── app/                    # Application logic
│   ├── Http/Controllers/   # Controller files
│   ├── Models/             # Eloquent models
│   └── ...
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── public/                 # Public assets
├── resources/
│   └── views/              # Blade templates
├── routes/
│   └── web.php             # Web routes
├── storage/                # Storage files
└── .env.example            # Environment template
```

---

## ⚙️ Konfigurasi Tambahan

### Storage Link (untuk upload foto)

```bash
php artisan storage:link
```

### Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔧 Troubleshooting

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "Permission denied" (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Error: "SQLSTATE Connection refused"
- Pastikan MySQL/MariaDB sudah running
- Cek konfigurasi DB_HOST, DB_PORT di .env

### Error: "The Mix manifest does not exist"
```bash
npm install && npm run build
```

---

## 🖥️ Menjalankan dengan Laragon (Windows)

1. Install [Laragon](https://laragon.org/download/)
2. Clone project ke folder `C:\laragon\www\accesscontrol`
3. Buka Laragon dan Start All
4. Akses via: **http://accesscontrol.test**

---

## 📱 API Endpoints

Aplikasi ini juga menyediakan API untuk integrasi dengan device:

| Endpoint | Method | Keterangan |
|----------|--------|------------|
| `/api/attendance` | POST | Log attendance |
| `/api/device/sync` | POST | Sync device data |

---

## 🤝 Contributing

1. Fork repository
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author

**Fariz Ahmad**
- GitHub: [@fariz7172](https://github.com/fariz7172)
