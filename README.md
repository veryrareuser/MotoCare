<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# MotoCare — Motorcycle Workshop Management System

MotoCare is a motorcycle workshop management system built with **PHP 8.4 + Laravel 13**, **MySQL**, **Blade + Tailwind 3 + Alpine.js**, and **Vite**. The UI is localized to **Indonesian** with **IDR** pricing and **Asia/Jakarta** display timezone.

It supports six roles (`customer`, `admin`, `mechanic`, `inventory_staff`, `cashier`, `owner`) with one role per user. Owners are read-only.

---

## ✨ Key Features

- **Authentication & Roles** — Laravel Breeze auth with six roles. Customer login at `/login`, staff login at `/login/staff`.
- **Service Orders** — Bookings flow into service orders with status tracking: `waiting_queue → being_inspected → in_progress → completed → picked_up`.
- **Customer Portal** — Track bookings, view service history, manage motorcycles, browse sparepart catalog, and manage maintenance reminders.
- **Staff Portal** — Queue management, check-in/check-out, service order workflow, invoicing, sparepart inventory, staff account management.
- **Maintenance Reminders** — Manual reminders with due date and/or due odometer, generating in-app notifications when due. Admin creates/updates/cancels/completes; customers read their own.
- **Notifications** — In-app notification dropdown with mark-as-read and read-all.
- **Invoicing** — `INV-YYYYMMDD-NNN` numbering, statuses `unpaid`/`partially_paid`/`paid`, immutable once finalized, cash-basis payments.
- **Low-Stock Alerts** — Scheduled alerts notify active admins and inventory staff once per user/sparepart/local day.
- **Soft Deletes** — Users, motorcycles, service types, and spareparts are soft-deleted/deactivated; no cascading deletes on financial or service history.
- **Scheduler** — `reminders:send-due` daily 07:00 Asia/Jakarta; `inventory:low-stock-alerts` daily 07:05 Asia/Jakarta; both `withoutOverlapping()`.

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.4, Laravel 13 |
| Database | MySQL |
| Frontend | Blade, Tailwind CSS 3, Alpine.js, Vite |
| Auth | Laravel Breeze |
| Testing | PHPUnit |
| Code Quality | Pint, Laravel Pint |

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.4
- Composer
- Node.js + npm
- MySQL 8.0
- Git

### Installation

```bash
# Clone the repo
git clone <your-repo-url>
cd motocare

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure your database in .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# Run migrations and seeders
php artisan migrate --seed

# Build frontend assets
npm run build

# Start the dev server
php artisan serve
```

Visit **http://127.0.0.1:8000**.

> **Note:** PHP is not on PATH in this environment. Use `C:\php84\php.exe` directly, e.g. `C:\php84\php.exe artisan serve`.

---

## 🔐 Demo Accounts

All accounts use password `password`. Emails end with `@motocare.test`.

| Role | Email | Access |
|------|-------|--------|
| Customer | `customer@motocare.test` | `/login` → `/dashboard` |
| Admin | `admin@motocare.test` | `/login/staff` → `/staff/dashboard` |
| Mechanic | `mechanic@motocare.test` | `/login/staff` → `/staff/dashboard` |
| Inventory Staff | `inventory@motocare.test` | `/login/staff` → `/staff/dashboard` |
| Cashier | `cashier@motocare.test` | `/login/staff` → `/staff/dashboard` |
| Owner | `owner@motocare.test` | `/login/staff` → `/staff/dashboard` (read-only) |

---

## 🧪 Testing

```bash
php artisan test
```

The suite covers authentication, bookings, service orders, service types, spareparts, stock movements, staff accounts, reminders, notifications, and invoicing.

---

## 📦 Project Structure

```
app/                 # Laravel application code
resources/views/     # Blade templates
routes/              # Route definitions (web, auth, customer, staff, console)
database/migrations/ # Database migrations
database/seeders/    # Demo data seeders
tests/               # PHPUnit feature tests
```

---

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).