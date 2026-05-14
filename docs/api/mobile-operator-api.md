# API Dokumentasi — Operator / Kader RW (Mobile App)

**Base URL:** `http://127.0.0.1:8000/api/v1`  
**Auth:** Bearer Token (Sanctum) — semua endpoint kecuali login wajib header:
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## Authentication

### Login
```
POST /auth/login
```
**Body:**
```json
{
  "email": "operator@example.com",
  "password": "password"
}
```
**Response 200:**
```json
{
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "nama": "Budi Santoso",
      "role": "operator"
    }
  }
}
```
**Response 422 (salah kredensial):**
```json
{
  "message": "Kredensial tidak valid.",
  "errors": { "email": ["Kredensial tidak valid."] }
}
```

---

### Logout
```
POST /auth/logout
Authorization: Bearer {token}
```
**Response 204** (no body)

---

## Pendataan Lansia

### List Lansia
```
GET /lansia
```
**Query Params (opsional):**
| Param | Type | Keterangan |
|---|---|---|
| `nama` | string | Filter nama (partial match) |
| `rw` | string | Filter RW, contoh: `001` |
| `kondisi_kesehatan` | string | `baik` / `sedang` / `buruk` |
| `status_bantuan` | string | `penerima` / `tidak_penerima` |
| `page` | int | Pagination, default 1 |

**Response 200:**
```json
{
  "data": [
    {
      "lansia_id": 1,
      "nik": "3271010101800001",
      "nama": "Siti Rahayu",
      "tanggal_lahir": "1950-01-01",
      "usia": 76,
      "jenis_kelamin": "P",
      "alamat": "Jl. Mawar No. 1",
      "rt": "001",
      "rw": "003",
      "foto_ktp": "http://127.0.0.1:8000/storage/foto-ktp/abc.jpg",
      "created_by": 2,
      "created_at": "2026-05-14T10:00:00+07:00",
      "updated_at": "2026-05-14T10:00:00+07:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 40 }
}
```

---

### Detail Lansia
```
GET /lansia/{lansia_id}
```
**Response 200:** sama dengan 1 objek di `data` atas  
**Response 404:** `{ "message": "No query results for model..." }`

---

### Input Data Lansia (Tambah)
```
POST /lansia
```
**Body:**
```json
{
  "nik": "3271010101800001",
  "nama": "Siti Rahayu",
  "tanggal_lahir": "1950-01-01",
  "jenis_kelamin": "P",
  "alamat": "Jl. Mawar No. 1",
  "rt": "001",
  "rw": "003"
}
```
**Validasi:**
| Field | Rule |
|---|---|
| `nik` | required, 16 karakter, unik |
| `nama` | required, max 255 |
| `tanggal_lahir` | required, format `YYYY-MM-DD` |
| `jenis_kelamin` | required, `L` atau `P` |
| `alamat` | required |
| `rt` | opsional |
| `rw` | required |

**Response 201:** objek LansiaResource  
**Response 422:** validation errors

---

### Update Data Lansia
```
PUT /lansia/{lansia_id}
```
**Body:** sama dengan POST, semua field opsional (partial update)  
**Response 200:** objek LansiaResource

---

### Hapus Lansia
```
DELETE /lansia/{lansia_id}
```
**Response 204** (no body)

---

### Upload Foto KTP
```
POST /lansia/{lansia_id}/foto-ktp
Content-Type: multipart/form-data
```
**Body:**
| Field | Type | Rule |
|---|---|---|
| `foto_ktp` | file (image) | required, max 2MB, format: jpg/png/jpeg |

**Response 200:** objek LansiaResource (dengan `foto_ktp` URL terupdate)

---

### Status Bantuan Lansia
```
GET /lansia/{lansia_id}/status-bantuan
```
**Response 200 (ada data bantuan):**
```json
{
  "data": {
    "bantuan_id": 5,
    "lansia_id": 1,
    "periode_bulan": 5,
    "periode_tahun": 2026,
    "skor_ranking": 0.8750,
    "status_penerima": "penerima",
    "approved_by": 3,
    "approved_at": "2026-05-10T09:00:00+07:00"
  }
}
```
**Response 200 (belum ada bantuan):**
```json
{
  "data": { "status_penerima": null, "lansia_id": 1 }
}
```

---

## Pemeriksaan Kesehatan

### List Pemeriksaan (per lansia)
```
GET /lansia/{lansia_id}/pemeriksaan
```
**Response 200:**
```json
{
  "data": [
    {
      "pemeriksaan_id": 1,
      "lansia_id": 1,
      "tanggal_periksa": "2026-05-01",
      "berat_badan": 52.5,
      "tekanan_darah": "120/80",
      "hasil_periksa": "baik",
      "catatan": "kondisi stabil"
    }
  ]
}
```

### Tambah Pemeriksaan
```
POST /lansia/{lansia_id}/pemeriksaan
```
**Body:**
```json
{
  "tanggal_periksa": "2026-05-14",
  "berat_badan": 52.5,
  "tekanan_darah": "120/80",
  "hasil_periksa": "baik",
  "catatan": "kondisi stabil"
}
```
**Validasi `hasil_periksa`:** `baik` / `sedang` / `buruk`  
**Response 201:** objek pemeriksaan

---

## Monitoring Data

### Daftar Lansia Terdaftar
Gunakan endpoint `GET /lansia` — data pagination lengkap.

### Status Penerimaan Bantuan
Gunakan `GET /lansia/{lansia_id}/status-bantuan` — cek per lansia.

---

## Notifikasi Berhasil/Gagal

API menggunakan HTTP status code standar:

| Code | Arti |
|---|---|
| `200` | Berhasil (GET/PUT) |
| `201` | Berhasil dibuat (POST) |
| `204` | Berhasil dihapus (DELETE/logout) |
| `401` | Token tidak valid / belum login |
| `403` | Tidak punya akses |
| `404` | Data tidak ditemukan |
| `422` | Validasi gagal (cek `errors` di response) |

**Response 422 contoh:**
```json
{
  "message": "The nik field is required.",
  "errors": {
    "nik": ["The nik field is required."],
    "nama": ["The nama field is required."]
  }
}
```

---

## Sinkronisasi

API ini **real-time** — setiap request langsung tersimpan ke server saat internet tersedia.

Untuk offline-first (sinkronisasi saat reconnect), implementasi di sisi mobile:
1. Simpan data ke Room/SQLite lokal saat offline
2. Saat online, kirim ke endpoint yang relevan (POST/PUT/DELETE)
3. Gunakan status code response untuk konfirmasi sukses

---

## Catatan

- Semua response JSON dibungkus dalam key `data`
- Pagination pakai `meta.current_page`, `meta.last_page`, `meta.total`
- `foto_ktp` URL langsung bisa dipakai di `<ImageView>` Android
- `tanggal_lahir` format ISO `YYYY-MM-DD`, `created_at` / `approved_at` format ISO 8601
- Field `usia` dihitung otomatis dari `tanggal_lahir` — tidak perlu kirim dari mobile
