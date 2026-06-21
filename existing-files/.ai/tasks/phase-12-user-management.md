# Phase 12: User Management

## Tujuan

Implementasi CRUD user, assign role/permission, reset password, dan manajemen status aktif/non-aktif untuk administrasi pengguna sistem.

## Scope

- Buat Livewire component untuk User: Index, Create, Edit
- Buat UserController (jika diperlukan untuk route)
- Implementasi form create/edit user dengan assign role
- Implementasi reset password (generate temporary password)
- Implementasi change password untuk user sendiri
- Implementasi toggle status aktif/non-aktif
- Integrasi dengan Spatie Permission untuk assign role
- Integrasi dengan AuditTrailService untuk pencatatan perubahan user
- Setup route dengan middleware role:admin
- Buat view Blade untuk setiap Livewire component

## Non-Goals (Eksplisit)

- TIDAK membuat fitur import user (tidak termasuk MVP)
- TIDAK membuat fitur export user (bisa ditambahkan jika diperlukan)
- TIDAK membuat user registration (aplikasi internal, user dibuat oleh admin)
- TIDAK membuat user profile page (hanya management oleh admin)
- TIDAK membuat activity log per user (hanya audit trail)

## Referensi ke PRD/Architecture

- **PRD Section 5.8 (Manajemen User):** US-024 (CRUD User), US-025 (Reset/Change Password)
- **PRD Section 3.2 (Matrix Permission):** Manajemen User hanya untuk Admin
- **architecture.md Section 3.1 (User Model):** Relasi dan $fillable, trait HasRoles
- **architecture.md Section 5.4 (AuditTrailService):** Pencatatan perubahan data master
- **tech-decisions.md Decision #002:** Spatie Laravel Permission untuk RBAC

## Acceptance Criteria (Testable)

### AC-01: User CRUD

- [ ] Halaman daftar user menampilkan tabel dengan kolom: nama, email, role, status aktif, tanggal dibuat, aksi (edit, reset password, toggle status, delete)
- [ ] Form create/edit user memiliki field: nama (required, max 255 chars), email (required, unique, email format), password (min 8 chars, required untuk create, optional untuk edit), role (dropdown: admin, kasir, audit, manager, staff_toko, required), status aktif (checkbox, default true)
- [ ] Sistem validasi email unique — jika email sudah ada, menampilkan error "Email sudah digunakan"
- [ ] Sistem menampilkan konfirmasi sebelum delete user
- [ ] Setelah create/edit berhasil, sistem menampilkan success notification dan redirect ke daftar user
- [ ] Setelah delete berhasil, sistem menampilkan success notification

### AC-02: Password Management

- [ ] Admin dapat reset password user lain — sistem generate password temporary (min 8 chars, kombinasi huruf dan angka) dan menampilkan ke admin dalam dialog
- [ ] Admin bisa copy password temporary ke clipboard
- [ ] Setelah reset password, sistem menampilkan warning "Password temporary harus diganti pada login pertama kali"
- [ ] User dapat mengganti password sendiri di halaman settings — sistem memvalidasi password lama
- [ ] Validasi password baru: minimal 8 karakter, harus berbeda dari password lama
- [ ] Setelah ganti password berhasil, sistem menampilkan success notification

### AC-03: Role & Permission Assignment

- [ ] Form create/edit user dapat memilih role dari dropdown
- [ ] Role yang tersedia: admin, kasir, audit, manager, staff_toko
- [ ] Setelah assign role, user langsung mendapatkan permission sesuai role (menggunakan Spatie)
- [ ] Admin dapat melihat permission yang dimiliki oleh setiap role

### AC-04: Status Aktif/Non-Aktif

- [ ] Admin dapat toggle status aktif/non-aktif user
- [ ] User non-aktif tidak bisa login — sistem menampilkan error "Akun Anda telah dinon-aktifkan"
- [ ] User non-aktif tetap muncul di daftar user (hanya ditandai sebagai non-aktif)
- [ ] Admin non-aktif tidak bisa menon-aktifkan akun sendiri

### AC-05: Self-Account Protection

