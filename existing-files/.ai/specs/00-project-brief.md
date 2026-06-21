# Project Brief: Aplikasi Inventaris Sederhana

## Ringkasan

Aplikasi web inventaris sederhana berbasis Laravel yang dirancang untuk mengotomatisasi proses inventarisasi yang saat ini dilakukan secara manual. Aplikasi ini bertujuan untuk mendigitalisasi pencatatan dan pelacakan barang inventaris.

## Masalah yang Diselesaikan

- Inventarisasi manual yang rawan terhadap human error
- Proses pencarian dan pelacakan barang yang lambat dan tidak efisien
- [PERLU KONFIRMASI: Masalah spesifik lain yang dihadapi dalam proses inventarisasi manual saat ini?]
- Laporan, audit dan keperluan inventarisasi agar dapat di unduh dalam pdf

## Target User

- Aplikasi digunakan oleh seluruh staff usaha
- Terdapat 5 peran pengguna: **admin, kasir, audit, manager, dan staff toko**
- Untuk penggunaan internal perusahaan

### Mapping Peran ke Level Approval

| Peran Pengguna | Level Approval | Deskripsi                                            |
| -------------- | -------------- | ---------------------------------------------------- |
| Staff toko     | Staff          | Input transaksi stok masuk/keluar                    |
| Kasir          | Staff          | Input transaksi stok masuk/keluar                    |
| Audit          | Supervisor     | Approve/reject transaksi                             |
| Manager        | Supervisor     | Approve/reject transaksi                             |
| Admin          | Admin/Owner    | Full access, kelola master data, lihat semua laporan |

## Tech Stack

- **Backend:** Laravel 13
- **Database:** MySQL
- **Frontend Framework:** Livewire
- **CSS Framework:** Tailwind CSS

## Constraint

- **Waktu:** Batasan waktu 1 bulan untuk development + testing (tidak termasuk
  planning & design). Development dimulai setelah Project Brief ini di-approve.
- **Budget:** Budget minimalis — prioritas pada solusi yang hemat biaya tanpa
  mengorbankan fungsionalitas inti.
- **Hosting/Environment:** Asumsikan shared hosting atau VPS entry-level
  (1 vCPU, 1-2GB RAM) yang kompatibel dengan budget minimalis.
  Hindari arsitektur yang butuh resource besar (Redis cluster, queue worker permanen,
  search engine terpisah seperti Elasticsearch)
- **Platform:** Aplikasi berbasis web (web-based), tidak ada kebutuhan native mobile app
- **Skala Pengguna:** Single-location (1 toko/gudang), maksimal 10 user concurrent.
  Tidak perlu desain multi-tenant atau multi-warehouse di MVP
- **Compliance & Keamanan:**
  ⚠️ **[PERLU KONFIRMASI]** — Apakah ada kebutuhan spesifik seperti:
  - Audit trail wajib (siapa mengubah stok, kapan, dari berapa ke berapa)?
  - Backup otomatis dengan retensi tertentu?
  - Role/permission lebih dari sekadar admin-staff (misal: approval untuk stok keluar)?
  - Standar keamanan tertentu (misal: karena data terhubung ke sistem keuangan)?
- **Tim & Resource:** [PERLU KONFIRMASI] — berapa developer yang mengerjakan,
  dan apakah dikerjakan oleh AI Agent + 1 developer, atau full AI-assisted solo?

## Definisi Selesai untuk MVP

Aplikasi dianggap MVP-ready jika seluruh kriteria berikut terpenuhi:

### Manajemen Barang

- [ ] CRUD barang/produk (create, read, update, delete/archive) dengan atribut
      minimal: nama, SKU, kategori, supplier, harga beli, harga jual, satuan,
      stok saat ini, **stok minimum (ambang batas low stock per barang)**
- [ ] CRUD kategori barang (grouping)
- [ ] CRUD supplier, dengan satu barang bisa punya lebih dari satu supplier
      (relasi many-to-many barang ↔ supplier)
- [ ] CRUD satuan (unit of measure) — misal: pcs, kg, liter, dll

### Manajemen Stok

- [ ] Pencatatan stok masuk (received), terhubung ke supplier & referensi
      (no. PO/faktur jika ada)
- [ ] Pencatatan stok keluar (issued/sold), dengan alasan/kategori keluar
      (penjualan, rusak, adjustment, dll)
- [ ] Stok ter-update otomatis dan konsisten setiap transaksi **disetujui** (approved)
- [ ] Histori mutasi stok tersimpan dan bisa ditelusuri per barang
- [ ] Stock adjustment (penyesuaian stok) untuk koreksi stok fisik
      dengan alasan yang jelas dan audit trail
- [ ] Validasi: stok tidak boleh negatif setelah transaksi keluar

### Autentikasi & Role (Approval Flow — 1 Level)

- [ ] Sistem login dengan 3 role:
  - **Staff** — input transaksi stok masuk/keluar (status awal: _pending_)
  - **Supervisor** — approve/reject transaksi yang di-input Staff
  - **Admin/Owner** — full access, termasuk approve, kelola master data, lihat semua laporan
- [ ] Transaksi stok masuk/keluar berstatus **pending** sampai di-approve oleh
      Supervisor atau Admin — stok TIDAK berubah sebelum approved
