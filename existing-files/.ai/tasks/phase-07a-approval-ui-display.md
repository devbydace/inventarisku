# Phase 7a: Approval UI & Display

## Tujuan

Implementasi halaman approval index untuk menampilkan daftar transaksi pending dengan tombol approve/reject dan form reject dengan alasan.

## Scope

- Buat Livewire component untuk Approval: Index (daftar transaksi pending)
- Buat ApprovalController untuk handle approve/reject action
- Implementasi form reject dengan input alasan (required, min 10 chars)
- Implementasi konfirmasi dialog untuk approve dan reject
- Setup route dengan middleware permission:approve-transaction
- Buat view Blade untuk approval index

## Non-Goals (Eksplisit)

- TIDAK membuat business logic approval (di Phase 7b)
- TIDAK membuat validasi self-approval prevention (di Phase 7b)
- TIDAK membuat database transaction dengan row locking (di Phase 7b)
- TIDAK membuat update stok otomatis setelah approval (di Phase 7b)
- TIDAK membuat stock adjustment (di Phase 8)
- TIDAK membuat histori mutasi stok (di Phase 9)
- TIDAK membuat notifikasi eksternal (email/WA) untuk approval
- TIDAK membuat approval berjenjang (multi-level) — hanya 1 level
- TIDAK membuat bulk approve (approve satu per satu saja)

## Referensi ke PRD/Architecture

- **PRD Section 5.2 (Manajemen Stok):** US-009 (Approve/Reject Transaksi)
- **PRD Section 3.3 (Aturan Approval):** Staff tidak bisa approve sendiri, reject wajib disertai alasan, 1 level approval
- **architecture.md Section 3.8 (Approval Model):** Relasi dan $fillable
- **architecture.md Section 4.1 (Route Design):** Route approval dengan middleware role:admin,manager,audit

## Acceptance Criteria (Testable)

### AC-01: Approval Index Page Display

- [ ] Halaman approval menampilkan daftar transaksi pending dengan informasi: jenis transaksi (masuk/keluar), nama barang, jumlah, tanggal, user pembuat, status
- [ ] Hanya menampilkan transaksi dengan status 'pending'
- [ ] Transaksi diurutkan berdasarkan created_at descending (yang paling baru di atas)
- [ ] Setiap transaksi memiliki tombol "Approve" dan "Reject"
- [ ] Jika tidak ada transaksi pending, menampilkan message "Tidak ada transaksi yang menunggu approval"
- [ ] Halaman menampilkan jumlah total transaksi pending di header

### AC-02: Transaction Information Display

- [ ] Setiap transaksi menampilkan: jenis transaksi (badge: hijau untuk masuk, merah untuk keluar), nama barang, SKU barang, jumlah, satuan
- [ ] Setiap transaksi menampilkan: tanggal pembuatan, nama user pembuat, waktu yang lalu (misal: "2 jam yang lalu")
- [ ] Untuk stok masuk, menampilkan informasi supplier
- [ ] Untuk stok keluar, menampilkan alasan keluar
- [ ] Jika ada referensi/PO, menampilkan referensi tersebut
- [ ] Jika ada catatan, menampilkan catatan tersebut

### AC-03: Approve Button & Confirmation

- [ ] Setiap transaksi memiliki tombol "Approve" dengan icon ceklis (✓)
- [ ] Setelah klik "Approve", sistem menampilkan konfirmasi dialog "Apakah Anda yakin ingin approve transaksi ini?"
- [ ] Dialog konfirmasi menampilkan informasi transaksi yang akan di-approve
- [ ] Tombol "Cancel" dan "Approve" di dialog konfirmasi
- [ ] Setelah approve, transaksi hilang dari daftar pending (karena status sudah approved)
- [ ] Setelah approve, sistem menampilkan success notification "Transaksi berhasil di-approve"

### AC-04: Reject Button & Form

- [ ] Setiap transaksi memiliki tombol "Reject" dengan icon X (warna merah)
- [ ] Setelah klik "Reject", sistem menampilkan form input alasan reject (modal atau inline form)
- [ ] Form reject menampilkan textarea dengan label "Alasan Reject (minimal 10 karakter)"
- [ ] Form reject menampilkan character counter (misal: "0/10 karakter")
- [ ] Tombol "Cancel" dan "Reject" di form reject
- [ ] Setelah reject dengan alasan, transaksi hilang dari daftar pending
- [ ] Setelah reject, sistem menampilkan success notification "Transaksi berhasil di-reject"

### AC-05: Reject Validation

- [ ] Sistem menolak reject jika alasan kosong — menampilkan error "Alasan reject harus diisi"
- [ ] Sistem menolak reject jika alasan kurang dari 10 karakter — menampilkan error "Alasan reject harus minimal 10 karakter"
- [ ] Error message ditampilkan dalam Bahasa Indonesia
- [ ] Form reject tetap terbuka jika validasi gagal (tidak di-close)

### AC-06: Authorization

- [ ] Halaman approval hanya bisa diakses oleh user dengan permission approve-transaction (admin, manager, audit)
- [ ] User dengan role kasir atau staff_toko tidak bisa akses halaman approval (403 Forbidden)
- [ ] Tombol approve/reject hanya muncul untuk user dengan permission approve-transaction
- [ ] User yang belum login tidak bisa akses halaman approval (redirect ke login)

### AC-07: Loading & Error States

- [ ] Saat proses approve/reject, tombol menampilkan loading state (spinner atau disabled)
- [ ] Jika terjadi error saat approve/reject, sistem menampilkan error notification
- [ ] Jika approve/reject berhasil, halaman refresh atau transaksi di-remove dari list
- [ ] Jika network error, sistem menampilkan error dan user bisa retry

### AC-08: Data Display & UX

- [ ] Tabel responsif dan dapat dibaca dengan baik di desktop dan tablet
- [ ] Warna dan styling konsisten dengan tema aplikasi
- [ ] Icon dan badge menggunakan Bahasa Indonesia
- [ ] Tooltip atau help text untuk informasi yang kurang jelas
- [ ] Empty state menampilkan icon dan message yang ramah pengguna

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Approval/` berisi `Index.php`
2. **Component Logic:**
   - Query transaksi pending: `StockTransaction::with(['product', 'user'])->where('status', 'pending')->orderByDesc('created_at')->get()`
   - Method `approve($transactionId)` untuk handle approve
   - Method `reject($transactionId, $reason)` untuk handle reject
   - Method `confirmApprove($transactionId)` untuk tampilkan konfirmasi
   - Method `showRejectForm($transactionId)` untuk tampilkan form reject
3. **View Structure:**
   - Tabel dengan kolom: jenis, barang, jumlah, tanggal, user, aksi
   - Modal untuk konfirmasi approve
   - Modal atau inline form untuk reject
   - Empty state jika tidak ada transaksi pending
4. **Styling:** Gunakan Tailwind CSS untuk styling yang konsisten
5. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 6 (stock transaction) — transaksi pending harus ada, user harus login dengan permission approve-transaction
6. **Next Phase:** Setelah Phase 7a selesai, Phase 7b (Approval Logic & Stock Update) bisa dimulai — business logic approval akan ditambahkan
7. **Testing:** Buat Feature test untuk approval index page, test approve/reject button, test reject form validation, test authorization
8. **Important:** Di phase ini, approve/reject hanya mengubah UI — logic sebenarnya akan diimplementasikan di Phase 7b