- [ ] Admin tidak bisa menghapus akun sendiri — menampilkan error "Tidak dapat menghapus akun sendiri"
- [ ] Admin tidak bisa menon-aktifkan akun sendiri — menampilkan error "Tidak dapat menon-aktifkan akun sendiri"
- [ ] Admin tidak bisa mengubah role sendiri — menampilkan error "Tidak dapat mengubah role sendiri"

### AC-06: Authorization

- [ ] Halaman daftar user hanya bisa diakses oleh user dengan role admin (403 Forbidden untuk role lain)
- [ ] Form create/edit/delete user hanya bisa diakses oleh admin
- [ ] Fitur reset password hanya bisa dilakukan oleh admin
- [ ] Fitur change password sendiri bisa diakses oleh semua user yang login

### AC-07: Audit Trail

- [ ] Setiap create user mencatat audit trail: user (admin), action=create, entity_type=User, entity_id, new_values (nama, email, role, is_active)
- [ ] Setiap update user mencatat audit trail: user (admin), action=update, entity_type=User, entity_id, old_values, new_values
- [ ] Setiap delete user mencatat audit trail: user (admin), action=delete, entity_type=User, entity_id, old_values
- [ ] Setiap reset password mencatat audit trail: user (admin), action=update, entity_type=User, entity_id, new_values (password_reset=true)
- [ ] Setiap change password mencatat audit trail: user (pemilik), action=update, entity_type=User, entity_id, new_values (password_changed=true)

### AC-08: Validation & Error Handling

- [ ] Form menolak input nama kosong — menampilkan error "Nama harus diisi"
- [ ] Form menolak input nama lebih dari 255 karakter — menampilkan error "Nama tidak boleh lebih dari 255 karakter"
- [ ] Form menolak input email kosong — menampilkan error "Email harus diisi"
- [ ] Form menolak input email tidak valid — menampilkan error "Format email tidak valid"
- [ ] Form menolak input email yang sudah ada — menampilkan error "Email sudah digunakan"
- [ ] Form menolak input password kurang dari 8 karakter — menampilkan error "Password harus minimal 8 karakter"
- [ ] Form menolak jika role tidak dipilih — menampilkan error "Role harus dipilih"
- [ ] Error message ditampilkan dalam Bahasa Indonesia dan mudah dipahami

## Catatan untuk Agent

1. **Livewire Component Structure:** Buat folder `app/Livewire/User/` berisi `Index.php`, `Create.php`, `Edit.php`
2. **Password Hashing:** Gunakan `Hash::make()` untuk hash password sebelum disimpan ke database (bcrypt dengan 10 rounds)
3. **Temporary Password Generation:** Gunakan `Str::random(8)` atau `bin2hex(random_bytes(4))` untuk generate password temporary
4. **Spatie Permission Integration:**
   - `$user->syncRoles($role)` untuk assign role
   - `$user->getRoleNames()` untuk mendapatkan role user
   - `$user->hasRole('admin')` untuk cek role
5. **Validation Rules:**
   - Nama: `required|string|max:255`
   - Email: `required|email|max:255|unique:users,email,NULL,id,deleted_at,NULL` (ignore soft delete)
   - Password: `nullable|string|min:8` (required untuk create, nullable untuk edit)
   - Role: `required|in:admin,kasir,audit,manager,staff_toko`
6. **Change Password Form:** Buat form terpisah untuk change password (bukan di form create/edit user)
7. **Audit Trail Integration:** Panggil `AuditTrailService::log()` di setiap create/update/delete/reset password action
8. **Dependency:** Phase ini dependen ke Phase 1 (database), Phase 2 (auth & Spatie Permission), Phase 11 (audit trail) — audit trail harus sudah ada
9. **Next Phase:** Setelah Phase 12 selesai, Phase 13 (Dashboard & Settings) bisa dimulai
10. **Testing:** Buat Feature test untuk CRUD user, test reset password, test change password, test self-account protection, test authorization
11. **Important:** Jangan lupa menambahkan `use HasRoles;` trait di User model (sudah dilakukan di Phase 2)
