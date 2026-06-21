# Phase 11: Audit Trail System

## Tujuan

Implementasi sistem pencatatan otomatis perubahan data master (create/update/delete) dengan old_values dan new_values untuk compliance dan debugging.

## Scope

- Buat AuditTrailService dengan method log dan getAuditTrails
- Integrasi model events untuk otomatis mencatat perubahan: Product, Category, Supplier, Unit, User
- Buat Livewire component untuk Audit Trail: Index (daftar histori perubahan)
- Buat AuditTrailController (jika diperlukan untuk route)
- Implementasi filter: entity type, user, rentang tanggal
- Integrasi dengan Repository untuk query audit trail
- Setup route dengan middleware role:admin,manager,audit
- Buat view Blade untuk audit trail index

## Non-Goals (Eksplisit)

- TIDAK membuat audit trail untuk stock transactions (sudah tercatat di Phase 7)
- TIDAK membuat audit trail untuk approval (sudah tercatat di Phase 7)
- TIDAK membuat audit trail untuk export (akan ditambahkan di Phase 10)
- TIDAK membuat undo/rollback functionality (hanya read-only log)
- TIDAK membuat export audit trail ke PDF (bisa ditambahkan di masa depan jika diperlukan)

## Referensi ke PRD/Architecture

- **PRD Section 5.7 (Audit Trail):** US-022 (Histori Perubahan Data Master), US-023 (Histori Approval Transaksi)
- **PRD Section 3.2 (Matrix Permission):** Audit Trail untuk Admin, Manager, Audit
- **architecture.md Section 3.9 (AuditTrail Model):** Relasi dan $fillable
- **architecture.md Section 5.4 (AuditTrailService):** Method log dan getAuditTrails
- **architecture.md Section 4.1 (Route Design):** Route audit-trails dengan middleware role:admin,manager,audit

## Acceptance Criteria (Testable)

### AC-01: Audit Trail Logging

- [ ] Setiap create produk mencatat audit trail: user_id, entity_type=Product, entity_id, action=create, new_values (semua field produk)
- [ ] Setiap update produk mencatat audit trail: user_id, entity_type=Product, entity_id, action=update, old_values (data sebelum), new_values (data sesudah)
- [ ] Setiap delete/archive produk mencatat audit trail: user_id, entity_type=Product, entity_id, action=delete, old_values (data sebelum)
- [ ] Setiap create/update/delete kategori mencatat audit trail dengan entity_type=Category
- [ ] Setiap create/update/delete supplier mencatat audit trail dengan entity_type=Supplier
- [ ] Setiap create/update/delete unit mencatat audit trail dengan entity_type=Unit
- [ ] Setiap create/update/delete user mencatat audit trail dengan entity_type=User
- [ ] User_id yang tercatat adalah user yang sedang login (bukan system user)

### AC-02: Audit Trail Display

- [ ] Halaman audit trail menampilkan tabel dengan kolom: tanggal, user (nama), aksi (create/update/delete), entity (Product/Category/Supplier/Unit/User), detail perubahan
- [ ] Untuk action=update, menampilkan perbandingan old_values vs new_values (misal: "nama: 'Laptop' → 'Laptop Gaming'")
- [ ] Untuk action=create, menampilkan "Created: [field1 => value1, field2 => value2]"
- [ ] Untuk action=delete, menampilkan "Deleted: [field1 => value1, field2 => value2]"
- [ ] Data diurutkan berdasarkan created_at descending (yang paling baru di atas)
- [ ] Data ditampilkan dengan pagination (50 items per halaman)

### AC-03: Filter

- [ ] Sistem menyediakan filter entity type (dropdown: semua, Product, Category, Supplier, Unit, User)
- [ ] Sistem menyediakan filter user (dropdown: semua user)
- [ ] Sistem menyediakan filter rentang tanggal: dari (date picker), sampai (date picker)
- [ ] Filter dapat dikombinasikan — user bisa filter berdasarkan entity type dan user sekaligus
- [ ] Jika tidak ada filter, sistem menampilkan semua audit trail
- [ ] Jika filter tidak ada hasil, menampilkan message "Tidak ada audit trail untuk filter yang dipilih"

### AC-04: Authorization

- [ ] Halaman audit trail hanya bisa diakses oleh user dengan role admin, manager, atau audit (403 Forbidden untuk kasir/staff_toko)
- [ ] User yang belum login tidak bisa akses halaman audit trail (redirect ke login)

### AC-05: Performance

- [ ] Query audit trail dengan 1000 records load dalam waktu maksimal 1 detik
- [ ] Query menggunakan index yang sudah didefinisikan di migration (idx_audit_trails_user_id, idx_audit_trails_entity, idx_audit_trails_created_at)
- [ ] Eager loading user relasi untuk menghindari N+1 query

## Catatan untuk Agent

1. **Model Events Integration:** Gunakan Laravel Model Events untuk otomatis mencatat perubahan:
   - `Product::created()` → AuditTrailService::log($user, 'Product', $id, 'create', null, $data)
   - `Product::updated()` → AuditTrailService::log($user, 'Product', $id, 'update', $oldData, $newData)
   - `Product::deleted()` → AuditTrailService::log($user, 'Product', $id, 'delete', $oldData, null)
   - Lakukan hal yang sama untuk Category, Supplier, Unit, User

2. **AuditTrailService Structure:**

   ```php
   class AuditTrailService
   {
       public static function log(User $user, string $entityType, int $entityId, string $action, ?array $oldValues, ?array $newValues): AuditTrail;
       public function getAuditTrails(array $filters = []): Collection;
   }
   ```

3. **Old Values & New Values:**
   - Gunakan `$model->getOriginal()` untuk mendapatkan old values sebelum update
   - Gunakan `$model->getAttributes()` untuk mendapatkan new values setelah update
   - Simpan sebagai JSON di kolom `old_values` dan `new_values`

4. **Filter Logic:**
   - Entity type: `->where('entity_type', $entityType)`
   - User: `->where('user_id', $userId)`
   - Tanggal: `->whereBetween('created_at', [$from, $to])`

5. **Display Detail Perubahan:**
   - Untuk update, compare old_values dan new_values, tampilkan hanya field yang berubah
   - Format: "Field: old_value → new_value"
   - Gunakan helper function untuk format JSON ke readable string

6. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth), Phase 3-5 (master data) — model events harus sudah ada
7. **Next Phase:** Setelah Phase 11 selesai, Phase 12 (User Management) bisa dimulai — user management akan menggunakan audit trail
8. **Testing:** Buat Feature test untuk audit trail logging, test filter, test authorization
9. **Important:** Audit trail harus mencatat SEMUA perubahan data master — jangan ada yang terlewat
