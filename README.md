# Laravel dan Vue.js Coding Test Application

Aplikasi web full-stack yang dibangun dengan Laravel (backend) dan Vue.js (frontend) sebagai bagian dari coding test. Aplikasi ini mencakup autentikasi, kontrol akses berbasis peran, operasi CRUD dengan relasi, audit trail, impor/ekspor Excel, dan banyak lagi.

## Fitur Utama

- Autentikasi dengan Laravel Sanctum
- Manajemen peran dan izin (admin, manager, user)
- Operasi CRUD untuk roles, users, categories, projects, dan tasks
- Relasi antar model (categories -> projects -> tasks)
- Soft deletes, UUIDs, datetime, boolean, dan field JSON
- Upload file (PDF saja, 100-500KB)
- Filtering, sorting, dan searching
- Auditing dengan audit trails dan riwayat yang tidak dapat diubah
- Ekspor/impor Excel dengan dukungan queue/background processing
- Select2/options dari database
- API dan rute SPA (Single Page Application)
- Vuex store untuk state management
- UI modern dengan Tailwind CSS

## Struktur File dan Folder

### Root Directory

- **.env** - File konfigurasi environment (database, URL, dll)
- **.env.example** - Template untuk file .env
- **artisan** - CLI Laravel untuk menjalankan perintah
- **composer.json** - Konfigurasi dependensi PHP
- **package.json** - Konfigurasi dependensi JavaScript
- **vite.config.js** - Konfigurasi Vite untuk build frontend
- **phpunit.xml** - Konfigurasi untuk unit testing
- **README.md** - Dokumentasi proyek
- **.gitignore** - Daftar file yang diabaikan oleh Git
- **.editorconfig** - Konfigurasi editor untuk konsistensi kode

### Backend (Laravel)

