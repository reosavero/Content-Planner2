# TVRI Jawa Timur - Social Media Planner

> **Enterprise Social Media Management System** untuk Tim Media Sosial TVRI Jawa Timur.
> Setara dengan Meta Business Suite, Hootsuite, Buffer — dibangun dengan PHP Native OOP MVC.

---

## 🚀 Fitur Utama

| Fitur | Status |
|-------|--------|
| ✅ **Dashboard Informatif** dengan Chart.js (statistik real-time) | ✅ Selesai |
| ✅ **Planning Konten** (CRUD lengkap dengan workflow approval) | ✅ Selesai |
| ✅ **Kalender Interaktif** (FullCalendar, drag & drop, monthly/weekly/daily) | ✅ Selesai |
| ✅ **Approval Workflow** (Kontributor → Editor → Admin → Posting) | ✅ Selesai |
| ✅ **Auto Scheduler** (Queue + Cron Job + Retry + Notification) | ✅ Selesai |
| ✅ **File Upload** (Drag & drop, preview, compression, thumbnail generator) | ✅ Selesai |
| ✅ **RBAC** (Super Admin, Admin Sosmed, Editor, Kontributor) | ✅ Selesai |
| ✅ **Dark Mode / Light Mode** | ✅ Selesai |
| ✅ **Responsive Design** (Desktop, Tablet, Mobile) | ✅ Selesai |
| ✅ **CSRF, XSS, SQL Injection Protection** | ✅ Selesai |
| ✅ **Captcha + Rate Limiting** pada Login | ✅ Selesai |
| ✅ **Analytics & Chart** (mingguan, bulanan, platform distribution) | ✅ Selesai |
| ✅ **Master Data** (Program TV, Kategori, Platform, Tags, Hashtag, Template, Lokasi, Talent) | ✅ Selesai |
| ✅ **User Management** (Super Admin) | ✅ Selesai |
| ✅ **Activity Log** (Audit Trail) | ✅ Selesai |
| ✅ **Backup & Restore Database** | ✅ Selesai |
| ✅ **Export** (PDF, Excel, CSV - framework siap) | 🔧 Preparasi |
| ⏳ **Integrasi Facebook Graph API** | 🔧 Perlu API Key |
| ⏳ **Integrasi Instagram Graph API** | 🔧 Perlu API Key |
| ⏳ **Integrasi YouTube Data API** | 🔧 Perlu API Key |
| ⏳ **Integrasi TikTok API** | 🔧 Perlu API Key |
| ⏳ **Integrasi Twitter/X API** | 🔧 Perlu API Key |
| ⏳ **Integrasi Threads API** | 🔧 Perlu API Key |

---

## 🏗️ Arsitektur

```
📁 D:\Planning_Content_TVRI/
├── 📁 assets/           # CSS, JS, Images, Fonts, Plugins
│   ├── css/tvri-theme.css     # 40KB+ tema lengkap TVRI
│   └── js/tvri-app.js         # 27KB+ JavaScript modular
├── 📁 config/           # Konfigurasi aplikasi, database, security, social
├── 📁 controllers/      # MVC Controllers (19 file)
├── 📁 database/         # Migrations + Seeds
│   ├── migrations/001_create_tables.sql  # 24KB (25+ tabel)
│   └── seeds/001_seed_initial_data.sql   # Data awal
├── 📁 helpers/          # Core: Database, Session, Security, Validator, Router, dll
├── 📁 middleware/        # Auth, SuperAdmin
├── 📁 models/           # Base Model (Active Record)
├── 📁 routes/           # Route definitions (90+ routes)
├── 📁 uploads/          # images, videos, thumbnails, documents, avatars
├── 📁 views/            # Layouts, Pages, Components
│   ├── layouts/         # main.php, auth.php
│   └── pages/           # dashboard, planning, calendar, approval, dll
├── 📁 logs/             # Application & cron logs
├── index.php            # Front Controller
├── router.php           # Dev server router
├── setup.bat            # Database setup script
├── start.bat            # Dev server start script
└── README.md            # Dokumentasi ini
```

---

## 🗄️ Database (25+ Tabel)

