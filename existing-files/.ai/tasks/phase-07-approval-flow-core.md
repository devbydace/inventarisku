# Phase 7: Approval Flow Core

## Tujuan

Implementasi sistem approve/reject transaksi dengan validasi self-approval prevention, row locking untuk mencegah race condition, dan update stok otomatis setelah approval.

## Scope

- Buat Livewire component untuk Approval: Index (daftar transaksi pending)
- Buat ApprovalController untuk handle approve/reject action
- Implementasi ApprovalService dengan business logic approval
- Implementasi validasi: user tidak bisa approve transaksi sendiri
- Implementasi database transaction dengan row locking (SELECT ... FOR UPDATE)
- Implementasi update stok otomatis setelah approval
- Implementasi reject dengan alasan yang required
- Integrasi dengan StockService untuk update stok
- Integrasi dengan AuditTrailService untuk pencatatan approval
- Setup route dengan middleware permission:approve-transaction
- Buat view Blade untuk approval index

## Non-Goals (Eksplisit)

- TIDAK membuat stock adjustment (di Phase 8)
- TIDAK membuat histori mutasi stok (di Phase 9)
- TIDAK membuat notifikasi eksternal (email/WA) untuk approval
- TIDAK membuat approval berjenjang (multi-level) — hanya 1 level
- TIDAK membuat bulk approve (approve satu per satu saja)

## Referensi ke PRD/Architecture

- **PRD Section 5.2 (Manajemen Stok):** US-009 (Approve/Reject Transaksi)
- **PRD Section 3.3 (Aturan Approval):** Staff tidak bisa approve sendiri, reject wajib disertai alasan, 1 level approval
- **architecture.md Section 3.8 (Approval Model):** Relasi dan $fillable
- **architecture.md Section 5.2 (ApprovalService):** Method approve, reject, canApprove, validateSelfApproval
- **architecture.md Section 4.1 (Route Design):** Route approval dengan middleware role:admin,manager,audit
- **tech-decisions.md Decision #003:** Database transaction dengan row locking

## Acceptance Criteria (Testable)

### AC-01: Approval Index Page

- [ ] Halaman approval menampilkan daftar transaksi pending dengan informasi: jenis transaksi (masuk/keluar), nama barang, jumlah, tanggal, user pembuat, status
- [ ] Hanya menampilkan transaksi dengan status 'pending'
- [ ] Transaksi diurutkan berdasarkan created_at descending (yang paling baru di atas)
- [ ] Setiap transaksi memiliki tombol "Approve" dan "Reject"
- [ ] Jika tidak ada transaksi pending, menampilkan message "Tidak ada transaksi yang menunggu approval"

### AC-02: Approve Transaction

- [ ] Setelah klik "Approve", sistem menampilkan konfirmasi "Apakah Anda yakin ingin approve transaksi ini?"
- [ ] Setelah approve, transaksi berstatus **approved**
- [ ] Setelah approve, stok barang bertambah (stok masuk) atau berkurang (stok keluar) sesuai jumlah transaksi
- [ ] Setelah approve, sistem membuat record di tabel `approvals` dengan action='approve'
- [ ] Setelah approve, sistem menampilkan success notification "Transaksi berhasil di-approve"
- [ ] Setelah approve, transaksi hilang dari daftar pending (karena status sudah approved)

### AC-03: Reject Transaction

- [ ] Setelah klik "Reject", sistem menampilkan form input alasan reject (required, min 10 chars)
- [ ] Sistem menolak reject tanpa alasan — menampilkan error "Alasan reject harus diisi (minimal 10 karakter)"
- [ ] Setelah reject dengan alasan, transaksi berstatus **rejected**
- [ ] Setelah reject, stok barang TIDAK berubah
- [ ] Setelah reject, sistem membuat record di tabel `approvals` dengan action='reject' dan notes berisi alasan
- [ ] Setelah reject, sistem menampilkan success notification "Transaksi berhasil di-reject"
- [ ] Setelah reject, transaksi hilang dari daftar pending

### AC-04: Self-Approval Prevention

- [ ] Sistem mencegah user approve transaksi yang mereka buat sendiri — menampilkan error "Tidak dapat approve transaksi sendiri"
- [ ] Validasi ini dilakukan sebelum approval dijalankan (di ApprovalService)
- [ ] Jika user mencoba approve transaksi sendiri, transaksi tetap berstatus pending

### AC-05: Concurrent Approval & Race Condition

- [ ] Jika 2 user approve transaksi yang sama secara bersamaan, hanya 1 yang berhasil (row locking)
- [ ] User kedua yang mencoba approve akan mendapatkan error "Transaksi sudah di-approve oleh user lain"
- [ ] Stok tidak menjadi negatif meskipun ada concurrent approval (atomic transaction)
- [ ] Database transaction digunakan: update stok + create approval + update status transaksi dalam satu transaction

### AC-06: Authorization

- [ ] Halaman approval hanya bisa diakses oleh user dengan permission approve-transaction (admin, manager, audit)
- [ ] User dengan role kasir atau staff_toko tidak bisa akses halaman approval (403 Forbidden)
- [ ] Tombol approve/reject hanya muncul untuk user dengan permission approve-transaction

### AC-07: Audit Trail

- [ ] Setiap approve mencatat audit trail: user (approver), action=update, entity_type=StockTransaction, entity_id, old_values (status=pending), new_values (status=approved)
- [ ] Setiap reject mencatat audit trail: user (approver), action=update, entity_type=StockTransaction, entity_id, old_values (status=pending), new_values (status=rejected)
- [ ] Setiap approval mencatat approval record: user_id (approver), stock_transaction_id, action, notes

### AC-08: Data Integrity

- [ ] Setelah approve stok masuk: product.current_stock bertambah sesuai quantity
- [ ] Setelah approve stok keluar: product.current_stock berkurang sesuai quantity
- [ ] Setelah approve, stok produk tidak boleh negatif (validasi ulang sebelum update)
- [ ] Timestamp updated_at di stock_transactions terupdate setelah approval

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/Approval/` berisi `Index.php`
2. **ApprovalService:** Buat service class dengan method:
   - `approve(StockTransaction $transaction, User $approver): Approval`
   - `reject(StockTransaction $transaction, User $approver, string $notes): Approval`
   - `canApprove(StockTransaction $transaction, User $user): bool`
   - `validateSelfApproval(StockTransaction $transaction, User $user): void`
3. **Row Locking:** Gunakan `DB::transaction()` dengan `StockTransaction::where('id', $id)->lockForUpdate()->first()` untuk mencegah race condition
4. **Validation:**
   - Self-approval: `if ($transaction->user_id === $user->id) throw new \Exception('Tidak dapat approve transaksi sendiri')`
   - Stok negatif: `if ($transaction->type === 'out' && $product->current_stock < $transaction->quantity) throw new \Exception('Stok tidak mencukupi')`
5. **Reject Notes:** Validasi minimal 10 karakter di form reject
6. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 6 (stock transaction) — transaksi pending harus ada, user harus login dengan permission approve-transaction
7. **Next Phase:** Setelah Phase 7 selesai, Phase 8 (Stock Adjustment) bisa dimulai — adjustment menggunakan approval flow yang sama
8. **Testing:** Buat Feature test untuk approve/reject dengan concurrent user, test self-approval prevention, test stok tidak negatif
9. **Important:** Gunakan database transaction untuk seluruh operasi approval — jika ada error, seluruh transaksi di-rollback
