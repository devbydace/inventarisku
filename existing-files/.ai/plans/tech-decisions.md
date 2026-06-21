# Technical Decisions Log

Dokumen ini mencatat semua keputusan teknis yang diambil selama development, termasuk konteks, opsi yang dipertimbangkan, dan dampaknya.

---

## Decision #001: Database Queue untuk Export

**Tanggal:** 2026-06-21

**Konteks:**

- Export data (Excel/PDF) dengan data besar (>1000 records) memerlukan waktu processing yang lama
- Hosting environment adalah shared hosting/VPS entry-level (1 vCPU, 1-2GB RAM)
- Hosting belum support Redis atau Supervisor untuk queue worker permanen
- PRD mensyaratkan export Excel/CSV dan PDF untuk laporan stok

**Opsi yang Dipertimbangkan:**

1. **Synchronous Export dengan Chunk Processing**
   - Kelebihan: Sederhana, tidak perlu setup queue, mudah di-debug
   - Kekurangan: User harus menunggu export selesai, risiko timeout pada data besar

2. **Database Queue (Laravel Queue dengan database driver)**
   - Kelebihan: Tidak perlu Redis/Supervisor, export dijalankan di background, user tidak perlu menunggu
   - Kekurangan: Perlu setup database queue tables, perlu mekanisme notifikasi hasil export

3. **Redis Queue dengan Supervisor**
   - Kelebihan: Performa terbaik, processing sangat cepat
   - Kekurangan: Perlu install Redis dan Supervisor, tidak compatible dengan shared hosting entry-level

**Keputusan:** Menggunakan **Database Queue** untuk export data

**Alasan:**

- Hosting belum support Redis — sesuai constraint di project brief
- Database queue tidak memerlukan service tambahan (cuma butuh tabel `jobs` dan `failed_jobs`)
- User experience lebih baik — export dijalankan di background
- Sesuai dengan NFR: export 10.000 records harus selesai dalam 30 detik
- Database queue bisa di-trigger secara on-demand tanpa queue worker permanen (menggunakan `php artisan queue:work --once` atau scheduled job)

**Dampak ke Task/File Lain:**

- **architecture.md Section 7.5 (Deployment):** Perlu ditambahkan migration untuk `create_jobs_table` dan `create_failed_jobs_table`
- **architecture.md Section 5.3 (ExportService):** Perlu diubah dari synchronous return ke queued job dengan notifikasi
- **architecture.md Section 9.5 (Export Strategy):** Perlu di-update dari "synchronous export" ke "database queue export"
- **PRD Section 5.4 (Export Data):** Perlu ditambahkan mekanisme notifikasi "Export sedang diproses" dan "Export selesai"
- **Migration:** Perlu tambahkan migration untuk queue tables
- **Controller:** ExportController perlu mengembalikan response "Export sedang diproses" bukan file langsung

---

## Decision #002: Spatie Laravel Permission untuk Role & Permission Management

**Tanggal:** 2026-06-21

**Konteks:**

- Aplikasi memiliki 5 role yang tetap: admin, kasir, audit, manager, staff toko
- PRD mensyaratkan role-based access control (RBAC) dengan permission matrix yang jelas
- Architecture plan awal memilih manual role check dengan middleware custom untuk menghindari dependency

**Opsi yang Dipertimbangkan:**

1. **Manual Role Check dengan Middleware Custom**
   - Kelebihan: Tanpa dependency eksternal, penuh kontrol
   - Kekurangan: Perlu buat middleware sendiri, sulit maintain jika permission bertambah, tidak ada fitur advanced (permission assignment, role hierarchy)

2. **Spatie Laravel Permission Package**
   - Kelebihan: Fitur lengkap (role, permission, assignment), mudah maintain, support gate & policy, community besar
   - Kekurangan: Menambahkan 1 dependency eksternal, learning curve kecil

3. **Laravel Gate & Policy**
   - Kelebihan: Built-in Laravel, tanpa dependency
   - Kekurangan: Tidak ada management UI, perlu buat seeder untuk permission, kurang fleksibel untuk skala menengah

**Keputusan:** Menggunakan **Spatie Laravel Permission** (`spatie/laravel-permission` v6.0)

**Alasan:**

- Meskipun hanya 5 role yang tetap, menggunakan Spatie memudahkan manajemen permission di masa depan jika ada penambahan role/permission
- Spatie menyediakan fitur `hasRole()`, `hasPermissionTo()` yang konsisten di seluruh aplikasi
- Lebih mudah di-test — bisa mock permission dengan `->givePermissionTo()`
- Mengurangi boilerplate code — tidak perlu buat tabel `roles`, `permissions`, `model_has_roles`, `model_has_permissions` manual
- Sesuai dengan best practice Laravel untuk RBAC

**Dampak ke Task/File Lain:**

