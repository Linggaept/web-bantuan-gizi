# API Design: Sistem Informasi Penyaluran Bantuan Gizi Lansia

**Date:** 2026-05-14  
**Status:** Approved

---

## Overview

REST API untuk sistem penyaluran bantuan gizi lansia. Digunakan oleh mobile app (Operator/Kader RW) dan web dashboard (Admin Kelurahan, Lurah).

**Stack:**
- Laravel 13, PHP 8.3
- Laravel Sanctum (token-based auth)
- Laravel API Resources (response transform)
- Prefix: `/api/v1/`

---

## Roles

| Role | Akses |
|------|-------|
| `operator` | CRUD lansia, upload foto KTP, monitoring status bantuan |
| `admin` | Semua operator + verifikasi, kuota, ranking, laporan, dashboard |
| `lurah` | Dashboard, monitoring, approval bantuan, laporan |

---

## Database (ERD)

```
USER: user_id, nama, username, password_hash, role, is_active, created_at

LANSIA: lansia_id, nik, nama, tanggal_lahir, jenis_kelamin, alamat, rt, rw,
        foto_ktp, created_by, created_at, updated_at, deleted_at

PEMERIKSAAN_KESEHATAN: pemeriksaan_id, lansia_id, tanggal_periksa,
        berat_badan, tekanan_darah, hasil_periksa, catatan

PENDATAAN: pendataan_id, lansia_id, user_id, status_verifikasi,
        verified_by, verified_at, tanggal_input

BANTUAN_GIZI: bantuan_id, lansia_id, periode_bulan, periode_tahun,
        skor_ranking, status_penerima, approved_by, approved_at
```

---

## Authentication

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/auth/login` | All | Login, return Sanctum token |
| POST | `/api/v1/auth/logout` | All (auth) | Revoke current token |

**Login request:**
```json
{ "username": "string", "password": "string" }
```

**Login response:**
```json
{
  "data": {
    "token": "string",
    "user": { "id": 1, "nama": "string", "role": "operator" }
  }
}
```

---

## Lansia Endpoints

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/lansia` | All | List lansia (filter: nama, rw, kondisi, status_bantuan) |
| POST | `/api/v1/lansia` | operator, admin | Input data lansia baru |
| GET | `/api/v1/lansia/{id}` | All | Detail lansia |
| PUT | `/api/v1/lansia/{id}` | operator, admin | Edit data lansia |
| DELETE | `/api/v1/lansia/{id}` | operator, admin | Hapus data lansia (soft delete) |
| POST | `/api/v1/lansia/{id}/foto-ktp` | operator, admin | Upload foto KTP |
| GET | `/api/v1/lansia/{id}/status-bantuan` | All | Status penerimaan bantuan lansia |

**Filter query params (GET /lansia):**
- `nama` — string, partial match
- `rw` — integer
- `kondisi_kesehatan` — string (dari hasil_periksa)
- `status_bantuan` — `penerima` | `tidak_penerima` | `pending`

---

## Pemeriksaan Kesehatan Endpoints

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/lansia/{id}/pemeriksaan` | All | List riwayat pemeriksaan |
| POST | `/api/v1/lansia/{id}/pemeriksaan` | operator, admin | Input hasil pemeriksaan |
| GET | `/api/v1/pemeriksaan/{id}` | All | Detail pemeriksaan |
| PUT | `/api/v1/pemeriksaan/{id}` | operator, admin | Edit pemeriksaan |

---

## Pendataan / Verifikasi Endpoints

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/pendataan` | admin | List pendataan menunggu verifikasi |
| POST | `/api/v1/pendataan/{id}/verifikasi` | admin | Verifikasi/validasi identitas lansia |

**Verifikasi request:**
```json
{ "status_verifikasi": "terverifikasi" | "ditolak", "catatan": "optional" }
```

---

## Bantuan Gizi Endpoints

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/bantuan` | admin, lurah | List penerima bantuan per periode |
| POST | `/api/v1/bantuan/kuota` | admin | Set kuota penerima per periode |
| GET | `/api/v1/bantuan/kuota` | admin, lurah | Lihat kuota aktif |
| POST | `/api/v1/bantuan/ranking` | admin | Trigger auto-ranking penerima |
| POST | `/api/v1/bantuan/{id}/approve` | lurah | Approval akhir penerima bantuan |

**Ranking Algorithm:**

Dipanggil via `POST /api/v1/bantuan/ranking` dengan body `{ "periode_bulan": 5, "periode_tahun": 2026 }`.

Langkah:
1. Ambil semua lansia dengan `status_verifikasi = terverifikasi`
2. Hitung usia dari `tanggal_lahir`
3. Ambil `hasil_periksa` terbaru dari `PEMERIKSAAN_KESEHATAN`
4. Map `hasil_periksa` ke skor numerik (0–10): `buruk=10, sedang=6, baik=3`
5. Hitung `skor_ranking = (usia / 100 * 0.6) + (skor_kesehatan / 10 * 0.4)`
6. Urutkan DESC
7. Potong sesuai kuota → set `status_penerima = penerima`, sisanya `tidak_penerima`
8. Simpan ke tabel `BANTUAN_GIZI`

---

## Dashboard & Laporan Endpoints

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/dashboard/stats` | admin, lurah | Statistik: jumlah lansia, per RW, usia, kondisi |
| GET | `/api/v1/laporan` | admin, lurah | List laporan (filter: rw, kondisi, jenis, limit) |
| GET | `/api/v1/laporan/download` | admin, lurah | Download laporan (PDF/CSV) |

**Filter query params (GET /laporan):**
- `rw` — integer
- `kondisi_kesehatan` — string
- `jenis` — `penerima` | `semua` | `rekap`
- `limit` — integer
- `periode_bulan`, `periode_tahun` — integer

---

## Middleware & Authorization

```
routes/api.php
  └── prefix: api/v1
        ├── (public) auth/login
        └── (middleware: auth:sanctum)
              ├── (middleware: role:operator,admin) → lansia, pemeriksaan
              ├── (middleware: role:admin) → pendataan, bantuan/kuota, bantuan/ranking, dashboard, laporan
              └── (middleware: role:lurah) → bantuan/approve, dashboard, laporan
```

Custom `role` middleware cek `users.role` kolom.

---

## Response Format

**Success:**
```json
{
  "data": { ... },
  "meta": { "current_page": 1, "total": 100 }
}
```

**Error:**
```json
{
  "message": "string",
  "errors": { "field": ["validation message"] }
}
```

**HTTP Status codes:**
- `200` OK, `201` Created, `204` No Content
- `400` Bad Request, `401` Unauthenticated, `403` Forbidden
- `404` Not Found, `422` Validation Error, `500` Server Error

---

## File Structure (akan dibuat)

```
app/
  Http/
    Controllers/Api/V1/
      AuthController.php
      LansiaController.php
      PemeriksaanController.php
      PendataanController.php
      BantuanController.php
      DashboardController.php
      LaporanController.php
    Middleware/
      RoleMiddleware.php
    Resources/
      LansiaResource.php
      LansiaCollection.php
      PemeriksaanResource.php
      PendataanResource.php
      BantuanResource.php
      DashboardStatsResource.php
  Models/
    User.php (update)
    Lansia.php
    PemeriksaanKesehatan.php
    Pendataan.php
    BantuanGizi.php
  Services/
    RankingService.php
routes/
  api.php
database/
  migrations/ (5 new)
```
