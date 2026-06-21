# Product Requirements Document (PRD)

## Aplikasi Inventaris Sederhana

---

## 1. Overview

### 1.1 Deskripsi Produk

Aplikasi web inventaris berbasis Laravel yang mengotomatisasi proses pencatatan dan pelacakan barang inventaris yang saat ini dilakukan secara manual. Aplikasi ini dirancang untuk penggunaan internal perusahaan dengan satu lokasi (single-location) dan maksimal 10 user concurrent.

### 1.2 Tujuan Utama

- Mendigitalisasi proses inventarisasi yang manual
- Mengurangi human error dalam pencatatan stok
- Mempercepat proses pencarian dan pelacakan barang
- Menyediakan laporan dan audit trail yang dapat di-export ke PDF/Excel

### 1.3 Target Pengguna

Seluruh staff usaha dengan 5 peran pengguna: admin, kasir, audit, manager, dan staff toko.

### 1.4 Lingkup MVP

MVP mencakup manajemen barang, manajemen stok dengan approval flow 1 level, autentikasi & role-based access, export data, laporan, pencarian & filter, audit trail, manajemen user, dashboard, dan settings/company profile.

---

## 2. Goals & Non-Goals

### 2.1 Goals (Tujuan yang Akan Dicapai)

1. Mengotomatisasi pencatatan inventaris yang saat ini manual
2. Mengurangi human error dalam pencatatan stok masuk/keluar
3. Mempercepat proses pencarian barang berdasarkan nama/SKU
4. Menyediakan approval flow untuk transaksi stok masuk/keluar
5. Menyediakan laporan stok dan mutasi yang dapat di-export ke PDF/Excel
6. Menyediakan audit trail untuk perubahan data master dan approval transaksi
7. Memberikan dashboard ringkasan statistik untuk monitoring inventaris

### 2.2 Non-Goals (Hal yang TIDAK Dilakukan)

1. Sistem TIDAK BOLEH mengimplementasikan multi-bahasa di fase ini — seluruh UI menggunakan Bahasa Indonesia saja
2. Sistem TIDAK BOLEH menyediakan import data dari sistem lain atau file Excel/CSV di fase ini — input data awal dilakukan manual atau via seeder
3. Sistem TIDAK BOLEH mengirim notifikasi eksternal (email atau WhatsApp) untuk approval — Supervisor/Admin mengecek daftar transaksi pending langsung di dashboard saat login
4. Sistem TIDAK BOLEH mengimplementasikan approval berjenjang (multi-level) — hanya 1 level approval (Staff → Supervisor/Admin)
5. Sistem TIDAK BOLEH mendukung multi-tenant/multi-organisasi — sistem dirancang untuk satu organisasi/toko saja, tidak ada kolom tenant_id atau isolasi data antar tenant
6. Sistem TIDAK BOLEH mendukung multi-cabang/multi-gudang — hanya satu lokasi inventaris
7. Sistem TIDAK BOLEH menyediakan barcode/QR code scanning untuk input atau pencarian barang
8. Sistem TIDAK BOLEH mengintegrasikan dengan sistem eksternal (ERP, akuntansi, POS, e-commerce) — sistem berdiri sendiri sepenuhnya
9. Sistem TIDAK BOLEH menyediakan aplikasi mobile native (iOS/Android) — hanya web-based dengan responsive design untuk browser mobile
10. Sistem TIDAK BOLEH menyediakan Progressive Web App (PWA) dengan offline capability di fase ini
11. Sistem TIDAK BOLEH menyediakan forecasting atau prediksi kebutuhan stok menggunakan AI/ML
12. Sistem TIDAK BOLEH menyediakan modul Purchase Order (PO) formal — pencatatan stok masuk cukup mereferensikan nomor PO/faktur sebagai teks
13. Sistem TIDAK BOLEH menyediakan modul manajemen retur barang sebagai modul terpisah
14. Sistem TIDAK BOLEH menyimpan histori harga (price history tracking) — hanya menyimpan harga beli/jual terkini
15. Sistem TIDAK BOLEH menyediakan dashboard analitik/visualisasi lanjutan dengan grafik tren — laporan cukup dalam bentuk tabel dan export Excel/CSV/PDF

---

## 3. User Roles & Permissions

### 3.1 Daftar Peran Pengguna

| Peran      | Level Approval | Deskripsi                                                               |
| ---------- | -------------- | ----------------------------------------------------------------------- |
| Staff toko | Staff          | Input transaksi stok masuk/keluar dengan status pending                 |
| Kasir      | Staff          | Input transaksi stok masuk/keluar dengan status pending                 |
| Audit      | Supervisor     | Approve/reject transaksi yang di-input Staff/Kasir                      |
| Manager    | Supervisor     | Approve/reject transaksi yang di-input Staff/Kasir                      |
| Admin      | Admin/Owner    | Full access, kelola master data, approve transaksi, lihat semua laporan |

### 3.2 Matrix Permission

| Fitur                     | Admin | Manager | Audit | Kasir   | Staff Toko |
| ------------------------- | ----- | ------- | ----- | ------- | ---------- |
| Dashboard                 | Full  | Full    | Full  | Limited | Limited    |
| Manajemen Barang (CRUD)   | ✓     | ✗       | ✗     | ✗       | ✗          |
| Manajemen Kategori (CRUD) | ✓     | ✗       | ✗     | ✗       | ✗          |
| Manajemen Supplier (CRUD) | ✓     | ✗       | ✗     | ✗       | ✗          |
| Manajemen Satuan (CRUD)   | ✓     | ✗       | ✗     | ✗       | ✗          |
| Input Stok Masuk          | ✓     | ✓       | ✓     | ✓       | ✓          |
| Input Stok Keluar         | ✓     | ✓       | ✓     | ✓       | ✓          |
| Stock Adjustment          | ✓     | ✓       | ✓     | ✗       | ✗          |
| Approve/Reject Transaksi  | ✓     | ✓       | ✓     | ✗       | ✗          |
| Lihat Semua Laporan       | ✓     | ✓       | ✓     | ✓       | ✓          |
| Export Data               | ✓     | ✓       | ✓     | ✓       | ✓          |
| Manajemen User (CRUD)     | ✓     | ✗       | ✗     | ✗       | ✗          |
| Settings/Company Profile  | ✓     | ✗       | ✗     | ✗       | ✗          |
| Audit Trail               | ✓     | ✓       | ✓     | ✗       | ✗          |

