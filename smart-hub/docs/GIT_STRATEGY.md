# Smart-Hub — Git Version Control Strategy

## Branch Strategy: GitHub Flow (Modified)

```
main (production-ready)
 └── develop (integration branch)
      ├── feature/email-notification     ← Tim fitur notifikasi
      ├── feature/equipment-crud         ← Core CRUD
      ├── feature/booking-system         ← Sistem booking
      └── hotfix/fix-checkout-bug        ← Perbaikan mendesak
```

---

## Aturan Branch

| Branch | Proteksi | Keterangan |
|--------|----------|-----------|
| `main` | ✅ Protected, require PR + review | Deploy production |
| `develop` | ✅ Protected, require PR | Integrasi semua fitur |
| `feature/*` | ❌ Bebas push | Pengerjaan fitur |
| `hotfix/*` | ❌ Bebas push | Bug critical di main |

---

## Alur Kerja Paralel: Fitur Email Notification

```bash
# 1. Tim Email Notification — buat branch dari develop
git checkout develop
git pull origin develop
git checkout -b feature/email-notification

# 2. Kerjakan fitur (contoh struktur file)
# app/Mail/BookingConfirmedMail.php
# app/Mail/CheckoutReminderMail.php
# app/Listeners/SendBookingConfirmationEmail.php
# app/Events/BookingConfirmed.php
# resources/views/emails/booking-confirmed.blade.php

# 3. Commit atomik dan deskriptif
git add app/Mail/BookingConfirmedMail.php
git commit -m "feat(email): add BookingConfirmedMail mailable class"

git add app/Events/BookingConfirmed.php app/Listeners/
git commit -m "feat(email): implement BookingConfirmed event & listener"

# 4. Push ke remote
git push origin feature/email-notification

# 5. Buat Pull Request ke develop (bukan langsung ke main)
# PR Title: "feat(email): Implementasi sistem notifikasi email booking"
# PR Description: Checklist, screenshot, test results

# 6. Setelah PR di-approve, merge ke develop
# Gunakan "Squash and Merge" untuk riwayat bersih
```

---

## Commit Message Convention (Conventional Commits)

```
<type>(<scope>): <subject>

Types:
  feat     → Fitur baru
  fix      → Bug fix
  refactor → Refactor kode
  docs     → Perubahan dokumentasi
  test     → Penambahan/perubahan test
  chore    → Maintenance (config, deps)
  style    → Formatting, tidak ada logika

Contoh:
  feat(equipment): add checkout endpoint with conflict detection
  fix(auth): resolve token expiry not returning 401
  docs(api): update endpoint documentation for bookings
  refactor(booking): extract availability check to service class
```

---

## .gitignore (Laravel)

```gitignore
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.*
!.env.example
.phpunit.result.cache
Homestead.json
Homestead.yaml
auth.json
npm-debug.log
yarn-error.log
/.fleet
/.idea
/.vscode
```

---

## Contoh `.env.example`

```env
APP_NAME="Smart-Hub Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smarthub_db
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DOMAIN=localhost

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@smarthub.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Flow Hotfix (Bug Darurat di Production)

```bash
# Checkout dari main (bukan develop!)
git checkout main
git pull origin main
git checkout -b hotfix/fix-checkout-conflict

# Perbaiki bug
git commit -m "fix(checkout): prevent double checkout on concurrent requests"

# Merge ke KEDUA branch
git checkout main
git merge --no-ff hotfix/fix-checkout-conflict
git tag -a v1.0.1 -m "Hotfix: concurrent checkout conflict"
git push origin main --tags

git checkout develop
git merge --no-ff hotfix/fix-checkout-conflict
git push origin develop

# Hapus hotfix branch
git branch -d hotfix/fix-checkout-conflict
```

---

## Tag & Release

```bash
# Release setelah semua fitur merge ke main
git tag -a v1.0.0 -m "Release: Smart-Hub MVP - Core CRUD + API"
git push origin v1.0.0
```
