# 🚀 SIAKAD SMK - Setup Instructions

## ✅ Setup Status: COMPLETED

Your SIAKAD SMK application has been successfully created and configured!

## 📊 What's Included

### ✅ Backend (Laravel 11)
- **21 Database Tables** with relationships (ERD-based)
- **Multi-role Authentication** (Admin, Guru, Wali Kelas, Kepala Sekolah, Siswa)
- **API Controllers** for all entities
- **Role-based Middleware** 
- **Database Seeders** with demo data

### ✅ Frontend (Vue 3 + Tailwind)
- **Vue 3 + Vite** integration
- **TailwindCSS** styling
- **Pinia** state management
- **Vue Router** with role-based routing
- **Authentication system**

### ✅ Database Structure
- **15 Users** created (including demo accounts)
- **6 Guru** records
- **6 Siswa** records  
- **24 Kelas** across 6 jurusan
- **23 Mata Pelajaran** (general + vocational)
- **Tahun Ajaran, Dimensi P5, and more**

## 🔧 Quick Start

### 1. Install Dependencies (Already Done)
```bash
composer install
npm install
```

### 2. Environment Setup (Already Done)
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup (Already Done)
```bash
php artisan migrate:fresh --seed
```

### 4. Build Assets (Already Done)
```bash
npm run build
```

### 5. Start the Application
```bash
php artisan serve
```

Visit: **http://localhost:8000**

## 🐳 Menjalankan dengan Docker di Server

Aplikasi bisa dijalankan di server menggunakan Docker (PHP-FPM, Nginx, MySQL).

### Prasyarat

- Docker dan Docker Compose terpasang di server.

### Langkah

1. **Clone repo** dan masuk ke direktori proyek:
   ```bash
   cd siakad-smk
   ```

2. **Siapkan file env** (gunakan contoh untuk Docker):
   ```bash
   cp .env.docker.example .env
   ```
   Edit `.env` dan isi:
   - `APP_KEY` — generate dengan: `php artisan key:generate` (lokal) lalu salin, atau setelah container jalan: `docker compose exec app php artisan key:generate`
   - `DB_PASSWORD` — password MySQL (sama dengan `MYSQL_ROOT_PASSWORD` di container)
   - `APP_URL` — URL akses aplikasi, mis. `http://ip-server` atau `http://domain.example.com`
   - `APP_PORT` — port di host (default `80`). Jika port 80 dipakai layanan lain, set mis. `8080` lalu akses `http://ip-server:8080`

3. **Build dan jalankan container**:
   ```bash
   docker compose up -d --build
   ```

4. **Generate APP_KEY** (jika belum diisi):
   ```bash
   docker compose exec app php artisan key:generate
   ```
   Lalu salin nilai `APP_KEY` dari output ke `.env` di host, dan restart: `docker compose restart app`.

5. **Jalankan migrasi dan seed** (sekali):
   ```bash
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed --force
   ```

6. **Permission storage** (jika ada error write):
   ```bash
   docker compose exec app chown -R www-data:www-data storage bootstrap/cache
   ```

7. **Akses aplikasi** di browser: `http://<ip-server>:<APP_PORT>` (default `http://<ip-server>`).

### Perintah berguna

```bash
# Lihat log
docker compose logs -f

# Masuk ke container app
docker compose exec app sh

# Artisan di container
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status
```

## 🔐 Demo Login Credentials

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| **Admin** | admin@demo.com | password | Full system access |
| **Guru** | guru@demo.com | password | Teacher dashboard |
| **Siswa** | siswa@demo.com | password | Student dashboard |

### Additional Test Accounts
- **Kepala Sekolah**: kepsek@smk.sch.id / password
- **Wali Kelas**: sri.wahyuni@smk.sch.id / password
- **More accounts**: Check UserSeeder.php

## 📁 Project Structure

```
siakad-smk/
├── app/
│   ├── Http/Controllers/Api/     # API Controllers
│   ├── Models/                   # Eloquent Models (21 models)
│   └── Http/Middleware/         # Role middleware
├── database/
│   ├── migrations/              # 21 migration files
│   └── seeders/                # Demo data seeders
├── resources/
│   ├── js/                     # Vue.js application
│   │   ├── pages/              # Vue pages by role
│   │   ├── stores/             # Pinia stores
│   │   └── App.vue            # Main Vue app
│   └── views/                  # Blade templates
├── routes/
│   ├── api.php                 # API routes (role-based)
│   └── web.php                # Web routes
└── public/                     # Built assets
```

## 🎯 Key Features Implemented

### 🔐 Authentication & Authorization
- ✅ Multi-role login system
- ✅ Laravel Sanctum API authentication
- ✅ Role-based middleware protection
- ✅ Secure password hashing

### 📊 Database Design
- ✅ ERD-based schema (21 tables)
- ✅ Proper relationships & foreign keys
- ✅ Kurikulum Merdeka compliance
- ✅ Demo data for testing

### 🎨 Frontend Foundation
- ✅ Vue 3 + Composition API
- ✅ TailwindCSS responsive design
- ✅ Role-based routing
- ✅ Authentication state management

### 📋 Core Modules Ready
- ✅ User Management
- ✅ Academic Year Management
- ✅ Student & Teacher Data
- ✅ Class & Subject Management
- ✅ Grade Processing Foundation
- ✅ P5 Assessment Structure
- ✅ Report Generation Base

## 🚧 Development Status

### ✅ Completed (100%)
- Database design & migrations
- Authentication system
- API endpoint structure
- Vue.js frontend setup
- Demo data & seeders

### 🔄 In Progress (Ready for Development)
- Complete CRUD interfaces
- Grade input forms
- Report generation (PDF)
- Advanced dashboard features

### 📝 Ready for Extension
- Additional API controllers
- Vue component libraries
- PDF export functionality
- Advanced reporting features

## 🛠️ Development Commands

```bash
# Database operations
php artisan migrate:fresh --seed  # Reset & reseed database
php artisan tinker                # Laravel console

# Frontend development
npm run dev                       # Development build with watch
npm run build                     # Production build

# Code quality
php artisan route:list            # List all routes
php artisan model:show User       # Show model details
```

## 📊 System Overview

This is a **production-ready foundation** for SMK Progresia Cianjur's academic information system implementing:

- **Kurikulum Merdeka** assessment framework
- **Multi-role access control** (5 user types)
- **Complete academic data structure** (21 entities)
- **Modern web technologies** (Laravel 11 + Vue 3)
- **Responsive design** (TailwindCSS)

## 🎉 Next Steps

1. **Start Development Server**: `php artisan serve`
2. **Login with Demo Account**: admin@demo.com / password
3. **Explore the Dashboard**: Navigate role-based interfaces
4. **Extend Features**: Add CRUD forms, reports, etc.
5. **Customize Design**: Modify TailwindCSS components

## 📞 Support

The application is ready for development and testing. All core infrastructure is in place following Laravel and Vue.js best practices.

**Happy Coding! 🚀**