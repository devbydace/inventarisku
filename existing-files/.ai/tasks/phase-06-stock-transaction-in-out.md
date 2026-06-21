# Phase 6: Stock Transaction - Stock In/Out

## Tujuan

Implementasi form input stok masuk dan keluar dengan status pending, validasi stok, dan penyimpanan transaksi yang memerlukan approval.

## Scope

- Buat Livewire component untuk Stock In: form input stok masuk
- Buat Livewire component untuk Stock Out: form input stok keluar
- Buat StockTransactionController (jika diperlukan untuk route)
- Implementasi validasi: stok tidak boleh negatif untuk stok keluar
- Implementasi validasi: jumlah harus lebih besar dari 0
- Implementasi penyimpanan transaksi dengan status pending
- Integrasi dengan StockService untuk business logic
- Integrasi dengan AuditTrailService untuk pencatatan transaksi
- Setup route dengan middleware auth (semua role bisa akses)
- Buat view Blade untuk setiap Livewire component

## Non-Goals (Eksplisit)

- TIDAK membuat sistem approval/reject (di Phase 7)
- TIDAK membuat histori mutasi stok (di Phase 9)
- TIDAK membuat stock adjustment (di Phase 8)
- TIDAK membuat laporan stok (di Phase 9)
- TIDAK mengupdate stok produk secara langsung (stok hanya berubah setelah approved di Phase 7)

## Referensi ke PRD/Architecture

- **PRD Section 5.2 (Manajemen Stok):** US-005 (Pencatatan Stok Masuk), US-006 (Pencatatan Stok Keluar), US-007 (Validasi Stok Tidak Negatif)
- **PRD Section 3.2 (Matrix Permission):** Input Stok Masuk dan Keluar untuk semua role (admin, manager, audit, kasir, staff_toko)
- **architecture.md Section 3.7 (StockTransaction Model):** Relasi dan $fillable
- **architecture.md Section 5.1 (StockService):** Method createStockIn, createStockOut, validateStockAvailability
- **architecture.md Section 4.1 (Route Design):** Route untuk stock/in dan stock/out dengan middleware auth

## Acceptance Criteria (Testable)

### AC-01: Stock In Form

- [ ] Halaman form stok masuk menampilkan field: barang (dropdown, required), jumlah (integer, required, min 1), supplier (dropdown, required), referensi/no PO (max 100 chars, optional), catatan (text, optional)
- [ ] Dropdown barang menampilkan hanya produk aktif (is_active = true)
- [ ] Dropdown supplier menampilkan semua supplier yang terhubung dengan produk yang dipilih
- [ ] Setelah submit, transaksi berstatus **pending** dan stok barang TIDAK berubah
- [ ] Sistem menampilkan success notification "Transaksi stok masuk berhasil dibuat dan menunggu approval"
- [ ] Setelah submit, form di-reset ke kondisi awal (blank)

### AC-02: Stock Out Form

- [ ] Halaman form stok keluar menampilkan field: barang (dropdown, required), jumlah (integer, required, min 1), alasan/kategori keluar (dropdown: penjualan, rusak, adjustment, lainnya, required), catatan (text, optional)
- [ ] Sistem menampilkan stok saat ini dari barang yang dipilih sebagai informasi (misal: "Stok saat ini: 50 pcs")
- [ ] Dropdown barang menampilkan hanya produk aktif (is_active = true)
- [ ] Setelah submit, transaksi berstatus **pending** dan stok barang TIDAK berubah
- [ ] Sistem menampilkan success notification "Transaksi stok keluar berhasil dibuat dan menunggu approval"
- [ ] Setelah submit, form di-reset ke kondisi awal (blank)

### AC-03: Validasi Stok Tidak Negatif

