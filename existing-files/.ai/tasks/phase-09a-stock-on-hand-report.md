# Phase 9a: Stock On-Hand Report

## Tujuan

Implementasi laporan stok on-hand dengan filter kategori/supplier dan penandaan low stock alert.

## Scope

- Buat Livewire component Report Stock On-Hand
- Implementasi filter: kategori (dropdown), supplier (dropdown)
- Implementasi penandaan barang dengan stok di bawah minimum
- Implementasi pagination
- Setup route dengan middleware auth
- Buat view Blade untuk laporan stok on-hand

## Non-Goals (Eksplisit)

- TIDAK membuat laporan mutasi stok (di Phase 9b)
- TIDAK membuat laporan low stock (di Phase 9b)
- TIDAK membuat export Excel/PDF (di Phase 10)
- TIDAK membuat fitur search real-time (di Phase 14)
- TIDAK membuat grafik/visualisasi lanjutan (hanya tabel)
- TIDAK membuat scheduled report (cuma on-demand)

## Referensi ke PRD/Architecture

- **PRD Section 5.5 (Laporan):** US-016 (Laporan Stok Saat Ini)
- **PRD Section 3.2 (Matrix Permission):** Lihat Semua Laporan untuk semua role
- **architecture.md Section 5.5 (ReportService):** Method getStockOnHand
- **architecture.md Section 4.1 (Route Design):** Route reports dengan middleware auth

## Acceptance Criteria (Testable)

### AC-01: Stock On-Hand Display

- [ ] Halaman laporan stok on-hand menampilkan tabel dengan kolom: nama barang, SKU, kategori, supplier, stok saat ini, stok minimum, status (normal/low stock)
- [ ] Tabel menampilkan semua produk aktif (is_active = true)
- [ ] Data diurutkan berdasarkan nama barang
- [ ] Data ditampilkan dengan pagination (50 items per halaman)
- [ ] Jika tidak ada data, menampilkan message "Tidak ada data stok"

### AC-02: Filter Functionality

- [ ] Sistem menyediakan filter kategori (dropdown) — menampilkan semua kategori
- [ ] Sistem menyediakan filter supplier (dropdown) — menampilkan semua supplier
- [ ] Filter dapat dikombinasikan — user bisa filter berdasarkan kategori dan supplier sekaligus
- [ ] Sistem menandai barang dengan stok di bawah stok minimum sebagai "Low Stock" dengan warna yang berbeda (misal: merah)
- [ ] Sistem menampilkan jumlah total barang dan jumlah low stock di header laporan

### AC-03: Authorization

- [ ] Semua halaman laporan bisa diakses oleh semua role yang login (admin, manager, audit, kasir, staff_toko)
- [ ] User yang belum login tidak bisa akses laporan (redirect ke login)

### AC-04: Performance

- [ ] Laporan stok on-hand dengan 1000 produk load dalam waktu maksimal 1 detik
- [ ] Query menggunakan eager loading untuk menghindari N+1 problem
- [ ] Filter menggunakan index yang sudah didefinisikan di migration

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Report/` berisi `StockOnHand.php`
2. **ReportService:** Buat method `getStockOnHand(array $filters = []): Collection`
3. **Repository Integration:** Gunakan Repository untuk query data
4. **Eager Loading:** `Product::with(['category', 'unit', 'suppliers'])->where('is_active', true)->get()`
5. **Filter Logic:**
   - Kategori: `->where('category_id', $categoryId)`
   - Supplier: `->whereHas('suppliers', fn($q) => $q->where('suppliers.id', $supplierId))`
6. **Pagination:** Gunakan `paginate(50)` di query
7. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 5 (products) — semua data harus ada
8. **Next Phase:** Setelah Phase 9a selesai, Phase 9b (Mutation & Low Stock Reports) bisa dimulai
9. **Testing:** Buat Feature test untuk laporan stok on-hand dengan filter yang berbeda, test pagination, test authorization
   </parameter>
   <task_progress>

- [x] Examine existing phase task files
- [x] Identify phases that are too large
- [x] Break down Phase 1 into sub-phases (1a, 1b)
- [x] Break down Phase 2 into sub-phases (2a, 2b)
- [x] Break down Phase 5 into sub-phases (5a, 5b)
- [x] Break down Phase 6 into sub-phases (6a, 6b)
- [x] Break down Phase 7 into sub-phases (7a, 7b)
- [ ] Break down Phase 9 into sub-phases (9a, 9b)
- [ ] Break down Phase 10 into sub-phases
- [ ] Break down Phase 11 into sub-phases
- [ ] Break down Phase 12 into sub-phases
- [ ] Break down Phase 13 into sub-phases
      </task_progress>
      </write_to_file>
