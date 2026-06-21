# Architecture Plan - Aplikasi Inventaris Sederhana

## 1. Struktur Folder Proyek

```
inventarisku/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── LogoutController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── UnitController.php
│   │   │   ├── StockTransactionController.php
│   │   │   ├── StockAdjustmentController.php
│   │   │   ├── ApprovalController.php
│   │   │   ├── ReportController.php
│   │   │   ├── UserController.php
│   │   │   ├── AuditTrailController.php
│   │   │   └── SettingsController.php
│   │   ├── Livewire/
│   │   │   ├── Product/
│   │   │   │   ├── Index.php
│   │   │   │   ├── Create.php
│   │   │   │   └── Edit.php
│   │   │   ├── Stock/
│   │   │   │   ├── In.php
│   │   │   │   ├── Out.php
│   │   │   │   └── Adjustment.php
│   │   │   ├── Approval/
│   │   │   │   └── Index.php
│   │   │   ├── Report/
│   │   │   │   ├── StockOnHand.php
│   │   │   │   ├── StockMutation.php
│   │   │   │   └── LowStock.php
│   │   │   └── Dashboard.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── EnsureUserIsActive.php
│   │   └── Requests/
│   │       ├── ProductRequest.php
│   │       ├── StockTransactionRequest.php
│   │       ├── ApprovalRequest.php
│   │       └── UserRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Supplier.php
│   │   ├── Unit.php
│   │   ├── ProductSupplier.php
│   │   ├── StockTransaction.php
│   │   ├── Approval.php
│   │   ├── AuditTrail.php
│   │   └── CompanyProfile.php
│   ├── Services/
│   │   ├── StockService.php
│   │   ├── ApprovalService.php
│   │   ├── ExportService.php
│   │   ├── AuditTrailService.php
│   │   └── ReportService.php
│   ├── Repositories/
│   │   ├ interfaces/
│   │   │   ├── ProductRepositoryInterface.php
│   │   │   ├── StockTransactionRepositoryInterface.php
│   │   │   └── AuditTrailRepositoryInterface.php
│   │   ├── ProductRepository.php
│   │   ├── StockTransactionRepository.php
│   │   └── AuditTrailRepository.php
│   └── Providers/
│       ├── RepositoryServiceProvider.php
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2026_01_01_000001_create_users_table.php
│   │   ├── 2026_01_01_000002_create_categories_table.php
│   │   ├── 2026_01_01_000003_create_suppliers_table.php
│   │   ├── 2026_01_01_000004_create_units_table.php
│   │   ├── 2026_01_01_000005_create_products_table.php
│   │   ├── 2026_01_01_000006_create_product_supplier_table.php
│   │   ├── 2026_01_01_000007_create_stock_transactions_table.php
│   │   ├── 2026_01_01_000008_create_approvals_table.php
│   │   ├── 2026_01_01_000009_create_audit_trails_table.php
│   │   └── 2026_01_01_000010_create_company_profiles_table.php
│   ├── seeders/
│   │   ├── RoleSeeder.php
│   │   ├── UserSeeder.php
│   │   ├── CategorySeeder.php
│   │   ├── UnitSeeder.php
│   │   └── CompanyProfileSeeder.php
│   └── factories/
│       ├── UserFactory.php
│       ├── ProductFactory.php
│       └── StockTransactionFactory.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   ├── dashboard.blade.php
│   │   ├── products/
│   │   │   ├── index.blade.php
│   │   │   └── form.blade.php
│   │   ├── stock/
│   │   │   ├── in.blade.php
│   │   │   ├── out.blade.php
│   │   │   └── adjustment.blade.php
│   │   ├── approvals/
│   │   │   └── index.blade.php
│   │   ├── reports/
│   │   │   ├── stock-on-hand.blade.php
│   │   │   ├── stock-mutation.blade.php
│   │   │   └── low-stock.blade.php
│   │   └── settings/
│   │       └── index.blade.php
│   └── css/
│       └── app.css
├── routes/
│   ├── web.php
│   └── auth.php
├── tests/
│   ├── Unit/
│   │   ├── Services/
│   │   │   ├── StockServiceTest.php
│   │   │   └── ApprovalServiceTest.php
│   │   └── Models/
│   │       └── ProductTest.php
│   ├── Feature/
│   │   ├── Auth/
│   │   │   └── LoginTest.php
│   │   ├── Stock/
│   │   │   ├── StockInTest.php
│   │   │   ├── StockOutTest.php
│   │   │   └── StockAdjustmentTest.php
│   │   ├── Approval/
│   │   │   └── ApprovalFlowTest.php
│   │   └── Report/
│   │       ├── StockReportTest.php
│   │       └── ExportTest.php
│   └── Integration/
│       └── ConcurrentApprovalTest.php
├── .env.example
├── composer.json
├── package.json
└── README.md
```

---

## 2. ERD (Entity Relationship Diagram)

### 2.1 Tabel Users

