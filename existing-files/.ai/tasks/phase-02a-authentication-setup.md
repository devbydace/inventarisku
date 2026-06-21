# Phase 2a: Authentication Setup

## Tujuan

Implementasi sistem login/logout, session management, dan form authentication dasar menggunakan Laravel Breeze.

## Scope

- Install dan setup Laravel Breeze untuk scaffolding authentication
- Buat Livewire component untuk login form
- Setup route authentication (login, logout, dashboard)
- Implementasi session management dengan timeout
- Integrasi dengan User model yang sudah ada
- Validasi kredensial dan error handling

## Non-Goals (Eksplisit)

- TIDAK membuat sistem role-based access control (di Phase 2b)
- TIDAK membuat middleware CheckRole (di Phase 2b)
- TIDAK membuat halaman dashboard (hanya route dan controller dasar)
- TIDAK membuat halaman forgot password / reset password (fitur ini di Phase 12)
- TIDAK membuat halaman register (aplikasi internal, user dibuat oleh admin)
- TIDAK mengimplementasikan email verification

## Referensi ke PRD/Architecture

- **PRD Section 5.3 (Autentikasi & Role):** US-011 (Login), US-012 (Logout)
- **PRD Section 3.1 (User Roles):** 5 role: admin, kasir, audit, manager, staff_toko
- **architecture.md Section 4.1 (Route Design):** Authentication routes dan middleware structure
- **architecture.md Section 3.1 (User Model):** Relasi dan atribut User

## Acceptance Criteria (Testable)

### AC-01: Laravel Breeze Setup

- [ ] Package `laravel/breeze` berhasil di-install via composer
- [ ] Scaffolding authentication berhasil dijalankan dengan `php artisan breeze:install`
- [ ] Migration untuk password_reset_tokens berhasil dijalankan
- [ ] File konfigurasi breeze sudah ada di `config/breeze.php`

### AC-02: Login Form

- [ ] Halaman login menampilkan form dengan field: email (required), password (required), remember me (optional)
- [ ] Form login menggunakan Livewire component `Auth/Login.php`
- [ ] Form memiliki validasi CSRF protection
- [ ] Tampilan form menggunakan Bahasa Indonesia
- [ ] Form menampilkan error message dalam Bahasa Indonesia jika ada validation error

### AC-03: Login Validation & Logic

- [ ] Sistem validasi kredensial — jika salah, menampilkan error "Email atau password salah"
- [ ] Sistem menolak login jika user tidak ditemukan di database
- [ ] Sistem menolak login jika password tidak cocok (menggunakan bcrypt)
- [ ] Setelah login berhasil, redirect ke route `dashboard`
- [ ] Session regenerate on login (mencegah session fixation)
- [ ] "Remember Me" menggunakan token yang aman (Laravel built-in)

### AC-04: Logout

- [ ] Setelah logout, redirect ke route `login` dengan message "Anda telah logout"
- [ ] Session dihancurkan setelah logout
- [ ] Remember me token dihapus setelah logout
- [ ] Logout hanya bisa dilakukan oleh user yang sedang login

### AC-05: Session Management

- [ ] Session timeout setelah 2 jam inactivity atau 8 jam total (sesuai NFR-11)
- [ ] Set `SESSION_LIFETIME=120` (2 jam) di `.env`
- [ ] Set `SESSION_EXPIRE_ON_CLOSE=false` di `.env`
- [ ] Jika session expired, user di-redirect ke login dengan message "Session Anda telah expired, silakan login kembali"
- [ ] Session aktif dilacak dengan `last_activity` timestamp

### AC-06: Route Protection

- [ ] Route `/login` dan `/logout` bisa diakses tanpa authentication
- [ ] Route `/dashboard` memerlukan authentication (redirect ke login jika belum login)
- [ ] Route `/forgot-password` dan `/reset-password` bisa diakses tanpa authentication
- [ ] Semua route lain memerlukan authentication (belum ada role-based protection di phase ini)

### AC-07: Security

- [ ] Password di-hash menggunakan bcrypt dengan minimal 10 rounds
- [ ] CSRF protection aktif di semua form login
- [ ] Session regenerate on login (mencegah session fixation)
- [ ] User inactive (is_active = false) tidak bisa login — menampilkan error "Akun Anda telah dinon-aktifkan"
- [ ] Rate limiting untuk login attempt (mencegah brute force) — maksimal 5 attempt per menit

### AC-08: User Model Integration

- [ ] User model sudah memiliki trait `HasRoles` untuk Spatie Permission (akan digunakan di Phase 2b)
- [ ] User model sudah memiliki kolom `is_active` untuk status aktif/non-aktif
- [ ] User model sudah memiliki kolom `role` (enum) untuk backward compatibility
- [ ] Admin user default (dari Phase 1b) dapat login dengan kredensial yang benar

## Catatan untuk Agent

1. **Laravel Breeze:** Gunakan Laravel Breeze untuk scaffolding authentication dasar (login, logout, password reset), lalu modify untuk integrate dengan Spatie Permission di Phase 2b
2. **Livewire Component:** Buat Livewire component `Auth/Login.php` dengan form email, password, remember me
3. **Validation:** Gunakan Form Request `LoginRequest` untuk validasi input login
4. **Session Config:** Set `SESSION_LIFETIME=120` (2 jam) dan `SESSION_EXPIRE_ON_CLOSE=false` di `.env`
5. **Inactive User:** Tambahkan validasi `if (!$user->is_active) abort(403, 'Akun Anda telah dinon-aktifkan')` di login logic
6. **Rate Limiting:** Laravel sudah memiliki built-in rate limiting, pastikan dikonfigurasi dengan benar
7. **Testing:** Buat Feature test untuk login/logout dengan valid dan invalid credentials
8. **Dependency:** Phase ini dependen ke Phase 1 (Database Foundation) — migration users harus sudah ada, admin user harus sudah di-seed
9. **Next Phase:** Setelah Phase 2a selesai, Phase 2b (Authorization & Middleware) bisa dimulai — authentication sudah ready untuk diintegrasikan dengan Spatie Permission
10. **Important:** Jangan lupa menjalankan `php artisan migrate` setelah install Breeze untuk membuat tabel password_reset_tokens
