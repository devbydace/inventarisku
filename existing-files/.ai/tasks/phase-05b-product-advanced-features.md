# Phase 5b: Product Advanced Features

## Tujuan

Implementasi fitur lanjutan produk: relasi many-to-many dengan supplier, validasi harga, stok management, soft delete (archive), dan integrasi AuditTrail.

## Scope

- Implementasi relasi many-to-many dengan supplier (multi-select di form)
- Implementasi validasi: harga beli/jual required, stok minimum required
- Implementasi soft delete (archive) untuk produk
- Integrasi dengan AuditTrailService untuk pencatatan perubahan
- Update form create/edit untuk include supplier, harga, dan stok minimum
- Update view untuk menampilkan supplier, harga, dan stok

## Non-Goals (Eksplisit)

- TIDAK membuat CRUD dasar produk (sudah di Phase 5a)
- TIDAK membuat fitur import/export untuk produk (export di Phase 10)
- TIDAK membuat fitur search/filter untuk produk (di Phase 14)
- TIDAK membuat halaman detail produk (hanya list dan form)
- TIDAK membuat fitur barcode/QR code untuk produk
- TIDAK membuat stok management di form produk (stok hanya bisa diubah via transaksi stok masuk/keluar/adjustment)

## Referensi ke PRD/Architecture

- **PRD Section 5.1 (Manajemen Barang):** US-001 (CRUD Barang/Produk)
- **PRD Section 3.2 (Matrix Permission):** Manajemen Produk hanya untuk Admin
- **architecture.md Section 3.2 (Product Model):** Relasi belongsTo Category, Unit, belongsToMany Supplier, hasMany StockTransaction
- **architecture.md Section 2.5 (Products Table):** Detail kolom buy_price, sell_price, current_stock, min_stock
- **architecture.md Section 5.4 (AuditTrailService):** Pencatatan perubahan data master

## Acceptance Criteria (Testable)

### AC-01: Supplier Relation

- [ ] Form create/edit produk dapat memilih satu atau lebih supplier dari multi-select
- [ ] Dropdown supplier menampilkan semua supplier aktif dari database
- [ ] Sistem menyimpan relasi many-to-many produk-supplier di tabel pivot `product_supplier`
- [ ] Sistem mencegah duplicate relasi produk-supplier (unique constraint)
- [ ] Di halaman daftar produk, kolom supplier menampilkan semua supplier yang terhubung (dipisah koma)
- [ ] Jika produk belum memiliki supplier, menampilkan "-" atau "Belum ada supplier"

### AC-02: Price & Stock Validation

- [ ] Form create/edit produk memiliki field: harga beli (decimal, required, min 0), harga jual (decimal, required, min 0), stok minimum (integer, required, min 0)
- [ ] Form menolak input harga beli negatif — menampilkan error "Harga beli tidak boleh negatif"
- [ ] Form menolak input harga jual negatif — menampilkan error "Harga jual tidak boleh negatif"
- [ ] Form menolak input stok minimum negatif — menampilkan error "Stok minimum tidak boleh negatif"
- [ ] Harga beli dan harga jual dapat diisi 0 (untuk produk yang belum ada harga)
- [ ] Stok minimum default adalah 0 jika tidak diisi

### AC-03: Product Display Update

- [ ] Halaman daftar produk menampilkan kolom baru: supplier, harga beli, harga jual, stok saat ini, stok minimum
- [ ] Harga beli dan harga jual diformat dengan mata uang (sesuai setting aplikasi, default IDR)
- [ ] Stok saat ini ditampilkan dengan satuan dari relasi unit
- [ ] Jika stok saat ini di bawah stok minimum, ditampilkan dengan warna yang berbeda (misal: merah)
- [ ] Tabel tetap responsif dengan kolom yang lebih banyak

### AC-04: Soft Delete (Archive)

- [ ] Sistem menampilkan tombol "Archive" selain tombol "Edit" dan "Delete"
- [ ] Setelah klik "Archive", sistem menampilkan konfirmasi "Apakah Anda yakin ingin archive produk ini?"
- [ ] Setelah archive, produk memiliki `deleted_at` terisi
- [ ] Setelah archive, produk tidak muncul di daftar produk aktif
- [ ] Setelah archive, sistem menampilkan success notification
- [ ] Produk yang di-archive tetap ada di database untuk histori
- [ ] Produk yang di-archive tidak bisa di-edit (hanya bisa di-restore jika diperlukan di masa depan)