| Kolom             | Tipe Data                                                | Constraint                     | Keterangan                 |
| ----------------- | -------------------------------------------------------- | ------------------------------ | -------------------------- |
| id                | bigint                                                   | PK, Auto Increment             | Primary key                |
| name              | varchar(255)                                             | NOT NULL                       | Nama lengkap user          |
| email             | varchar(255)                                             | NOT NULL, UNIQUE               | Email untuk login          |
| password          | varchar(255)                                             | NOT NULL                       | Password (bcrypt)          |
| role              | enum('admin', 'kasir', 'audit', 'manager', 'staff_toko') | NOT NULL, DEFAULT 'staff_toko' | Role pengguna              |
| is_active         | boolean                                                  | NOT NULL, DEFAULT true         | Status aktif/non-aktif     |
| email_verified_at | timestamp                                                | NULL                           | Timestamp verifikasi email |
| remember_token    | varchar(100)                                             | NULL                           | Token "Remember Me"        |
| created_at        | timestamp                                                | NULL                           | Timestamp dibuat           |
| updated_at        | timestamp                                                | NULL                           | Timestamp diupdate         |

**Index:**

- `idx_users_email` (email)
- `idx_users_role` (role)
- `idx_users_is_active` (is_active)

---

### 2.2 Tabel Categories

| Kolom      | Tipe Data    | Constraint         | Keterangan         |
| ---------- | ------------ | ------------------ | ------------------ |
| id         | bigint       | PK, Auto Increment | Primary key        |
| name       | varchar(100) | NOT NULL, UNIQUE   | Nama kategori      |
| created_at | timestamp    | NULL               | Timestamp dibuat   |
| updated_at | timestamp    | NULL               | Timestamp diupdate |

**Index:**

- `idx_categories_name` (name)

**Relasi:**

- `categories.id` → `products.category_id` (One to Many)

---

### 2.3 Tabel Suppliers

| Kolom      | Tipe Data    | Constraint         | Keterangan         |
| ---------- | ------------ | ------------------ | ------------------ |
| id         | bigint       | PK, Auto Increment | Primary key        |
| name       | varchar(255) | NOT NULL           | Nama supplier      |
| contact    | varchar(100) | NULL               | Kontak person      |
| address    | text         | NULL               | Alamat             |
| email      | varchar(255) | NULL, email format | Email supplier     |
| phone      | varchar(20)  | NULL               | Nomor telepon      |
| created_at | timestamp    | NULL               | Timestamp dibuat   |
| updated_at | timestamp    | NULL               | Timestamp diupdate |

**Index:**

- `idx_suppliers_name` (name)
- `idx_suppliers_email` (email)

**Relasi:**

- `suppliers.id` → `product_supplier.supplier_id` (Many to Many via pivot table)

---

### 2.4 Tabel Units

| Kolom        | Tipe Data   | Constraint         | Keterangan                     |
| ------------ | ----------- | ------------------ | ------------------------------ |
| id           | bigint      | PK, Auto Increment | Primary key                    |
| name         | varchar(50) | NOT NULL           | Nama satuan (contoh: "Pieces") |
| abbreviation | varchar(10) | NOT NULL, UNIQUE   | Singkatan (contoh: "pcs")      |
| created_at   | timestamp   | NULL               | Timestamp dibuat               |
| updated_at   | timestamp   | NULL               | Timestamp diupdate             |

**Index:**

- `idx_units_abbreviation` (abbreviation)

**Relasi:**

- `units.id` → `products.unit_id` (One to Many)

---

### 2.5 Tabel Products

| Kolom         | Tipe Data     | Constraint                   | Keterangan             |
| ------------- | ------------- | ---------------------------- | ---------------------- |
| id            | bigint        | PK, Auto Increment           | Primary key            |
| name          | varchar(255)  | NOT NULL                     | Nama produk            |
| sku           | varchar(100)  | NOT NULL, UNIQUE             | Stock Keeping Unit     |
| category_id   | bigint        | FK → categories.id, NOT NULL | Relasi ke kategori     |
| unit_id       | bigint        | FK → units.id, NOT NULL      | Relasi ke satuan       |
| buy_price     | decimal(12,2) | NOT NULL, DEFAULT 0          | Harga beli             |
| sell_price    | decimal(12,2) | NOT NULL, DEFAULT 0          | Harga jual             |
| current_stock | integer       | NOT NULL, DEFAULT 0          | Stok saat ini          |
| min_stock     | integer       | NOT NULL, DEFAULT 0          | Stok minimum           |
| is_active     | boolean       | NOT NULL, DEFAULT true       | Status aktif/non-aktif |
| created_at    | timestamp     | NULL                         | Timestamp dibuat       |
| updated_at    | timestamp     | NULL                         | Timestamp diupdate     |

**Index:**

- `idx_products_sku` (sku)
- `idx_products_category_id` (category_id)
- `idx_products_is_active` (is_active)
- `idx_products_current_stock` (current_stock)

**Relasi:**

- `products.category_id` → `categories.id` (Many to One)
- `products.unit_id` → `units.id` (Many to One)
- `products.id` → `product_supplier.product_id` (Many to Many via pivot)
- `products.id` → `stock_transactions.product_id` (One to Many)

---

### 2.6 Tabel Product_Supplier (Pivot Table)

| Kolom       | Tipe Data | Constraint                  | Keterangan         |
| ----------- | --------- | --------------------------- | ------------------ |
| id          | bigint    | PK, Auto Increment          | Primary key        |
| product_id  | bigint    | FK → products.id, NOT NULL  | Relasi ke produk   |
| supplier_id | bigint    | FK → suppliers.id, NOT NULL | Relasi ke supplier |
| created_at  | timestamp | NULL                        | Timestamp dibuat   |
| updated_at  | timestamp | NULL                        | Timestamp diupdate |

**Unique Constraint:**

