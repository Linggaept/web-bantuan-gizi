# Lurah Web Dashboard Design

**Date:** 2026-05-14  
**Status:** Approved

---

## Overview

Web dashboard untuk Lurah pada sistem penyaluran bantuan gizi lansia. Dibangun dengan Livewire v4 + Blade + Tailwind CSS v4 + Chart.js. Data langsung dari Eloquent (tidak melalui API internal). Sama stack dengan Admin dashboard.

**Stack:**
- Laravel 13, PHP 8.3
- Laravel Livewire v4
- Tailwind CSS v4
- Chart.js (via npm, sudah terpasang)
- Alpine.js (bundled dengan Livewire)
- Session auth (shared login page `/login`)

---

## Architecture

### Routes (`routes/web.php`)

```
/login (shared)  → AdminAuthController@showLogin / login / logout
                   login redirect: role=admin → /dashboard, role=lurah → /lurah

/lurah (middleware: auth, web.role:lurah)
  GET /                  → Lurah/Dashboard Livewire page (route: lurah.dashboard)
  GET /approval          → Lurah/ApprovalTable Livewire page (route: lurah.approval)
  GET /laporan           → Lurah/LaporanTable Livewire page (route: lurah.laporan)
  GET /laporan/print     → AdminAuthController@laporanPrint (reuse existing, route: lurah.laporan.print)
```

### File Structure

```
app/
  Http/
    Controllers/
      Auth/AdminAuthController.php   ← modify login() redirect logic
  Livewire/
    Lurah/
      Dashboard.php
      ApprovalTable.php
      LaporanTable.php

resources/views/
  layouts/
    lurah.blade.php                  ← navbar lurah (3 links: Dashboard, Approval, Laporan)
  livewire/
    lurah/
      dashboard.blade.php
      approval-table.blade.php
      laporan-table.blade.php

tests/Feature/Web/
  LurahAuthTest.php
  LurahDashboardTest.php
  LurahApprovalTest.php
  LurahLaporanTest.php
```

---

## Authentication

- Shared `/login` page — `AdminAuthController::login()` modified:
```php
$role = Auth::user()->role;
return redirect()->intended(match($role) {
    'lurah' => route('lurah.dashboard'),
    default => route('dashboard'),
});
```
- `WebRoleMiddleware` (existing) guard `/lurah/*`: cek `role === 'lurah'`
- Admin tidak bisa akses `/lurah/*`, Lurah tidak bisa akses `/dashboard/*`
- Logout: shared `POST /logout` → redirect `/login`

---

## Pages & Components

### Layout (`layouts/lurah.blade.php`)
- Top navbar: logo "Lansia" (kiri) + 3 nav links: Dashboard, Approval, Laporan + tombol Logout (kanan)
- Active nav link highlight berdasarkan current route
- Hamburger menu Alpine.js untuk mobile
- Responsive (same pattern as admin layout)

### Dashboard (`Livewire/Lurah/Dashboard.php`)

**UI:**
- 4 stat cards: Total Lansia, Total Penerima Bantuan, Pending Approval (approved_at IS NULL + status_penerima=penerima), RW Terbanyak
- Bar chart (Chart.js): distribusi usia per kelompok (60-64, 65-69, 70-74, 75-79, 80+)
- Tabel: distribusi bantuan per RW — kolom: RW, Total Lansia, Penerima, Tidak Penerima, Approved

**Data (Eloquent direct):**
- `Lansia::count()`
- `BantuanGizi::where('status_penerima', 'penerima')->count()`
- `BantuanGizi::where('status_penerima', 'penerima')->whereNull('approved_at')->count()`
- Group by RW untuk distribusi

### Approval (`Livewire/Lurah/ApprovalTable.php`)

**UI:**
- Filter: select periode bulan + input tahun
- Tombol "Approve Semua Penerima" (bulk) — hanya muncul jika ada penerima belum di-approve
- Tabel: No, Nama, Usia, RW, Skor Ranking, Status Penerima (badge), Status Approval (badge: Approved/Pending)
- Tombol "Approve" per row untuk `status_penerima = penerima` yang belum approved
- Summary: "X dari Y penerima sudah disetujui"

**Livewire Properties:**
- `$periodeBulan` — int, default current month
- `$periodeTahun` — int, default current year

**Behavior:**
- `approve(int $bantuanId)` → update `approved_by = auth()->id()`, `approved_at = now()`
- `approveAll()` → bulk update semua `status_penerima = penerima` periode itu yang belum approved
- Tabel refresh otomatis setelah approve

**Approval = "Persetujuan laporan penyaluran bantuan"** — 1 action mencakup keduanya. `approved_by` + `approved_at` pada `bantuan_gizi` sebagai proof of sign-off.

### Laporan (`Livewire/Lurah/LaporanTable.php`)

**UI (read-only, mirip admin laporan):**
- Filter: RW (dropdown), Jenis (Semua/Penerima/Tidak Penerima), Periode bulan+tahun
- Tabel: No, NIK, Nama, Usia, RW, Periode, Status Penerima, Status Approval, Skor
- Footer: "Menampilkan X sampai Y dari total Z data"
- Tombol "Unduh Laporan" (CSV) + "Cetak Laporan" (open print view — reuse `laporan-print.blade.php`)
- Print view: reuse existing `AdminAuthController@laporanPrint` dengan route `lurah.laporan.print`

---

## Data Flow

```
Browser (Livewire request)
  └── Livewire Component (PHP)
        └── Eloquent Model → MySQL DB
              ├── BantuanGizi (approve action)
              └── Lansia, Pendataan (read)
```

Tidak ada HTTP call ke `/api/v1/`.

---

## Key Technical Decisions

| Decision | Choice | Reason |
|----------|--------|--------|
| URL prefix | `/lurah/` | Separation of concern, role clarity |
| Login | Shared `/login` | No duplication, role-based redirect |
| Approval | `bantuan_gizi.approved_by/at` | DB already has fields, YAGNI |
| Print view | Reuse `AdminAuthController@laporanPrint` | Same data, no duplication |
| Layout | Separate `layouts/lurah.blade.php` | Different nav links from admin |
| Role guard | Existing `WebRoleMiddleware` | Already supports variadic roles |