### 3.3 Aturan Approval

- Staff/Kasir hanya bisa input transaksi dengan status **pending**
- Supervisor (Manager/Audit) dan Admin bisa approve/reject transaksi pending
- Staff/Kasir TIDAK BISA approve transaksi yang mereka buat (validasi: user_id pembuat ≠ user_id approver)
- Reject wajib disertai catatan/alasan
- Stok hanya berubah setelah transaksi di-approve

---

## 4. Data Model (Ringkasan)

### 4.1 Entity Relationship Summary

**Core Entities:**

- **Users** — Data pengguna dengan role (admin, kasir, audit, manager, staff toko)
- **Products** — Data barang/produk dengan atribut: nama, SKU, kategori, supplier, harga beli, harga jual, satuan, stok saat ini, stok minimum
- **Categories** — Kategori barang untuk grouping
- **Suppliers** — Data supplier (relasi many-to-many dengan products)
- **Units** — Satuan pengukuran (pcs, kg, liter, dll)
- **Stock Transactions** — Transaksi stok masuk/keluar dengan status (pending/approved/rejected)
- **Stock Adjustments** — Penyesuaian stok untuk koreksi stok fisik
- **Approvals** — Histori approval transaksi (siapa approve/reject, kapan, catatan)
- **Audit Trails** — Histori perubahan data master (siapa mengubah, kapan, dari nilai apa ke nilai apa)
- **Company Profile** — Konfigurasi profil perusahaan dan setting aplikasi

**Key Relationships:**

- Product belongs to Category
- Product belongs to Unit
- Product and Supplier have many-to-many relationship
- Stock Transaction belongs to Product
- Stock Transaction belongs to User (creator)
- Approval belongs to Stock Transaction and User (approver)
- Audit Trail belongs to User (actor) dan dapat terkait dengan berbagai entity

---

## 5. Fitur & User Stories

### 5.1 Manajemen Barang

**US-001: CRUD Barang/Produk**

- Sebagai Admin, saya dapat membuat, membaca, mengupdate, dan mengarsipkan data barang/produk
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar barang dengan kolom: nama, SKU, kategori, supplier, harga beli, harga jual, satuan, stok saat ini, stok minimum, status aktif/non-aktif
  - AC2: Sistem menyediakan form create/edit barang dengan field: nama (required, max 255 chars), SKU (required, unique, max 100 chars), kategori (dropdown, required), supplier (multi-select, required), harga beli (decimal, required), harga jual (decimal, required), satuan (dropdown, required), stok minimum (integer, required, min 0)
  - AC3: Sistem mencegah duplicate SKU — jika SKU sudah ada, form menampilkan error message "SKU sudah digunakan"
  - AC4: Sistem menyediakan aksi archive (soft delete) — barang yang di-archive tidak muncul di daftar barang aktif tetapi tetap ada di database untuk histori
  - AC5: Sistem menampilkan konfirmasi sebelum delete/archive barang
  - AC6: Setelah create/edit berhasil, sistem menampilkan success notification dan redirect ke daftar barang

**US-002: CRUD Kategori Barang**

- Sebagai Admin, saya dapat mengelola kategori barang untuk grouping
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar kategori dengan kolom: nama, jumlah barang, aksi
  - AC2: Sistem menyediakan form create/edit kategori dengan field: nama (required, max 100 chars, unique)
  - AC3: Sistem mencegah delete kategori yang masih memiliki barang terkait — menampilkan error "Kategori tidak dapat dihapus karena masih digunakan oleh X barang"
  - AC4: Sistem menampilkan konfirmasi sebelum delete kategori

**US-003: CRUD Supplier**

- Sebagai Admin, saya dapat mengelola data supplier
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar supplier dengan kolom: nama, kontak, alamat, jumlah barang, aksi
  - AC2: Sistem menyediakan form create/edit supplier dengan field: nama (required, max 255 chars), kontak (max 100 chars), alamat (text), email (email format), telepon (max 20 chars)
  - AC3: Satu barang dapat memiliki lebih dari satu supplier (relasi many-to-many)
  - AC4: Sistem menampilkan konfirmasi sebelum delete supplier

**US-004: CRUD Satuan**

- Sebagai Admin, saya dapat mengelola satuan pengukuran
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar satuan dengan kolom: nama, singkatan, jumlah barang, aksi
  - AC2: Sistem menyediakan form create/edit satuan dengan field: nama (required, max 50 chars), singkatan (required, max 10 chars, unique)
  - AC3: Sistem mencegah delete satuan yang masih digunakan oleh barang — menampilkan error "Satuan tidak dapat dihapus karena masih digunakan oleh X barang"

### 5.2 Manajemen Stok

**US-005: Pencatatan Stok Masuk**

- Sebagai Staff/Kasir, saya dapat input transaksi stok masuk dengan status pending untuk di-approve
- Acceptance Criteria:
  - AC1: Sistem menampilkan form stok masuk dengan field: barang (dropdown, required), jumlah (integer, required, min 1), supplier (dropdown, required), referensi/no PO (max 100 chars, optional), catatan (text, optional)
  - AC2: Setelah submit, transaksi berstatus **pending** dan stok barang TIDAK berubah
  - AC3: Sistem menampilkan success notification "Transaksi stok masuk berhasil dibuat dan menunggu approval"
  - AC4: Transaksi pending muncul di daftar transaksi yang menunggu approval untuk Supervisor/Admin