- `unique_product_supplier` (product_id, supplier_id)

**Relasi:**

- `product_supplier.product_id` → `products.id` (Many to Many)
- `product_supplier.supplier_id` → `suppliers.id` (Many to Many)

---

### 2.7 Tabel Stock_Transactions

| Kolom        | Tipe Data                               | Constraint                  | Keterangan                  |
| ------------ | --------------------------------------- | --------------------------- | --------------------------- |
| id           | bigint                                  | PK, Auto Increment          | Primary key                 |
| product_id   | bigint                                  | FK → products.id, NOT NULL  | Relasi ke produk            |
| user_id      | bigint                                  | FK → users.id, NOT NULL     | User yang membuat transaksi |
| type         | enum('in', 'out', 'adjustment')         | NOT NULL                    | Jenis transaksi             |
| quantity     | integer                                 | NOT NULL                    | Jumlah barang               |
| reference_no | varchar(100)                            | NULL                        | Nomor referensi/PO          |
| notes        | text                                    | NULL                        | Catatan tambahan            |
| status       | enum('pending', 'approved', 'rejected') | NOT NULL, DEFAULT 'pending' | Status transaksi            |
| created_at   | timestamp                               | NULL                        | Timestamp dibuat            |
| updated_at   | timestamp                               | NULL                        | Timestamp diupdate          |

**Index:**

- `idx_stock_transactions_product_id` (product_id)
- `idx_stock_transactions_user_id` (user_id)
- `idx_stock_transactions_status` (status)
- `idx_stock_transactions_created_at` (created_at)

**Relasi:**

- `stock_transactions.product_id` → `products.id` (Many to One)
- `stock_transactions.user_id` → `users.id` (Many to One)
- `stock_transactions.id` → `approvals.stock_transaction_id` (One to Many)

---

### 2.8 Tabel Approvals

| Kolom                | Tipe Data                 | Constraint                           | Keterangan               |
| -------------------- | ------------------------- | ------------------------------------ | ------------------------ |
| id                   | bigint                    | PK, Auto Increment                   | Primary key              |
| stock_transaction_id | bigint                    | FK → stock_transactions.id, NOT NULL | Relasi ke transaksi      |
| user_id              | bigint                    | FK → users.id, NOT NULL              | User yang approve/reject |
| action               | enum('approve', 'reject') | NOT NULL                             | Aksi yang dilakukan      |
| notes                | text                      | NULL                                 | Catatan/alasan           |
| created_at           | timestamp                 | NULL                                 | Timestamp dibuat         |
| updated_at           | timestamp                 | NULL                                 | Timestamp diupdate       |

**Index:**

- `idx_approvals_stock_transaction_id` (stock_transaction_id)
- `idx_approvals_user_id` (user_id)
- `idx_approvals_action` (action)

**Relasi:**

- `approvals.stock_transaction_id` → `stock_transactions.id` (Many to One)
- `approvals.user_id` → `users.id` (Many to One)

---

### 2.9 Tabel Audit_Trails

| Kolom       | Tipe Data                          | Constraint              | Keterangan                           |
| ----------- | ---------------------------------- | ----------------------- | ------------------------------------ |
| id          | bigint                             | PK, Auto Increment      | Primary key                          |
| user_id     | bigint                             | FK → users.id, NOT NULL | User yang melakukan aksi             |
| entity_type | varchar(100)                       | NOT NULL                | Tipe entity (Product, Category, dll) |
| entity_id   | bigint                             | NOT NULL                | ID dari entity                       |
| action      | enum('create', 'update', 'delete') | NOT NULL                | Aksi yang dilakukan                  |
| old_values  | json                               | NULL                    | Data sebelum perubahan               |
| new_values  | json                               | NULL                    | Data setelah perubahan               |
| created_at  | timestamp                          | NULL                    | Timestamp dibuat                     |
| updated_at  | timestamp                          | NULL                    | Timestamp diupdate                   |

**Index:**

- `idx_audit_trails_user_id` (user_id)
- `idx_audit_trails_entity` (entity_type, entity_id)
- `idx_audit_trails_created_at` (created_at)

**Relasi:**

- `audit_trails.user_id` → `users.id` (Many to One)

---

### 2.10 Tabel Company_Profiles

| Kolom        | Tipe Data                       | Constraint                | Keterangan                     |
| ------------ | ------------------------------- | ------------------------- | ------------------------------ |
| id           | bigint                          | PK, Auto Increment        | Primary key                    |
| company_name | varchar(255)                    | NOT NULL                  | Nama perusahaan                |
| address      | text                            | NULL                      | Alamat perusahaan              |
| logo_path    | varchar(255)                    | NULL                      | Path logo (storage/app/public) |
| contact      | varchar(255)                    | NULL                      | Kontak perusahaan              |
| date_format  | enum('Y-m-d', 'd/m/Y', 'm/d/Y') | NOT NULL, DEFAULT 'Y-m-d' | Format tanggal                 |
| currency     | varchar(10)                     | NOT NULL, DEFAULT 'IDR'   | Mata uang default              |
| created_at   | timestamp                       | NULL                      | Timestamp dibuat               |
| updated_at   | timestamp                       | NULL                      | Timestamp diupdate             |

**Relasi:**

- Tidak ada relasi FK (single record, diambil dengan `first()`)

---

### 2.11 ERD Diagram (Text Representation)