| Tabel | Deskripsi |
|-------|-----------|
| `roles` | Role user (super_admin, admin_sosmed, editor, kontributor) |
| `users` | Data user + autentikasi |
| `login_attempts` | Log percobaan login (rate limiting) |
| `user_sessions` | Manajemen session |
| `program_tv` | Program acara TVRI Jatim |
| `kategori_konten` | Kategori konten |
| `jenis_konten` | Jenis konten (hard news, feature, dll) |
| `platform_sosmed` | 6 platform sosial media |
| `tags` | Tag konten |
| `hashtag` | Hashtag library |
| `template_caption` | Template caption reusable |
| `lokasi_shooting` | Lokasi produksi |
| `talent` | Talent/presenter |
| `platform_akun` | Akun sosial media terhubung |
| `planning_konten` | **Main table** - semua data konten |
| `planning_tags` | Many-to-many tags |
| `planning_media` | Multiple media per konten |
| `scheduler_queue` | Antrian posting |
| `posting_logs` | Log hasil posting |
| `activity_logs` | Audit trail |
| `notifications` | Notifikasi user |
| `settings` | Pengaturan sistem |
| `backup_logs` | Riwayat backup |

---

## 🎨 UI/UX Features

- **Glassmorphism** ringan pada topbar & card
- **Gradient Biru TVRI** sebagai aksen
- **Rounded Card** dengan soft shadow
- **Smooth Animation** (fade, slide, bounce)
- **Responsive** hingga mobile (320px+)
- **Dark Mode** dengan toggle
- **Loading Skeleton** (CSS shimmer)
- **Toast Notification** (4 tipe: success, error, warning, info)
- **Modal Elegan** dengan backdrop blur
- **Empty State** di setiap halaman
- **Realtime Search** dengan debounce
- **Advanced Filter** (status, platform, program, kategori)
- **Pagination** dengan ellipsis
- **Bulk Actions** (select all, multi delete/submit)
- **Font Poppins** + Bootstrap Icons

---

## 👥 Role & Hak Akses

### Super Admin
- ✅ Manajemen semua user
- ✅ Semua akun sosial media
- ✅ Semua planning
- ✅ Approval final
- ✅ Analytics penuh
- ✅ Log aktivitas
- ✅ Pengaturan sistem
- ✅ Backup & restore database

### Admin Sosmed
- ✅ Membuat planning
- ✅ Approval editor
- ✅ Upload konten
- ✅ Scheduler
- ✅ Analytics

### Editor
- ✅ Membuat konten
- ✅ Upload gambar/video
- ✅ Edit caption
- ✅ Request revisi

### Kontributor
- ✅ Submit ide konten
- ✅ Upload draft
- ✅ Tracking status

---

## 🔐 Keamanan

- ✅ **CSRF Token** di setiap form
- ✅ **XSS Protection** (htmlspecialchars)
- ✅ **SQL Injection Protection** (prepared statements)
- ✅ **Password Bcrypt** (cost 12)
- ✅ **Rate Limiting** (5 attempt / 15 menit)
- ✅ **Captcha** pada login
- ✅ **Session Timeout** otomatis
- ✅ **Secure Headers** (CSP, X-Frame, HSTS)
- ✅ **Input Validation** (Validator class)
- ✅ **Encryption** (AES-256-CBC untuk token)
- ✅ **Role-based access control** (middleware)

---

## ⚙️ Instalasi

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Composer (optional)
- XAMPP / WAMP / Laragon

### Langkah Instalasi

```bash
# 1. Clone project ke htdocs
cd C:\xampp\htdocs
git clone [repo-url] tvri-planner

# 2. Setup database
cd tvri-planner
setup.bat

# 3. Start development server
start.bat

# 4. Buka browser
# http://localhost:8000
```

### Login Default
| Role | Username | Password |
|------|----------|----------|
| Super Admin | `superadmin` | `Admin@12345` |

---

## 🔑 Konfigurasi Awal (config)

File config berisi kredensial rahasia (SMTP app password, Google OAuth secret, TikTok key) **tidak ikut di-commit** ke git. Setelah clone, salin template lalu isi kredensialmu sendiri:

