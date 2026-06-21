# Phase 13: Dashboard & Settings

## Tujuan

Implementasi halaman dashboard dengan statistik ringkasan, quick access ke fitur utama, dan halaman settings untuk konfigurasi profil perusahaan serta setting aplikasi.

## Scope

- Buat Livewire component untuk Dashboard dengan statistik
- Buat Livewire component untuk Settings: Company Profile dan App Settings
- Buat DashboardController dan SettingsController (jika diperlukan)
- Implementasi statistik dashboard: total produk, jumlah low stock, transaksi pending
- Implementasi quick access buttons: tambah stok masuk, tambah stok keluar, lihat laporan
- Implementasi form settings: nama perusahaan, alamat, logo, kontak, format tanggal, mata uang
- Integrasi dengan ReportService untuk getDashboardStats
- Integrasi dengan CompanyProfile model
- Setup route dengan middleware auth (dashboard) dan role:admin (settings)
- Buat view Blade untuk dashboard dan settings

## Non-Goals (Eksplisit)

- TIDAK membuat grafik/visualisasi lanjutan (hanya statistik angka)
- TIDAK membuat widget custom (hanya statistik yang sudah ditentukan di PRD)
- TIDAK membuat multi-language settings (hanya Bahasa Indonesia)
- TIDAK membuat email settings (tidak ada notifikasi eksternal)
- TIDAK membuat backup settings (backup otomatis di server)

## Referensi ke PRD/Architecture

- **PRD Section 5.9 (Dashboard):** US-026 (Halaman Dashboard)
- **PRD Section 5.10 (Settings):** US-027 (Konfigurasi Profil Perusahaan), US-028 (Konfigurasi Aplikasi)
- **PRD Section 3.2 (Matrix Permission):** Dashboard untuk semua role, Settings hanya untuk Admin
- **architecture.md Section 5.5 (ReportService):** Method getDashboardStats
- **architecture.md Section 4.1 (Route Design):** Route dashboard dan settings

## Acceptance Criteria (Testable)

### AC-01: Dashboard Statistics

- [ ] Halaman dashboard menampilkan 3 statistik utama:
  - Total produk (jumlah produk aktif)
  - Jumlah low stock (produk dengan stok di bawah stok minimum)
  - Transaksi pending (transaksi yang menunggu approval)
- [ ] Setiap statistik menampilkan angka dan label yang jelas
- [ ] Statistik low stock menampilkan jumlah barang dengan stok di bawah stok minimum
- [ ] Statistik transaksi pending menampilkan jumlah transaksi dengan status pending
- [ ] Jika ada transaksi pending, dashboard menampilkan alert visual (misal: badge merah atau banner)

### AC-02: Quick Access

- [ ] Dashboard menyediakan tombol "Tambah Stok Masuk" — redirect ke halaman stok masuk (Phase 6)
- [ ] Dashboard menyediakan tombol "Tambah Stok Keluar" — redirect ke halaman stok keluar (Phase 6)
- [ ] Dashboard menyediakan tombol "Lihat Laporan" — redirect ke halaman laporan stok on-hand (Phase 9)
- [ ] Dashboard menyediakan tombol "Approval" (hanya untuk admin/manager/audit) — redirect ke halaman approval (Phase 7)
- [ ] Quick access buttons menggunakan warna yang jelas dan icon yang sesuai

### AC-03: Dashboard Authorization

- [ ] Halaman dashboard bisa diakses oleh semua role yang login (admin, manager, audit, kasir, staff_toko)
- [ ] User yang belum login tidak bisa akses dashboard (redirect ke login)
- [ ] Tombol "Approval" hanya muncul untuk user dengan permission approve-transaction

### AC-04: Company Profile Settings

- [ ] Halaman settings menampilkan form dengan field: nama perusahaan (required, max 255 chars), alamat (text, optional), logo (image upload, max 2MB, format jpg/png, optional), kontak (max 255 chars, optional)
- [ ] Sistem menampilkan preview logo sebelum save
- [ ] Setelah save, sistem menampilkan success notification "Pengaturan berhasil disimpan"
- [ ] Logo yang diupload disimpan di storage/app/public/logos dengan nama unik
- [ ] Jika tidak ada logo, menampilkan placeholder/default logo

### AC-05: App Settings

- [ ] Halaman settings menyediakan dropdown format tanggal: YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY
- [ ] Halaman settings menyediakan dropdown mata uang: IDR, USD, EUR, dll
- [ ] Default format tanggal: YYYY-MM-DD
- [ ] Default mata uang: IDR
- [ ] Setelah save, konfigurasi diterapkan ke seluruh aplikasi (laporan, export, display)

### AC-06: Settings Authorization

- [ ] Halaman settings hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] User yang belum login tidak bisa akses settings (redirect ke login)

### AC-07: Data Display

- [ ] Dashboard menampilkan informasi perusahaan (nama, logo) di header
- [ ] Settings menampilkan nilai current configuration (format tanggal dan mata uang yang sedang aktif)
- [ ] Semua label dan text menggunakan Bahasa Indonesia

## Catatan untuk Agent

1. **Livewire Component Structure:**
   - Dashboard: `app/Livewire/Dashboard.php`
   - Settings: `app/Livewire/Settings/CompanyProfile.php` dan `app/Livewire/Settings/AppSettings.php` (atau gabung jadi satu)

2. **Dashboard Stats Query:**

   ```php
   // Total produk aktif
   Product::where('is_active', true)->count()

   // Jumlah low stock
   Product::where('is_active', true)->where('current_stock', '<', 'min_stock')->count()

   // Transaksi pending
   StockTransaction::where('status', 'pending')->count()
   ```

3. **ReportService Integration:**
   - Buat method `getDashboardStats(): array` di ReportService
   - Return array: `['total_products' => X, 'low_stock_count' => Y, 'pending_transactions' => Z]`

4. **Logo Upload:**
   - Gunakan `Storage::putFile('logos', $file)` untuk upload
   - Validasi: max 2MB, mimes: jpg,jpeg,png
   - Hapus logo lama jika ada saat update

5. **Settings Storage:**
   - Company profile: Simpan di tabel `company_profiles` (single record)
   - App settings: Simpan di tabel `company_profiles` juga (format_date, currency)
   - Atau gunakan Laravel config cache untuk app settings

6. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 5 (products), Phase 6 (stock transaction), Phase 7 (approval), Phase 9 (reporting) — semua data harus ada
7. **Next Phase:** Setelah Phase 13 selesai, Phase 14 (Polish & Edge Cases) bisa dimulai — polish UI/UX
8. **Testing:** Buat Feature test untuk dashboard stats, test quick access, test settings save, test authorization
9. **Important:** Dashboard adalah halaman pertama yang dilihat user setelah login — pastikan load time < 2 detik (sesuai NFR-01)