```
┌─────────────────┐       ┌─────────────────┐
│    Categories   │       │     Units       │
├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │
│ name            │       │ name            │
│ timestamps      │       │ abbreviation    │
└────────┬────────┘       │ timestamps      │
         │                └─────────────────┘
         │ 1                       1
         │                         │
         │ *                       │ *
         │                         │
┌────────▼─────────────────────────▼─────────┐
│                Products                     │
├─────────────────────────────────────────────┤
│ id (PK)                                     │
│ sku (UNIQUE)                                │
│ name                                        │
│ category_id (FK → categories.id)            │
│ unit_id (FK → units.id)                     │
│ buy_price                                   │
│ sell_price                                  │
│ current_stock                               │
│ min_stock                                   │
│ is_active                                   │
│ timestamps                                  │
└──────────┬──────────────────────────────────┘
           │ 1
           │
           │ *
           │
┌──────────▼──────────────────────────────────┐
│         Product_Supplier (Pivot)            │
├─────────────────────────────────────────────┤
│ id (PK)                                     │
│ product_id (FK → products.id)               │
│ supplier_id (FK → suppliers.id)             │
│ UNIQUE(product_id, supplier_id)             │
└──────────┬──────────────────────────────────┘
           │
           │ *
           │
┌──────────▼──────────────────────────────────┐
│              Suppliers                      │
├─────────────────────────────────────────────┤
│ id (PK)                                     │
│ name                                        │
│ contact                                     │
│ address                                     │
│ email                                       │
│ phone                                       │
│ timestamps                                  │
└─────────────────────────────────────────────┘

┌─────────────────┐       ┌─────────────────┐
│     Users       │       │ Stock_Trans     │
├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │
│ name            │       │ product_id (FK) │
│ email           │       │ user_id (FK)    │
│ password        │       │ type            │
│ role            │       │ quantity        │
│ is_active       │       │ reference_no    │
│ timestamps      │       │ notes           │
└────────┬────────┘       │ status          │
         │ 1              │ timestamps      │
         │                └────────┬────────┘
         │ *                       │ 1
         │                         │
         │                         │ *
         │                         │
┌────────▼─────────────────────────▼─────────┐
│              Approvals                      │
├─────────────────────────────────────────────┤
│ id (PK)                                     │
│ stock_transaction_id (FK)                   │
│ user_id (FK → users.id)                     │
│ action (approve/reject)                     │
│ notes                                       │
│ timestamps                                  │
└─────────────────────────────────────────────┘

┌─────────────────┐       ┌─────────────────┐
│     Users       │       │  Audit_Trails   │
├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │
│ ...             │       │ user_id (FK)    │
└────────┬────────┘       │ entity_type     │
         │ 1              │ entity_id       │
         │                │ action          │
         │ *              │ old_values      │
         │                │ new_values      │
         │                │ timestamps      │
         │                └─────────────────┘
         │
         │ *
         │
┌────────▼──────────────────────────────────┐
│         Stock_Transactions                 │
├─────────────────────────────────────────────┤
│ id (PK)                                    │
│ product_id (FK → products.id)              │
│ user_id (FK → users.id)                    │
│ type (in/out/adjustment)                   │
│ quantity                                   │
│ reference_no                               │
│ notes                                      │
│ status (pending/approved/rejected)         │
│ timestamps                                 │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│         Company_Profiles                    │
├─────────────────────────────────────────────┤
│ id (PK)                                    │
│ company_name                               │
│ address                                    │
│ logo_path                                  │
│ contact                                    │
│ date_format                                │
│ currency                                   │
│ timestamps                                 │
└─────────────────────────────────────────────┘
```

---

## 3. Daftar Model & Tanggung Jawab

### 3.1 User Model

**Tanggung Jawab:**

- Mengelola data autentikasi pengguna
- Menyimpan role dan status aktif
- Relasi ke stock transactions (sebagai pembuat)
- Relasi ke approvals (sebagai approver)
- Relasi ke audit trails (sebagai actor)

**Relasi:**

- `hasMany(StockTransaction::class)` — Transaksi yang dibuat user
- `hasMany(Approval::class)` — Approval yang dilakukan user
- `hasMany(AuditTrail::class)` — Audit trail yang dicatat

**$fillable:**

```php
['name', 'email', 'password', 'role', 'is_active']
```

---

### 3.2 Product Model

**Tanggung Jawab:**

- Menyimpan data master produk
- Mengelola stok saat ini dan stok minimum
- Relasi ke kategori, satuan, dan supplier
- Relasi ke stock transactions

**Relasi:**

- `belongsTo(Category::class)`
- `belongsTo(Unit::class)`
- `belongsToMany(Supplier::class)` via `product_supplier`
- `hasMany(StockTransaction::class)`

**$fillable:**

```php
['name', 'sku', 'category_id', 'unit_id', 'buy_price', 'sell_price', 'current_stock', 'min_stock', 'is_active']
```

**Business Logic:**

- `canDecreaseStock($quantity)` — Validasi apakah stok cukup untuk dikurangi
- `updateStock($quantity, $type)` — Update stok dengan atomic transaction

---

### 3.3 Category Model

**Tanggung Jawab:**

- Menyimpan kategori produk
- Relasi ke products

**Relasi:**

- `hasMany(Product::class)`

**$fillable:**

```php
['name']
```

---

### 3.4 Supplier Model

**Tanggung Jawab:**

- Menyimpan data supplier
- Relasi many-to-many ke products