**US-006: Pencatatan Stok Keluar**

- Sebagai Staff/Kasir, saya dapat input transaksi stok keluar dengan status pending untuk di-approve
- Acceptance Criteria:
  - AC1: Sistem menampilkan form stok keluar dengan field: barang (dropdown, required), jumlah (integer, required, min 1), alasan/kategori keluar (dropdown: penjualan, rusak, adjustment, lainnya, required), catatan (text, optional)
  - AC2: Sistem menampilkan stok saat ini dari barang yang dipilih sebagai informasi
  - AC3: Setelah submit, transaksi berstatus **pending** dan stok barang TIDAK berubah
  - AC4: Sistem menampilkan success notification "Transaksi stok keluar berhasil dibuat dan menunggu approval"

**US-007: Validasi Stok Tidak Negatif**

- Sebagai sistem, saya mencegah transaksi stok keluar yang menyebabkan stok negatif
- Acceptance Criteria:
  - AC1: Saat input stok keluar, jika jumlah yang diminta melebihi stok saat ini, sistem menampilkan error "Stok tidak mencukupi. Stok saat ini: X, yang diminta: Y"
  - AC2: Validasi ini berlaku untuk transaksi pending — stok belum berkurang, tetapi sistem tetap memvalidasi berdasarkan stok saat ini

**US-008: Stock Adjustment (Penyesuaian Stok)**

- Sebagai Admin/Supervisor, saya dapat melakukan penyesuaian stok untuk koreksi stok fisik
- Acceptance Criteria:
  - AC1: Sistem menampilkan form stock adjustment dengan field: barang (dropdown, required), stok fisik (integer, required, min 0), alasan (text, required)
  - AC2: Sistem menampilkan stok sistem saat ini sebagai referensi
  - AC3: Sistem menampilkan perhitungan selisih secara otomatis: Stok Sistem: X, Stok Fisik: Y, Selisih: Z (Y-X)
  - AC4: Setelah submit, transaksi adjustment berstatus **pending** dan memerlukan approval
  - AC5: Stock adjustment tidak bisa di-approve oleh user yang membuat adjustment (sama seperti transaksi stok masuk/keluar)
  - AC6: Setelah approved, stok barang diupdate ke nilai stok fisik yang diinput
  - AC7: Sistem mencatat selisih antara stok sistem dan stok fisik di audit trail

**US-009: Approve/Reject Transaksi**

- Sebagai Supervisor/Admin, saya dapat approve atau reject transaksi pending
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar transaksi pending dengan informasi: jenis transaksi, barang, jumlah, tanggal, user pembuat
  - AC2: Sistem menyediakan aksi Approve dan Reject untuk setiap transaksi
  - AC3: Saat approve, sistem menggunakan database transaction dengan row locking (SELECT ... FOR UPDATE) pada record produk untuk mencegah race condition
  - AC4: Saat approve, sistem melakukan validasi ulang stok mencukupi sebelum update stok. Jika stok tidak mencukupi saat approval, transaksi otomatis di-reject dengan alasan "Stok tidak mencukupi saat approval"
  - AC5: Saat approve, sistem mengupdate stok barang sesuai jumlah transaksi (stok bertambah untuk masuk, berkurang untuk keluar/adjustment)
  - AC6: Saat reject, sistem TIDAK mengupdate stok barang
  - AC7: Reject wajib disertai catatan/alasan — sistem menampilkan form input alasan reject (required, min 10 chars)
  - AC8: Sistem menolak reject tanpa alasan — menampilkan error "Alasan reject harus diisi (minimal 10 karakter)"
  - AC9: Setelah approve/reject, transaksi berstatus approved/rejected dan stok berubah (jika approved)
  - AC10: Jika terjadi error di tengah proses approval (update stok, catat approval, dll), seluruh transaksi di-rollback dan transaksi tetap berstatus pending
  - AC11: Sistem mencatat histori approval: siapa approver, kapan, transaksi apa, status, catatan

**US-010: Histori Mutasi Stok**

- Sebagai pengguna, saya dapat melihat histori mutasi stok per barang
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar mutasi stok untuk barang tertentu dengan kolom: tanggal, jenis (masuk/keluar/adjustment), jumlah, stok sebelum, stok sesudah, status (approved/rejected/pending), user pembuat, approver
  - AC2: Sistem hanya menampilkan mutasi dengan status approved dan pending — rejected tidak ditampilkan di histori utama
  - AC3: Sistem menyediakan filter berdasarkan rentang tanggal

### 5.3 Autentikasi & Role

**US-011: Login**

- Sebagai pengguna, saya dapat login ke sistem dengan kredensial saya
- Acceptance Criteria:
  - AC1: Sistem menampilkan form login dengan field: email/username (required), password (required), checkbox "Remember Me" (opsional)
  - AC2: Sistem memvalidasi kredensial — jika salah, menampilkan error "Email atau password salah"
  - AC3: Setelah login berhasil, sistem redirect ke dashboard
  - AC4: Sistem menyimpan session login dengan timeout sesuai konfigurasi (default: 2 jam inactivity atau 8 jam total)
  - AC5: Jika "Remember Me" dicentang, session diperpanjang menjadi 30 hari (atau sesuai konfigurasi)

**US-012: Logout**

- Sebagai pengguna, saya dapat logout dari sistem
- Acceptance Criteria:
  - AC1: Sistem menyediakan aksi logout di navigation bar
  - AC2: Setelah logout, sistem menghapus session dan redirect ke halaman login

**US-013: Role-Based Access Control**

