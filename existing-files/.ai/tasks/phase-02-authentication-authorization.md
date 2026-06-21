# Phase 2: Authentication & Authorization Setup

## Tujuan

Implementasi sistem login/logout, role-based access control menggunakan Spatie Permission, dan middleware protection untuk seluruh aplikasi.

## Scope

- Install dan setup Spatie Laravel Permission
- Buat LoginController dan LogoutController
- Buat middleware CheckRole menggunakan Spatie
- Buat Livewire component untuk login form
- Setup route authentication (login, logout, dashboard)
- Implementasi session management dengan timeout
- Setup role-based middleware untuk seluruh route group
- Buat seeder untuk assign permission ke role (Decision #002)

## Non-Goals (Eksplisit)

- TIDAK membuat halaman dashboard (hanya route dan controller dasar)
- TIDAK membuat halaman forgot password / reset password (fitur ini di Phase 12)
- TIDAK membuat halaman register (aplikasi internal, user dibuat oleh admin)
- TIDAK mengimplementasikan email verification
- TIDAK membuat Livewire component untuk dashboard

## Referensi ke PRD/Architecture

- **PRD Section 5.3 (Autentikasi & Role):** US-011 (Login), US-012 (Logout), US-013 (Role-Based Access Control)
- **PRD Section 3.1 (User Roles):** 5 role: admin, kasir, audit, manager, staff_toko
- **PRD Section 3.3 (Aturan Approval):** Staff tidak bisa approve transaksi sendiri
- **architecture.md Section 4.1 (Route Design):** Authentication routes dan middleware structure
- **architecture.md Section 3.1 (User Model):** Relasi ke Spatie HasRoles
- **tech-decisions.md Decision #002:** Spatie Laravel Permission untuk RBAC

## Acceptance Criteria (Testable)

### AC-01: Spatie Permission Setup

- [ ] Package `spatie/laravel-permission` berhasil di-install via composer
- [ ] Migration Spatie berhasil dijalankan (5 tabel: roles, permissions, model_has_roles, model_has_permissions, role_has_permissions)
- [ ] Model User memiliki trait `HasRoles`
- [ ] PermissionSeeder menge-seed 5 role: admin, kasir, audit, manager, staff_toko
- [ ] PermissionSeeder menge-seed minimal 10 permission: view-dashboard, manage-products, manage-categories, manage-suppliers, manage-units, create-stock-in, create-stock-out, create-stock-adjustment, approve-transaction, manage-users, manage-settings, view-audit-trail, view-reports
- [ ] Role admin memiliki semua permission
- [ ] Role manager dan audit memiliki permission: view-dashboard, approve-transaction, view-reports, view-audit-trail
- [ ] Role kasir dan staff_toko memiliki permission: view-dashboard, create-stock-in, create-stock-out, view-reports

### AC-02: Login/Logout

- [ ] Halaman login menampilkan form dengan field: email (required), password (required), remember me (optional)
- [ ] Sistem validasi kredensial — jika salah, menampilkan error "Email atau password salah"
- [ ] Setelah login berhasil, redirect ke route `dashboard`
- [ ] Setelah logout, redirect ke route `login` dengan message "Anda telah logout"
- [ ] Session timeout setelah 2 jam inactivity atau 8 jam total (sesuai NFR-11)
- [ ] "Remember Me" menggunakan token yang aman (Laravel built-in)

### AC-03: Middleware & Authorization

- [ ] Middleware `role:admin` berhasil melindungi route yang hanya bisa diakses admin
- [ ] Middleware `role:admin,manager,audit` berhasil melindungi route approval
- [ ] Middleware `permission:approve-transaction` berhasil melindungi route approval
- [ ] Jika user tanpa role yang sesuai akses route, sistem menampilkan error 403 (Forbidden)
- [ ] User dengan role staff_toko tidak bisa akses route approval (403 Forbidden)
- [ ] User dengan role admin bisa akses semua route

### AC-04: Route Protection

- [ ] Route `/login` dan `/logout` bisa diakses tanpa authentication
- [ ] Route `/dashboard` memerlukan authentication
- [ ] Route `/products`, `/categories`, `/suppliers`, `/units` memerlukan role admin
- [ ] Route `/stock/in`, `/stock/out` memerlukan authentication (semua role)
- [ ] Route `/stock/adjustment` memerlukan role admin, manager, atau audit
- [ ] Route `/approvals` memerlukan permission approve-transaction
- [ ] Route `/reports/*` memerlukan authentication
- [ ] Route `/users` memerlukan role admin
- [ ] Route `/settings` memerlukan role admin

### AC-05: Security

- [ ] Password di-hash menggunakan bcrypt dengan minimal 10 rounds
- [ ] CSRF protection aktif di semua form login
- [ ] Session regenerate on login (mencegah session fixation)
- [ ] User inactive (is_active = false) tidak bisa login

## Catatan untuk Agent

1. **Laravel Breeze:** Gunakan Laravel Breeze untuk scaffolding authentication dasar (login, logout, password reset), lalu modify untuk integrate dengan Spatie Permission
2. **Spatie Middleware:** Gunakan middleware bawaan Spatie: `role:admin`, `permission:approve-transaction`, atau `role:admin|manager|audit`
3. **Login Form:** Buat Livewire component `Auth/Login.php` dengan form email, password, remember me
4. **Validation:** Gunakan Form Request `LoginRequest` untuk validasi input login
5. **Session Config:** Set `SESSION_LIFETIME=120` (2 jam) dan `SESSION_EXPIRE_ON_CLOSE=false` di `.env`
6. **Testing:** Buat Feature test untuk login/logout dengan berbagai role
7. **Dependency:** Phase ini dependen ke Phase 1 (Database Foundation) — migration users dan roles harus sudah ada
8. **Next Phase:** Setelah Phase 2 selesai, Phase 3 (Master Data - Categories & Units) bisa dimulai karena route dan middleware sudah ready
