# PNRKM - Pengolahan Nilai Rapor Kurikulum Merdeka

Sistem Pengolahan Nilai Rapor Kurikulum Merdeka berbasis website untuk institusi pendidikan SMK.

## 🚀 Tech Stack

- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: Vue 3 + Vite + TailwindCSS
- **Database**: MySQL 8
- **ORM**: Eloquent
- **Authentication**: Laravel Sanctum + Role-based middleware
- **PDF Export**: Laravel DomPDF
- **Testing**: PHPUnit + Laravel Pest

## 📋 Features

### 🔐 Authentication & Authorization
- Multi-role login system (Admin, Guru, Wali Kelas, Kepala Sekolah, Siswa)
- Role-based access control
- Secure API authentication with Laravel Sanctum

### 👨‍💼 Admin/Operator Features
- ✅ Manajemen data siswa (CRUD)
- ✅ Manajemen data guru (CRUD)
- ✅ Manajemen data kelas
- ✅ Manajemen jurusan
- ✅ Manajemen mata pelajaran
- ✅ Manajemen tahun ajaran
- ✅ Pengaturan jadwal pelajaran
- ✅ Manajemen ekstrakurikuler

### 👩‍🏫 Guru Features
- ✅ Input dan manajemen nilai sumatif
- ✅ Manajemen capaian pembelajaran (CP)
- ✅ Manajemen tujuan pembelajaran (TP)
- ✅ Input nilai P5 (Projek Penguatan Profil Pelajar Pancasila)
- ✅ Pembimbingan PKL (Praktik Kerja Lapangan)
- ✅ Dashboard dengan statistik mengajar

### 👨‍🎓 Wali Kelas Features
- ✅ Monitoring nilai kelas
- ✅ Input dan manajemen kehadiran siswa
- ✅ Penulisan catatan akademik
- ✅ Generate dan manajemen rapor
- ✅ Rekap nilai dan kehadiran kelas

### 👔 Kepala Sekolah Features
- ✅ Approval rapor siswa
- ✅ Dashboard statistik sekolah
- ✅ Rekap dan laporan akademik
- ✅ Legger nilai per kelas
- ✅ Monitoring kinerja sekolah

### 🎓 Siswa Features
- ✅ Melihat nilai akademik
- ✅ Melihat rapor
- ✅ Melihat jadwal pelajaran
- ✅ Melihat kehadiran
- ✅ Download rapor (PDF)

## 🏗️ Database Schema

Sistem ini mengimplementasikan ERD dengan 21 tabel utama:

### Core Tables
- `users` - Data pengguna sistem
- `tahun_ajaran` - Tahun ajaran dan semester
- `jurusan` - Program keahlian
- `kelas` - Data kelas
- `siswa` - Data siswa
- `guru` - Data guru

### Academic Tables
- `mata_pelajaran` - Mata pelajaran
- `jadwal_pelajaran` - Jadwal mengajar
- `capaian_pembelajaran` - CP Kurikulum Merdeka
- `tujuan_pembelajaran` - TP dari CP
- `nilai` - Nilai akademik siswa

### Assessment Tables
- `ekstrakurikuler` - Kegiatan ekstrakurikuler
- `nilai_ekstrakurikuler` - Nilai ekskul
- `pkl` - Data PKL siswa
- `p5` - Projek P5
- `dimensi_p5` - Dimensi Profil Pelajar Pancasila
- `nilai_p5` - Penilaian P5

### Report Tables
- `kehadiran` - Data absensi siswa
- `ukk` - Uji Kompetensi Keahlian
- `catatan_akademik` - Catatan wali kelas
- `rapor` - Data rapor siswa

## 🛠️ Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+

### Step-by-step Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd pnrkm
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database in `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pnrkm
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

9. **Access the application**
   - Open browser and go to `http://localhost:8000`

## 🎯 Demo Accounts

After running the seeders, you can use these demo accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@demo.com | password |
| Guru | guru@demo.com | password |
| Siswa | siswa@demo.com | password |
| Kepala Sekolah | kepalasekolah@demo.com | password

## 🏛️ System Architecture

### Frontend Architecture
```
resources/js/
├── components/          # Reusable Vue components
├── pages/              # Page components
│   ├── admin/          # Admin pages
│   ├── guru/           # Teacher pages
│   ├── wali-kelas/     # Homeroom teacher pages
│   ├── kepala-sekolah/ # Principal pages
│   └── siswa/          # Student pages
├── stores/             # Pinia stores
├── composables/        # Vue composables
└── App.vue            # Main app component
```

### Backend Architecture
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/        # API controllers
│   │       ├── Admin/  # Admin controllers
│   │       ├── Guru/   # Teacher controllers
│   │       └── ...     # Other role controllers
│   └── Middleware/     # Custom middleware
├── Models/             # Eloquent models
└── Policies/           # Authorization policies
```

## 🔄 API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get authenticated user
- `PUT /api/update-password` - Update password

### Dashboard (Role-based)
- `GET /api/dashboard/admin` - Admin dashboard
- `GET /api/dashboard/guru` - Teacher dashboard
- `GET /api/dashboard/wali-kelas` - Homeroom teacher dashboard
- `GET /api/dashboard/kepala-sekolah` - Principal dashboard
- `GET /api/dashboard/siswa` - Student dashboard

### Admin Endpoints
- `GET|POST|PUT|DELETE /api/admin/siswa` - Student management
- `GET|POST|PUT|DELETE /api/admin/guru` - Teacher management
- `GET|POST|PUT|DELETE /api/admin/kelas` - Class management
- And more...

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ExampleTest

# Run with coverage
php artisan test --coverage
```

## 📊 Features Status

✅ = Completed | 🚧 = In Progress | ❌ = Not Started

- ✅ Database design and migrations
- ✅ Eloquent models with relationships
- ✅ Authentication system
- ✅ Role-based authorization
- ✅ API controllers (partial)
- ✅ Vue.js frontend setup
- ✅ Admin dashboard
- ✅ Database seeders
- 🚧 CRUD components
- ❌ PDF export functionality
- ❌ Complete feature tests
- ❌ Production deployment

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🏫 About This System

This system was developed to implement the Kurikulum Merdeka assessment system for SMK institutions. The system supports various vocational programs including:

- Rekayasa Perangkat Lunak (RPL)
- Teknik Komputer dan Jaringan (TKJ)
- Multimedia (MM)
- Otomatisasi dan Tata Kelola Perkantoran (OTKP)
- Akuntansi dan Keuangan Lembaga (AKL)
- Bisnis Daring dan Pemasaran (BDP)

## 📞 Support

For support and inquiries about this system, please contact the development team.

---

**Built with ❤️ for education in Indonesia**