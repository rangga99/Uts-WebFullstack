# Smart-Hub Management System — Database Schema Analysis

## Analisis Kebutuhan Entitas

Berdasarkan kebutuhan sistem (peminjaman ruang kerja + peralatan studio, dua jenis pengguna, check-in via tablet), diperoleh **6 tabel utama**:

---

## ERD (Entity Relationship Diagram)

```
users (1) ──────────────< (N) bookings
users (1) ──────────────< (N) equipment_checkouts
users (1) ──────────────< (N) personal_access_tokens  [Sanctum]

equipment (1) ───────────< (N) equipment_checkouts
rooms (1) ────────────────< (N) bookings

roles: admin | member  (kolom enum di tabel users)
```

---

## Skema Tabel

### 1. `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | Auto-increment |
| name | varchar(100) | Nama anggota |
| email | varchar(150) | Unique, login |
| email_verified_at | timestamp | Nullable |
| password | varchar(255) | Bcrypt |
| role | enum('admin','member') | Default: member |
| phone | varchar(20) | Nullable |
| membership_number | varchar(30) | Unique, nullable |
| is_active | boolean | Default: true |
| remember_token | varchar(100) | Nullable |
| created_at / updated_at | timestamp | Auto |

**Indeks:** `email` (unique), `membership_number` (unique), `role`

---

### 2. `rooms`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) | Nama ruangan |
| code | varchar(20) | Unique (mis: STUDIO-A) |
| type | enum('workspace','studio','meeting') | |
| capacity | tinyint | Maks pengguna |
| description | text | Nullable |
| facilities | json | List fasilitas |
| price_per_hour | decimal(10,2) | Biaya/jam |
| is_available | boolean | Default: true |
| created_at / updated_at | timestamp | |

**Indeks:** `code` (unique), `type`, `is_available`

---

### 3. `bookings`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| booking_code | varchar(30) | Unique (mis: BK-20250512-001) |
| user_id | FK → users.id | |
| room_id | FK → rooms.id | |
| start_datetime | datetime | |
| end_datetime | datetime | |
| duration_hours | decimal(4,2) | Computed saat create |
| total_price | decimal(10,2) | duration × price_per_hour |
| status | enum('pending','confirmed','ongoing','completed','cancelled') | |
| notes | text | Nullable |
| confirmed_by | FK → users.id | Admin yang konfirmasi, nullable |
| confirmed_at | timestamp | Nullable |
| cancelled_at | timestamp | Nullable |
| cancellation_reason | text | Nullable |
| created_at / updated_at | timestamp | |

**Indeks:** `booking_code` (unique), `user_id`, `room_id`, `status`, `start_datetime`

---

### 4. `equipment`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) | Nama peralatan |
| code | varchar(30) | Unique (mis: CAM-001) |
| category | enum('camera','audio','lighting','computer','other') | |
| brand | varchar(80) | Nullable |
| model | varchar(80) | Nullable |
| serial_number | varchar(80) | Unique, nullable |
| condition | enum('excellent','good','fair','needs_repair') | Default: good |
| status | enum('available','checked_out','maintenance','retired') | Default: available |
| description | text | Nullable |
| purchase_date | date | Nullable |
| purchase_price | decimal(10,2) | Nullable |
| location | varchar(100) | Lokasi penyimpanan |
| created_at / updated_at | timestamp | |

**Indeks:** `code` (unique), `serial_number` (unique), `status`, `category`

---

### 5. `equipment_checkouts`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| checkout_code | varchar(30) | Unique (mis: CO-20250512-001) |
| user_id | FK → users.id | Anggota peminjam |
| equipment_id | FK → equipment.id | |
| booking_id | FK → bookings.id | Nullable (opsional terkait booking) |
| checked_out_at | timestamp | Waktu ambil |
| expected_return_at | timestamp | |
| returned_at | timestamp | Nullable |
| status | enum('active','returned','overdue','lost') | Default: active |
| condition_before | enum('excellent','good','fair','needs_repair') | Kondisi sebelum |
| condition_after | enum('excellent','good','fair','needs_repair') | Nullable |
| notes_checkout | text | Nullable |
| notes_return | text | Nullable |
| processed_by | FK → users.id | Admin/staf |
| created_at / updated_at | timestamp | |

**Indeks:** `checkout_code` (unique), `user_id`, `equipment_id`, `status`

---

### 6. `personal_access_tokens` (Laravel Sanctum)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| tokenable_type | varchar | Polymorphic |
| tokenable_id | bigint | |
| name | varchar(255) | Nama device/app |
| token | varchar(64) | SHA-256 hash, unique |
| abilities | text | JSON abilities |
| last_used_at | timestamp | Nullable |
| expires_at | timestamp | Nullable |
| created_at / updated_at | timestamp | |

---

## Strategi Optimasi Database

1. **Soft Deletes** — Tidak digunakan (hard delete + status tracking lebih bersih)
2. **JSON Column** untuk `rooms.facilities` — fleksibel tanpa tabel pivot
3. **Composite Index** pada `bookings(room_id, start_datetime, end_datetime)` — query ketersediaan ruangan
4. **Enum vs Lookup Table** — Enum dipilih karena nilai stabil dan performa lebih baik
5. **Decimal(10,2)** untuk semua nilai uang — presisi finansial
