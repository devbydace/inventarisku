# Phase 9: Reporting - Data Display

## Tujuan

Implementasi laporan stok on-hand, mutasi stok, dan low stock alert dengan filter kategori/supplier/tanggal untuk menampilkan data inventaris.

## Scope

- Buat Livewire component untuk Report Stock On-Hand
- Buat Livewire component untuk Report Stock Mutation
- Buat Livewire component untuk Report Low Stock
- Buat ReportController (jika diperlukan untuk route)
- Implementasi ReportService dengan query laporan
- Implementasi filter: kategori (dropdown), supplier (dropdown), rentang tanggal (date picker)
- Implementasi pagination untuk data besar
- Integrasi dengan Repository untuk query data
- Setup route dengan middleware auth (semua role bisa lihat laporan)
- Buat view Blade untuk setiap laporan

## Non-Goals (Eksplisit)

- TIDAK membuat export Excel/PDF (di Phase 10)
- TIDAK membuat fitur search real-time (di Phase 14)
- TIDAK membuat grafik/visualisasi lanjutan (hanya tabel)
- TIDAK membuat laporan custom (hanya 3 laporan yang sudah ditentukan di PRD)
- TIDAK membuat scheduled report (cuma on-demand)

## Referensi ke PRD/Architecture

- **PRD Section 5.5 (Laporan):** US-016 (Laporan Stok Saat Ini), US-017 (Laporan Mutasi Stok), US-018 (Laporan/Alert Low Stock)
- **PRD Section 3.2 (Matrix Permission):** Lihat Semua Laporan untuk semua role
- **architecture.md Section 5.5 (ReportService):** Method getStockOnHand, getStockMutation, getLowStock, getDashboardStats
- **architecture.md Section 4.1 (Route Design):** Route reports dengan middleware auth

## Acceptance Criteria (Testable)

### AC-01: Laporan Stok Saat Ini (On-Hand)

- [ ] Halaman laporan stok on-hand menampilkan tabel dengan kolom: nama barang, SKU, kategori, supplier, stok saat ini, stok minimum, status (normal/low stock)
- [ ] Sistem menyediakan filter kategori (dropdown) — menampilkan semua kategori
- [ ] Sistem menyediakan filter supplier (dropdown) — menampilkan semua supplier
- [ ] Filter dapat dikombinasikan — user bisa filter berdasarkan kategori dan supplier sekaligus
- [ ] Sistem menandai barang dengan stok di bawah stok minimum sebagai "Low Stock" dengan warna yang berbeda (misal: merah)
- [ ] Sistem menampilkan jumlah total barang dan jumlah low stock di header laporan
- [ ] Data ditampilkan dengan pagination (50 items per halaman)
- [ ] Jika tidak ada data, menampilkan message "Tidak ada data stok"

### AC-02: Laporan Mutasi Stok

- [ ] Halaman laporan mutasi stok menampilkan tabel dengan kolom: tanggal, jenis (masuk/keluar/adjustment), barang, jumlah, stok sebelum, stok sesudah, status, user pembuat, approver
- [ ] Sistem menyediakan filter rentang tanggal: dari (date picker), sampai (date picker)
- [ ] Jika hanya tanggal dari diisi, sistem menampilkan mutasi dari tanggal tersebut sampai hari ini
- [ ] Jika tidak ada filter tanggal, sistem menampilkan semua mutasi
- [ ] Sistem hanya menampilkan transaksi dengan status approved dan pending (tidak menampilkan rejected)
- [ ] Sistem menampilkan total masuk, total keluar, dan total adjustment di header laporan
- [ ] Data ditampilkan dengan pagination (50 items per halaman)
- [ ] Jika tidak ada data, menampilkan message "Tidak ada mutasi stok untuk filter yang dipilih"

### AC-03: Laporan Low Stock

- [ ] Halaman laporan low stock menampilkan daftar barang dengan stok di bawah stok minimum masing-masing
- [ ] Sistem menampilkan kolom: nama barang, SKU, stok saat ini, stok minimum, selisih (stok minimum - stok saat ini)
- [ ] Sistem mengurutkan berdasarkan stok saat ini (ascending) — barang dengan stok paling rendah ditampilkan pertama
- [ ] Sistem menyediakan link ke halaman edit barang untuk setiap item
- [ ] Jika tidak ada barang low stock, menampilkan message "Tidak ada barang dengan stok di bawah minimum"
- [ ] Data ditampilkan dengan pagination (50 items per halaman)

### AC-04: Authorization

- [ ] Semua halaman laporan bisa diakses oleh semua role yang login (admin, manager, audit, kasir, staff_toko)
- [ ] User yang belum login tidak bisa akses laporan (redirect ke login)

### AC-05: Performance

- [ ] Laporan stok on-hand dengan 1000 produk load dalam waktu maksimal 1 detik
- [ ] Laporan mutasi stok dengan 1000 transaksi load dalam waktu maksimal 1 detik
- [ ] Query menggunakan eager loading untuk menghindari N+1 problem
- [ ] Filter menggunakan index yang sudah didefinisikan di migration

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Report/` berisi `StockOnHand.php`, `StockMutation.php`, `LowStock.php`
2. **ReportService:** Buat service class dengan method:
   - `getStockOnHand(array $filters = []): Collection`
   - `getStockMutation(array $filters = []): Collection`
   - `getLowStock(): Collection`
   - `getDashboardStats(): array`
3. **Repository Integration:** Gunakan Repository untuk query data, jangan query langsung di Livewire component
4. **Eager Loading:**
   - Stock On-Hand: `Product::with(['category', 'unit', 'suppliers'])->where('is_active', true)->get()`
   - Stock Mutation: `StockTransaction::with(['product', 'user', 'approval.approver'])->whereIn('status', ['pending', 'approved'])->get()`
5. **Filter Logic:**
   - Kategori: `->where('category_id', $categoryId)`
   - Supplier: `->whereHas('suppliers', fn($q) => $q->where('suppliers.id', $supplierId))`
   - Tanggal: `->whereBetween('created_at', [$from, $to])`
6. **Pagination:** Gunakan `paginate(50)` di query, atau manual pagination di Livewire
7. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 5 (products), Phase 6 (stock transaction), Phase 7 (approval) — semua data harus ada
8. **Next Phase:** Setelah Phase 9 selesai, Phase 10 (Reporting - Export Engine) bisa dimulai — export akan menggunakan data dari laporan ini
9. **Testing:** Buat Feature test untuk setiap laporan dengan filter yang berbeda, test pagination, test authorization
10. **Important:** Laporan mutasi stok hanya menampilkan transaksi approved dan pending, TIDAK menampilkan rejected
