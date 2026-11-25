# API routes

- `POST /api/register.php` — регистрация `{name, phone}`.
- `GET /api/me.php?t=<64-char-token>` — данные карты.
- `POST /api/admin-login.php` — `{username,password}`.
- `GET /api/admin-session.php` — состояние сессии.
- `POST /api/admin-logout.php` — CSRF header.
- `POST /api/admin-scan.php` — `{qr}`.
- `POST /api/admin-stamp.php` — `{memberId,requestId}` + CSRF.
- `POST /api/admin-redeem.php` — `{memberId}` + CSRF.
- `GET /api/admin-stats.php` — статистика и последние операции.

Схема БД находится в `schema.sql`.
