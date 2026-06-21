# Phase 5: Master Data - Products

## Tujuan

Implementasi CRUD produk dengan relasi ke category, unit, dan supplier (many-to-many), termasuk validasi SKU unique, stok management, dan audit trail.

## Scope

- Buat Livewire component untuk Product: Index, Create, Edit
- Buat ProductController (jika diperlukan untuk route)
- Implementasi validasi: SKU unique, nama required, harga beli/jual required
- Implementasi relasi many-to-many dengan supplier (multi-select di form)
- Implementasi validasi: kategori dan satuan harus dipilih
- Implementasi soft delete (archive) untuk produk
- Integrasi dengan AuditTrailService untuk pencatatan perubahan
- Setup route dengan middleware role:admin
- Buat view Blade untuk setiap Livewire component

## Non-Goals (Eksplisit)

- TIDAK membuat fitur import/export untuk produk (export di Phase 10)
- TIDAK membuat fitur search/filter untuk produk (di Phase 14)
- TIDAK membuat halaman detail produk (hanya list dan form)
- TIDAK mengimplementasikan stok management di form produk (stok hanya bisa diubah via transaksi stok masuk/keluar/adjustment)
- TIDAK membuat fitur barcode/QR code untuk produk

## Referensi ke PRD/Architecture

- **PRD Section 5.1 (Manajemen Barang):** US-001 (CRUD Barang/Produk)
- **PRD Section 3.2 (Matrix Permission):** Manajemen Produk hanya untuk Admin
- **architecture.md Section 3.2 (Product Model):** Relasi belongsTo Category, Unit, belongsToMany Supplier, hasMany StockTransaction
- **architecture.md Section 2.5 (Products Table):** Detail kolom, tipe data, constraint, index
- **architecture.md Section 5.4 (AuditTrailService):** Pencatatan perubahan data master

## Acceptance Criteria (Testable)

### AC-01: Product CRUD

- [ ] Halaman daftar produk menampilkan tabel dengan kolom: nama, SKU, kategori, supplier, harga beli, harga jual, satuan, stok saat ini, stok minimum, status aktif/non-aktif, aksi (edit, archive)
- [ ] Form create/edit produk memiliki field: nama (required, max 255 chars), SKU (required, unique, max 100 chars), kategori (dropdown, required), supplier (multi-select, required), harga beli (decimal, required), harga jual (decimal, required), satuan (dropdown, required), stok minimum (integer, required, min 0), is_active (checkbox, default true)
- [ ] Sistem mencegah duplicate SKU — menampilkan error "SKU sudah digunakan"
- [ ] Sistem menampilkan konfirmasi sebelum archive produk
- [ ] Setelah archive, produk tidak muncul di daftar produk aktif tetapi tetap ada di database untuk histori
- [ ] Setelah create/edit berhasil, sistem menampilkan success notification dan redirect ke daftar produk
- [ ] Setelah archive berhasil, sistem menampilkan success notification

### AC-02: Product Relationships

- [ ] Form create/edit produk dapat memilih satu kategori dari dropdown
- [ ] Form create/edit produk dapat memilih satu satuan dari dropdown
- [ ] Form create/edit produk dapat memilih satu atau lebih supplier dari multi-select
- [ ] Sistem menyimpan relasi many-to-many produk-supplier di tabel pivot `product_supplier`
- [ ] Sistem mencegah duplicate relasi produk-supplier (unique constraint)

### AC-03: Authorization

- [ ] Halaman daftar produk hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] Form create/edit/archive produk hanya bisa diakses oleh admin

### AC-04: Audit Trail

- [ ] Setiap create produk mencatat audit trail: user, action=create, entity_type=Product, entity_id, new_values
- [ ] Setiap update produk mencatat audit trail: user, action=update, entity_type=Product, entity_id, old_values, new_values
- [ ] Setiap archive produk mencatat audit trail: user, action=delete, entity_type=Product, entity_id, old_values

### AC-05: Validation & Error Handling

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

### AC-06: Soft Delete (Archive)

- [ ] Produk yang di-archive memiliki `deleted_at` terisi
- [ ] Produk yang di-archive tidak muncul di daftar produk aktif
- [ ] Produk yang di-archive masih muncul di laporan historis (jika ada filter untuk include archived)
- [ ] Produk yang di-archive tidak bisa di-edit (hanya bisa di-restore jika diperlukan di masa depan)

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Product/` berisi `Index.php`, `Create.php`, `Edit.php`
2. **Multi-Select Supplier:** Gunakan Livewire `select2` atau `tom-select` untuk multi-select supplier, atau gunakan checkbox list jika jumlah supplier sedikit
3. **Validation Rules:**
   - Nama: `required|string|max:255`
   - SKU: `required|string|max:100|unique:products,sku,NULL,id,deleted_at,NULL` (ignore soft delete)
   - Harga beli: `required|numeric|min:0`
   - Harga jual: `required|numeric|min:0`
   - Stok minimum: `required|integer|min:0`
   - Kategori: `required|exists:categories,id`
   - Satuan: `required|exists:units,id`
   - Supplier: `required|array|min:1` (minimal 1 supplier)
4. **Soft Delete:** Tambahkan `use SoftDeletes;` di model Product dan kolom `deleted_at` di migration
5. **Eager Loading:** Di Index, gunakan `with(['category', 'unit', 'suppliers'])` untuk menghindari N+1 query
6. **Audit Trail Integration:** Panggil `AuditTrailService::log()` di setiap create/update/archive action
7. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 3 (categories & units), Phase 4 (suppliers) — semua migration dan relasi harus sudah ada
8. **Next Phase:** Setelah Phase 5 selesai, Phase 6 (Stock Transaction - Stock In/Out) bisa dimulai karena produk sudah ada
9. **Testing:** Buat Feature test untuk CRUD produk dengan role admin dan non-admin, termasuk test untuk SKU unique dan relasi many-to-many dengan supplier