**Relasi:**

- `belongsToMany(Product::class)` via `product_supplier`

**$fillable:**

```php
['name', 'contact', 'address', 'email', 'phone']
```

---

### 3.5 Unit Model

**Tanggung Jawab:**

- Menyimpan satuan pengukuran
- Relasi ke products

**Relasi:**

- `hasMany(Product::class)`

**$fillable:**

```php
['name', 'abbreviation']
```

---

### 3.6 ProductSupplier Model (Pivot)

**Tanggung Jawab:**

- Menghubungkan products dan suppliers (many-to-many)

**Relasi:**

- `belongsTo(Product::class)`
- `belongsTo(Supplier::class)`

**$fillable:**

```php
['product_id', 'supplier_id']
```

---

### 3.7 StockTransaction Model

**Tanggung Jawab:**

- Mencatat transaksi stok masuk/keluar/adjustment
- Menyimpan status pending/approved/rejected
- Relasi ke product, user, dan approval

**Relasi:**

- `belongsTo(Product::class)`
- `belongsTo(User::class, 'user_id')` — Pembuat transaksi
- `hasOne(Approval::class)`

**$fillable:**

```php
['product_id', 'user_id', 'type', 'quantity', 'reference_no', 'notes', 'status']
```

**Business Logic:**

- `isPending()` — Cek apakah transaksi masih pending
- `isApproved()` — Cek apakah transaksi sudah approved
- `canBeApprovedBy($userId)` — Cek apakah user bisa approve transaksi ini

---

### 3.8 Approval Model

**Tanggung Jawab:**

- Mencatat histori approval/reject transaksi
- Menyimpan siapa approver, kapan, dan catatan

**Relasi:**

- `belongsTo(StockTransaction::class)`
- `belongsTo(User::class, 'user_id')` — Approver

**$fillable:**

```php
['stock_transaction_id', 'user_id', 'action', 'notes']
```

---

### 3.9 AuditTrail Model

**Tanggung Jawab:**

- Mencatat semua perubahan data master
- Menyimpan data sebelum dan sesudah perubahan

**Relasi:**

- `belongsTo(User::class, 'user_id')` — Actor

**$fillable:**

```php
['user_id', 'entity_type', 'entity_id', 'action', 'old_values', 'new_values']
```

**Business Logic:**

- `static::log($user, $entityType, $entityId, $action, $oldValues, $newValues)` — Static method untuk membuat audit trail

---

### 3.10 CompanyProfile Model

**Tanggung Jawab:**

- Menyimpan konfigurasi profil perusahaan
- Single record (tidak ada relasi)

**$fillable:**

```php
['company_name', 'address', 'logo_path', 'contact', 'date_format', 'currency']
```

---

## 4. API/Route Design

### 4.1 Route Structure (web.php)

```php
// Authentication Routes
Route::get('/login', [LoginController::class 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Products (Admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('units', UnitController::class);
});

// Stock Transactions (Admin, Manager, Audit, Kasir, Staff Toko)
Route::middleware(['auth'])->group(function () {
    Route::get('/stock/in', [StockTransactionController::class, 'createIn'])->name('stock.in.create');
    Route::post('/stock/in', [StockTransactionController::class, 'storeIn'])->name('stock.in.store');
    Route::get('/stock/out', [StockTransactionController::class, 'createOut'])->name('stock.out.create');
    Route::post('/stock/out', [StockTransactionController::class, 'storeOut'])->name('stock.out.store');
});

// Stock Adjustment (Admin, Manager, Audit only)
Route::middleware(['auth', 'role:admin,manager,audit'])->group(function () {
    Route::get('/stock/adjustment', [StockAdjustmentController::class, 'create'])->name('stock.adjustment.create');
    Route::post('/stock/adjustment', [StockAdjustmentController::class, 'store'])->name('stock.adjustment.store');
});

// Approvals (Admin, Manager, Audit only)
Route::middleware(['auth', 'role:admin,manager,audit'])->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{transaction}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{transaction}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
});

// Reports (All authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/stock-on-hand', [ReportController::class, 'stockOnHand'])->name('reports.stock-on-hand');
    Route::get('/reports/stock-mutation', [ReportController::class, 'stockMutation'])->name('reports.stock-mutation');
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low-stock');
    Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
});

// My Transactions (Kasir, Staff Toko)
Route::middleware(['auth'])->group(function () {
    Route::get('/my-transactions', [StockTransactionController::class, 'myTransactions'])->name('transactions.my');
});

// Audit Trail (Admin, Manager, Audit only)
Route::middleware(['auth', 'role:admin,manager,audit'])->group(function () {
    Route::get('/audit-trails', [AuditTrailController::class, 'index'])->name('audit-trails.index');
});

// User Management (Admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
});

// Settings (Admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
```

### 4.2 Named Routes

Semua route menggunakan named route untuk kemudahan maintenance dan testing.

---

## 5. Service & Business Logic Layer

### 5.1 StockService

**Tanggung Jawab:**

- Menangani semua logika bisnis terkait stok
- Validasi stok sebelum transaksi
- Update stok dengan atomic transaction
- Mencegah stok negatif

**Methods:**

```php
class StockService
{
    public function createStockIn(array $data): StockTransaction;
    public function createStockOut(array $data): StockTransaction;
    public function approveStockTransaction(StockTransaction $transaction, User $approver): Approval;
    public function rejectStockTransaction(StockTransaction $transaction, User $approver, string $notes): Approval;
    public function validateStockAvailability(Product $product, int $quantity): bool;
    public function updateProductStock(Product $product, int $quantity, string $type): void;
}
```