- **architecture.md Section 3.1 (User Model):** Perlu tambahkan trait `HasRoles` dari Spatie
- **architecture.md Section 3.2 (Matrix Permission):** Perlu diubah dari matrix manual ke Spatie permission seeding
- **architecture.md Section 4.1 (Route Design):** Middleware `role:admin,manager,audit` perlu diubah ke `permission:approve-transaction` atau `role:admin|manager|audit`
- **architecture.md Section 6.2 (Package):** Tambahkan `spatie/laravel-permission` ke Composer packages
- **Migration:** Perlu jalankan migration Spatie: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- **Seeder:** Perlu buat `PermissionSeeder` untuk seed role dan permission default
- **Middleware:** Custom `CheckRole` middleware bisa dihapus atau di-refactor menggunakan Spatie middleware
- **Model:** User model perlu tambahkan `use HasRoles;`

---

## Decision #003: Repository Pattern untuk Data Access Layer

**Tanggal:** 2026-06-21

**Konteks:**

- Aplikasi memiliki 3 entity utama dengan query kompleks: Product, StockTransaction, AuditTrail
- Query laporan memerlukan filter, join, dan aggregate yang kompleks
- Architecture plan awal memilih kombinasi Service Layer + Repository Pattern

**Opsi yang Dipertimbangkan:**

1. **Hanya Service Layer (Tanpa Repository)**
   - Kelebihan: Lebih sederhana, langsung query di service menggunakan Eloquent
   - Kekurangan: Query kompleks menumpuk di service, sulit di-test jika query berubah

2. **Service Layer + Repository Pattern**
   - Kelebihan: Separation of concerns, query terpisah dari business logic, mudah di-swap implementation
   - Kekurangan: Menambahkan layer abstraksi, lebih banyak file

3. **Query Builder / Eloquent Langsung di Controller**
   - Kelebihan: Paling sederhana, tidak ada layer tambahan
   - Kekurangan: Business logic tercampur dengan HTTP logic, sulit di-test, sulit maintain

**Keputusan:** Menggunakan **Repository Pattern** untuk data access layer

**Alasan:**

- Query laporan (ReportService) memerlukan filter dinamis (kategori, supplier, tanggal) — repository memudahkan manage query kompleks
- Repository memudahkan testing — bisa mock repository untuk test service tanpa hit database
- Jika di masa depan perlu ganti database (misal: ke PostgreSQL atau tambah cache), tinggal ganti repository implementation
- Separation of concerns — Service fokus ke business logic, Repository fokus ke data access
- Skala aplikasi (10 user concurrent, maksimal 10.000 records) masih cukup ringan untuk overhead repository

**Dampak ke Task/File Lain:**

- **architecture.md Section 3 (Models & Tanggung Jawab):** Tidak ada perubahan — model tetap sama
- **architecture.md Section 5 (Service & Business Logic):** Service akan menggunakan Repository Interface, bukan Model langsung
- **architecture.md Section 6.1 (Repository):** Perlu buat interface dan implementation untuk setiap repository
- **architecture.md Section 7.2 (Unit Tests):** Test untuk Service perlu mock Repository Interface
- **File Baru:**
  - `app/Repositories/Interfaces/ProductRepositoryInterface.php`
  - `app/Repositories/Interfaces/StockTransactionRepositoryInterface.php`
  - `app/Repositories/Interfaces/AuditTrailRepositoryInterface.php`
  - `app/Repositories/ProductRepository.php`
  - `app/Repositories/StockTransactionRepository.php`
  - `app/Repositories/AuditTrailRepository.php`
  - `app/Providers/RepositoryServiceProvider.php`
- **ServiceProvider:** Perlu bind interface ke implementation di `RepositoryServiceProvider`

---

## Summary Dampak ke Architecture Plan

| File                        | Section                        | Perubahan yang Diperlukan                            |
| --------------------------- | ------------------------------ | ---------------------------------------------------- |
| `.ai/plans/architecture.md` | Section 5.3 (Export Strategy)  | Ubah dari synchronous ke database queue              |
| `.ai/plans/architecture.md` | Section 6.2 (Packages)         | Tambahkan `spatie/laravel-permission`                |
| `.ai/plans/architecture.md` | Section 3.1 (User Model)       | Tambahkan trait `HasRoles`                           |
| `.ai/plans/architecture.md` | Section 4.1 (Route Middleware) | Update middleware untuk menggunakan Spatie           |
| `.ai/plans/architecture.md` | Section 5 (Service Layer)      | Service menggunakan Repository                       |
| Migration                   | Queue tables                   | Tambahkan migration untuk `jobs` dan `failed_jobs`   |
| Migration                   | Spatie tables                  | Jalankan migration Spatie Permission                 |
| Seeder                      | Permission seeder              | Buat `PermissionSeeder` untuk seed role & permission |
| Controller                  | ExportController               | Ubah ke queued export dengan notifikasi              |

---

## Catatan untuk Development Tim

1. **Database Queue Setup:** Pastikan `config/queue.php` menggunakan driver `database` untuk environment production
2. **Queue Worker:** Di shared hosting, bisa menggunakan cron job untuk menjalankan `php artisan queue:work --once` setiap menit, atau setup Supervisor jika VPS
3. **Spatie Permission:** Jangan lupa migrate dan seed permission setelah install package
4. **Repository Binding:** Semua repository harus di-bind di `RepositoryServiceProvider` untuk dependency injection
5. **Testing:** Unit test untuk Service perlu mock Repository Interface, bukan Model

---

_Dokumen ini akan di-update setiap kali ada keputusan teknis baru yang diambil selama development._
