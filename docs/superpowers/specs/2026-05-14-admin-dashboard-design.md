# Admin Kelurahan Web Dashboard Design

**Date:** 2026-05-14  
**Status:** Approved

---

## Overview

Web dashboard untuk Admin Kelurahan pada sistem penyaluran bantuan gizi lansia. Dibangun dengan Livewire + Blade + Tailwind CSS v4 + Chart.js. Data langsung dari Eloquent (tidak melalui API internal).

**Stack:**
- Laravel 13, PHP 8.3
- Laravel Livewire v3
- Tailwind CSS v4 (sudah terpasang)
- Chart.js (via CDN atau npm)
- Alpine.js (bundled dengan Livewire)
- Session auth (bukan Sanctum token)

---

## Architecture

### Routes (`routes/web.php`)

```
GET  /login                    → AdminAuthController@showLogin
POST /login                    → AdminAuthController@login
POST /logout                   → AdminAuthController@logout

/dashboard (middleware: auth, role:admin)
  GET /                        → Dashboard Livewire page
  GET /lansia                  → LansiaTable Livewire page
  GET /lansia/{id}/edit        → LansiaForm Livewire page
  GET /bantuan                 → BantuanManagement Livewire page
  GET /laporan                 → LaporanTable Livewire page
  GET /laporan/print           → LaporanPrint blade (no auth middleware strip)
  GET /monitoring              → MonitoringTable Livewire page
```

### File Structure

```
app/
  Http/
    Controllers/
      Auth/AdminAuthController.php
    Middleware/
      WebRoleMiddleware.php        ← cek users.role untuk web session
  Livewire/
    Admin/
      Dashboard.php
      LansiaTable.php
      LansiaForm.php
      BantuanManagement.php
      LaporanTable.php
      MonitoringTable.php

resources/views/
  layouts/
    admin.blade.php              ← navbar + content slot
  auth/
    login.blade.php
  livewire/
    admin/
      dashboard.blade.php
      lansia-table.blade.php
      lansia-form.blade.php
      bantuan-management.blade.php
      laporan-table.blade.php
      laporan-print.blade.php    ← print-only view (no navbar)
      monitoring-table.blade.php
```

---

## Authentication

- Login via `Auth::attempt(['email' => ..., 'password' => ...])` — session based
- `WebRoleMiddleware` guard semua `/dashboard/*`: cek `auth()->user()->role === 'admin'`
- Redirect unauthorized ke `/login`
- Logout: `Auth::logout()` + session invalidate + redirect `/login`
- Login page sesuai reference: card centered, field Email + Password, tombol "Masuk"

---

## Pages & Components

### Layout (`layouts/admin.blade.php`)
- Top navbar: logo "Lansia" (kiri) + nav links: Dashboard, Data Lansia, Laporan, Pengaturan + tombol Logout (kanan)
- Active nav link highlight berdasarkan current route
- Content area full width di bawah navbar
- Tailwind responsive

### Dashboard (`Livewire/Admin/Dashboard.php`)

**UI:**
- 4 stat cards (row): Total Lansia, RW terbanyak (+ jumlah), Kondisi Sehat (count), Kondisi Sakit/Ringan (count)
- Bar chart (Chart.js): distribusi usia per kelompok (60-64, 65-69, 70-74, 75-79, 80+)
- Tabel kanan: 5 lansia terbaru per RW — kolom: RW, Nama, Usia, Kondisi

**Data (Eloquent direct):**
- `Lansia::count()`
- Group by RW, ambil max
- Join `pemeriksaan_kesehatan` latest, group by `hasil_periksa`
- Age distribution: group `tanggal_lahir` ranges

**Chart.js integration:**
- Data di-pass dari Livewire ke blade sebagai `@json($chartData)`
- Alpine.js init Chart.js saat mount
- Livewire `$refresh` tiap 30 detik untuk monitoring real-time

### Data Lansia (`Livewire/Admin/LansiaTable.php`)

**UI:**
- Search bar (nama atau NIK) + filter dropdown RW + filter kondisi kesehatan
- Tombol "+ Tambah Data" → redirect form input
- Tabel: No, Nama, Usia, Kondisi, Status Verifikasi (badge), Aksi (edit | hapus | verifikasi)
- Pagination 15 per halaman
- Modal konfirmasi hapus (Alpine.js)