**Alasan:**

- Memisahkan business logic dari controller
- Memudahkan testing (dapat di-test tanpa HTTP layer)
- Dapat digunakan oleh Livewire dan Controller

---

### 5.2 ApprovalService

**Tanggung Jawab:**

- Menangani proses approval/reject transaksi
- Validasi user bisa approve atau tidak
- Mencegah self-approval
- Menangani concurrent approval dengan row locking

**Methods:**

```php
class ApprovalService
{
    public function approve(StockTransaction $transaction, User $approver): Approval;
    public function reject(StockTransaction $transaction, User $approver, string $notes): Approval;
    public function canApprove(StockTransaction $transaction, User $user): bool;
    public function validateSelfApproval(StockTransaction $transaction, User $user): void;
}
```

**Alasan:**

- Business logic approval kompleks (validasi, row locking, transaction)
- Dapat digunakan di ApprovalController dan Livewire

---

### 5.3 ExportService

**Tanggung Jawab:**

- Menangani export data ke Excel/CSV dan PDF
- Format data sesuai kebutuhan
- Menambahkan header perusahaan di PDF

**Methods:**

```php
class ExportService
{
    public function exportProductsToExcel(): \Maatwebsite\Excel\Excel;
    public function exportProductsToPdf(): \Barryvdh\DomPDF\PDF;
    public function exportStockReportToExcel(string $type): \Maatwebsite\Excel\Excel;
    public function exportStockReportToPdf(string $type): \Barryvdh\DomPDF\PDF;
}
```

**Alasan:**

- Export logic terpisah dari controller
- Dapat digunakan di ReportController dan Livewire

---

### 5.4 AuditTrailService

**Tanggung Jawab:**

- Mencatat semua perubahan data master
- Menyimpan data sebelum dan sesudah perubahan

**Methods:**

```php
class AuditTrailService
{
    public static function log(User $user, string $entityType, int $entityId, string $action, ?array $oldValues, ?array $newValues): AuditTrail;
    public function getAuditTrails(string $entityType = null, int $entityId = null, string $userId = null, string $dateRange = null);
}
```

**Alasan:**

- Static method untuk memudahkan penggunaan di model events
- Konsolidasi logika pencarian audit trail

---

### 5.5 ReportService

**Tanggung Jawab:**

- Menangani query dan filter laporan
- Menghitung statistik untuk dashboard
- Filter berdasarkan kategori, supplier, tanggal, dll

**Methods:**

```php
class ReportService
{
    public function getStockOnHand(array $filters = []): Collection;
    public function getStockMutation(array $filters = []): Collection;
    public function getLowStock(): Collection;
    public function getDashboardStats(): array;
}
```

**Alasan:**

- Query logic terpisah dari controller
- Dapat digunakan di ReportController, Livewire, dan API

---

## 6. Package Pihak Ketiga

### 6.1 Composer Packages

| Package                     | Version | Kegunaan                     | Alasan                                                                                               |
| --------------------------- | ------- | ---------------------------- | ---------------------------------------------------------------------------------------------------- |
| `barryvdh/laravel-dompdf`   | ^2.0    | Export PDF                   | Library PDF generation terpopuler untuk Laravel, mudah digunakan                                     |
| `maatwebsite/excel`         | ^3.1    | Export Excel/CSV             | Library Excel terbaik untuk Laravel, support chunk processing                                        |
| `spatie/laravel-permission` | ^6.0    | Role & Permission management | Opsional, bisa diganti implementasi manual. Digunakan untuk memudahkan manajemen role dan permission |
| `laravel/breeze`            | ^2.0    | Authentication scaffolding   | Starter kit untuk autentikasi (login, logout, password reset)                                        |
| `livewire/livewire`         | ^3.0    | Frontend framework           | Framework untuk membuat UI interaktif tanpa JavaScript                                               |

### 6.2 NPM Packages

| Package       | Version | Kegunaan                                               |
| ------------- | ------- | ------------------------------------------------------ |
| `tailwindcss` | ^3.4    | CSS Framework                                          |
| `alpinejs`    | ^3.0    | JavaScript interaksi ringan (opsional, untuk Livewire) |
| `vite`        | ^5.0    | Asset bundler                                          |

---

## 7. Strategi Testing

### 7.1 Testing Pyramid

```
        /\
       /  \     E2E Tests (10%)
      /____\    - Full workflow testing
     /      \
    /________\  Integration Tests (30%)
   /          \ - Database integration
  /____________\ - API testing
 /
/______________\ Unit Tests (60%)
                  - Service layer
                  - Model logic
                  - Business rules
```

### 7.2 Unit Tests

**Coverage Target:** 80%+

**Fokus:**

- StockService (validasi stok, update stok)
- ApprovalService (validasi approval, self-approval check)
- AuditTrailService (logging)
- Model relationships dan accessors

**Contoh Test Cases:**

```php
// StockServiceTest
- test_cannot_decrease_stock_below_zero()
- test_stock_increases_when_approved()
- test_stock_decreases_when_approved()
- test_concurrent_approval_handled_correctly()

// ApprovalServiceTest
- test_cannot_approve_own_transaction()
- test_can_approve_others_transaction()
- test_reject_requires_notes()
```

