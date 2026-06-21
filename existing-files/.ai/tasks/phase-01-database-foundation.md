# Phase 1: Database Foundation

## Tujuan

Membangun fondasi database dengan membuat semua migrasi, model, seeder, factory, dan repository pattern untuk seluruh entitas yang dibutuhkan aplikasi inventaris.

## Scope

- Buat migration untuk 10 tabel: users, categories, suppliers, units, products, product_supplier, stock_transactions, approvals, audit_trails, company_profiles
- Buat Model Eloquent untuk setiap entitas dengan relasi yang sesuai
- Buat Seeder: RoleSeeder, UserSeeder (admin default), CategorySeeder, UnitSeeder, CompanyProfileSeeder
- Buat Factory untuk testing: UserFactory, ProductFactory, StockTransactionFactory
- Buat Repository pattern dengan interface dan implementasi dasar untuk Product, StockTransaction, dan AuditTrail
- Buat RepositoryServiceProvider untuk binding interface ke implementasi
- Jalankan migrasi dan seeder untuk verifikasi schema berjalan dengan benar

## Non-Goals (Eksplisit)

- TIDAK membuat Controller untuk CRUD operasi (itu di Phase 3-5)
- TIDAK membuat Livewire component (itu di Phase berikutnya)
- TIDAK membuat Form Request validation (itu di Phase 5-6)
- TIDAK membuat Approval flow logic (itu di Phase 7)
- TIDAK membuat Export functionality (itu di Phase 10)
- TIDAK membuat Reporting/Query logic (itu di Phase 9)
- TIDAK membuat Authentication setup (itu di Phase 2)
- TIDAK membuat Audit trail logging logic (itu di Phase 11)
- TIDAK membuat Dashboard logic (itu di Phase 13)
- TIDAK membuat Settings/Company Profile controller (itu di Phase 13)

## Referensi ke PRD/Architecture

- **PRD Section 4 (Data Model):** Entity Relationship Summary dan Key Relationships
- **PRD Section 7.3 (Database Schema):** Spesifikasi kolom untuk setiap tabel
- **architecture.md Section 2 (ERD):** Detail kolom, tipe data, constraint, dan index untuk setiap tabel
- **architecture.md Section 3 (Model):** Tanggung jawab, relasi, $fillable, dan business logic untuk setiap model
- **architecture.md Section 6.1 (Repository Pattern):** Interface dan implementasi repository
- **PRD Section 3.1 (User Roles):** 5 role: admin, kasir, audit, manager, staff_toko

## Acceptance Criteria (Testable)

### AC-01: Migrations

- [ ] Migration `create_users_table` berhasil dibuat dengan kolom: id, name, email, password, role (enum), is_active, email_verified_at, remember_token, timestamps
- [ ] Migration `create_categories_table` berhasil dibuat dengan kolom: id, name (unique), timestamps
- [ ] Migration `create_suppliers_table` berhasil dibuat dengan kolom: id, name, contact, address, email, phone, timestamps
- [ ] Migration `create_units_table` berhasil dibuat dengan kolom: id, name, abbreviation (unique), timestamps
- [ ] Migration `create_products_table` berhasil dibuat dengan kolom: id, name, sku (unique), category_id (FK), unit_id (FK), buy_price, sell_price, current_stock, min_stock, is_active, timestamps
- [ ] Migration `create_product_supplier_table` berhasil dibuat sebagai pivot table dengan unique constraint (product_id, supplier_id)
- [ ] Migration `create_stock_transactions_table` berhasil dibuat dengan kolom: id, product_id (FK), user_id (FK), type (enum: in/out/adjustment), quantity, reference_no, notes, status (enum: pending/approved/rejected), timestamps
- [ ] Migration `create_approvals_table` berhasil dibuat dengan kolom: id, stock_transaction_id (FK), user_id (FK), action (enum: approve/reject), notes, timestamps
- [ ] Migration `create_audit_trails_table` berhasil dibuat dengan kolom: id, user_id (FK), entity_type, entity_id, action (enum: create/update/delete), old_values (json), new_values (json), timestamps
- [ ] Migration `create_company_profiles_table` berhasil dibuat dengan kolom: id, company_name, address, logo_path, contact, date_format (enum), currency, timestamps
- [ ] Semua index sesuai architecture.md: idx_users_email, idx_users_role, idx_categories_name, idx_products_sku, dll
- [ ] Semua foreign key constraint sesuai ERD dengan onDelete sesuai kebutuhan

### AC-02: Models