- Sebagai sistem, saya membatasi akses fitur sesuai role pengguna
- Acceptance Criteria:
  - AC1: Menu dan fitur yang tidak diizinkan untuk role tertentu tidak ditampilkan atau dinonaktifkan
  - AC2: Jika user mencoba akses URL yang tidak diizinkan, sistem menampilkan error 403 (Forbidden)
  - AC3: Admin dapat mengakses semua fitur
  - AC4: Supervisor dapat approve/reject transaksi dan melihat semua laporan
  - AC5: Staff/Kasir hanya dapat input transaksi dan melihat laporan yang relevan

### 5.4 Export Data

**US-014: Export Data Barang**

- Sebagai pengguna, saya dapat export data barang ke Excel/CSV dan PDF
- Acceptance Criteria:
  - AC1: Sistem menyediakan tombol Export di halaman daftar barang
  - AC2: Sistem mengexport data barang ke format Excel/CSV dengan kolom: nama, SKU, kategori, supplier, harga beli, harga jual, satuan, stok saat ini, stok minimum
  - AC3: Sistem mengexport data barang ke format PDF dengan header perusahaan dari company profile
  - AC4: Export PDF menyertakan tanggal export dan nama user yang melakukan export

**US-015: Export Laporan Stok**

- Sebagai pengguna, saya dapat export laporan stok ke Excel/CSV dan PDF
- Acceptance Criteria:
  - AC1: Sistem menyediakan tombol Export di halaman laporan stok
  - AC2: Export Excel/CSV mencakup kolom: nama barang, SKU, kategori, stok saat ini, stok minimum, status (normal/low stock)
  - AC3: Export PDF menyertakan header perusahaan, tanggal export, dan filter yang diterapkan
  - AC4: Export laporan mutasi mencakup kolom: tanggal, jenis transaksi, barang, jumlah, stok sebelum, stok sesudah, status, user pembuat, approver

### 5.5 Laporan

**US-016: Laporan Stok Saat Ini (On-Hand)**

- Sebagai pengguna, saya dapat melihat laporan stok saat ini yang dapat difilter
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar stok saat ini dengan kolom: nama barang, SKU, kategori, supplier, stok saat ini, stok minimum, status (normal/low stock)
  - AC2: Sistem menyediakan filter berdasarkan kategori (dropdown) dan supplier (dropdown)
  - AC3: Sistem menandai barang dengan stok di bawah stok minimum sebagai "Low Stock" dengan warna yang berbeda
  - AC4: Sistem menampilkan jumlah total barang dan jumlah low stock di header laporan
  - AC5: Sistem menyediakan tombol Export Excel/CSV dan Export PDF

**US-017: Laporan Mutasi Stok**

- Sebagai pengguna, saya dapat melihat laporan mutasi stok per rentang tanggal
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar mutasi stok dengan kolom: tanggal, jenis (masuk/keluar/adjustment), barang, jumlah, stok sebelum, stok sesudah, status, user pembuat, approver
  - AC2: Sistem menyediakan filter rentang tanggal (dari - sampai)
  - AC3: Sistem hanya menampilkan transaksi dengan status approved dan pending
  - AC4: Sistem menampilkan total masuk, total keluar, dan total adjustment di header laporan
  - AC5: Sistem menyediakan filter cepat: "Hari Ini", "Minggu Ini", "Bulan Ini"

**US-018: Laporan/Alert Low Stock**

- Sebagai pengguna, saya dapat melihat daftar barang dengan stok di bawah ambang batas minimum
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar barang dengan stok di bawah stok minimum masing-masing
  - AC2: Sistem menampilkan kolom: nama barang, SKU, stok saat ini, stok minimum, selisih (stok minimum - stok saat ini)
  - AC3: Sistem mengurutkan berdasarkan stok saat ini (ascending) — barang dengan stok paling rendah ditampilkan pertama
  - AC4: Sistem menyediakan link ke halaman detail barang untuk setiap item
  - AC5: Sistem menyediakan tombol Export Excel/CSV dan Export PDF

### 5.6 Pencarian & Filter

**US-019: Pencarian Barang**

- Sebagai pengguna, saya dapat mencari barang berdasarkan nama atau SKU
- Acceptance Criteria:
  - AC1: Sistem menyediakan search box di halaman daftar barang
  - AC2: Pencarian melakukan match terhadap nama barang (case-insensitive) dan SKU
  - AC3: Hasil pencarian ditampilkan secara real-time saat user mengetik (minimal 2 karakter)
  - AC4: Jika tidak ada hasil, sistem menampilkan message "Tidak ada barang yang ditemukan"

**US-020: Filter Barang**

- Sebagai pengguna, saya dapat filter barang berdasarkan kategori dan/atau supplier
- Acceptance Criteria:
  - AC1: Sistem menyediakan dropdown filter kategori dan supplier di halaman daftar barang
  - AC2: Filter dapat dikombinasikan — user dapat filter berdasarkan kategori dan supplier sekaligus
  - AC3: Filter dapat dikombinasikan dengan search — user dapat mencari dan filter secara bersamaan
  - AC4: Sistem menampilkan jumlah hasil filter

**US-021: Filter Laporan Mutasi**

- Sebagai pengguna, saya dapat filter laporan mutasi berdasarkan rentang tanggal
- Acceptance Criteria:
  - AC1: Sistem menyediakan input tanggal dari dan sampai di halaman laporan mutasi
  - AC2: Format tanggal menggunakan format yang dikonfigurasi di settings (default: YYYY-MM-DD)
  - AC3: Jika hanya tanggal dari diisi, sistem menampilkan mutasi dari tanggal tersebut sampai hari ini
  - AC4: Jika tidak ada filter tanggal, sistem menampilkan semua mutasi

### 5.7 Audit Trail

**US-022: Histori Perubahan Data Master**