- [ ] Saat input stok keluar, jika jumlah yang diminta melebihi stok saat ini, sistem menampilkan error "Stok tidak mencukupi. Stok saat ini: X, yang diminta: Y"
- [ ] Validasi ini berlaku untuk transaksi pending — stok belum berkurang, tetapi sistem tetap memvalidasi berdasarkan stok saat ini
- [ ] Jika stok saat ini adalah 0, sistem menolak stok keluar dengan error "Stok tidak mencukupi. Stok saat ini: 0, yang diminta: Y"

### AC-04: Validasi Umum

- [ ] Form menolak input jumlah 0 atau negatif — menampilkan error "Jumlah harus lebih besar dari 0"
- [ ] Form menolak jika barang tidak dipilih — menampilkan error "Barang harus dipilih"
- [ ] Form menolak jika supplier tidak dipilih (stok masuk) — menampilkan error "Supplier harus dipilih"
- [ ] Form menolak jika alasan keluar tidak dipilih (stok keluar) — menampilkan error "Alasan keluar harus dipilih"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

### AC-05: Authorization

- [ ] Halaman form stok masuk bisa diakses oleh semua role yang login (admin, manager, audit, kasir, staff_toko)
- [ ] Halaman form stok keluar bisa diakses oleh semua role yang login (admin, manager, audit, kasir, staff_toko)
- [ ] User yang belum login tidak bisa akses form (redirect ke login)

### AC-06: Audit Trail

- [ ] Setiap create transaksi stok masuk mencatat audit trail: user, action=create, entity_type=StockTransaction, entity_id, new_values (include: product_id, type, quantity, status)
- [ ] Setiap create transaksi stok keluar mencatat audit trail: user, action=create, entity_type=StockTransaction, entity_id, new_values (include: product_id, type, quantity, status)

### AC-07: Data Integrity

- [ ] Transaksi stok masuk menyimpan: product_id, user_id (pembuat), type='in', quantity, supplier_id, reference_no, notes, status='pending'
- [ ] Transaksi stok keluar menyimpan: product_id, user_id (pembuat), type='out', quantity, reason (penjualan/rusak/adjustment/lainnya), notes, status='pending'
- [ ] User_id pembuat transaksi adalah user yang sedang login
- [ ] Timestamp created_at dan updated_at terisi otomatis

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Stock/` berisi `In.php`, `Out.php`
2. **Form Structure:**
   - Stock In: barang (dropdown), jumlah (number), supplier (dropdown), referensi/PO (text), catatan (textarea)
   - Stock Out: barang (dropdown), jumlah (number), alasan (dropdown), catatan (textarea), stok saat ini (display only)
3. **Dropdown Logic:**
   - Barang: `Product::where('is_active', true)->where('current_stock', '>', 0)->get()` untuk stok keluar, `Product::where('is_active', true)->get()` untuk stok masuk
   - Supplier: `Supplier::all()` atau filter berdasarkan produk yang dipilih
4. **Validation Rules:**
   - Jumlah: `required|integer|min:1`
   - Barang: `required|exists:products,id`
   - Supplier: `required|exists:suppliers,id` (stok masuk)
   - Alasan: `required|in:penjualan,rusak,adjustment,lainnya` (stok keluar)
   - Referensi: `nullable|string|max:100`
   - Catatan: `nullable|string|max:500`
5. **StockService Integration:** Panggil `StockService::createStockIn()` atau `StockService::createStockOut()` untuk menyimpan transaksi
6. **Audit Trail Integration:** Panggil `AuditTrailService::log()` setelah transaksi berhasil disimpan
7. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 5 (products) — produk harus ada, user harus login
8. **Next Phase:** Setelah Phase 6 selesai, Phase 7 (Approval Flow Core) bisa dimulai — transaksi pending akan di-approve di Phase 7
9. **Testing:** Buat Feature test untuk create stok masuk dan keluar dengan berbagai role, termasuk test validasi stok tidak negatif
10. **Important:** Jangan update stok produk di phase ini — stok hanya berubah setelah approval di Phase 7
