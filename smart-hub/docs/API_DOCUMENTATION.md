# Smart-Hub API — Analisis & Dokumentasi Endpoint

## Strategi Autentikasi

**Laravel Sanctum Token-Based Auth**

```
Tablet App  →  POST /api/v1/auth/login  →  Terima token
Tablet App  →  Header: Authorization: Bearer {token}  →  Akses endpoint
```

Alasan memilih Sanctum:
- Ringan, cocok untuk SPA & mobile/tablet
- Token bisa diberi nama device ("Tablet-01")
- Bisa set `abilities` (scope: hanya member actions)
- `expires_at` — token tablet bisa diberi TTL panjang (30 hari)

---

## Base URL

```
Production : https://smarthub.local/api/v1
Development: http://localhost:8000/api/v1
```

## Format Response Standar

### Success (2xx)
```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": { ... },
    "meta": {
        "current_page": 1,
        "total": 50
    }
}
```

### Error (4xx / 5xx)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

---

## Endpoint Map

### AUTH ENDPOINTS (Public)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/auth/login` | Login → terima Bearer token |
| POST | `/auth/logout` | Revoke token aktif |
| GET | `/auth/me` | Info user terautentikasi |

---

### EQUIPMENT ENDPOINTS

**Akses Tablet (member)**

| Method | Endpoint | Middleware | Deskripsi |
|--------|----------|-----------|-----------|
| GET | `/equipment` | `auth:sanctum` | List peralatan tersedia |
| GET | `/equipment/{id}` | `auth:sanctum` | Detail peralatan |
| POST | `/equipment/{id}/checkout` | `auth:sanctum, role:member` | Check-out peralatan |
| POST | `/equipment/checkouts/{checkoutId}/return` | `auth:sanctum, role:member` | Return peralatan |
| GET | `/equipment/checkouts/my` | `auth:sanctum` | Riwayat checkout user |

**Akses Admin**

| Method | Endpoint | Middleware | Deskripsi |
|--------|----------|-----------|-----------|
| GET | `/admin/equipment` | `auth:sanctum, role:admin` | List semua peralatan |
| POST | `/admin/equipment` | `auth:sanctum, role:admin` | Tambah peralatan |
| PUT | `/admin/equipment/{id}` | `auth:sanctum, role:admin` | Update peralatan |
| DELETE | `/admin/equipment/{id}` | `auth:sanctum, role:admin` | Hapus peralatan |
| GET | `/admin/equipment/checkouts` | `auth:sanctum, role:admin` | Semua transaksi checkout |
| PUT | `/admin/equipment/checkouts/{id}` | `auth:sanctum, role:admin` | Update status checkout |

---

### ROOM ENDPOINTS

| Method | Endpoint | Middleware | Deskripsi |
|--------|----------|-----------|-----------|
| GET | `/rooms` | `auth:sanctum` | List ruangan aktif |
| GET | `/rooms/{id}` | `auth:sanctum` | Detail ruangan |
| GET | `/rooms/{id}/availability` | `auth:sanctum` | Cek ketersediaan ruangan |
| POST | `/admin/rooms` | `auth:sanctum, role:admin` | Tambah ruangan |
| PUT | `/admin/rooms/{id}` | `auth:sanctum, role:admin` | Update ruangan |
| DELETE | `/admin/rooms/{id}` | `auth:sanctum, role:admin` | Hapus ruangan |

---

### BOOKING ENDPOINTS

| Method | Endpoint | Middleware | Deskripsi |
|--------|----------|-----------|-----------|
| POST | `/bookings` | `auth:sanctum, role:member` | Buat booking baru |
| GET | `/bookings/my` | `auth:sanctum` | Daftar booking user sendiri |
| GET | `/bookings/{id}` | `auth:sanctum` | Detail booking (owner/admin) |
| POST | `/bookings/{id}/cancel` | `auth:sanctum` | Batalkan booking |
| GET | `/admin/bookings` | `auth:sanctum, role:admin` | Semua booking |
| PUT | `/admin/bookings/{id}/confirm` | `auth:sanctum, role:admin` | Konfirmasi booking |
| PUT | `/admin/bookings/{id}/status` | `auth:sanctum, role:admin` | Update status booking |

---

### DASHBOARD ENDPOINT (Admin)

| Method | Endpoint | Middleware | Deskripsi |
|--------|----------|-----------|-----------|
| GET | `/admin/dashboard/stats` | `auth:sanctum, role:admin` | Statistik ringkasan |

---

## Contoh Request/Response

### POST /api/v1/auth/login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "member@smarthub.com",
    "password": "password123",
    "device_name": "Tablet-Studio-01"
}
```
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 5,
            "name": "Budi Santoso",
            "email": "member@smarthub.com",
            "role": "member",
            "membership_number": "MBR-2025-005"
        },
        "token": "5|laravel_sanctum_token_here",
        "token_type": "Bearer"
    }
}
```

### POST /api/v1/equipment/{id}/checkout
```http
POST /api/v1/equipment/3/checkout
Authorization: Bearer 5|laravel_sanctum_token_here
Content-Type: application/json

{
    "expected_return_at": "2025-05-12 18:00:00",
    "notes_checkout": "Untuk keperluan shooting produk"
}
```
```json
{
    "success": true,
    "message": "Peralatan berhasil di-checkout",
    "data": {
        "checkout_code": "CO-20250512-003",
        "equipment": {
            "id": 3,
            "name": "Canon EOS R5",
            "code": "CAM-003"
        },
        "checked_out_at": "2025-05-12 10:30:00",
        "expected_return_at": "2025-05-12 18:00:00",
        "status": "active"
    }
}
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK — Request berhasil |
| 201 | Created — Resource baru dibuat |
| 400 | Bad Request — Parameter salah |
| 401 | Unauthorized — Token tidak valid/expired |
| 403 | Forbidden — Tidak punya hak akses |
| 404 | Not Found — Resource tidak ditemukan |
| 409 | Conflict — Konflik data (mis: equipment sudah ter-checkout) |
| 422 | Unprocessable Entity — Validasi gagal |
| 500 | Internal Server Error |
