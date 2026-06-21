# Phase 14: Polish & Edge Cases

## Tujuan

Implementasi fitur pencarian real-time, filter kombinasi, validasi error message yang konsisten, responsive design, dan penanganan edge case untuk menyelesaikan MVP.

## Scope

- Buat Livewire component untuk Search Barang (real-time search)
- Buat Livewire component untuk Filter Barang (kombinasi kategori + supplier + search)
- Implementasi responsive design untuk mobile (minimal 320px width)
- Implementasi error message yang konsisten dan user-friendly
- Implementasi konfirmasi dialog untuk aksi destruktif (delete, reject, archive)
- Implementasi empty state untuk halaman yang tidak ada data
- Implementasi loading state untuk async operation
- Implementasi validasi edge case yang belum tercover di fase sebelumnya
- Polish UI/UX: spacing, typography, color scheme, icon
- Buat view components reusable untuk button, card, alert, dll

## Non-Goals (Eksplisit)

- TIDAK membuat fitur baru yang belum ada di PRD
- TIDAK membuat grafik/visualisasi lanjutan
- TIDAK membuat barcode/QR code scanning
- TIDAK membuat import data
- TIDAK membuat notifikasi eksternal
- TIDAK membuat multi-bahasa

## Referensi ke PRD/Architecture

- **PRD Section 5.6 (Pencarian & Filter):** US-019 (Pencarian Barang), US-020 (Filter Barang), US-021 (Filter Laporan Mutasi)
- **PRD Section 8.3 (Usability):** NFR-14 (Responsive), NFR-15 (Validation Message), NFR-16 (Konfirmasi), NFR-17 (Error Message), NFR-18 (Terminology)
- **PRD Section 9.2 (Edge Cases):** EC-01 sampai EC-15
- **architecture.md Section 6.2 (Packages):** Tailwind CSS untuk styling

## Acceptance Criteria (Testable)

### AC-01: Pencarian Real-Time Barang

- [ ] Search box di halaman daftar barang melakukan pencarian real-time saat user mengetik (minimal 2 karakter)
- [ ] Pencarian match terhadap nama barang (case-insensitive) dan SKU
- [ ] Hasil pencarian ditampilkan secara instan (debounce 300ms)
- [ ] Jika tidak ada hasil, menampilkan message "Tidak ada barang yang ditemukan"
- [ ] Search bisa dikombinasikan dengan filter kategori dan supplier
- [ ] Search menampilkan maksimal 50 hasil per halaman dengan pagination

### AC-02: Filter Kombinasi Barang

- [ ] Dropdown filter kategori menampilkan semua kategori
- [ ] Dropdown filter supplier menampilkan semua supplier
- [ ] Filter dapat dikombinasikan — user bisa filter berdasarkan kategori dan supplier sekaligus
- [ ] Filter dapat dikombinasikan dengan search
- [ ] Sistem menampilkan jumlah hasil filter (misal: "Menampilkan 25 dari 100 barang")
- [ ] Tombol "Reset Filter" untuk menghapus semua filter

### AC-03: Responsive Design

- [ ] Aplikasi dapat diakses dari desktop (minimal 1024px)
- [ ] Aplikasi dapat diakses dari tablet (768px - 1023px)
- [ ] Aplikasi dapat diakses dari smartphone (320px - 767px)
- [ ] Tabel di mobile menampilkan horizontal scroll jika terlalu banyak kolom
- [ ] Form di mobile memiliki input yang cukup besar untuk di-tap (minimal 44px height)
- [ ] Button di mobile memiliki ukuran yang cukup untuk di-tap (minimal 44px height)
- [ ] Navigation menu di mobile menjadi hamburger menu atau bottom navigation

### AC-04: Error Message & Validation

- [ ] Semua error message ditampilkan dalam Bahasa Indonesia
- [ ] Error message spesifik dan actionable (misal: "SKU sudah digunakan" bukan "Validation error")
- [ ] Error message ditampilkan di atas form, bukan di bawah setiap field
- [ ] Error message menggunakan warna merah dan icon warning
- [ ] Success message menggunakan warna hijau dan icon check
- [ ] Warning message menggunakan warna kuning dan icon alert

### AC-05: Konfirmasi Aksi Destruktif

- [ ] Setiap delete/archive menampilkan konfirmasi dialog: "Apakah Anda yakin ingin menghapus [nama]?"
- [ ] Setiap reject menampilkan konfirmasi dialog dengan form alasan
- [ ] Setiap approve menampilkan konfirmasi dialog: "Apakah Anda yakin ingin approve transaksi ini?"
- [ ] Konfirmasi dialog memiliki tombol "Cancel" dan "Confirm"
- [ ] Konfirmasi dialog menggunakan modal overlay dengan backdrop blur