- **app/** - Kode utama aplikasi
  - **Console/** - Command line interface
    - **Commands/** - Custom Artisan commands
    - **Kernel.php** - Scheduler dan daftar commands
  - **Exceptions/** - Handler untuk exceptions
    - **Handler.php** - Global exception handler
  - **Http/** - HTTP layer (controllers, middleware, requests)
    - **Controllers/** - Semua controller aplikasi
      - **Auth/** - Controller untuk autentikasi
        - **LoginController.php** - Menangani login
        - **RegisterController.php** - Menangani registrasi
      - **CategoryController.php** - CRUD untuk kategori
      - **ProjectController.php** - CRUD untuk proyek
      - **RoleController.php** - Manajemen peran
      - **TaskController.php** - CRUD untuk tugas
      - **UserController.php** - Manajemen pengguna
      - **Controller.php** - Base controller
    - **Middleware/** - Middleware aplikasi
      - **Authenticate.php** - Middleware autentikasi
      - **RedirectIfAuthenticated.php** - Redirect jika sudah login
      - **TrustHosts.php** - Keamanan host
      - **TrustProxies.php** - Konfigurasi proxy
      - **VerifyCsrfToken.php** - Proteksi CSRF
    - **Requests/** - Form request untuk validasi
      - **CategoryRequest.php** - Validasi kategori
      - **ProjectRequest.php** - Validasi proyek
      - **RoleRequest.php** - Validasi peran
      - **TaskRequest.php** - Validasi tugas
      - **UserRequest.php** - Validasi pengguna
    - **Kernel.php** - HTTP kernel, mendaftarkan middleware
  - **Jobs/** - Queue jobs
    - **ExportJob.php** - Job untuk export Excel
    - **ImportJob.php** - Job untuk import Excel
  - **Models/** - Model Eloquent
    - **User.php** - Model pengguna dengan trait HasRoles
    - **Category.php** - Model kategori
    - **Project.php** - Model proyek
    - **Task.php** - Model tugas
  - **Notifications/** - Notifikasi aplikasi
  - **Policies/** - Authorization policies
  - **Providers/** - Service providers
    - **AppServiceProvider.php** - Provider utama aplikasi
    - **AuthServiceProvider.php** - Provider untuk autentikasi
    - **BroadcastServiceProvider.php** - Provider untuk broadcasting
    - **EventServiceProvider.php** - Provider untuk events
    - **RouteServiceProvider.php** - Provider untuk routing

- **bootstrap/** - File bootstrap aplikasi
  - **app.php** - Bootstrap aplikasi
  - **cache/** - Cache untuk optimasi performa

- **config/** - File konfigurasi aplikasi
  - **app.php** - Konfigurasi aplikasi
  - **auth.php** - Konfigurasi autentikasi
  - **broadcasting.php** - Konfigurasi broadcasting
  - **cache.php** - Konfigurasi cache
  - **cors.php** - Konfigurasi CORS
  - **database.php** - Konfigurasi database
  - **filesystems.php** - Konfigurasi filesystem
  - **hashing.php** - Konfigurasi hashing
  - **logging.php** - Konfigurasi logging
  - **mail.php** - Konfigurasi email
  - **permission.php** - Konfigurasi Spatie Permission
  - **queue.php** - Konfigurasi queue
  - **sanctum.php** - Konfigurasi Sanctum
  - **services.php** - Konfigurasi services
  - **session.php** - Konfigurasi session
  - **view.php** - Konfigurasi view

- **database/** - Database migrations, factories, dan seeders
  - **factories/** - Model factories untuk testing
    - **UserFactory.php** - Factory untuk User
    - **CategoryFactory.php** - Factory untuk Category
    - **ProjectFactory.php** - Factory untuk Project
    - **TaskFactory.php** - Factory untuk Task
  - **migrations/** - Migrasi database
    - **2014_10_12_000000_create_users_table.php** - Migrasi tabel users
    - **2014_10_12_100000_create_password_reset_tokens_table.php** - Migrasi tabel password reset
    - **2019_08_19_000000_create_failed_jobs_table.php** - Migrasi tabel failed jobs
    - **2019_12_14_000001_create_personal_access_tokens_table.php** - Migrasi tabel tokens
    - **2023_01_01_000000_create_permission_tables.php** - Migrasi tabel permission
    - **2023_01_02_000000_create_categories_table.php** - Migrasi tabel categories
    - **2023_01_03_000000_create_projects_table.php** - Migrasi tabel projects
    - **2023_01_04_000000_create_tasks_table.php** - Migrasi tabel tasks
    - **2023_01_05_000000_create_audits_table.php** - Migrasi tabel audits
  - **seeders/** - Seeder database
    - **DatabaseSeeder.php** - Seeder utama
    - **RolePermissionSeeder.php** - Seeder untuk peran dan izin
    - **UserSeeder.php** - Seeder untuk pengguna
    - **CategorySeeder.php** - Seeder untuk kategori
    - **ProjectSeeder.php** - Seeder untuk proyek
    - **TaskSeeder.php** - Seeder untuk tugas

- **lang/** - File bahasa untuk lokalisasi
  - **en/** - File bahasa Inggris

- **public/** - Publicly accessible files
  - **build/** - File yang di-build oleh Vite
  - **index.php** - Entry point aplikasi
  - **favicon.ico** - Favicon
  - **.htaccess** - Konfigurasi Apache
  - **robots.txt** - File untuk search engines

- **resources/** - Frontend resources
  - **css/** - File CSS
    - **app.css** - CSS utama
  - **js/** - File JavaScript
    - **app.js** - Entry point aplikasi Vue
    - **store.js** - Vuex store untuk state management
    - **routes.js** - Konfigurasi Vue Router
    - **bootstrap.js** - Bootstrap JavaScript
    - **components/** - Komponen Vue
      - **App.vue** - Komponen root
      - **Home.vue** - Halaman home
      - **Dashboard.vue** - Halaman dashboard
      - **auth/** - Komponen autentikasi
        - **Login.vue** - Form login
        - **Register.vue** - Form registrasi
      - **categories/** - Komponen untuk kategori
        - **Index.vue** - List kategori
        - **Create.vue** - Form pembuatan kategori
        - **Edit.vue** - Form edit kategori
        - **View.vue** - Detail kategori
      - **projects/** - Komponen untuk proyek
        - **Index.vue** - List proyek
        - **Create.vue** - Form pembuatan proyek
        - **Edit.vue** - Form edit proyek
        - **View.vue** - Detail proyek
      - **tasks/** - Komponen untuk tugas
        - **Index.vue** - List tugas
        - **Create.vue** - Form pembuatan tugas
        - **Edit.vue** - Form edit tugas
        - **View.vue** - Detail tugas
      - **roles/** - Komponen untuk peran
        - **Index.vue** - List peran
        - **Create.vue** - Form pembuatan peran
        - **Edit.vue** - Form edit peran
        - **View.vue** - Detail peran
      - **users/** - Komponen untuk pengguna
        - **Index.vue** - List pengguna
        - **Create.vue** - Form pembuatan pengguna
        - **Edit.vue** - Form edit pengguna
        - **View.vue** - Detail pengguna
  - **views/** - Blade templates
    - **app.blade.php** - Template utama yang memuat aplikasi Vue
    - **welcome.blade.php** - Halaman welcome

- **routes/** - Route definitions
  - **api.php** - Rute API
  - **web.php** - Rute web
  - **channels.php** - Rute broadcasting
  - **console.php** - Rute console

- **storage/** - File yang di-upload, cache, dan logs
  - **app/** - File yang di-upload
    - **public/** - File yang dapat diakses publik
  - **framework/** - File framework
    - **cache/** - Cache
    - **sessions/** - Session data
    - **views/** - Compiled views
  - **logs/** - Log files

- **tests/** - Unit dan feature tests
  - **Feature/** - Feature tests
  - **Unit/** - Unit tests
  - **TestCase.php** - Base test case

- **vendor/** - Composer dependencies (dihasilkan oleh composer install)

- **node_modules/** - NPM dependencies (dihasilkan oleh npm install)

## Library dan Dependensi

### Backend (PHP/Laravel)

- **Laravel 10.x** - Framework PHP
- **Laravel Sanctum** - Autentikasi API dengan token
- **Spatie Laravel Permission** - Manajemen peran dan izin
- **Laravel Excel** - Import/export Excel dengan dukungan queue
- **OwenIt Auditing** - Audit trail untuk model
- **Laravel UUID** - Generasi UUID untuk model

### Frontend (JavaScript/Vue)

- **Vue.js 3.x** - Framework JavaScript progresif
- **Vue Router 4.x** - Routing untuk SPA
- **Vuex 4.x** - State management
- **Axios** - HTTP client untuk API requests
- **Tailwind CSS** - Framework CSS utility-first
- **Bootstrap 5** - Framework CSS untuk komponen UI
- **Vue Select** - Komponen select yang dapat dicari
- **Vue SweetAlert2** - Alert dan konfirmasi yang lebih baik
- **Vue Excel Editor** - Editor Excel untuk Vue
- **Vuelidate** - Validasi form untuk Vue

## Pengguna Default

Setelah menjalankan seeder, pengguna berikut akan tersedia:

- **Admin**: admin@example.com / password
- **Manager**: manager@example.com / password
- **User**: user@example.com / password

## Alur Kerja Aplikasi

1. **Autentikasi**
   - Pengguna login dengan email dan password
   - Token Sanctum dibuat dan disimpan di localStorage
   - Data pengguna dengan peran dan izin dimuat

2. **Dashboard**
   - Menampilkan ringkasan kategori, proyek, dan tugas
   - Akses ke semua fitur berdasarkan peran pengguna

3. **Manajemen Kategori**
   - CRUD untuk kategori
   - Import/export Excel
   - Audit trail

4. **Manajemen Proyek**
   - CRUD untuk proyek
   - Relasi dengan kategori
   - Import/export Excel
   - Audit trail

5. **Manajemen Tugas**
   - CRUD untuk tugas
   - Relasi dengan proyek
   - Status tugas (pending, in_progress, completed, cancelled)
   - Import/export Excel
   - Audit trail

6. **Manajemen Peran dan Pengguna** (Admin only)
   - CRUD untuk peran dan izin
   - CRUD untuk pengguna
   - Assign peran ke pengguna

## Fitur Berdasarkan Peran

- **Admin**: Akses penuh ke semua fitur
- **Manager**: Akses ke kategori, proyek, tugas, dan audit trail
- **User**: Akses terbatas ke kategori (view), proyek (view, create, edit), dan tugas (view, create, edit)

## Endpoint API

Aplikasi menyediakan endpoint RESTful API untuk semua entitas:

- `/api/login` - Autentikasi pengguna
- `/api/register` - Registrasi pengguna
- `/api/logout` - Logout pengguna
- `/api/user` - Mendapatkan data pengguna saat ini
- `/api/roles` - Manajemen peran
- `/api/permissions` - Mendapatkan semua izin
- `/api/users` - Manajemen pengguna (admin only)
- `/api/categories` - Manajemen kategori
- `/api/projects` - Manajemen proyek
- `/api/tasks` - Manajemen tugas

Semua endpoint API dilindungi dengan autentikasi Laravel Sanctum kecuali login dan register.

## Tips untuk Presentasi

1. **Demonstrasi Login dengan Berbagai Peran**
   - Tunjukkan perbedaan UI dan akses untuk admin, manager, dan user
   - Jelaskan bagaimana peran dan izin diimplementasikan dengan Spatie Permission

2. **Fitur CRUD**
   - Tunjukkan operasi CRUD untuk kategori, proyek, dan tugas
   - Jelaskan relasi antar model dan bagaimana data divalidasi

3. **Fitur Khusus**
   - Demonstrasikan import/export Excel
   - Tunjukkan audit trail untuk perubahan data
   - Jelaskan penggunaan UUID dan soft deletes

4. **Arsitektur Aplikasi**
   - Jelaskan pemisahan backend (Laravel) dan frontend (Vue.js)
   - Jelaskan bagaimana state management bekerja dengan Vuex
   - Jelaskan bagaimana autentikasi bekerja dengan Sanctum

5. **UI/UX**
   - Tunjukkan responsivitas UI dengan Tailwind CSS
   - Jelaskan penggunaan komponen Vue untuk reusability

## Persyaratan Sistem
- PHP 8.1+
- Laravel 10.x
- Vue.js 3.x
- Node.js 16+
- MySQL/PostgreSQL