- Sebagai Admin, saya dapat melihat histori perubahan data master (barang, kategori, supplier, satuan)
- Acceptance Criteria:
  - AC1: Sistem mencatat setiap create, update, delete/archive pada data master
  - AC2: Audit trail mencakup: siapa pengguna, kapan, aksi apa (create/update/delete), entity apa, data sebelum dan sesudah (untuk update)
  - AC3: Sistem menampilkan audit trail dengan filter berdasarkan: entity type, user, rentang tanggal
  - AC4: Sistem menampilkan audit trail dalam format tabel dengan kolom: tanggal, user, aksi, entity, detail perubahan

**US-023: Histori Approval Transaksi**

- Sebagai pengguna, saya dapat melihat histori approval transaksi
- Acceptance Criteria:
  - AC1: Sistem mencatat setiap aksi approve/reject dengan: siapa approver, kapan, transaksi apa, status, catatan/alasan
  - AC2: Histori approval ditampilkan di detail transaksi
  - AC3: Admin dapat melihat semua histori approval
  - AC4: Supervisor dapat melihat histori approval yang mereka lakukan
  - AC5: Staff/Kasir dapat melihat histori approval untuk transaksi yang mereka buat, termasuk status final dan alasan reject (jika ada)

### 5.8 Manajemen User

**US-024: CRUD User**

- Sebagai Admin, saya dapat mengelola data pengguna
- Acceptance Criteria:
  - AC1: Sistem menampilkan daftar user dengan kolom: nama, email, role, status aktif, tanggal dibuat
  - AC2: Sistem menyediakan form create/edit user dengan field: nama (required, max 255 chars), email (required, unique, email format), password (min 8 chars, required untuk create, optional untuk edit), role (dropdown: admin, kasir, audit, manager, staff toko, required), status aktif (checkbox)
  - AC3: Sistem menyimpan password dengan hashing (bcrypt)
  - AC4: Sistem menampilkan konfirmasi sebelum delete user
  - AC5: Sistem mencegah admin menghapus akunnya sendiri — menampilkan error "Tidak dapat menghapus akun sendiri"

**US-025: Reset/Change Password**

- Sebagai Admin/User, saya dapat mereset atau mengganti password
- Acceptance Criteria:
  - AC1: Admin dapat reset password user lain — sistem generate password temporary dan menampilkan ke admin
  - AC2: Password temporary harus diganti pada login pertama kali
  - AC3: User dapat mengganti password sendiri di halaman settings — sistem memvalidasi password lama
  - AC4: Validasi password baru: minimal 8 karakter, harus berbeda dari password lama

### 5.9 Dashboard

**US-026: Halaman Dashboard**

- Sebagai pengguna, saya dapat melihat dashboard ringkasan setelah login
- Acceptance Criteria:
  - AC1: Sistem menampilkan dashboard dengan statistik: total produk, jumlah low stock, transaksi pending untuk approval
  - AC2: Statistik low stock menampilkan jumlah barang dengan stok di bawah stok minimum
  - AC3: Statistik transaksi pending menampilkan jumlah transaksi yang menunggu approval
  - AC4: Sistem menyediakan quick access ke fitur utama: tambah transaksi masuk, tambah transaksi keluar, lihat laporan
  - AC5: Dashboard menampilkan alert jika ada transaksi pending yang perlu di-approve (hanya untuk Supervisor/Admin)

### 5.10 Settings / Company Profile

**US-027: Konfigurasi Profil Perusahaan**

- Sebagai Admin, saya dapat mengkonfigurasi profil perusahaan
- Acceptance Criteria:
  - AC1: Sistem menyediakan form settings dengan field: nama perusahaan (required, max 255 chars), alamat (text), logo (image upload, max 2MB, format: jpg/png), kontak (max 255 chars)
  - AC2: Logo yang diupload disimpan di storage/app/public dan ditampilkan di export PDF
  - AC3: Sistem menyediakan preview logo sebelum save

**US-028: Konfigurasi Aplikasi**

- Sebagai Admin, saya dapat mengkonfigurasi setting dasar aplikasi
- Acceptance Criteria:
  - AC1: Sistem menyediakan setting: format tanggal (dropdown: YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY), mata uang default (dropdown: IDR, USD, dll)
  - AC2: Konfigurasi ini diterapkan ke seluruh aplikasi — laporan, export, dan display tanggal
  - AC3: Default format tanggal: YYYY-MM-DD, default mata uang: IDR

---

## 6. Alur Kerja Utama

### 6.1 Alur Input Stok Masur

1. Staff/Kasir login ke sistem
2. Staff/Kasir memilih menu "Stok Masuk"
3. Staff/Kasir mengisi form: pilih barang, jumlah, supplier, referensi/PO, catatan
4. Sistem validasi: semua field required terisi
5. Staff/Kasir submit form
6. Sistem menyimpan transaksi dengan status **pending**
7. Stok barang TIDAK berubah
8. Sistem menampilkan notifikasi "Transaksi berhasil dibuat, menunggu approval"
9. Supervisor/Admin melihat transaksi pending di dashboard "Menunggu Approval"
10. Supervisor/Admin memilih transaksi untuk di-approve atau di-reject
11. Jika approve: sistem update stok barang (stok bertambah), transaksi berstatus approved, histori approval tercatat
12. Jika reject: sistem meminta alasan reject, transaksi berstatus rejected, stok TIDAK berubah, histori approval tercatat

### 6.2 Alur Input Stok Keluar

