# Phase 4: Master Data - Suppliers

## Tujuan

Implementasi CRUD supplier dengan relasi many-to-many ke products, termasuk validasi dan audit trail untuk manajemen data supplier.

## Scope

- Buat Livewire component untuk Supplier: Index, Create, Edit
- Buat SupplierController (jika diperlukan untuk route)
- Implementasi validasi: nama required, email format, phone format
- Implementasi relasi many-to-many dengan products (tanpa product management dulu, hanya supplier management)
- Integrasi dengan AuditTrailService untuk pencatatan perubahan
- Setup route dengan middleware role:admin
- Buat view Blade untuk setiap Livewire component

## Non-Goals (Eksplisit)

- TIDAK membuat fitur import/export untuk supplier
- TIDAK membuat fitur search/filter untuk supplier (karena jumlah data kecil)
- TIDAK membuat halaman detail supplier (hanya list dan form)
- TIDAK mengimplementasikan soft delete untuk supplier (hanya hard delete)
- TIDAK membuat relasi ke product di form supplier (relasi many-to-many akan di-handle di Phase 5)

## Referensi ke PRD/Architecture

- **PRD Section 5.1 (Manajemen Barang):** US-003 (CRUD Supplier)
- **PRD Section 3.2 (Matrix Permission):** Manajemen Supplier hanya untuk Admin
- **architecture.md Section 3.4 (Supplier Model):** Relasi belongsToMany Product
- **architecture.md Section 2.3 (Supplier Table):** Detail kolom, tipe data, constraint
- **architecture.md Section 5.4 (AuditTrailService):** Pencatatan perubahan data master

## Acceptance Criteria (Testable)

### AC-01: Supplier CRUD

- [ ] Halaman daftar supplier menampilkan tabel dengan kolom: nama, kontak, alamat, email, telepon, jumlah barang, aksi (edit, delete)
- [ ] Form create/edit supplier memiliki field: nama (required, max 255 chars), kontak (max 100 chars, optional), alamat (text, optional), email (email format, optional), telepon (max 20 chars, optional)
- [ ] Sistem validasi email format — jika tidak valid, menampilkan error "Format email tidak valid"
- [ ] Sistem menampilkan konfirmasi sebelum delete supplier
- [ ] Setelah create/edit berhasil, sistem menampilkan success notification dan redirect ke daftar supplier
- [ ] Setelah delete berhasil, sistem menampilkan success notification

### AC-02: Authorization

- [ ] Halaman daftar supplier hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] Form create/edit/delete supplier hanya bisa diakses oleh admin

### AC-03: Audit Trail

- [ ] Setiap create supplier mencatat audit trail: user, action=create, entity_type=Supplier, entity_id, new_values
- [ ] Setiap update supplier mencatat audit trail: user, action=update, entity_type=Supplier, entity_id, old_values, new_values
- [ ] Setiap delete supplier mencatat audit trail: user, action=delete, entity_type=Supplier, entity_id, old_values

### AC-04: Validation & Error Handling

- [ ] Form menolak input nama kosong — menampilkan error "Nama harus diisi"
- [ ] Form menolak input nama lebih dari 255 karakter — menampilkan error "Nama tidak boleh lebih dari 255 karakter"
- [ ] Form menolak input email yang tidak sesuai format — menampilkan error "Format email tidak valid"
- [ ] Form menolak input telepon lebih dari 20 karakter — menampilkan error "Telepon tidak boleh lebih dari 20 karakter"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Supplier/` berisi `Index.php`, `Create.php`, `Edit.php`
2. **Reusable Form:** Form supplier bisa menggunakan struktur yang sama dengan category/unit (nama + optional fields)
3. **Validation Rules:**
   - Nama: `required|string|max:255`
   - Email: `nullable|email|max:255`
   - Telepon: `nullable|string|max:20`
   - Kontak: `nullable|string|max:100`
4. **Count Query:** Untuk menampilkan "jumlah barang" di tabel, gunakan `Product::whereHas('suppliers', function($q) { $q->where('suppliers.id', $id); })->count()` — eager load jika perlu
5. **Audit Trail Integration:** Panggil `AuditTrailService::log()` di setiap create/update/delete action
6. **Dependency:** Phase ini dependen ke Phase 1 (database) dan Phase 2 (auth & middleware) — migration suppliers dan product_supplier harus ada, middleware role:admin harus ready
7. **Next Phase:** Setelah Phase 4 selesai, Phase 5 (Master Data - Products) bisa dimalkan — products akan menggunakan relasi many-to-many dengan supplier
8. **Testing:** Buat Feature test untuk CRUD supplier dengan role admin dan non-admin
9. **Future Integration:** Di Phase 5, form product akan memiliki multi-select supplier yang menggunakan relasi many-to-many yang sudah didefinisikan di sini
