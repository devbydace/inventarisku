# Phase 6a: Stock In Transaction

## Tujuan

Implementasi form input stok masuk dengan validasi, penyimpanan transaksi dengan status pending, dan integrasi dengan supplier.

## Scope

- Buat Livewire component untuk Stock In: form input stok masuk
- Buat StockTransactionController (jika diperlukan untuk route)
- Implementasi validasi: jumlah harus lebih besar dari 0
- Implementasi penyimpanan transaksi dengan status pending
- Integrasi dengan StockService untuk business logic
- Integrasi dengan AuditTrailService untuk pencatatan transaksi
- Setup route dengan middleware auth (semua role bisa akses)
- Buat view Blade untuk form stock in

## Non-Goals (Eksplisit)

- TIDAK membuat form stok keluar (di Phase 6b)
- TIDAK membuat sistem approval/reject (di Phase 7)
- TIDAK membuat histori mutasi stok (di Phase 9)
- TIDAK membuat stock adjustment (di Phase 8)
- TIDAK membuat laporan stok (di Phase 9)
- TIDAK mengupdate stok produk secara langsung (stok hanya berubah setelah approved di Phase 7)

## Referensi ke PRD/Architecture

- **PRD Section 5.2 (Manajemen Stok):** US-005 (Pencatatan Stok Masuk)
- **PRD Section 3.2 (Matrix Permission):** Input Stok Masuk untuk semua role (admin, manager, audit, kasir, staff_toko)
- **architecture.md Section 3.7 (StockTransaction Model):** Relasi dan $fillable
- **architecture.md Section 5.1 (StockService):** Method createStockIn
- **architecture.md Section 4.1 (Route Design):** Route untuk stock/in dengan middleware auth

## Acceptance Criteria (Testable)

### AC-01: Stock In Form Display

- [ ] Halaman form stok masuk menampilkan field: barang (dropdown, required), jumlah (integer, required, min 1), supplier (dropdown, required), referensi/no PO (max 100 chars, optional), catatan (text, optional)
- [ ] Dropdown barang menampilkan hanya produk aktif (is_active = true)
- [ ] Dropdown supplier menampilkan semua supplier yang terhubung dengan produk yang dipilih
- [ ] Form memiliki validasi CSRF protection
- [ ] Tampilan form menggunakan Bahasa Indonesia

### AC-02: Stock In Validation

- [ ] Form menolak input jumlah 0 atau negatif — menampilkan error "Jumlah harus lebih besar dari 0"
- [ ] Form menolak jika barang tidak dipilih — menampilkan error "Barang harus dipilih"
- [ ] Form menolak jika supplier tidak dipilih — menampilkan error "Supplier harus dipilih"
- [ ] Form menolak input referensi lebih dari 100 karakter — menampilkan error "Referensi tidak boleh lebih dari 100 karakter"
- [ ] Form menolak input catatan lebih dari 500 karakter — menampilkan error "Catatan tidak boleh lebih dari 500 karakter"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

### AC-03: Stock In Submission

- [ ] Setelah submit, transaksi berstatus **pending** dan stok barang TIDAK berubah
- [ ] Sistem menampilkan success notification "Transaksi stok masuk berhasil dibuat dan menunggu approval"
- [ ] Setelah submit, form di-reset ke kondisi awal (blank)
- [ ] Transaksi menyimpan: product_id, user_id (pembuat), type='in', quantity, supplier_id, reference_no, notes, status='pending'
- [ ] User_id pembuat transaksi adalah user yang sedang login
- [ ] Timestamp created_at dan updated_at terisi otomatis

### AC-04: Dynamic Dropdown Logic

- [ ] Saat pilih barang, dropdown supplier otomatis menampilkan supplier yang terhubung dengan barang tersebut
- [ ] Jika barang belum memiliki supplier, dropdown supplier menampilkan semua supplier
- [ ] Jika barang diubah, dropdown supplier ter-update sesuai barang yang dipilih
- [ ] Dropdown barang menampilkan semua produk aktif tanpa filter stok (stok masuk bisa untuk produk dengan stok 0)

### AC-05: Authorization

- [ ] Halaman form stok masuk bisa diakses oleh semua role yang login (admin, manager, audit, kasir, staff_toko)
- [ ] User yang belum login tidak bisa akses form (redirect ke login)
- [ ] Semua role memiliki permission untuk create stok masuk

### AC-06: Audit Trail

- [ ] Setiap create transaksi stok masuk mencatat audit trail: user, action=create, entity_type=StockTransaction, entity_id, new_values (include: product_id, type, quantity, supplier_id, status)
- [ ] Audit trail tercatat dengan user yang sedang login
- [ ] Audit trail menyimpan semua data yang relevan untuk compliance

### AC-07: Data Display & UX

- [ ] Form menampilkan informasi barang yang dipilih (nama, SKU, stok saat ini) setelah memilih barang
- [ ] Form menampilkan informasi supplier yang dipilih (nama, kontak) setelah memilih supplier
- [ ] Tombol "Submit" menampilkan loading state saat proses submit
- [ ] Setelah submit, user dapat membuat transaksi baru tanpa harus refresh halaman

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Stock/` berisi `In.php`
2. **Form Structure:**
   - Barang: dropdown dengan `Product::where('is_active', true)->get()`
   - Jumlah: number input dengan min=1
   - Supplier: dropdown dengan `Supplier::all()` atau filter berdasarkan produk
   - Referensi/PO: text input (optional)
   - Catatan: textarea (optional)
3. **Validation Rules:**
   - Jumlah: `required|integer|min:1`
   - Barang: `required|exists:products,id`
   - Supplier: `required|exists:suppliers,id`
   - Referensi: `nullable|string|max:100`
   - Catatan: `nullable|string|max:500`
4. **StockService Integration:** Panggil `StockService::createStockIn()` untuk menyimpan transaksi
5. **Audit Trail Integration:** Panggil `AuditTrailService::log()` setelah transaksi berhasil disimpan
6. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 4 (suppliers), Phase 5 (products) — produk harus ada, supplier harus ada, user harus login
7. **Next Phase:** Setelah Phase 6a selesai, Phase 6b (Stock Out Transaction) bisa dimulai
8. **Testing:** Buat Feature test untuk create stok masuk dengan berbagai role, test validasi, test audit trail
9. **Important:** Jangan update stok produk di phase ini — stok hanya berubah setelah approval di Phase 7
