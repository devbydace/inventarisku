# Phase 3: Master Data - Categories & Units

## Tujuan

Implementasi CRUD kategori dan satuan barang, termasuk validasi, audit trail, dan role-based access control untuk kedua entity ini.

## Scope

- Buat Livewire component untuk Category: Index, Create, Edit
- Buat Livewire component untuk Unit: Index, Create, Edit
- Buat CategoryController dan UnitController (jika diperlukan untuk API/route)
- Implementasi validasi: nama unique, tidak boleh kosong
- Implementasi validasi: kategori tidak bisa dihapus jika masih ada produk terkait
- Implementasi validasi: satuan tidak bisa dihapus jika masih ada produk terkait
- Integrasi dengan AuditTrailService untuk pencatatan perubahan
- Setup route dengan middleware role:admin
- Buat view Blade untuk setiap Livewire component

## Non-Goals (Eksplisit)

- TIDAK membuat fitur import/export untuk kategori dan satuan
- TIDAK membuat fitur search/filter untuk kategori dan satuan (karena jumlah data kecil)
- TIDAK membuat halaman detail kategori atau satuan (hanya list dan form)
- TIDAK mengimplementasikan soft delete untuk kategori dan satuan (hanya hard delete dengan validasi)

## Referensi ke PRD/Architecture

- **PRD Section 5.1 (Manajemen Barang):** US-002 (CRUD Kategori Barang), US-004 (CRUD Satuan)
- **PRD Section 3.2 (Matrix Permission):** Manajemen Kategori dan Satuan hanya untuk Admin
- **architecture.md Section 3.3 (Category Model):** Relasi hasMany Product
- **architecture.md Section 3.5 (Unit Model):** Relasi hasMany Product
- **architecture.md Section 4.1 (Route Design):** Route resource untuk categories dan units dengan middleware role:admin
- **architecture.md Section 5.4 (AuditTrailService):** Pencatatan perubahan data master

## Acceptance Criteria (Testable)

### AC-01: Category CRUD

- [ ] Halaman daftar kategori menampilkan tabel dengan kolom: nama, jumlah barang, aksi (edit, delete)
- [ ] Form create/edit kategori memiliki field: nama (required, max 100 chars, unique)
- [ ] Sistem mencegah duplicate nama kategori — menampilkan error "Kategori dengan nama ini sudah ada"
- [ ] Sistem menampilkan konfirmasi sebelum delete kategori
- [ ] Sistem mencegah delete kategori yang masih memiliki barang terkait — menampilkan error "Kategori tidak dapat dihapus karena masih digunakan oleh X barang"
- [ ] Setelah create/edit berhasil, sistem menampilkan success notification dan redirect ke daftar kategori
- [ ] Setelah delete berhasil, sistem menampilkan success notification

### AC-02: Unit CRUD

- [ ] Halaman daftar satuan menampilkan tabel dengan kolom: nama, singkatan, jumlah barang, aksi (edit, delete)
- [ ] Form create/edit satuan memiliki field: nama (required, max 50 chars), singkatan (required, max 10 chars, unique)
- [ ] Sistem mencegah duplicate singkatan — menampilkan error "Satuan dengan singkatan ini sudah ada"
- [ ] Sistem menampilkan konfirmasi sebelum delete satuan
- [ ] Sistem mencegah delete satuan yang masih digunakan oleh barang — menampilkan error "Satuan tidak dapat dihapus karena masih digunakan oleh X barang"
- [ ] Setelah create/edit berhasil, sistem menampilkan success notification dan redirect ke daftar satuan
- [ ] Setelah delete berhasil, sistem menampilkan success notification

### AC-03: Authorization

- [ ] Halaman daftar kategori hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] Halaman daftar satuan hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] Form create/edit/delete kategori hanya bisa diakses oleh admin
- [ ] Form create/edit/delete satuan hanya bisa diakses oleh admin

### AC-04: Audit Trail

- [ ] Setiap create kategori mencatat audit trail: user, action=create, entity_type=Category, entity_id, new_values
- [ ] Setiap update kategori mencatat audit trail: user, action=update, entity_type=Category, entity_id, old_values, new_values
- [ ] Setiap delete kategori mencatat audit trail: user, action=delete, entity_type=Category, entity_id, old_values
- [ ] Setiap create satuan mencatat audit trail: user, action=create, entity_type=Unit, entity_id, new_values
- [ ] Setiap update satuan mencatat audit trail: user, action=update, entity_type=Unit, entity_id, old_values, new_values
- [ ] Setiap delete satuan mencatat audit trail: user, action=delete, entity_type=Unit, entity_id, old_values

### AC-05: Validation & Error Handling

- [ ] Form menolak input nama kosong — menampilkan error "Nama harus diisi"
- [ ] Form menolak input nama lebih dari 100 karakter (kategori) — menampilkan error "Nama tidak boleh lebih dari 100 karakter"
- [ ] Form menolak input nama lebih dari 50 karakter (satuan) — menampilkan error "Nama tidak boleh lebih dari 50 karakter"
- [ ] Form menolak input singkatan lebih dari 10 karakter — menampilkan error "Singkatan tidak boleh lebih dari 10 karakter"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat 2 folder: `app/Livewire/Category/` dan `app/Livewire/Unit/`, masing-masing berisi `Index.php`, `Create.php`, `Edit.php`
2. **Reusable Form:** Buat form reusable untuk category dan unit (karena struktur form mirip: nama + optional field)
3. **Validation:** Gunakan Laravel validation rules: `required`, `max:100`, `unique:categories,name,NULL,id,deleted_at,NULL` (untuk ignore soft delete jika ada)
4. **Count Query:** Untuk menampilkan "jumlah barang" di tabel, gunakan `Product::where('category_id', $id)->count()` — eager load jika perlu
5. **Audit Trail Integration:** Panggil `AuditTrailService::log()` di setiap create/update/delete action
6. **Dependency:** Phase ini dependen ke Phase 1 (database) dan Phase 2 (auth & middleware) — migration harus ada, middleware role:admin harus ready
7. **Next Phase:** Setelah Phase 3 selesai, Phase 4 (Master Data - Suppliers) bisa dimulai
8. **Testing:** Buat Feature test untuk setiap CRUD operation dengan role admin dan non-admin