---

### 7.3 Feature Tests

**Coverage Target:** 70%+

**Fokus:**

- Authentication flow (login, logout)
- Stock transaction flow (create, approve, reject)
- Approval flow dengan concurrent users
- Report generation dan export
- Role-based access control

**Contoh Test Cases:**

```php
// StockInTest
- test_user_can_create_stock_in_transaction()
- test_stock_not_updated_until_approved()
- test_stock_increases_after_approval()

// ApprovalFlowTest
- test_staff_cannot_approve_own_transaction()
- test_supervisor_can_approve_transaction()
- test_concurrent_approval_does_not_cause_negative_stock()

// ReportTest
- test_can_export_stock_report_to_excel()
- test_can_export_stock_report_to_pdf()
- test_report_filter_works_correctly()
```

---

### 7.4 Integration Tests

**Coverage Target:** 50%+

**Fokus:**

- Concurrent approval scenario (EC-16)
- Database transaction dan rollback
- Race condition handling
- Export dengan data besar

**Contoh Test Cases:**

```php
// ConcurrentApprovalTest
- test_concurrent_approval_for_low_stock_product()
- test_database_transaction_rollback_on_error()
- test_row_locking_prevents_race_condition()
```

---

### 7.5 Testing Tools

| Tool                    | Kegunaan                              |
| ----------------------- | ------------------------------------- |
| PHPUnit                 | Unit dan Feature testing              |
| Laravel Dusk (opsional) | E2E testing dengan browser automation |
| Faker                   | Generate fake data untuk testing      |
| Database Transactions   | Rollback database setelah setiap test |

---

## 8. Security Checklist

### 8.1 Authentication & Authorization

- [ ] Password di-hash menggunakan bcrypt (minimal 10 rounds)
- [ ] Session timeout setelah 2 jam inactivity atau 8 jam total
- [ ] "Remember Me" menggunakan token yang aman
- [ ] Role-based access di-enforce di backend (bukan hanya frontend)
- [ ] Middleware `CheckRole` untuk setiap route yang membutuhkan role tertentu
- [ ] Admin tidak bisa menghapus akun sendiri
- [ ] Password temporary harus diganti pada login pertama kali

### 8.2 Input Validation & Sanitization

- [ ] Semua input divalidasi menggunakan Form Request
- [ ] SQL Injection dicegah menggunakan Eloquent ORM dan parameterized query
- [ ] XSS dicegah menggunakan escaping output dari Blade/Livewire
- [ ] CSRF protection menggunakan Laravel CSRF token
- [ ] File upload divalidasi (max 2MB, format jpg/png)
- [ ] Email divalidasi menggunakan email format

### 8.3 Data Protection

- [ ] Sensitive data (password) tidak pernah ditampilkan
- [ ] Audit trail mencatat semua perubahan data master
- [ ] Soft delete untuk products (archive, bukan hard delete)
- [ ] Database backup minimal 1x sehari dengan retensi 7 hari
- [ ] HTTPS enforced di production

### 8.4 Access Control

- [ ] Staff/Kasir tidak bisa approve transaksi sendiri (validasi: user_id pembuat ≠ user_id approver)
- [ ] Staff/Kasir tidak bisa akses halaman approval (403 Forbidden)
- [ ] Hanya Admin yang bisa CRUD master data (category, supplier, unit, user)
- [ ] Hanya Admin yang bisa manage settings
- [ ] Export hanya bisa dilakukan oleh user yang login

### 8.5 API Security (jika ada API di masa depan)

- [ ] API authentication menggunakan Sanctum atau Passport
- [ ] Rate limiting untuk API endpoints
- [ ] Input sanitization untuk API requests

---

## 9. Keputusan Teknis & Alasannya

### 9.1 Service Layer Pattern vs Repository Pattern

**Keputusan:** Menggunakan **Service Layer Pattern** dengan **Repository Pattern** untuk data access.

**Alasan:**

- Service Layer memudahkan pengujian business logic secara terpisah dari controller
- Repository Pattern memudahkan pengelolaan query kompleks dan dapat di-swap jika diperlukan
- Kombinasi keduanya memberikan separation of concerns yang jelas

**Alternatif yang Dipertimbangkan:**

1. **Hanya Controller Logic** — Ditolak karena business logic akan tercampur dengan HTTP logic, sulit di-test
2. **Hanya Repository Pattern** — Ditolak karena repository hanya fokus pada data access, tidak mencakup business logic
3. **Action Pattern (Laravel Actions)** — Dipertimbangkan, tapi untuk MVP ini Service Layer sudah cukup

---

### 9.2 Livewire vs Traditional Controller + Blade

**Keputusan:** Menggunakan **Livewire** untuk UI interaktif.

**Alasan:**

- Livewire memungkinkan pembuatan UI interaktif tanpa JavaScript
- Cocok untuk aplikasi internal dengan skala kecil (10 user concurrent)
- Mengurangi development time karena tidak perlu API terpisah
- State management otomatis

**Alternatif yang Dipertimbangkan:**

1. **Traditional Controller + Blade** — Ditolak karena memerlukan lebih banyak kode JavaScript untuk interaksi
2. **Inertia.js + Vue/React** — Ditolak karena terlalu kompleks untuk MVP
3. **Full SPA (Vue/React)** — Ditolak karena memerlukan setup API terpisah dan lebih lama development time

---