**Livewire Properties:**
- `$search` — string, filter nama/NIK LIKE
- `$filterRw` — string, filter exact RW
- `$filterKondisi` — string, filter hasil_periksa latest
- `$filterStatus` — string, filter status_verifikasi pendataan

**Behavior:**
- Verifikasi langsung dari tabel → update `pendataan.status_verifikasi` + `verified_by` + `verified_at`
- Hapus → soft delete `lansia`
- Edit → redirect ke `/dashboard/lansia/{id}/edit`

### Form Lansia (`Livewire/Admin/LansiaForm.php`)

**UI (sesuai reference `input-lansia.png`):**
- Fields: Nama Lansia, RW (dropdown), NIK, Foto KTP (file upload), Usia (computed dari tanggal_lahir), Jenis Kelamin (radio Pria/Wanita), Kondisi Kesehatan (dropdown)
- Tombol Batal + Simpan

**Behavior:**
- Edit mode: load existing lansia data
- Foto KTP: upload ke `storage/app/public/foto-ktp/`
- Simpan → update `lansia` + update/create `pemeriksaan_kesehatan` (kondisi)
- Validasi: NIK 16 digit, unique (kecuali self), required fields

### Manajemen Bantuan Gizi (`Livewire/Admin/BantuanManagement.php`)

**UI:**
- Form set kuota: input angka kuota + select periode bulan + input tahun + tombol "Simpan Kuota"
- Tombol "Jalankan Ranking Otomatis" (dengan periode bulan/tahun)
- Tabel hasil ranking: No, Nama, Usia, Kondisi, Skor Ranking, Status Penerima (badge)
- Alert success/error setelah ranking

**Behavior:**
- Set kuota → `cache()->put("bantuan_kuota_{bulan}_{tahun}", [...])`
- Trigger ranking → `RankingService::rank()` (reuse existing service)
- Tabel update otomatis setelah ranking selesai

### Laporan (`Livewire/Admin/LaporanTable.php`)

**UI (sesuai reference `laporan-pendataan.png`):**
- Filter row: RW (dropdown), Jenis Laporan (dropdown: Semua/Penerima/Tidak Penerima/Rekap), Kondisi (dropdown), Jumlah Data (input)
- Tabel: No, RW, Nama RW, Jumlah Lansia, Lansia Sakit
- Footer: "Menampilkan X sampai Y dari total Z RW"
- Tombol "Unduh Laporan" (CSV download) + "Cetak Laporan" (open print view)

**Print View (`laporan-print.blade.php`):**
- No navbar, clean table layout
- Header: "Laporan Jumlah Lansia Per RW dari tanggal X s/d Y"
- Tombol cetak trigger `window.print()` via Alpine

### Monitoring Operasional (`Livewire/Admin/MonitoringTable.php`)

**UI:**
- Stat cards: total input hari ini, total verifikasi pending, total penerima bantuan
- Tabel log input operator: Operator, Lansia, Aksi, Waktu
- Tabel distribusi bantuan per RW: RW, Total Lansia, Penerima, Tidak Penerima

**Data:**
- `Pendataan` dengan eager load `user` + `lansia`
- `BantuanGizi` group by RW via join lansia

---

## Data Flow

```
Browser (Livewire request)
  └── Livewire Component (PHP)
        ├── Eloquent Model → MySQL DB
        └── RankingService (reuse) → BantuanGizi update
```

Tidak ada HTTP call ke `/api/v1/`. API tetap jalan terpisah untuk mobile.

---

## Key Technical Decisions

| Decision | Choice | Reason |
|----------|--------|--------|
| Auth | Session (Auth::attempt) | Web dashboard, bukan mobile |
| Reactivity | Livewire v3 | Server-side reactive, no SPA overhead |
| Charts | Chart.js via npm | Sudah familiar, cukup untuk bar/donut |
| Data source | Direct Eloquent | No HTTP overhead, same DB |
| Print | window.print() + dedicated blade | Simpel, no PDF library needed |
| Role check | WebRoleMiddleware (separate dari API RoleMiddleware) | Web session berbeda dari API token |

---

## Dependencies to Install

- `livewire/livewire` v3
- `chart.js` (via npm)
