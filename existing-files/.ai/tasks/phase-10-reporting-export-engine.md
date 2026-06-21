# Phase 10: Reporting - Export Engine

## Tujuan

Implementasi export data ke Excel/CSV dan PDF menggunakan database queue dengan notifikasi progres untuk laporan stok dan data barang.

## Scope

- Buat ExportService dengan method export ke Excel/CSV dan PDF
- Buat ExportJob untuk proses export di background (database queue)
- Buat Livewire component untuk Export dengan notifikasi status
- Implementasi export laporan stok on-hand, mutasi stok, dan low stock
- Implementasi export data barang
- Integrasi dengan Maatwebsite Excel untuk Excel/CSV export
- Integrasi dengan Barryvdh DomPDF untuk PDF export
- Implementasi notifikasi: "Export sedang diproses" dan "Export selesai"
- Setup queue database (jobs, failed_jobs tables)
- Buat route untuk trigger export

## Non-Goals (Eksplisit)

- TIDAK membuat import data (tidak termasuk MVP)
- TIDAK membuat export dengan format selain Excel/CSV dan PDF
- TIDAK membuat scheduled export (cuma on-demand)
- TIDAK membuat export dengan filter custom (menggunakan filter yang sama dengan laporan)
- TIDAK membuat email notification untuk export selesai (hanya notifikasi di aplikasi)

## Referensi ke PRD/Architecture

- **PRD Section 5.4 (Export Data):** US-014 (Export Data Barang), US-015 (Export Laporan Stok)
- **PRD Section 3.2 (Matrix Permission):** Export Data untuk semua role
- **architecture.md Section 5.3 (ExportService):** Method exportProductsToExcel, exportProductsToPdf, exportStockReportToExcel, exportStockReportToPdf
- **architecture.md Section 6.1 (Packages):** barryvdh/laravel-dompdf, maatwebsite/excel
- **tech-decisions.md Decision #001:** Database Queue untuk export

## Acceptance Criteria (Testable)

### AC-01: Export Data Barang

- [ ] Halaman daftar produk menyediakan tombol "Export Excel" dan "Export PDF"
- [ ] Export Excel/CSV berisi kolom: nama, SKU, kategori, supplier, harga beli, harga jual, satuan, stok saat ini, stok minimum
- [ ] Export PDF berisi header perusahaan (nama, alamat, logo), tabel data barang, tanggal export, nama user yang export
- [ ] Export hanya mengekspor produk aktif (is_active = true)
- [ ] File Excel/CSV dapat dibuka dengan Microsoft Excel atau Google Sheets tanpa error
- [ ] File PDF dapat dibuka dengan PDF reader tanpa error

### AC-02: Export Laporan Stok

- [ ] Halaman laporan stok on-hand menyediakan tombol "Export Excel" dan "Export PDF"
- [ ] Export Excel/CSV stok on-hand berisi kolom: nama barang, SKU, kategori, supplier, stok saat ini, stok minimum, status (normal/low stock)
- [ ] Export PDF stok on-hand berisi header perusahaan, filter yang diterapkan, tanggal export, tabel data
- [ ] Halaman laporan mutasi stok menyediakan tombol "Export Excel" dan "Export PDF"
- [ ] Export Excel/CSV mutasi stok berisi kolom: tanggal, jenis transaksi, barang, jumlah, stok sebelum, stok sesudah, status, user pembuat, approver
- [ ] Export PDF mutasi stok berisi header perusahaan, filter tanggal, tabel data
- [ ] Halaman laporan low stock menyediakan tombol "Export Excel" dan "Export PDF"
- [ ] Export hanya mengekspor data sesuai filter yang diterapkan di laporan

### AC-03: Database Queue & Notifikasi

- [ ] Setelah klik export, sistem menampilkan notifikasi "Export sedang diproses..."
- [ ] Export dijalankan di background menggunakan database queue (tidak blocking UI)
- [ ] Setelah export selesai, sistem menampilkan notifikasi "Export selesai" dengan link download
- [ ] Jika export gagal, sistem menampilkan notifikasi error "Export gagal, silakan coba lagi"
- [ ] File export disimpan di storage/app/public/exports dengan nama unik (misal: products-2026-06-21-123456.xlsx)
- [ ] File export otomatis terhapus setelah 24 jam (cleanup job)

### AC-04: Authorization

- [ ] Tombol export hanya muncul untuk user yang login
- [ ] Semua role bisa export (admin, manager, audit, kasir, staff_toko)
- [ ] User yang belum login tidak bisa akses halaman export (redirect ke login)

### AC-05: Performance

- [ ] Export 1000 records selesai dalam waktu maksimal 15 detik (PDF) atau 30 detik (Excel)
- [ ] Export 10.000 records selesai dalam waktu maksimal 30 detik (Excel) atau 60 detik (PDF)
- [ ] Export menggunakan chunk processing (1000 records per chunk) untuk mencegah memory overflow
- [ ] Jika export timeout (>60 detik), sistem menampilkan error dan menyimpan job ke failed_jobs

## Catatan untuk Agent

1. **ExportJob:** Buat class `ExportJob` yang implements `ShouldQueue` dengan method `handle()`:

   ```php
   public function handle(ExportService $exportService)
   {
       $exportService->exportProductsToExcel();
   }
   ```

2. **ExportService Structure:**
   - `exportProductsToExcel(): string` — return path file
   - `exportProductsToPdf(): string` — return path file
   - `exportStockReportToExcel(string $type, array $filters): string` — return path file
   - `exportStockReportToPdf(string $type, array $filters): string` — return path file

3. **Maatwebsite Excel:**
   - Buat Export class untuk setiap tipe export (ProductsExport, StockOnHandExport, StockMutationExport, LowStockExport)
   - Gunakan `FromCollection`, `WithHeadings`, `WithMapping` untuk format Excel
   - Gunakan chunk reading untuk data besar: `->chunk(1000)`

4. **Barryvdh DomPDF:**
   - Load view Blade untuk PDF template
   - Pass data ke view: company profile, report data, filters, tanggal export
   - Set paper size: A4 landscape untuk laporan dengan banyak kolom
   - Load logo dari storage jika ada

5. **Queue Setup:**
   - Jalankan migration untuk `jobs` dan `failed_jobs` tables
   - Set `QUEUE_CONNECTION=database` di `.env`
   - Buat command `php artisan queue:work --once` untuk processing job
   - Di shared hosting, bisa menggunakan cron job: `* * * * * php /path/to/artisan queue:work --once`

6. **Notifikasi:**
   - Buat tabel `export_notifications` atau gunakan session flash untuk notifikasi sederhana
   - Atau gunakan database notification: `php artisan notifications:table` untuk membuat migration
   - Simpan path file export di notification untuk link download

7. **File Cleanup:**
   - Buat command `php artisan export:cleanup` untuk menghapus file export yang lebih dari 24 jam
   - Atau gunakan Laravel Scheduler: `$schedule->command('export:cleanup')->daily()`

8. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 9 (reporting) — laporan harus sudah ada, queue tables harus sudah dibuat
9. **Next Phase:** Setelah Phase 10 selesai, Phase 11 (Audit Trail System) bisa dimulai — audit trail akan mencatat export activity
10. **Testing:** Buat Feature test untuk export Excel/PDF, test queue job, test notifikasi, test file cleanup
11. **Important:** Pastikan storage/exports memiliki permission write untuk web server