- [ ] Staff TIDAK BISA approve transaksinya sendiri (validasi: user_id pembuat
      ≠ user_id approver)
- [ ] Aksi approve/reject memerlukan satu langkah persetujuan (1 level, tidak berjenjang)
- [ ] Reject wajib disertai catatan/alasan
- [ ] Histori approval tercatat: siapa approve/reject, kapan, catatan
- [ ] **Tidak ada notifikasi eksternal (email/WA)** — Supervisor/Admin mengecek
      daftar transaksi pending langsung di sistem saat login (dashboard "Menunggu Approval")

### Export Data

- [ ] Export data barang ke Excel/CSV dan PDF
- [ ] Export laporan stok (lihat bawah) ke Excel/CSV dan PDF
- [ ] **Import TIDAK termasuk MVP** — input data awal dilakukan manual atau via seeder

### Laporan

- [ ] Laporan stok saat ini (on-hand) — bisa difilter per kategori/supplier
- [ ] Laporan mutasi stok (masuk/keluar, hanya yang berstatus approved) per rentang tanggal
- [ ] Laporan/alert barang dengan stok di bawah ambang batas **minimum per barang**
      (bukan satu angka global — setiap produk punya nilai `stok_minimum` sendiri)

### Pencarian & Filter

- [ ] Pencarian barang berdasarkan nama/SKU
- [ ] Filter barang berdasarkan kategori dan/atau supplier
- [ ] Filter laporan mutasi berdasarkan rentang tanggal

### Yang SECARA EKSPLISIT TIDAK Termasuk MVP

- ❌ Import data dari sistem lain
- ❌ Notifikasi eksternal (email/WhatsApp) untuk approval
- ❌ Approval berjenjang (multi-level)
- ❌ Multi-cabang/multi-gudang
- ❌ Barcode scanning

### Audit Trail

- [ ] Histori perubahan data master (barang, kategori, supplier, satuan) —
      siapa mengubah, kapan, dan dari nilai apa ke nilai apa
- [ ] Histori approval transaksi (sudah disebutkan di Autentikasi & Role)

### Manajemen User

- [ ] CRUD user (create, read, update, delete user)
- [ ] Assign role ke user (admin, kasir, audit, manager, staff toko)
- [ ] Reset password / ganti password
- [ ] User hanya bisa melihat dan mengedit data sesuai role yang dimiliki

### Dashboard

- [ ] Halaman utama (dashboard) setelah login
- [ ] Ringkasan statistik: total produk, jumlah low stock, transaksi pending untuk approval
- [ ] Quick access ke fitur utama (tambah transaksi, lihat laporan)

### Settings / Company Profile

- [ ] Konfigurasi profil perusahaan: nama, alamat, logo, kontak
- [ ] Konfigurasi dasar aplikasi (misal: format tanggal, mata uang default)
- [ ] Data profil ditampilkan di export laporan (PDF/Excel)

## Out of Scope

> ⚠️ AI Agent TIDAK BOLEH mengerjakan hal-hal berikut meskipun terlihat "wajar"
> untuk ditambahkan ke aplikasi inventaris pada umumnya.

### Integrasi & Sistem Eksternal

- ❌ Integrasi dengan sistem lain (ERP, akuntansi, POS, e-commerce, dll.) —
  sistem ini **berdiri sendiri sepenuhnya** (standalone). Tidak ada API
  konsumsi/expose ke sistem eksternal, tidak ada sinkronisasi data otomatis
  dalam bentuk apapun

### Platform

- ❌ Aplikasi mobile native (iOS/Android) — hanya web-based, responsive
  untuk diakses dari browser mobile jika perlu, bukan aplikasi terpisah
- ❌ Progressive Web App (PWA) dengan offline capability — kecuali
  dikonfirmasi sebagai kebutuhan terpisah

### Organisasi & Akses

- ❌ Multi-tenant/multi-organisasi — sistem didesain untuk **satu organisasi/toko
  saja**. Tidak perlu kolom `tenant_id`/`organization_id` atau isolasi data
  antar tenant di database
- ❌ Multi-cabang/multi-gudang — satu lokasi inventaris saja (sudah disebutkan
  di Constraint sebelumnya, ditegaskan ulang di sini)

### Fitur Tambahan yang Sengaja Ditunda

- ❌ Import data dari sistem lain (sudah disebutkan di Definisi Selesai MVP)
- ❌ Notifikasi eksternal (email/WhatsApp) untuk approval
- ❌ Approval berjenjang (multi-level) — hanya 1 level (Staff → Supervisor/Admin)
- ❌ Barcode/QR code scanning untuk input/cari barang
- ❌ Forecasting atau prediksi kebutuhan stok (AI/ML based reordering)
- ❌ Manajemen Purchase Order (PO) formal dengan approval terpisah — pencatatan
  stok masuk cukup mereferensikan nomor PO/faktur sebagai teks, bukan modul PO sendiri
- ❌ Manajemen retur barang (ke supplier atau dari customer) sebagai modul terpisah
- ❌ Histori harga (price history tracking) — sistem hanya menyimpan harga
  beli/jual terkini, bukan riwayat perubahan harga dari waktu ke waktu
- ❌ Dashboard analitik/visualisasi lanjutan (grafik tren, dll) — laporan
  cukup dalam bentuk tabel dan export Excel/CSV
