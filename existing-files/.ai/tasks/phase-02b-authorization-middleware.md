# Phase 2b: Authorization & Middleware

## Tujuan

Implementasi sistem role-based access control menggunakan Spatie Permission, middleware protection, dan permission assignment untuk seluruh aplikasi.

## Scope

- Install dan setup Spatie Laravel Permission
- Buat middleware CheckRole menggunakan Spatie
- Setup role-based middleware untuk seluruh route group
- Buat seeder untuk assign permission ke role
- Implementasi permission-based route protection
- Test authorization dengan berbagai role

## Non-Goals (Eksplisit)

- TIDAK membuat form login/logout (sudah di Phase 2a)
- TIDAK membuat halaman dashboard (hanya route dan controller dasar)
- TIDAK membuat halaman forgot password / reset password (fitur ini di Phase 12)
- TIDAK membuat halaman register (aplikasi internal, user dibuat oleh admin)
- TIDAK mengimplementasikan email verification

## Referensi ke PRD/Architecture

- **PRD Section 5.3 (Autentikasi & Role):** US-013 (Role-Based Access Control)
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

### AC-02: Middleware & Authorization

- [ ] Middleware `role:admin` berhasil melindungi route yang hanya bisa diakses admin
- [ ] Middleware `role:admin,manager,audit` berhasil melindungi route approval
- [ ] Middleware `permission:approve-transaction` berhasil melindungi route approval
- [ ] Jika user tanpa role yang sesuai akses route, sistem menampilkan error 403 (Forbidden)
- [ ] User dengan role staff_toko tidak bisa akses route approval (403 Forbidden)
- [ ] User dengan role admin bisa akses semua route

### AC-03: Route Protection

- [ ] Route `/login` dan `/logout` bisa diakses tanpa authentication
- [ ] Route `/dashboard` memerlukan authentication
- [ ] Route `/products`, `/categories`, `/suppliers`, `/units` memerlukan role admin
- [ ] Route `/stock/in`, `/stock/out` memerlukan authentication (semua role)
- [ ] Route `/stock/adjustment` memerlukan role admin, manager, atau audit
- [ ] Route `/approvals` memerlukan permission approve-transaction
- [ ] Route `/reports/*` memerlukan authentication
- [ ] Route `/users` memerlukan role admin
- [ ] Route `/settings` memerlukan role admin

### AC-04: Permission Assignment

- [ ] Admin dapat assign role ke user lain
- [ ] Setelah assign role, user langsung mendapatkan permission sesuai role
- [ ] Admin dapat melihat permission yang dimiliki oleh setiap role
- [ ] Permission dapat di-check dengan `$user->hasPermissionTo('approve-transaction')`
- [ ] Role dapat di-check dengan `$user->hasRole('admin')`

### AC-05: Security Testing

- [ ] User dengan role kasir tidak bisa akses halaman products (403 Forbidden)
- [ ] User dengan role staff_toko tidak bisa akses halaman approvals (403 Forbidden)
- [ ] User dengan role manager bisa akses halaman approvals
- [ ] User dengan role audit bisa akses halaman approvals
- [ ] User dengan role admin bisa akses semua halaman
- [ ] User yang belum login di-redirect ke halaman login jika mencoba akses route yang dilindungi

### AC-06: Integration with Phase 2a

- [ ] Login dari Phase 2a tetap berfungsi setelah integrasi Spatie Permission
- [ ] Session dari Phase 2a tetap berfungsi dengan Spatie Permission
- [ ] Admin user default (dari Phase 1b) memiliki role admin dan semua permission
- [ ] Logout dari Phase 2a tetap berfungsi

## Catatan untuk Agent

1. **Spatie Middleware:** Gunakan middleware bawaan Spatie: `role:admin`, `permission:approve-transaction`, atau `role:admin|manager|audit`
2. **Route Groups:** Buat route groups dengan middleware untuk memudahkan protection:
   - `Route::middleware(['auth', 'role:admin'])->group(function () { ... })`
   - `Route::middleware(['auth', 'permission:approve-transaction'])->group(function () { ... })`
3. **PermissionSeeder:** Buat seeder yang menge-seed semua permission dan assign ke role yang sesuai
4. **Testing:** Buat Feature test untuk setiap role mengakses route yang dilindungi
5. **Dependency:** Phase ini dependen ke Phase 1 (Database Foundation) dan Phase 2a (Authentication Setup) — migration Spatie harus ada, login/logout harus sudah bekerja
6. **Next Phase:** Setelah Phase 2b selesai, Phase 3 (Master Data - Categories & Units) bisa dimulai — route dan middleware sudah ready
7. **Important:** Pastikan semua route yang memerlukan authentication sudah dilindungi dengan middleware `auth`