1. Staff/Kasir login ke sistem
2. Staff/Kasir memilih menu "Stok Keluar"
3. Staff/Kasir mengisi form: pilih barang, jumlah, alasan keluar, catatan
4. Sistem validasi: stok mencukupi (jumlah ≤ stok saat ini)
5. Staff/Kasir submit form
6. Sistem menyimpan transaksi dengan status **pending**
7. Stok barang TIDAK berubah
8. Sistem menampilkan notifikasi "Transaksi berhasil dibuat, menunggu approval"
9. Supervisor/Admin melihat transaksi pending di dashboard
10. Supervisor/Admin approve/reject transaksi (sama seperti stok masuk)
11. Jika approve: sistem update stok barang (stok berkurang), transaksi berstatus approved
12. Jika reject: stok TIDAK berubah, transaksi berstatus rejected

### 6.3 Alur Stock Adjustment

1. Admin/Supervisor login ke sistem
2. Admin/Supervisor memilih menu "Stock Adjustment"
3. Admin/Supervisor mengisi form: pilih barang, input stok fisik, alasan adjustment
4. Sistem menampilkan stok sistem saat ini sebagai referensi
5. Admin/Supervisor submit form
6. Sistem menyimpan transaksi adjustment dengan status **pending**
7. Supervisor/Admin (yang berbeda) approve transaksi
8. Sistem update stok barang ke nilai stok fisik
9. Sistem mencatat selisih di audit trail

### 6.4 Alur Export Laporan

1. Pengguna login ke sistem
2. Pengguna memilih menu "Laporan" dan memilih jenis laporan (stok saat ini, mutasi stok, low stock)
3. Pengguna menerapkan filter jika diperlukan (kategori, supplier, rentang tanggal)
4. Pengguna klik tombol "Export Excel" atau "Export PDF"
5. Sistem generate file sesuai format yang dipilih
6. Sistem trigger download file ke browser pengguna

---

## 7. Spesifikasi Teknis

### 7.1 Tech Stack

- **Backend:** Laravel 13
- **Database:** MySQL
- **Frontend Framework:** Livewire
- **CSS Framework:** Tailwind CSS
- **Authentication:** Laravel Breeze atau Laravel UI (sesuai preferensi, dengan modifikasi untuk role-based access)

### 7.2 Arsitektur

- Monolithic architecture (Laravel standard)
- Single database (MySQL) tanpa Redis cluster atau queue worker permanen
- Shared hosting atau VPS entry-level (1 vCPU, 1-2GB RAM)
- Maksimal 10 user concurrent

### 7.3 Database Schema (Ringkasan)

**Tables Utama:**

- `users` — id, name, email, password, role (enum: admin, kasir, audit, manager, staff_toko), is_active, timestamps
- `products` — id, name, sku, category_id, unit_id, buy_price, sell_price, current_stock, min_stock, is_active, timestamps
- `categories` — id, name, timestamps
- `suppliers` — id, name, contact, address, email, phone, timestamps
- `units` — id, name, abbreviation, timestamps
- `product_supplier` — id, product_id, supplier_id (pivot table untuk many-to-many)
- `stock_transactions` — id, product_id, user_id, type (enum: in, out, adjustment), quantity, reference_no, notes, status (enum: pending, approved, rejected), timestamps
- `approvals` — id, stock_transaction_id, user_id, action (enum: approve, reject), notes, timestamps
- `stock_adjustments` — id, product_id, user_id, physical_stock, reason, status (enum: pending, approved, rejected), timestamps
- `audit_trails` — id, user_id, entity_type, entity_id, action (enum: create, update, delete), old_values, new_values, timestamps
- `company_profiles` — id, company_name, address, logo_path, contact, date_format, currency, timestamps

### 7.4 Package & Library yang Direkomendasikan

- **PDF Export:** barryvdh/laravel-dompdf atau wkhtmltopdf
- **Excel Export:** maatwebsite/excel
- **Authorization:** spatie/laravel-permission (opsional, atau implementasi manual dengan middleware)
- **Validation:** Laravel built-in validation
- **File Upload:** Laravel built-in storage

### 7.5 Deployment

- Shared hosting atau VPS entry-level
- PHP 8.2+, MySQL 8.0+, Composer
- Web server: Nginx atau Apache
- Storage: storage/app/public untuk logo dan export temporary
- Tidak memerlukan queue worker permanen — export bisa synchronous atau menggunakan Laravel job dengan database queue (jika diperlukan)

---

## 8. Non-Functional Requirements

### 8.1 Performance

- **NFR-01:** Halaman dashboard harus load dalam waktu maksimal 2 detik pada koneksi internet standar (4G/5G)
- **NFR-02:** Daftar barang dengan 1000 records harus dapat ditampilkan dengan pagination (20-50 items per halaman) dan load dalam waktu maksimal 1 detik
- **NFR-03:** Export Excel/CSV dengan 10.000 records harus selesai dalam waktu maksimal 30 detik
- **NFR-04:** Export PDF dengan 1000 records harus selesai dalam waktu maksimal 15 detik
- **NFR-05:** Form submit transaksi stok harus memberikan feedback dalam waktu maksimal 500ms

### 8.2 Security

- **NFR-06:** Password harus di-hash menggunakan bcrypt dengan minimal 10 rounds
- **NFR-07:** Sistem harus menggunakan HTTPS pada production
- **NFR-08:** Sistem harus proteksi terhadap SQL Injection (menggunakan Eloquent ORM dan parameterized query)
- **NFR-09:** Sistem harus proteksi terhadap XSS (menggunakan escaping output dari Blade/Livewire)
- **NFR-10:** Sistem harus proteksi terhadap CSRF (menggunakan Laravel CSRF token)
- **NFR-11:** Session harus expire setelah 2 jam inactivity atau 8 jam total
- **NFR-12:** File upload (logo) harus divalidasi: max 2MB, format jpg/png saja
- **NFR-13:** Role-based access harus di-enforce di backend — tidak cukup hanya menyembunyikan UI element

### 8.3 Usability