### AC-05: Audit Trail Integration

- [ ] Setiap create produk mencatat audit trail: user, action=create, entity_type=Product, entity_id, new_values (include: nama, SKU, kategori, satuan, supplier, harga beli, harga jual, stok minimum)
- [ ] Setiap update produk mencatat audit trail: user, action=update, entity_type=Product, entity_id, old_values, new_values
- [ ] Setiap archive produk mencatat audit trail: user, action=delete, entity_type=Product, entity_id, old_values
- [ ] Audit trail tercatat dengan user yang sedang login

### AC-06: Validation & Error Handling

- [ ] Form menolak input nama kosong — menampilkan error "Nama harus diisi"
- [ ] Form menolak input nama lebih dari 255 karakter — menampilkan error "Nama tidak boleh lebih dari 255 karakter"
- [ ] Form menolak input SKU kosong — menampilkan error "SKU harus diisi"
- [ ] Form menolak input SKU lebih dari 100 karakter — menampilkan error "SKU tidak boleh lebih dari 100 karakter"
- [ ] Form menolak input SKU yang sudah ada — menampilkan error "SKU sudah digunakan"
- [ ] Form menolak input harga beli negatif — menampilkan error "Harga beli tidak boleh negatif"
- [ ] Form menolak input harga jual negatif — menampilkan error "Harga jual tidak boleh negatif"
- [ ] Form menolak input stok minimum negatif — menampilkan error "Stok minimum tidak boleh negatif"
- [ ] Form menolak jika kategori tidak dipilih — menampilkan error "Kategori harus dipilih"
- [ ] Form menolak jika satuan tidak dipilih — menampilkan error "Satuan harus dipilih"
- [ ] Form menolak jika supplier tidak dipilih — menampilkan error "Supplier harus dipilih"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

### AC-07: Authorization

- [ ] Halaman daftar produk hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] Form create/edit/archive produk hanya bisa diakses oleh admin
- [ ] User yang belum login tidak bisa akses halaman produk (redirect ke login)

## Catatan untuk Agent

1. **Multi-Select Supplier:** Gunakan Livewire `select2` atau `tom-select` untuk multi-select supplier, atau gunakan checkbox list jika jumlah supplier sedikit
2. **Validation Rules:**
   - Nama: `required|string|max:255`
   - SKU: `required|string|max:100|unique:products,sku,NULL,id,deleted_at,NULL` (ignore soft delete)
   - Harga beli: `required|numeric|min:0`
   - Harga jual: `required|numeric|min:0`
   - Stok minimum: `required|integer|min:0`
   - Kategori: `required|exists:categories,id`
   - Satuan: `required|exists:units,id`
   - Supplier: `required|array|min:1` (minimal 1 supplier)
3. **Soft Delete:** Tambahkan `use SoftDeletes;` di model Product dan kolom `deleted_at` di migration (sudah ada dari Phase 1a)
4. **Eager Loading:** Di Index, gunakan `with(['category', 'unit', 'suppliers'])` untuk menghindari N+1 query
5. **Supplier Sync:** Gunakan `$product->suppliers()->sync($supplierIds)` untuk menyimpan relasi many-to-many
6. **Audit Trail Integration:** Panggil `AuditTrailService::log()` di setiap create/update/archive action
7. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 3 (categories & units), Phase 4 (suppliers), Phase 5a (product CRUD core), Phase 11 (audit trail) — semua migration dan relasi harus sudah ada
8. **Next Phase:** Setelah Phase 5b selesai, Phase 6 (Stock Transaction - Stock In/Out) bisa dimulai — produk sudah lengkap dengan harga, stok, dan supplier
9. **Testing:** Buat Feature test untuk CRUD produk dengan supplier, test harga validation, test soft delete, test audit trail
10. **Important:** Stok produk (current_stock) hanya bisa diubah via transaksi stok masuk/keluar/adjustment, TIDAK di form produk
