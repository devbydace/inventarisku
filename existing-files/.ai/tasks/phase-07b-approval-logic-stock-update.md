# Phase 7b: Approval Logic & Stock Update

## Tujuan

Implementasi business logic approval/reject dengan validasi self-approval prevention, row locking untuk mencegah race condition, dan update stok otomatis setelah approval.

## Scope

- Implementasi ApprovalService dengan business logic approval
- Implementasi validasi: user tidak bisa approve transaksi sendiri
- Implementasi database transaction dengan row locking (SELECT ... FOR UPDATE)
- Implementasi update stok otomatis setelah approval
- Implementasi reject dengan validasi alasan
- Integrasi dengan StockService untuk update stok
- Integrasi dengan AuditTrailService untuk pencatatan approval

## Non-Goals (Eksplisit)

- TIDAK membuat UI approval (sudah di Phase 7a)
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
- **tech-decisions.md Decision #003:** Database transaction dengan row locking

## Acceptance Criteria (Testable)

### AC-01: Approve Transaction Logic

- [ ] Setelah approve, transaksi berstatus **approved**
- [ ] Setelah approve, stok barang bertambah (stok masuk) atau berkurang (stok keluar) sesuai jumlah transaksi
- [ ] Setelah approve, sistem membuat record di tabel `approvals` dengan action='approve'
- [ ] Stok produk tidak boleh negatif setelah approval stok keluar
- [ ] Timestamp updated_at di stock_transactions terupdate setelah approval

### AC-02: Reject Transaction Logic

- [ ] Setelah reject dengan alasan, transaksi berstatus **rejected**
- [ ] Setelah reject, stok barang TIDAK berubah
- [ ] Setelah reject, sistem membuat record di tabel `approvals` dengan action='reject' dan notes berisi alasan
- [ ] Reject memerlukan alasan minimal 10 karakter

### AC-03: Self-Approval Prevention

- [ ] Sistem mencegah user approve transaksi yang mereka buat sendiri — menampilkan error "Tidak dapat approve transaksi sendiri"
- [ ] Validasi ini dilakukan sebelum approval dijalankan (di ApprovalService)
- [ ] Jika user mencoba approve transaksi sendiri, transaksi tetap berstatus pending
- [ ] Error ditampilkan dengan jelas dan user bisa mencoba approve transaksi lain

### AC-04: Concurrent Approval & Race Condition

- [ ] Jika 2 user approve transaksi yang sama secara bersamaan, hanya 1 yang berhasil (row locking)
- [ ] User kedua yang mencoba approve akan mendapatkan error "Transaksi sudah di-approve oleh user lain"
- [ ] Stok tidak menjadi negatif meskipun ada concurrent approval (atomic transaction)
- [ ] Database transaction digunakan: update stok + create approval + update status transaksi dalam satu transaction

### AC-05: Stock Update Logic

- [ ] Setelah approve stok masuk: product.current_stock bertambah sesuai quantity
- [ ] Setelah approve stok keluar: product.current_stock berkurang sesuai quantity
- [ ] Setelah approve, stok produk tidak boleh negatif (validasi ulang sebelum update)
- [ ] Update stok menggunakan Eloquent dengan atomic operation
- [ ] Jika update stok gagal, seluruh transaksi di-rollback

### AC-06: Audit Trail

- [ ] Setiap approve mencatat audit trail: user (approver), action=update, entity_type=StockTransaction, entity_id, old_values (status=pending), new_values (status=approved)
- [ ] Setiap reject mencatat audit trail: user (approver), action=update, entity_type=StockTransaction, entity_id, old_values (status=pending), new_values (status=rejected)
- [ ] Setiap approval mencatat approval record: user_id (approver), stock_transaction_id, action, notes
- [ ] Audit trail tercatat dengan user yang sedang login (approver)

### AC-07: Authorization

- [ ] Hanya user dengan permission approve-transaction yang bisa approve/reject
- [ ] User dengan role kasir atau staff_toko tidak bisa approve (403 Forbidden)
- [ ] Self-approval prevention berlaku untuk semua role termasuk admin

### AC-08: Error Handling & Data Integrity

- [ ] Jika terjadi error saat approval, sistem menampilkan error message yang jelas
- [ ] Jika approval gagal, transaksi tetap berstatus pending
- [ ] Jika approval berhasil, tidak ada rollback yang terjadi
- [ ] Semua operasi database menggunakan transaction untuk menjaga data integrity

## Catatan untuk Agent

1. **ApprovalService Structure:** Buat service class dengan method:
   - `approve(StockTransaction $transaction, User $approver): Approval`
   - `reject(StockTransaction $transaction, User $approver, string $notes): Approval`
   - `canApprove(StockTransaction $transaction, User $user): bool`
   - `validateSelfApproval(StockTransaction $transaction, User $user): void`

2. **Row Locking:** Gunakan `DB::transaction()` dengan `StockTransaction::where('id', $id)->lockForUpdate()->first()` untuk mencegah race condition

3. **Validation:**
   - Self-approval: `if ($transaction->user_id === $user->id) throw new \Exception('Tidak dapat approve transaksi sendiri')`
   - Stok negatif: `if ($transaction->type === 'out' && $product->current_stock < $transaction->quantity) throw new \Exception('Stok tidak mencukupi')`

4. **Database Transaction:** Gunakan `DB::transaction()` untuk seluruh operasi approval — jika ada error, seluruh transaksi di-rollback

5. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 6 (stock transaction), Phase 7a (approval UI) — transaksi pending harus ada, UI approval harus ready

6. **Next Phase:** Setelah Phase 7b selesai, Phase 8 (Stock Adjustment) bisa dimulai — adjustment menggunakan approval flow yang sama

7. **Testing:** Buat Feature test untuk approve/reject dengan concurrent user, test self-approval prevention, test stok tidak negatif, test database transaction rollback

8. **Important:** Gunakan database transaction untuk seluruh operasi approval — jika ada error, seluruh transaksi di-rollback
