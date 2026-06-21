# Phase 1b: Database Seeders & Repository Setup

## Tujuan

Buat seeder untuk data default dan setup Repository Service Provider untuk dependency injection pattern.

## Scope

- Buat Factory untuk testing (User, Product, StockTransaction)
- Buat Seeder: RoleSeeder, UserSeeder, CategorySeeder, UnitSeeder, CompanyProfileSeeder, PermissionSeeder
- Setup Repository Service Provider untuk binding interface
- Buat interface dan implementation repository dasar

## Non-Goals (Eksplisit)

- TIDAK membuat migration (sudah di Phase 1a)
- TIDAK membuat model (sudah di Phase 1a)
- TIDAK membuat controller atau route apapun
- TIDAK membuat Livewire component
- TIDAK membuat form atau view

## Referensi ke PRD/Architecture

- **PRD Section 3.1 (User Roles):** 5 role yang harus di-seed
- **PRD Section 4.1 (Data Model):** Data default untuk categories, units, company profile
- **architecture.md Section 3 (Models & Tanggung Jawab):** Relasi dan $fillable untuk setiap model
- **architecture.md Section 5 (Services):** Repository pattern dan interface
- **tech-decisions.md Decision #002:** Spatie Permission dan PermissionSeeder

## Acceptance Criteria (Testable)

### AC-01: Factory

- [ ] Factory `UserFactory` berhasil dibuat dengan Faker untuk generate realistic data
- [ ] Factory `ProductFactory` berhasil dibuat dengan Faker untuk generate realistic data
- [ ] Factory `StockTransactionFactory` berhasil dibuat dengan Faker untuk generate realistic data
- [ ] Semua factory dapat digunakan untuk testing dengan `User::factory()->count(10)->create()`

### AC-02: RoleSeeder

- [ ] `RoleSeeder` menge-seed 5 role: admin, kasir, audit, manager, staff_toko
- [ ] Semua role berhasil disimpan di tabel `roles` Spatie Permission
- [ ] Role dapat di-assign ke user dengan `$user->assignRole('admin')`

### AC-03: UserSeeder

- [ ] `UserSeeder` menge-seed admin user default
- [ ] Admin user memiliki email: admin@inventaris.local
- [ ] Admin user memiliki password: password (hashed dengan bcrypt)
- [ ] Admin user memiliki role: admin
- [ ] Admin user dapat login dengan kredensial tersebut

### AC-04: CategorySeeder

- [ ] `CategorySeeder` menge-seed minimal 3 kategori default
- [ ] Kategori default contoh: Elektronik, Alat Tulis, Makanan
- [ ] Semua kategori memiliki `is_active = true`
- [ ] Kategori dapat digunakan di form create/edit produk

### AC-05: UnitSeeder

- [ ] `UnitSeeder` menge-seed minimal 3 satuan default
- [ ] Satuan default contoh: pcs, kg, liter
- [ ] Semua satuan memiliki `is_active = true`
- [ ] Satuan dapat digunakan di form create/edit produk

### AC-06: CompanyProfileSeeder

- [ ] `CompanyProfileSeeder` menge-seed company profile default
- [ ] Nama perusahaan default: "PT. Inventaris Sederhana"
- [ ] Company profile dapat di-edit di halaman settings
- [ ] Company profile digunakan di export PDF untuk header

### AC-07: PermissionSeeder

- [ ] `PermissionSeeder` menge-seed role dan permission default menggunakan Spatie
- [ ] Minimal 10 permission di-seed: view-dashboard, manage-products, manage-categories, manage-suppliers, manage-units, create-stock-in, create-stock-out, create-stock-adjustment, approve-transaction, manage-users, manage-settings, view-audit-trail, view-reports
- [ ] Role admin memiliki semua permission
- [ ] Role manager dan audit memiliki permission: view-dashboard, approve-transaction, view-reports, view-audit-trail
- [ ] Role kasir dan staff_toko memiliki permission: view-dashboard, create-stock-in, create-stock-out, view-reports

### AC-08: Repository Setup

- [ ] `RepositoryServiceProvider` berhasil bind 3 interface ke implementation: ProductRepositoryInterface, StockTransactionRepositoryInterface, AuditTrailRepositoryInterface
- [ ] Interface `ProductRepositoryInterface` didefinisikan dengan method signature untuk CRUD produk
- [ ] Interface `StockTransactionRepositoryInterface` didefinisikan dengan method signature untuk query transaksi stok
- [ ] Interface `AuditTrailRepositoryInterface` didefinisikan dengan method signature untuk query audit trail
- [ ] Implementation `ProductRepository` menggunakan Eloquent untuk query produk
- [ ] Implementation `StockTransactionRepository` menggunakan Eloquent untuk query transaksi
- [ ] Implementation `AuditTrailRepository` menggunakan Eloquent untuk query audit trail
- [ ] Semua interface dan implementation repository dapat di-inject via constructor tanpa error

### AC-09: Seeder Execution

- [ ] Semua seeder berjalan tanpa error dengan `php artisan db:seed`
- [ ] Data default berhasil diinsert ke database
- [ ] Admin user dapat login setelah seeder dijalankan
- [ ] Role dan permission berhasil di-assign

## Catatan untuk Agent

1. **Factory:** Gunakan Faker untuk generate realistic data (nama produk, harga, stok, dll)
2. **Password Hashing:** Gunakan `Hash::make('password')` untuk hash password admin di UserSeeder
3. **Spatie Permission:** Setelah seed roles, assign permissions menggunakan `$role->givePermissionTo('permission-name')`
4. **Repository Pattern:** Buat interface terlebih dahulu, baru implementation. Interface hanya mendefinisikan method signature, implementation menggunakan Eloquent
5. **Testing:** Setelah migration dan model selesai (Phase 1a), jalankan `php artisan migrate:fresh --seed` untuk verifikasi seeders bekerja
6. **Dependency:** Phase ini dependen ke Phase 1a (migrations & models) — migration harus sudah ada
7. **Next Phase:** Setelah Phase 1b selesai, Phase 2 (Authentication & Authorization) bisa dimulai — database dan seeders sudah ready
8. **Important:** Jangan seed data sample yang terlalu banyak — hanya data default yang diperlukan untuk testing dan development