- **NFR-14:** Sistem harus responsive — dapat diakses dari desktop, tablet, dan smartphone (minimal 320px width)
- **NFR-15:** Form harus menyediakan validation message yang jelas dan spesifik di atas form
- **NFR-16:** Sistem harus menyediakan konfirmasi sebelum aksi destruktif (delete, reject)
- **NFR-17:** Error message harus ditampilkan dalam Bahasa Indonesia dan mudah dipahami oleh user non-teknis
- **NFR-18:** Sistem harus konsisten dalam penggunaan terminology (misal: "Stok Masuk" bukan "Stock In" atau "Barang Masuk")

### 8.4 Reliability

- **NFR-19:** Sistem harus memiliki uptime minimal 99% (maksimal 43.8 menit downtime per bulan)
- **NFR-20:** Database backup harus dilakukan minimal sekali sehari dengan retensi 7 hari
- **NFR-21:** Transaksi stok harus atomic — jika ada error di tengah proses, seluruh transaksi di-rollback
- **NFR-22:** Sistem harus menangani error dengan baik — menampilkan user-friendly error message, bukan stack trace

### 8.5 Maintainability

- **NFR-23:** Kode harus mengikuti PSR-12 coding standard
- **NFR-24:** Setiap route harus memiliki nama (named route) untuk kemudahan maintenance
- **NFR-25:** Database migration harus digunakan untuk semua perubahan schema
- **NFR-26:** Dokumentasi API (jika ada) atau dokumentasi endpoint harus disediakan

---

## 9. Risiko & Edge Cases

### 9.1 Risiko

| ID   | Risiko                                                                  | Dampak | Probabilitas | Mitigasi                                                                                                            |
| ---- | ----------------------------------------------------------------------- | ------ | ------------ | ------------------------------------------------------------------------------------------------------------------- |
| R-01 | Stok menjadi negatif karena bug pada approval flow                      | High   | Medium       | Implementasi database transaction dan validasi stok sebelum update. Unit test untuk approval flow.                  |
| R-02 | Race condition pada concurrent approval transaksi yang sama             | High   | Low          | Gunakan database transaction dengan row locking (SELECT ... FOR UPDATE) saat update stok                            |
| R-03 | Export PDF/Excel timeout pada data besar (>10.000 records)              | Medium | Medium       | Implementasi chunk processing untuk export, atau limit export maksimal 10.000 records dengan pesan error yang jelas |
| R-04 | User lupa password dan tidak bisa login                                 | Medium | Low          | Implementasi fitur reset password yang aman                                                                         |
| R-05 | Bug pada role-based access — user bisa akses fitur yang tidak diizinkan | High   | Medium       | Enforce authorization di backend, bukan hanya di frontend. Integration test untuk setiap role.                      |
| R-06 | File upload logo terlalu besar atau format tidak didukung               | Low    | Medium       | Validasi file size dan format di backend dan frontend                                                               |
| R-07 | Database connection issue pada shared hosting                           | High   | Low          | Konfigurasi database connection pooling, implementasi retry mechanism                                               |
| R-08 | Timeout pada shared hosting untuk export besar                          | Medium | Medium       | Gunakan queue job untuk export besar, atau batasi ukuran export                                                     |

### 9.2 Edge Cases

| ID    | Edge Case                                                                    | Expected Behavior                                                                                                 |
| ----- | ---------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| EC-01 | User input stok keluar dengan jumlah exactly sama dengan stok saat ini       | Transaksi berhasil, stok menjadi 0                                                                                |
| EC-02 | User input stok keluar dengan jumlah lebih besar dari stok saat ini          | Sistem menolak dengan error "Stok tidak mencukupi"                                                                |
| EC-03 | Staff mencoba approve transaksi yang mereka buat sendiri                     | Sistem menolak dengan error "Tidak dapat approve transaksi sendiri"                                               |
| EC-04 | Supervisor approve transaksi yang sudah di-approve oleh user lain            | Sistem menampilkan error "Transaksi sudah di-approve"                                                             |
| EC-05 | User menghapus barang yang sedang direferensikan di transaksi pending        | Sistem mencegah delete dengan error "Barang tidak dapat dihapus karena digunakan dalam transaksi pending"         |
| EC-06 | Export laporan dengan filter yang tidak ada hasil                            | Sistem menampilkan message "Tidak ada data untuk filter yang dipilih" dan mengembalikan file kosong atau error    |
| EC-07 | Upload logo dengan ukuran 2.1MB                                              | Sistem menolak dengan error "Ukuran file maksimal 2MB"                                                            |
| EC-08 | Upload logo dengan format .webp                                              | Sistem menolak dengan error "Format file harus jpg atau png"                                                      |
| EC-09 | User login dengan email yang belum terverifikasi (jika ada verifikasi email) | Sistem menampilkan error "Email belum diverifikasi" atau langsung login (tergantung konfigurasi)                  |
| EC-10 | Stock adjustment dengan stok fisik sama dengan stok sistem                   | Sistem tetap memproses adjustment, stok tidak berubah, tetapi audit trail tercatat                                |
| EC-11 | Reject transaksi tanpa alasan                                                | Sistem menolak submit dengan error "Alasan reject harus diisi (minimal 10 karakter)"                              |
| EC-12 | User input stok masuk dengan jumlah 0 atau negatif                           | Sistem menolak dengan error "Jumlah harus lebih besar dari 0"                                                     |
| EC-13 | Cari barang dengan query yang sangat umum (misal: "a") dan hasil >1000       | Sistem menampilkan maksimal 50 hasil per halaman dengan pagination                                                |
| EC-14 | Export PDF dengan perusahaan yang belum mengisi logo                         | Sistem generate PDF tanpa logo, tidak menampilkan error                                                           |
| EC-15 | User mencoba akses halaman approval dengan role Staff                        | Sistem menampilkan error 403 Forbidden                                                                            |
| EC-16 | Concurrent approval transaksi stok keluar untuk barang dengan stok terbatas  | Salah satu transaksi di-approve, yang kedua di-reject otomatis dengan alasan "Stok tidak mencukupi saat approval" |