- [ ] Model User memiliki $fillable: ['name', 'email', 'password', 'role', 'is_active']
- [ ] Model User memiliki relasi: hasMany(StockTransaction), hasMany(Approval), hasMany(AuditTrail)
- [ ] Model Product memiliki $fillable: ['name', 'sku', 'category_id', 'unit_id', 'buy_price', 'sell_price', 'current_stock', 'min_stock', 'is_active']
- [ ] Model Product memiliki relasi: belongsTo(Category), belongsTo(Unit), belongsToMany(Supplier), hasMany(StockTransaction)
- [ ] Model Category memiliki relasi: hasMany(Product)
- [ ] Model Supplier memiliki relasi: belongsToMany(Product)
- [ ] Model Unit memiliki relasi: hasMany(Product)
- [ ] Model StockTransaction memiliki relasi: belongsTo(Product), belongsTo(User), hasOne(Approval)
- [ ] Model Approval memiliki relasi: belongsTo(StockTransaction), belongsTo(User)
- [ ] Model AuditTrail memiliki relasi: belongsTo(User)
- [ ] Model CompanyProfile tidak memiliki relasi FK (single record)

### AC-03: Seeders

- [ ] RoleSeeder menge-seed 5 role: admin, kasir, audit, manager, staff_toko
- [ ] UserSeeder membuat user admin default dengan email: admin@inventaris.test, password: password (untuk development)
- [ ] CategorySeeder menge-seed minimal 3 kategori contoh: "Elektronik", "Alat Tulis", "Perlengkapan Kantor"
- [ ] UnitSeeder menge-seed minimal 5 satuan contoh: pcs, kg, liter, box, pack
- [ ] CompanyProfileSeeder membuat company profile default dengan nama "PT. Inventaris Ku"

### AC-04: Factories

- [ ] UserFactory dapat generate user dengan role acak
- [ ] ProductFactory dapat generate product dengan relasi ke category, unit, dan supplier
- [ ] StockTransactionFactory dapat generate transaksi dengan tipe dan status acak

### AC-05: Repository Pattern

- [ ] Interface ProductRepositoryInterface didefinisikan dengan method: findAll, findById, create, update, delete, findBySku
- [ ] Interface StockTransactionRepositoryInterface didefinisikan dengan method: findAll, findById, create, update, findByStatus, findByProduct
- [ ] Interface AuditTrailRepositoryInterface didefinisikan dengan method: log, findByEntity, findByUser, findByDateRange
- [ ] Implementasi ProductRepository mengimplementasikan interface dengan Eloquent
- [ ] Implementasi StockTransactionRepository mengimplementasikan interface dengan Eloquent
- [ ] Implementasi AuditTrailRepository mengimplementasikan interface dengan Eloquent
- [ ] RepositoryServiceProvider berhasil binding interface ke implementasi

### AC-06: Database Verification

- [ ] Semua migrasi berhasil dijalankan tanpa error
- [ ] Tabel users terbuat dengan kolom role enum sesuai: admin, kasir, audit, manager, staff_toko
- [ ] Tabel products terbuat dengan foreign key ke categories dan units
- [ ] Tabel product_supplier terbuat dengan unique constraint
- [ ] Tabel stock_transactions terbuat dengan foreign key ke products dan users
- [ ] Tabel approvals terbuat dengan foreign key ke stock_transactions dan users
- [ ] Tabel audit_trails terbuat dengan kolom json old_values dan new_values
- [ ] Seeder berhasil dijalankan tanpa error
- [ ] Data seeder muncul di database sesuai expectations

## Catatan untuk Agent

1. **Enum di MySQL:** Laravel migration menggunakan `enum()` untuk kolom dengan nilai tetap. Pastikan nilai enum sesuai PRD: role (admin, kasir, audit, manager, staff_toko), type (in, out, adjustment), status (pending, approved, rejected), action (approve, reject), date_format (Y-m-d, d/m/Y, m/d/Y)
2. **Soft Delete untuk Products:** Meskipun tidak ada kolom `deleted_at` di schema PRD, sebaiknya tambahkan `SoftDeletes` trait di model Product untuk mendukung archive (US-001 AC4)
3. **JSON Columns:** MySQL 5.7+ mendukung JSON column type. Pastikan kolom old_values dan new_values menggunakan tipe `json` di migration
4. **Foreign Key Naming:** Laravel akan otomatis generate nama foreign key, tapi pastikan konsisten dengan naming convention di architecture.md
5. **Repository Pattern:** Repository pattern bersifat opsional untuk MVP, tapi sudah ditentukan di architecture.md. Fokus ke interface yang clean dan mudah di-test
6. **Factory untuk Testing:** Factory digunakan untuk unit test dan feature test di phase berikutnya. Buat factory yang realistic dengan Faker
7. **Seeder untuk Development:** Seeder hanya untuk development environment. Jangan menyertakan sensitive data di seeder untuk production
8. **Dependency:** Phase ini adalah fondasi untuk seluruh aplikasi. Pastikan semua migrasi berjalan sebelum lanjut ke Phase 2
9. **Next Phase:** Setelah Phase 1 selesai, Phase 2 (Authentication & Authorization) bisa dimulai karena tabel users sudah ada