### AC-06: Empty State & Loading State

- [ ] Jika daftar barang kosong, menampilkan empty state dengan icon, message "Belum ada barang", dan tombol "Tambah Barang"
- [ ] Jika daftar transaksi pending kosong, menampilkan empty state dengan message "Tidak ada transaksi yang menunggu approval"
- [ ] Jika laporan tidak ada data, menampilkan empty state dengan message sesuai konteks
- [ ] Loading state ditampilkan saat data sedang di-load (spinner atau skeleton loader)
- [ ] Loading state ditampilkan saat export sedang diproses

### AC-07: Edge Case Handling

- [ ] EC-01: Stok keluar dengan jumlah exactly sama dengan stok saat ini — transaksi berhasil, stok menjadi 0
- [ ] EC-02: Stok keluar dengan jumlah lebih besar dari stok saat ini — error "Stok tidak mencukupi"
- [ ] EC-03: Staff approve transaksi sendiri — error "Tidak dapat approve transaksi sendiri"
- [ ] EC-04: Approve transaksi yang sudah approved — error "Transaksi sudah di-approve"
- [ ] EC-05: Delete barang yang digunakan di transaksi pending — error "Barang tidak dapat dihapus karena digunakan dalam transaksi pending"
- [ ] EC-06: Export dengan filter tidak ada hasil — message "Tidak ada data untuk filter yang dipilih"
- [ ] EC-07: Upload logo >2MB — error "Ukuran file maksimal 2MB"
- [ ] EC-08: Upload logo format .webp — error "Format file harus jpg atau png"
- [ ] EC-09: User non-aktif login — error "Akun Anda telah dinon-aktifkan"
- [ ] EC-10: Stock adjustment dengan stok fisik sama dengan stok sistem — proses tetap berjalan, audit trail tercatat
- [ ] EC-11: Reject tanpa alasan — error "Alasan reject harus diisi (minimal 10 karakter)"
- [ ] EC-12: Stok masuk dengan jumlah 0 atau negatif — error "Jumlah harus lebih besar dari 0"
- [ ] EC-13: Search dengan query umum dan hasil >1000 — pagination 50 per halaman
- [ ] EC-14: Export PDF tanpa logo — PDF generated tanpa logo, tidak error
- [ ] EC-15: Staff akses halaman approval — error 403 Forbidden

### AC-08: Terminology Consistency

- [ ] Menggunakan "Stok Masuk" bukan "Stock In" atau "Barang Masuk"
- [ ] Menggunakan "Stok Keluar" bukan "Stock Out" atau "Barang Keluar"
- [ ] Menggunakan "Approval" bukan "Approve" atau "Persetujuan"
- [ ] Menggunakan "Low Stock" atau "Stok Rendah" secara konsisten
- [ ] Menggunakan "Barang" bukan "Produk" di beberapa tempat (konsisten dengan PRD)

## Catatan untuk Agent

1. **Search Component:** Buat Livewire component `app/Livewire/Search/Barang.php` atau integrasi search di setiap halaman yang membutuhkan
2. **Debounce:** Gunakan Livewire `wire:model.debounce.300ms` untuk search real-time
3. **Responsive Design:**
   - Gunakan Tailwind CSS classes: `md:`, `lg:`, `sm:`
   - Tabel: `overflow-x-auto` untuk horizontal scroll di mobile
   - Button: `min-h-[44px]` untuk ukuran tap yang cukup
4. **Reusable Components:** Buat components di `resources/views/components/`:
   - `alert.blade.php` — untuk success, error, warning message
   - `confirm-dialog.blade.php` — untuk konfirmasi aksi
   - `empty-state.blade.php` — untuk empty state
   - `loading-spinner.blade.php` — untuk loading state
5. **Color Scheme:**
   - Primary: Biru (misal: blue-600)
   - Success: Hijau (green-600)
   - Error: Merah (red-600)
   - Warning: Kuning (yellow-600)
   - Info: Abu-abu (gray-600)
6. **Typography:** Gunakan font yang konsisten (misal: Inter atau system font)
7. **Icon:** Gunakan icon library (misal: Heroicons atau Font Awesome) untuk konsistensi
8. **Dependency:** Phase ini dependen ke semua fase sebelumnya — semua fitur harus sudah ada
9. **Next Phase:** Setelah Phase 14 selesai, aplikasi MVP-ready dan siap untuk testing
10. **Testing:** Buat Feature test untuk search, filter, responsive design, edge cases
11. **Important:** Fase ini adalah fase terakhir sebelum MVP — pastikan semua edge case sudah tercover dan UI/UX sudah konsisten
