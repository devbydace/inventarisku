# Phase 6b: Stock Out Transaction

## Tujuan

Implementasi form input stok keluar dengan validasi stok tidak negatif, penyimpanan transaksi dengan status pending, dan alasan keluar.

## Scope

- Buat Livewire component untuk Stock Out: form input stok keluar
- Buat StockTransactionController (jika diperlukan untuk route)
- Implementasi validasi: stok tidak boleh negatif untuk stok keluar
- Implementasi validasi: jumlah harus lebih besar dari 0
- Implementasi penyimpanan transaksi dengan status pending
- Integrasi dengan StockService untuk business logic
- Integrasi dengan AuditTrailService untuk pencatatan transaksi
- Setup route dengan middleware auth (semua role bisa akses)
- Buat view Blade untuk form stock out

## Non-Goals (Eksplisit)

- TIDAK membuat form stok masuk (sudah di Phase 6a)
- TIDAK membuat sistem approval/reject (di Phase 7)
- TIDAK membuat histori mutasi stok (di Phase 9)
- TIDAK membuat stock adjustment (di Phase 8)
- TIDAK membuat laporan stok (di Phase 9)
- TIDAK mengupdate stok produk secara langsung (stok hanya berubah setelah approved di Phase 7)

## Referensi ke PRD/Architecture

- **PRD Section 5.2 (Manajemen Stok):** US-006 (Pencatatan Stok Keluar), US-007 (Validasi Stok Tidak Negatif)
- **PRD Section 3.2 (Matrix Permission):** Input Stok Keluar untuk semua role (admin, manager, audit, kasir, staff_toko)
- **architecture.md Section 3.7 (StockTransaction Model):** Relasi dan $fillable
- **architecture.md Section 5.1 (StockService):** Method createStockOut, validateStockAvailability
- **architecture.md Section 4.1 (Route Design):** Route untuk stock/out dengan middleware auth

## Acceptance Criteria (Testable)

### AC-01: Stock Out Form Display

- [ ] Halaman form stok keluar menampilkan field: barang (dropdown, required), jumlah (integer, required, min 1), alasan/kategori keluar (dropdown: penjualan, rusak, adjustment, lainnya, required), catatan (text, optional)
- [ ] Dropdown barang menampilkan hanya produk aktif (is_active = true)
- [ ] Form menampilkan stok saat ini dari barang yang dipilih sebagai informasi (misal: "Stok saat ini: 50 pcs")
- [ ] Form memiliki validasi CSRF protection
- [ ] Tampilan form menggunakan Bahasa Indonesia

### AC-02: Stock Out Validation

- [ ] Form menolak input jumlah 0 atau negatif — menampilkan error "Jumlah harus lebih besar dari 0"
- [ ] Form menolak jika barang tidak dipilih — menampilkan error "Barang harus dipilih"
- [ ] Form menolak jika alasan keluar tidak dipilih — menampilkan error "Alasan keluar harus dipilih"
- [ ] Form menolak input catatan lebih dari 500 karakter — menampilkan error "Catatan tidak boleh lebih dari 500 karakter"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

### AC-03: Stock Availability Validation

- [ ] Saat input stok keluar, jika jumlah yang diminta melebihi stok saat ini, sistem menampilkan error "Stok tidak mencukupi. Stok saat ini: X, yang diminta: Y"
- [ ] Validasi ini berlaku untuk transaksi pending — stok belum berkurang, tetapi sistem tetap memvalidasi berdasarkan stok saat ini
- [ ] Jika stok saat ini adalah 0, sistem menolak stok keluar dengan error "Stok tidak mencukupi. Stok saat ini: 0, yang diminta: Y"
- [ ] Validasi stok dilakukan sebelum transaksi disimpan (di form validation)

### AC-04: Stock Out Submission

- [ ] Setelah submit, transaksi berstatus **pending** dan stok barang TIDAK berubah
- [ ] Sistem menampilkan success notification "Transaksi stok keluar berhasil dibuat dan menunggu approval"
- [ ] Setelah submit, form di-reset ke kondisi awal (blank)
- [ ] Transaksi menyimpan: product_id, user_id (pembuat), type='out', quantity, reason (penjualan/rusak/adjustment/lainnya), notes, status='pending'
- [ ] User_id pembuat transaksi adalah user yang sedang login
- [ ] Timestamp created_at dan updated_at terisi otomatis

### AC-05: Dynamic Display Logic

- [ ] Saat pilih barang, form menampilkan informasi: nama barang, SKU, stok saat ini, satuan
- [ ] Jika barang diubah, informasi ter-update sesuai barang yang dipilih
- [ ] Dropdown barang menampilkan hanya produk dengan stok > 0 (tidak bisa stok keluar jika stok 0)
- [ ] Jika tidak ada produk dengan stok > 0, menampilkan message "Tidak ada produk dengan stok tersedia"

### AC-06: Authorization

- [ ] Halaman form stok keluar bisa diakses oleh semua role yang login (admin, manager, audit, kasir, staff_toko)
- [ ] User yang belum login tidak bisa akses form (redirect ke login)
- [ ] Semua role memiliki permission untuk create stok keluar

### AC-07: Audit Trail

- [ ] Setiap create transaksi stok keluar mencatat audit trail: user, action=create, entity_type=StockTransaction, entity_id, new_values (include: product_id, type, quantity, reason, status)
- [ ] Audit trail tercatat dengan user yang sedang login
- [ ] Audit trail menyimpan semua data yang relevan untuk compliance

### AC-08: Data Display & UX

- [ ] Form menampilkan informasi barang yang dipilih (nama, SKU, stok saat ini, satuan) setelah memilih barang
- [ ] Tombol "Submit" menampilkan loading state saat proses submit
- [ ] Setelah submit, user dapat membuat transaksi baru tanpa harus refresh halaman
- [ ] Jika validasi stok gagal, form tetap terisi dan menampilkan error

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Stock/` berisi `Out.php`
2. **Form Structure:**
   - Barang: dropdown dengan `Product::where('is_active', true)->where('current_stock', '>', 0)->get()`
   - Jumlah: number input dengan min=1
   - Alasan: dropdown dengan options: penjualan, rusak, adjustment, lainnya
   - Catatan: textarea (optional)
   - Stok saat ini: display only (read-only text)
3. **Validation Rules:**
   - Jumlah: `required|integer|min:1'
   - Barang: `required|exists:products,id`
   - Alasan: `required|in:penjualan,rusak,adjustment,lainnya'
   - Catatan: `nullable|string|max:500'
   - Custom validation: `if ($product->current_stock < $quantity) throw new \Exception('Stok tidak mencukupi')`
4. **StockService Integration:** Panggil `StockService::createStockOut()` untuk menyimpan transaksi
5. **Audit Trail Integration:** Panggil `AuditTrailService::log()` setelah transaksi berhasil disimpan
6. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 5 (products) — produk harus ada, user harus login
7. **Next Phase:** Setelah Phase 6b selesai, Phase 7 (Approval Flow Core) bisa dimulai — transaksi pending akan di-approve di Phase 7
8. **Testing:** Buat Feature test untuk create stok keluar dengan berbagai role, test validasi stok tidak negatif, test audit trail
9. **Important:** Jangan update stok produk di phase ini — stok hanya berubah setelah approval di Phase 7
