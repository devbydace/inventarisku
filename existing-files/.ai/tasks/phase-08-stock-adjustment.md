# Phase 8: Stock Adjustment

## Tujuan

Implementasi fitur penyesuaian stok fisik untuk koreksi stok dengan alasan yang jelas, menggunakan approval flow yang sama dengan transaksi stok masuk/keluar.

## Scope

- Buat Livewire component untuk Stock Adjustment: form input penyesuaian stok
- Buat StockAdjustmentController (jika diperlukan untuk route)
- Implementasi form: pilih barang, input stok fisik, alasan adjustment
- Implementasi validasi: stok fisik tidak boleh negatif
- Integrasi dengan ApprovalService untuk approval flow (menggunakan flow yang sama dengan Phase 7)
- Integrasi dengan StockService untuk update stok setelah approval
- Integrasi dengan AuditTrailService untuk pencatatan adjustment
- Setup route dengan middleware role:admin,manager,audit
- Buat view Blade untuk stock adjustment form

## Non-Goals (Eksplisit)

- TIDAK membuat approval flow baru (menggunakan approval flow yang sama dengan Phase 7)
- TIDAK membuat histori mutasi stok (di Phase 9)
- TIDAK membuat laporan adjustment (akan termasuk di laporan mutasi stok di Phase 9)
- TIDAK membuat batch adjustment (satu produk per transaksi)
- TIDAK mengizinkan adjustment tanpa approval

## Referensi ke PRD/Architecture

- **PRD Section 5.2 (Manajemen Stok):** US-008 (Stock Adjustment)
- **PRD Section 3.2 (Matrix Permission):** Stock Adjustment hanya untuk Admin, Manager, Audit
- **architecture.md Section 3.9 (StockAdjustment Model):** Relasi dan $fillable
- **architecture.md Section 5.1 (StockService):** Method untuk update stok
- **architecture.md Section 4.1 (Route Design):** Route stock/adjustment dengan middleware role:admin,manager,audit

## Acceptance Criteria (Testable)

### AC-01: Stock Adjustment Form

- [ ] Halaman form stock adjustment menampilkan field: barang (dropdown, required), stok fisik (integer, required, min 0), alasan (text, required)
- [ ] Sistem menampilkan stok sistem saat ini sebagai referensi (misal: "Stok sistem saat ini: 50 pcs")
- [ ] Dropdown barang menampilkan hanya produk aktif (is_active = true)
- [ ] Setelah submit, transaksi adjustment berstatus **pending** dan stok barang TIDAK berubah
- [ ] Sistem menampilkan success notification "Permintaan penyesuaian stok berhasil dibuat dan menunggu approval"
- [ ] Setelah submit, form di-reset ke kondisi awal (blank)

### AC-02: Validasi

- [ ] Form menolak input stok fisik negatif — menampilkan error "Stok fisik tidak boleh negatif"
- [ ] Form menolak jika barang tidak dipilih — menampilkan error "Barang harus dipilih"
- [ ] Form menolak jika alasan kosong — menampilkan error "Alasan harus diisi"
- [ ] Form menolak jika alasan kurang dari 10 karakter — menampilkan error "Alasan harus minimal 10 karakter"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

### AC-03: Approval Flow

- [ ] Setelah submit, transaksi adjustment muncul di halaman approval (Phase 7) untuk di-approve/reject
- [ ] Setelah approve, stok barang diupdate ke nilai stok fisik yang diinput
- [ ] Setelah approve, transaksi adjustment berstatus **approved**
- [ ] Setelah reject, stok barang TIDAK berubah
- [ ] Setelah reject, transaksi adjustment berstatus **rejected**
- [ ] Selisih antara stok sistem dan stok fisik tercatat di audit trail

### AC-04: Authorization

- [ ] Halaman form stock adjustment hanya bisa diakses oleh user dengan role admin, manager, atau audit (403 Forbidden untuk kasir/staff_toko)
- [ ] User yang belum login tidak bisa akses form (redirect ke login)

### AC-05: Audit Trail

- [ ] Setiap create adjustment mencatat audit trail: user, action=create, entity_type=StockAdjustment, entity_id, new_values (include: product_id, physical_stock, reason, status)
- [ ] Setelah approve, mencatat audit trail: user (approver), action=update, entity_type=StockAdjustment, entity_id, old_values (stok sistem), new_values (stok fisik)
- [ ] Setelah approve, mencatat audit trail untuk product: user (approver), action=update, entity_type=Product, entity_id, old_values (current_stock lama), new_values (current_stock baru)

### AC-06: Data Integrity

- [ ] Transaksi adjustment menyimpan: product_id, user_id (pembuat), physical_stock, reason, status='pending'
- [ ] Setelah approve, product.current_stock diupdate ke nilai physical_stock yang diinput
- [ ] Timestamp created_at dan updated_at terisi otomatis
- [ ] Relasi ke stock_transactions atau approvals tercatat dengan benar

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Stock/` berisi `Adjustment.php` (atau buat folder terpisah `app/Livewire/StockAdjustment/`)
2. **Form Structure:**
   - Barang (dropdown, required)
   - Stok fisik (number input, required, min 0)
   - Stok sistem saat ini (display only, read-only)
   - Alasan (textarea, required, min 10 chars)
3. **Dropdown Logic:**
   - Barang: `Product::where('is_active', true)->get()`
   - Tampilkan stok saat ini di sebelah dropdown barang
4. **Validation Rules:**
   - Barang: `required|exists:products,id`
   - Stok fisik: `required|integer|min:0`
   - Alasan: `required|string|min:10|max:500`
5. **Approval Integration:** Setelah adjustment disimpan dengan status pending, redirect ke halaman approval (Phase 7) atau tampilkan notifikasi "Menunggu approval"
6. **Stock Update Logic:** Setelah approved, update `product.current_stock = $adjustment->physical_stock` (bukan tambah/kurang, tapi set ke nilai baru)
7. **Audit Trail Integration:** Panggil `AuditTrailService::log()` setelah adjustment dibuat dan setelah approval
8. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 5 (products), Phase 7 (approval flow) — approval flow harus sudah ada
9. **Next Phase:** Setelah Phase 8 selesai, Phase 9 (Reporting - Data Display) bisa dimulai — laporan akan menampilkan adjustment sebagai bagian dari mutasi stok
10. **Testing:** Buat Feature test untuk create adjustment, approve adjustment, reject adjustment, dan validasi stok tidak negatif
11. **Important:** Adjustment menggunakan tabel terpisah `stock_adjustments` (bukan `stock_transactions`) sesuai architecture.md Decision #003