```bash
copy config\app.example.php config\app.php
copy config\social.example.php config\social.php
```

Yang wajib diisi di `config/app.php`:
- `GOOGLE_DRIVE_CLIENT_ID`, `GOOGLE_DRIVE_CLIENT_SECRET`, `GOOGLE_DRIVE_FOLDER_ID` — untuk integrasi Google Drive
- `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM` — untuk email notifikasi (gunakan App Password Gmail)

Yang wajib diisi di `config/social.php` (via env variable):
- `META_APP_ID`, `META_APP_SECRET` — Facebook/Instagram
- `YT_API_KEY`, `YT_CLIENT_ID`, `YT_CLIENT_SECRET` — YouTube
- `TT_CLIENT_KEY`, `TT_CLIENT_SECRET` — TikTok

> ⚠️ Jangan pernah mengubah file di `config/` yang memuat kredensial ke dalam commit.
> Template `*.example.php` adalah satu-satunya versi config yang boleh di-push.

---

## ⏰ Cron Job Setup

Tambahkan ke task scheduler / cron:

```bash
# Every minute - proses scheduler
* * * * * curl -s http://localhost:8000/cron/scheduler

# Every hour - refresh token
0 * * * * curl -s http://localhost:8000/cron/refresh-tokens

# Daily at 3 AM - cleanup logs
0 3 * * * curl -s http://localhost:8000/cron/cleanup-logs

# Daily at 4 AM - auto backup
0 4 * * * curl -s http://localhost:8000/cron/backup
```

---

## 🔌 Integrasi API Sosial Media

Berkas konfigurasi: `config/social.php`

| Platform | Method | API Version | Scope |
|----------|--------|-------------|-------|
| Facebook | Graph API | v18.0 | pages_manage_posts, pages_read_engagement |
| Instagram | Graph API | v18.0 | instagram_basic, instagram_content_publish |
| YouTube | Data API v3 | v3 | youtube.upload, youtube.readonly |
| TikTok | TikTok API | v2 | video.publish, user.info.basic |
| Twitter/X | API v2 | v2 | tweet.write, tweet.read, users.read |
| Threads | Threads API | v1 | threads_basic, threads_content_publish |

---

## 📊 Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.x Native OOP |
| **Architecture** | MVC (Front Controller Pattern) |
| **Database** | MySQL 8.0 (InnoDB, Foreign Keys, utf8mb4) |
| **Frontend** | HTML5, CSS3, JavaScript ES6 |
| **CSS Framework** | Custom (setara Bootstrap 5) |
| **Icons** | Bootstrap Icons 1.11 |
| **Charts** | Chart.js 4.4 |
| **Calendar** | FullCalendar 6.1 |
| **AJAX** | jQuery 3.7 + Native Fetch |
| **Security** | CSRF, XSS, SQL Injection, Rate Limit, Captcha |

---

## 🎯 Target UI/UX

Aplikasi ini didesain setara dengan:
- **Meta Business Suite** (dashboard analytics)
- **Notion** (clean UI, typography)
- **Monday.com** (colorful cards, status badges)
- **ClickUp** (hierarchy, filters)
- **Hootsuite** (scheduling, calendar)
- **Buffer** (simplicity, publishing flow)

---

## 📈 Statistik Kode

| Metrik | Value |
|--------|-------|
| **Total File** | 40+ files |
| **Controllers** | 19 files |
| **Views** | 15+ files |
| **CSS** | 40KB+ theme |
| **JavaScript** | 27KB+ app logic |
| **SQL Migration** | 24KB schema |
| **Total Routes** | 90+ API endpoints |
| **Database Tables** | 25 tables |

---

## 👨‍💻 Developer

**TVRI Jawa Timur - Digital Team**
- Teknologi: PHP Native OOP MVC
- Database: MySQL
- Frontend: Bootstrap 5 Style + Chart.js
- Tahun: 2026

---

## 📝 Lisensi

Hak Cipta © 2026 TVRI Jawa Timur. Seluruh hak cipta dilindungi undang-undang.