---

## 10. Catatan & Pertimbangan

### 10.1 Item yang Perlu Konfirmasi (dari Project Brief)

Berikut adalah item yang ditandai **[PERLU KONFIRMASI]** di project brief yang belum dijawab:

1. **Masalah spesifik lain** — Apakah ada masalah spesifik lain yang dihadapi dalam proses inventarisasi manual selain yang sudah disebutkan?
2. **Audit trail wajib** — Audit trail sudah termasuk di PRD ini. Apakah ada kebutuhan spesifik lain?
3. **Backup otomatis** — Backup otomatis dengan retensi tertentu sudah disebutkan di NFR-20. Apakah retensi 7 hari sudah cukup?
4. **Standar keamanan** — Apakah ada standar keamanan tertentu yang perlu dipatuhi (misal: karena data terhubung ke sistem keuangan)?
5. **Tim & Resource** — Berapa developer yang mengerjakan, dan apakah dikerjakan oleh AI Agent + 1 developer, atau full AI-assisted solo?

### 10.2 Fitur yang Dianggap Penting tapi Belum Disebut di Project Brief

Berikut adalah fitur yang dianggap penting untuk aplikasi inventaris tetapi belum disebut di project brief. Fitur-fitur ini TIDAK dimasukkan ke PRD dan perlu dikonfirmasi apakah akan ditambahkan:

1. **Low Stock Alert/Notification di Dashboard** — Menampilkan alert visual jika ada barang dengan stok di bawah minimum. (Catatan: Project brief sudah menyebutkan "jumlah low stock" di dashboard, tetapi tidak menjelaskan apakah ada alert/notification khusus)
2. **Bulk Action untuk Approve Multiple Transaksi** — Kemampuan approve beberapa transaksi pending sekaligus untuk meningkatkan efisiensi. (Catatan: Saat ini hanya approve satu per satu)
3. **Print/Export Struk untuk Transaksi** — Kemampuan print struk atau invoice untuk transaksi stok masuk/keluar. (Catatan: Berguna untuk audit fisik, tetapi belum disebut di project brief)
4. **Filter Barang Berdasarkan Status Stok** — Filter khusus untuk menampilkan hanya barang normal, low stock, atau out of stock. (Catatan: Bisa jadi pengembangan dari filter yang sudah ada)
5. **Reorder Point / Recommended Order Quantity** — Menampilkan saran jumlah pemesanan ulang berdasarkan stok minimum dan lead time supplier. (Catatan: Berhubungan dengan forecasting yang sudah di-out-of-scope)

---

## 11. Acceptance Criteria Summary

### 11.1 Kriteria MVP Success

Aplikasi dianggap MVP-ready jika seluruh kriteria berikut terpenuhi:

**Manajemen Barang:**

- [ ] CRUD barang/produk dengan atribut minimal: nama, SKU, kategori, supplier, harga beli, harga jual, satuan, stok saat ini, stok minimum
- [ ] CRUD kategori barang
- [ ] CRUD supplier dengan relasi many-to-many barang ↔ supplier
- [ ] CRUD satuan

**Manajemen Stok:**

- [ ] Pencatatan stok masuk dengan status pending
- [ ] Pencatatan stok keluar dengan status pending
- [ ] Stok ter-update otomatis setelah approved
- [ ] Histori mutasi stok tersimpan dan bisa ditelusuri per barang
- [ ] Stock adjustment dengan alasan yang jelas
- [ ] Validasi: stok tidak boleh negatif
- [ ] Validasi ulang stok saat approval untuk mencegah race condition
- [ ] Transaksi rejected bisa dilacak oleh pembuat

**Autentikasi & Role:**

- [ ] Login dengan 5 role (admin, kasir, audit, manager, staff toko)
- [ ] Transaksi pending sampai di-approve
- [ ] Staff tidak bisa approve transaksi sendiri
- [ ] Reject wajib disertai alasan
- [ ] Histori approval tercatat
- [ ] Tidak ada notifikasi eksternal
- [ ] Fitur "Remember Me" untuk memperpanjang session

**Export Data:**

- [ ] Export barang ke Excel/CSV dan PDF
- [ ] Export laporan stok ke Excel/CSV dan PDF
- [ ] Import TIDAK termasuk MVP

**Laporan:**

- [ ] Laporan stok saat ini dengan filter kategori/supplier
- [ ] Laporan mutasi stok per rentang tanggal
- [ ] Laporan/alert barang dengan stok di bawah minimum
- [ ] Export laporan stok saat ini dan low stock ke Excel/PDF
- [ ] Filter laporan mutasi berdasarkan jenis transaksi dan status

**Pencarian & Filter:**

- [ ] Pencarian barang berdasarkan nama/SKU
- [ ] Filter barang berdasarkan kategori dan/atau supplier
- [ ] Filter laporan mutasi berdasarkan rentang tanggal

**Audit Trail:**

- [ ] Histori perubahan data master
- [ ] Histori approval transaksi

**Manajemen User:**

- [ ] CRUD user
- [ ] Assign role ke user
- [ ] Reset/ganti password
- [ ] Halaman "Transaksi Saya" untuk Staff/Kasir

**Dashboard:**

- [ ] Halaman utama dengan statistik
- [ ] Ringkasan: total produk, low stock, transaksi pending
- [ ] Quick access ke fitur utama

**Settings:**

- [ ] Konfigurasi profil perusahaan
- [ ] Konfigurasi format tanggal dan mata uang
- [ ] Data profil ditampilkan di export

---

_Dokumen ini dibuat berdasarkan Project Brief v1.0 dan perlu direview serta di-approve sebelum development dimulai._