### 9.3 Spatie Laravel Permission vs Manual Role Check

**Keputusan:** Menggunakan **manual role check** dengan middleware custom.

**Alasan:**

- Aplikasi hanya punya 5 role yang tetap
- Tidak perlu fitur advanced permission management
- Mengurangi dependency eksternal
- Lebih mudah di-maintain untuk skala kecil

**Alternatif yang Dipertimbangkan:**

1. **Spatie Laravel Permission** — Dipertimbangkan, tapi dianggap overkill untuk 5 role yang tetap
2. **Gate & Policy** — Dipertimbangkan, tapi untuk MVP manual middleware sudah cukup

---

### 9.4 Database Transaction Strategy

**Keputusan:** Menggunakan **database transaction dengan row locking (SELECT ... FOR UPDATE)** saat approval.

**Alasan:**

- Mencegah race condition pada concurrent approval (EC-16)
- Memastikan stok tidak menjadi negatif
- Atomic operation memastikan data konsisten

**Alternatif yang Dipertimbangkan:**

1. **Optimistic Locking** — Ditolak karena memerlukan kolom `version` di setiap tabel dan lebih kompleks
2. **Queue-based Approval** — Ditolak karena tidak diperlukan untuk 10 user concurrent
3. **Tidak ada locking** — Ditolak karena berisiko menyebabkan stok negatif

---

### 9.5 Export Strategy: Synchronous vs Queue

**Keputusan:** Menggunakan **synchronous export** dengan chunk processing untuk data besar.

**Alasan:**

- Aplikasi hanya untuk 10 user concurrent
- Data maksimal 10.000 records (sesuai NFR)
- Synchronous lebih sederhana dan mudah di-debug
- Chunk processing mencegah timeout

**Alternatif yang Dipertimbangkan:**

1. **Queue-based Export** — Dipertimbangkan untuk data >10.000 records, tapi untuk MVP synchronous sudah cukup
2. **Streaming Export** — Ditolah karena lebih kompleks dan tidak diperlukan untuk skala ini

---

### 9.6 Audit Trail: JSON vs Separate Table

**Keputusan:** Menggunakan **JSON columns** (`old_values` dan `new_values`) di tabel `audit_trails`.

**Alasan:**

- Fleksibel untuk berbagai entity (Product, Category, Supplier, dll)
- Mudah di-query untuk perubahan spesifik
- Tidak perlu tabel terpisah untuk setiap entity

**Alternatif yang Dipertimbangkan:**

1. **Separate table per entity** — Ditolak karena terlalu banyak tabel dan tidak fleksibel
2. **Text column** — Ditolak karena sulah di-query dan tidak ada struktur

---

### 9.7 Stock Adjustment: Separate Table vs Type in Stock Transactions

**Keputusan:** Menggunakan **tabel terpisah `stock_adjustments`** (sesuai PRD asli).

**Alasan:**

- Sesuai dengan PRD yang sudah ditentukan
- Memudahkan tracking adjustment secara khusus
- Tidak mengacaukan tabel `stock_transactions` yang sudah ada

**Alternatif yang Dipertimbangkan:**

1. **Hanya tabel `stock_transactions` dengan type 'adjustment'** — Dipertimbangkan, tapi PRD sudah menentukan tabel terpisah
2. **Tidak ada tabel adjustment** — Ditolak karena adjustment adalah fitur penting

---

### 9.8 Password Reset: Temporary Password vs Email Token

**Keputuan:** Menggunakan **temporary password** yang digenerate oleh admin.

**Alasan:**

- Sesuai PRD (tidak ada notifikasi eksternal)
- Admin langsung memberikan password temporary ke user
- Lebih sederhana untuk skala kecil (10 user)

**Alternatif yang Dipertimbangkan:**

1. **Email token reset** — Ditolak karena PRD menyebutkan tidak ada notifikasi eksternal
2. **Security questions** — Ditolak karena kurang aman

---

## 10. Deployment Strategy

### 10.1 Environment

- **Shared Hosting / VPS Entry-level** (1 vCPU, 1-2GB RAM)
- **PHP 8.2+**
- **MySQL 8.0+**
- **Nginx atau Apache**
- **Composer**

### 10.2 Storage

- **Logo dan export temporary:** `storage/app/public`
- **Symlink:** `public/storage` → `storage/app/public`

### 10.3 Queue (Opsional)

- **Database Queue** untuk export besar (jika diperlukan)
- Tidak memerlukan queue worker permanen

### 10.4 Backup Strategy

- **Database backup:** Minimal 1x sehari
- **Retention:** 7 hari
- **Method:** `mysqldump` atau Laravel backup package

---

## 11. Monitoring & Maintenance

### 11.1 Logging

- **Laravel Log:** Menyimpan error dan exception
- **Audit Trail:** Menyimpan semua perubahan data master
- **Approval History:** Menyimpan semua aksi approve/reject

### 11.2 Performance Monitoring

- **Dashboard load time:** Target < 2 detik
- **Database query log:** Monitor slow query
- **Export time:** Target < 30 detik untuk 10.000 records

### 11.3 Maintenance

- **PSR-12 coding standard**
- **Named routes** untuk kemudahan maintenance
- **Database migration** untuk semua perubahan schema
- **Dokumentasi endpoint** (jika ada API)

---

_Dokumen ini dibuat untuk memandu development tim dan memastikan konsistensi arsitektur throughout aplikasi._
