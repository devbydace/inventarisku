# Phase 5a: Product CRUD Core

## Tujuan

Implementasi CRUD dasar produk dengan form create/edit, validasi, dan integrasi dengan kategori serta satuan.

## Scope

- Buat Livewire component untuk Product: Index, Create, Edit
- Buat ProductController (jika diperlukan untuk route)
- Implementasi validasi: nama required, SKU unique
- Implementasi validasi: kategori dan satuan harus dipilih
- Setup route dengan middleware role:admin
- Buat view Blade untuk setiap Livewire component

## Non-Goals (Eksplisit)

- TIDAK membuat relasi many-to-many dengan supplier (di Phase 5b)
- TIDAK membuat validasi harga beli/jual (di Phase 5b)
- TIDAK membuat stok management (stok hanya bisa diubah via transaksi stok masuk/keluar/adjustment)
- TIDAK membuat soft delete/archive (di Phase 5b)
- TIDAK membuat integrasi AuditTrail (di Phase 5b)
- TIDAK membuat fitur import/export untuk produk (export di Phase 10)
- TIDAK membuat fitur search/filter untuk produk (di Phase 14)
- TIDAK membuat halaman detail produk (hanya list dan form)
- TIDAK membuat fitur barcode/QR code untuk produk

## Referensi ke PRD/Architecture

- **PRD Section 5.1 (Manajemen Barang):** US-001 (CRUD Barang/Produk)
- **PRD Section 3.2 (Matrix Permission):** Manajemen Produk hanya untuk Admin
- **architecture.md Section 3.2 (Product Model):** Relasi belongsTo Category, Unit
- **architecture.md Section 2.5 (Products Table):** Detail kolom, tipe data, constraint, index

## Acceptance Criteria (Testable)

### AC-01: Product Index Page

- [ ] Halaman daftar produk menampilkan tabel dengan kolom: nama, SKU, kategori, satuan, aksi (edit, delete)
- [ ] Tabel menampilkan semua produk aktif (is_active = true)
- [ ] Data diurutkan berdasarkan created_at descending (yang paling baru di atas)
- [ ] Data ditampilkan dengan pagination (50 items per halaman)
- [ ] Jika tidak ada produk, menampilkan message "Tidak ada data produk"
- [ ] Setiap baris memiliki tombol "Edit" dan "Delete"

### AC-02: Product Create Form

- [ ] Halaman form create produk menampilkan field: nama (required, max 255 chars), SKU (required, unique, max 100 chars), kategori (dropdown, required), satuan (dropdown, required)
- [ ] Dropdown kategori menampilkan semua kategori aktif dari database
- [ ] Dropdown satuan menampilkan semua satuan aktif dari database
- [ ] Form memiliki validasi CSRF protection
- [ ] Setelah submit berhasil, sistem menampilkan success notification dan redirect ke daftar produk
- [ ] Setelah submit gagal, sistem menampilkan error message dan form tetap terisi

### AC-03: Product Edit Form

- [ ] Halaman form edit produk menampilkan data yang sudah ada (pre-filled)
- [ ] Form edit memiliki field yang sama dengan form create
- [ ] SKU dapat di-edit tetapi tetap harus unique (tidak boleh duplikat dengan produk lain)
- [ ] Setelah update berhasil, sistem menampilkan success notification dan redirect ke daftar produk
- [ ] Setelah update gagal, sistem menampilkan error message dan form tetap terisi

### AC-04: Product Delete

- [ ] Sistem menampilkan konfirmasi sebelum delete produk
- [ ] Setelah delete berhasil, sistem menampilkan success notification
- [ ] Produk yang di-delete tidak muncul di daftar produk lagi
- [ ] Delete menggunakan hard delete (tidak soft delete di phase ini)

### AC-05: Validation & Error Handling

- [ ] Form menolak input nama kosong — menampilkan error "Nama harus diisi"
- [ ] Form menolak input nama lebih dari 255 karakter — menampilkan error "Nama tidak boleh lebih dari 255 karakter"
- [ ] Form menolak input SKU kosong — menampilkan error "SKU harus diisi"
- [ ] Form menolak input SKU lebih dari 100 karakter — menampilkan error "SKU tidak boleh lebih dari 100 karakter"
- [ ] Form menolak input SKU yang sudah ada — menampilkan error "SKU sudah digunakan"
- [ ] Form menolak jika kategori tidak dipilih — menampilkan error "Kategori harus dipilih"
- [ ] Form menolak jika satuan tidak dipilih — menampilkan error "Satuan harus dipilih"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

### AC-06: Authorization

- [ ] Halaman daftar produk hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] Form create/edit/delete produk hanya bisa diakses oleh admin
- [ ] User yang belum login tidak bisa akses halaman produk (redirect ke login)

### AC-07: Data Display

- [ ] Tabel menampilkan nama kategori dari relasi belongsTo
- [ ] Tabel menampilkan nama satuan dari relasi belongsTo
- [ ] Format tampilan menggunakan Bahasa Indonesia
- [ ] Tabel responsif dan dapat dibaca dengan baik

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Product/` berisi `Index.php`, `Create.php`, `Edit.php`
2. **Validation Rules:**
   - Nama: `required|string|max:255`
   - SKU: `required|string|max:100|unique:products,sku,NULL,id,deleted_at,NULL` (ignore soft delete untuk compatibility)
   - Kategori: `required|exists:categories,id`
   - Satuan: `required|exists:units,id`
3. **Dropdown Data:** Gunakan `Category::where('is_active', true)->get()` dan `Unit::where('is_active', true)->get()` untuk populate dropdown
4. **Eager Loading:** Di Index, gunakan `with(['category', 'unit'])` untuk menghindari N+1 query
5. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 3 (categories & units) — migration harus ada, middleware role:admin harus ready
6. **Next Phase:** Setelah Phase 5a selesai, Phase 5b (Product Advanced Features) bisa dimulai — relasi supplier, harga, stok, dan soft delete akan ditambahkan
7. **Testing:** Buat Feature test untuk CRUD produk dengan role admin dan non-admin, termasuk test untuk SKU unique
8. **Important:** Di phase ini, produk belum memiliki harga beli/jual, stok, dan relasi supplier — itu akan ditambahkan di Phase 5b
