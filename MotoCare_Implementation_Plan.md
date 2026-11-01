# MotoCare — Implementation Plan

**Status:** Draft for approval  
**Date:** 2026-11-01  
**Sources:** `MotoCare (PRD).md`, `Motocare BRD.md`, `MotoCare_Database_Architecture.md`, `MotoCare_Kelompok_2.md`, `System Request Motocare.md`, `AGENTS.md`, and the Figma file `4T0l3JEhdU0kH8pXKIskRa` (MotoCare).

---

## 1. Approved direction

The user approved the recommended defaults from the documentation-discovery report.

| ID | Decision |
|---|---|
| D1 | **Laravel 13 + PHP 8.3/8.4 + MySQL 8.0.44.** Laravel 13 requires PHP `^8.3`; the current PHP is 8.2.12. Phase 0 includes a PHP upgrade. Fallback: Laravel 12 on PHP 8.2.12 if the environment must not change. |
| D2 | **Booking:** auto-confirm when capacity is available; staff may cancel/no-show; daily queue number issued at confirmation; walk-ins supported; reschedule allowed until check-in; cancellation allowed until check-in/service-order start. Capacity values remain configurable and must be supplied before Phase 3. |
| D3 | **Reminders:** manual creation by authorized staff, using a due date, a due odometer, or both. In-app notification when due. No automatic prediction. The PRD's automatic-reminder wording will be treated as superseded by `AGENTS.md`. |
| D4 | **Sparepart purchase:** catalog browsing only. No cart, checkout, or online ordering in MVP. |
| D5 | **Loyalty:** later phase. Points earned after an invoice is fully paid, using a configurable conversion rate. Redemption disabled until rules are approved. |
| D6 | **Billing:** draft invoice created at check-in, finalized at completion. Deposit optional per invoice, default 50%. Multiple payments per invoice. Cash, transfer, and QRIS recorded manually. No payment gateway, refunds, tax, or discounts in MVP. |
| D7 | **Service statuses:** `waiting_queue → being_inspected → in_progress → completed → picked_up`. No customer-approval status. |
| D8 | **Roles:** six roles (`customer`, `admin`, `mechanic`, `inventory_staff`, `cashier`, `owner`); one role per user; owner read-only; staff accounts created by admin/owner. |
| D9 | **Frontend:** Blade + Tailwind CSS + Alpine.js + Vite; Laravel Breeze for authentication. No SPA. |
| D10 | **Live tracking:** polling every 3–5 seconds; no websockets in MVP. |
| D11 | **Locale:** Indonesian UI, IDR currency, `Asia/Jakarta` display timezone; UTC storage. |
| D12 | **Testing:** Pest feature/unit tests against a dedicated MySQL test database; Laravel Pint; Larastan/PHPStan. |
| D13 | **Soft deletes/deactivation** for users, motorcycles, service types, and spareparts. No cascading deletes on financial or service history. |
| D14 | **Reports:** revenue on a cash basis (successful payments), plus invoiced amount; daily/monthly service volume; sparepart usage; low-stock list. |
| D15 | **Figma:** use the existing four screens as the visual baseline; extend the same design language to the remaining screens with approval. Correct motorcycle/car content inconsistencies. |
| D16 | **Queue number:** daily sequence in the `A-024` style shown in Figma, unique per queue date. |

---

## 2. Scope

### In scope

- Customer registration, authentication, profile, and up to five motorcycles.
- Service catalog and sparepart catalog with motorcycle compatibility.
- Online booking with multiple service types, slot capacity, daily queue number, reschedule, and cancellation.
- Staff queue management, check-in, walk-in service orders, and mechanic assignment.
- Service order execution with multiple mechanics, status history, service lines, sparepart usage, and odometer capture.
- Inventory with stock-in, adjustments, atomic deduction, and low-stock monitoring.
- Invoices with price snapshots, optional deposit, multiple payments, and service-history linkage.
- Manual maintenance reminders and in-app notifications.
- Loyalty accounts and earning after full payment (later phase).
- Owner reporting and role-based access control.

### Out of scope

- Payroll/HRIS.
- Out-of-town marketplace/e-commerce shipping.
- IoT/GPS emergency towing.
- Native Android/iOS apps.
- Online sparepart checkout.
- Payment gateway integration, refunds, tax, and discounts (until separately approved).

---

## 3. Roles and permissions

| Capability | Customer | Admin | Mechanic | Inventory | Cashier | Owner |
|---|---|---|---|---|---|---|
| Own profile / motorcycles | CRUD (max 5) | Read/manage | — | — | — | Read |
| Service catalog | Read | Manage | Read | Read | Read | Read |
| Sparepart catalog / stock | Read | Read | Read | Manage | Read | Read |
| Booking | Create/read own/reschedule/cancel | Manage all | Read assigned | — | Read | Read |
| Check-in / service order | Read own | Create/manage | Read assigned/update | — | Read | Read |
| Mechanic assignment | — | Manage | Read own | — | — | Read |
| Status updates | — | Manage | Update assigned | — | — | Read |
| Service/part recording | — | Manage | Record assigned | Stock view | Read | Read |
| Invoices / payments | Read own | Read | — | — | Manage | Read |
| Reminders | Read own | Manage | — | — | — | Read |
| Notifications | Own | Own | Own | Own | Own | Own |
| Reports | — | Operational | — | Stock | Daily transactions | Full |
| Staff accounts | — | Manage | — | — | — | Manage |

Authorization is enforced server-side with Laravel policies and role middleware, not only by hiding navigation.

---

## 4. Architecture

### Stack

- **PHP 8.3/8.4**, **Laravel 13**, **MySQL 8.0.44** (InnoDB, `utf8mb4`).
- **Blade + Tailwind CSS + Alpine.js + Vite**; Laravel Breeze authentication.
- Queue driver: database; scheduler for due reminders and low-stock checks.
- Local development on Windows; production target is a standard VPS with Nginx/Apache, PHP-FPM, MySQL, queue worker, and scheduler.

### Application structure

```
app/
  Actions/            # Booking, ServiceOrder, Billing, Inventory, Loyalty
  Enums/              # BookingStatus, ServiceOrderStatus, PaymentStatus, ...
  Events/ Listeners/  # Status-change notifications
  Http/
    Controllers/      # Customer, Staff, Owner (thin)
    Middleware/       # Role enforcement
    Requests/         # Validation
  Jobs/               # Queued notifications and scheduled checks
  Models/
  Notifications/
  Policies/
  Services/           # Cross-cutting domain services
resources/views/
  layouts/ components/ customer/ staff/ owner/ auth/
routes/
  web.php             # Split by domain/role as routes grow
```

### Key patterns

- **Thin controllers**, **Form Requests** for validation, **Policies** for authorization, **Action classes** for multi-step business workflows.
- **PHP backed enums** for statuses with explicit transition guards.
- **Database transactions + `lockForUpdate`** for:
  - the five-motorcycle limit,
  - slot capacity and queue-number generation,
  - stock deduction,
  - invoice payment totals,
  - loyalty balances.
- **Price snapshots** on booking services, service-order services, service-order parts, and invoice items.
- **Immutable history:** booking status logs, service status logs, inventory movements, loyalty ledger.
- **Events/listeners** for status changes and notifications; queued jobs for delivery.
- **Polling** on the tracking page every 3–5 seconds.

---

## 5. Data model

The approved ERD remains the baseline (20 tables). The following additions/corrections are required for the approved decisions.

### 5.1 New tables

**`workshop_schedules`** — configurable operating pattern.

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT | PK |
| `day_of_week` | TINYINT | 1–7 |
| `open_time` | TIME | |
| `close_time` | TIME | |
| `slot_duration_minutes` | INT UNSIGNED | |
| `capacity_per_slot` | INT UNSIGNED | |
| `is_active` | BOOLEAN | |

**`booking_slots`** — materialized slots for a date.

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT | PK |
| `slot_date` | DATE | |
| `start_time` | TIME | |
| `end_time` | TIME | |
| `capacity` | INT UNSIGNED | |
| `booked_count` | INT UNSIGNED | Default 0 |
| `is_active` | BOOLEAN | |

Unique: (`slot_date`, `start_time`).

**`service_order_services`** — services actually performed.

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT | PK |
| `service_order_id` | BIGINT | FK |
| `service_type_id` | BIGINT | Nullable FK |
| `description` | VARCHAR(255) | Snapshot |
| `quantity` | DECIMAL(12,2) | |
| `unit_price_snapshot` | DECIMAL(12,2) | |
| `duration_snapshot_minutes` | INT UNSIGNED | Nullable |
| `created_at`, `updated_at` | TIMESTAMP | |

### 5.2 Schema changes

- **`bookings`:** add `booking_slot_id` (FK), `queue_date` (DATE), `queue_number` (VARCHAR), `confirmed_at`, `checked_in_at`. Unique (`queue_date`, `queue_number`).
- **`service_order_parts.quantity`:** change to `DECIMAL(12,2)`.
- **`spareparts.stock_quantity`:** change to `DECIMAL(12,2)`.
- **`invoices`:** `issued_at` null = draft; set on finalization. Add `finalized_at` if needed.
- **`loyalty_transactions`:** unique (`invoice_id`, `transaction_type`) for `earn` rows, or an idempotency key, to prevent duplicate awards.
- **Soft deletes:** `deleted_at` on `users`, `motorcycles`, `service_types`, `spareparts`.
- **`service_order_mechanics`:** enforce one active assignment per (`service_order_id`, `mechanic_id`) using a generated active key or an application-level guard inside a transaction.
- **Notifications:** use Laravel's database notifications (`notifiable_type`, `notifiable_id`, `data`, `read_at`) instead of the custom table, or keep the ERD table with a custom channel. Recommendation: Laravel's standard table for less custom code; store title/message/type/related in `data`.

### 5.3 Integrity rules retained

- A motorcycle must belong to the customer on the booking/service order.
- A customer may have at most five active motorcycles.
- One booking may contain multiple distinct services.
- One booking generates at most one service order; walk-ins have a nullable `booking_id`.
- A service order may have multiple mechanics.
- Cancellation is rejected after check-in/service start.
- Status changes update the current row and append history atomically.
- Stock never goes negative without an approved backorder policy.
- Invoice item prices never change when catalog prices change.
- Paid amount never exceeds the invoice total.
- Only successful payments count toward the paid balance.
- Loyalty points are traceable to an invoice or an authorized adjustment.
- Reminders require at least one due condition.
- No cascading deletes on financial/service history.

---

## 6. Workflows

### Booking

1. Customer selects a motorcycle and one or more service types.
2. System snapshots service prices/durations.
3. Customer selects an available slot (capacity checked under lock).
4. Booking is created as `confirmed`; daily queue number is generated.
5. Reschedule updates `scheduled_at` and appends a booking history record.
6. Cancellation is allowed until check-in; it appends history and releases the slot count.

### Check-in and service

1. Staff checks in the booking or creates a walk-in service order.
2. Staff records `odometer_in`; the system creates the service order and draft invoice.
3. Staff assigns one or more mechanics.
4. Mechanics update status; every transition is logged.
5. Services and parts are recorded; stock is deducted atomically with an inventory movement.
6. On completion, `completed_at` is set and the invoice is finalized.
7. On pickup, `picked_up_at` is set.

### Billing

1. Draft invoice contains estimated/actual service and part lines.
2. Deposit, if selected, defaults to 50% and is recorded as a payment.
3. Each payment is a separate row; paid/remaining totals recalculate in the same transaction.
4. Invoice is marked paid when successful payments cover the total.
5. Receipt is printable; invoice appears in the customer's service history.

### Reminders

1. Authorized staff creates a reminder for a motorcycle.
2. At least one of `due_date` or `due_odometer` is required.
3. Scheduler checks due conditions and creates an in-app notification.
4. Customer sees the reminder and can book a service.

### Loyalty (later phase)

1. After full payment, eligible spend is calculated.
2. Configured conversion rate is applied.
3. Ledger row stores the rate snapshot.
4. Account balance updates in the same transaction.
5. Redemption remains disabled.

---

## 7. Screens and Figma

### Designed screens (inspected)

| Screen | Node |
|---|---|
| Home / landing | `2020:406` |
| Login Customer | `2020:577` |
| Login Staff | `2020:654` |
| Customer Dashboard | `2019:2` |

### Design system observed

- **Font:** Inter (Regular, Semi Bold, Bold, Extra Bold, Black).
- **Colors:** lime `#D7FF00`; dark ink `#1D1D1D`; background `#F3F4F6`; surface `#FFFFFF`; border `#E5E7EB`; muted `#6B7280`; soft lime `#EDF8D2`; dark green text `#4E6700`; warm soft `#FFF1D9`; accent `#9FBD00`.
- **Radii:** 16px cards, 14px, 10px, 8px, 4px; pill for badges.
- **Layout:** 248px sidebar, 84px topbar, 34px page padding, 25px card padding.
- **Components:** app shell, sidebar navigation, topbar with notifications/avatar, metric cards, status badge, stepper, data table, reminder card, primary/secondary buttons, auth split layout.
- **Icons:** Material Symbols-style outlined icons.
- No Figma variables/tokens are defined; tokens must be codified from the frames.

### Missing screens (not designed)

Customer: register, forgot/reset password, vehicle list/add/edit, booking wizard, booking detail/reschedule/cancel, tracking detail, sparepart catalog/detail, service history/detail/invoice, reminders, profile, notifications.

Staff: admin queue/dashboard, check-in/create service order, customer/motorcycle management, mechanic dashboard and assigned orders, status update, service/part recording, inventory dashboard and sparepart CRUD, stock movements, cashier invoice/payment/receipt, owner reports, staff account management.

Shared: loading, empty, error, disabled, success states; responsive/mobile variants.

### Content corrections required

- Replace the car hero icon with a motorcycle icon.
- Change “mobil dan motor” copy to motorcycle-only wording.
- Do not ship unsupported marketing claims (`12K+`, `25K+`, `4.9`) as real data; replace with neutral copy or real metrics.
- Align the dashboard reminder card with manual reminders: render a manually created due date, not an automatic prediction. “Estimasi berikutnya” should become “Jatuh tempo pengingat” (or equivalent).
- Use the `A-024` queue format and `WO-YYYY-MMDD-###` work-order format shown in the design.

**Approval needed:** extend the four-screen visual language to the missing screens. Without new Figma frames, each screen will be built from the existing tokens/components and reviewed against the dashboard.

---

## 8. Phased plan

### Phase 0 — Foundation

- Confirm PHP upgrade path (PHP 8.3/8.4 + Laravel 13) or approve Laravel 12 fallback.
- Initialize Git and Laravel; configure MySQL 8 database; install Breeze, Tailwind, Alpine, Pest, Pint, Larastan.
- Create base app shell, role enum, middleware, seeders, test database.
- **Acceptance:** app boots; login/logout works; role middleware blocks unauthorized access; tests run; Pint/Larastan pass.

### Phase 1 — Identity and motorcycles

- Customer registration/login/profile; staff account provisioning.
- Motorcycle CRUD with five-unit limit and ownership policies.
- **Acceptance/tests:** sixth motorcycle rejected; concurrent-safe limit; cross-customer access denied; validation errors shown.

### Phase 2 — Catalog and inventory

- Service types, spareparts, compatibility, stock-in/adjustment, low-stock list.
- **Acceptance/tests:** stock ledger matches balance; negative stock blocked; catalog price edit does not alter snapshots; role restrictions enforced.

### Phase 3 — Booking and queue

- Workshop schedules/slots, capacity, multi-service booking, price snapshots, daily queue number, reschedule, cancellation, status logs, staff queue and check-in.
- **Acceptance/tests:** full slot rejected; duplicate service blocked; queue number unique per day; reschedule/cancel history recorded; cancellation after check-in rejected; ownership enforced.
- **Blocker:** capacity values (days, hours, slot length, capacity per slot) must be supplied.

### Phase 4 — Workshop execution

- Check-in/walk-in, service order, multiple mechanics, status transitions, service/part recording, atomic stock deduction, odometer.
- **Acceptance/tests:** multiple mechanics assigned; transition guards; status log actor/timestamp; stock never negative; part removal creates an adjustment; odometer validation.

### Phase 5 — Billing and payments

- Draft/final invoice lifecycle, item snapshots, optional deposit, multiple payments, paid/remaining recalculation, receipt, history linkage.
- **Acceptance/tests:** partial payments; overpayment blocked; deposit flow; snapshot immutability; authorization.

### Phase 6 — History, reminders, notifications

- Customer service history, manual reminders, in-app notifications, scheduler, low-stock alerts.
- **Acceptance/tests:** reminder requires due condition; due detection; ownership; read/unread state.

### Phase 7 — Loyalty

- Account, earn after full payment, configurable rate, ledger, redemption disabled.
- **Acceptance/tests:** no duplicate earning per invoice; rate snapshot; balance consistency; redemption blocked.

### Phase 8 — Owner reporting and hardening

- Daily/monthly revenue, service volume, sparepart usage, low stock.
- Authorization audit, indexes, query performance, security review.
- **Acceptance/tests:** report totals match source data; unauthorized access denied; key pages free of N+1 queries.

### Phase 9 — UI completion and accessibility

- Build missing screens from the established design language; responsive layouts; loading/empty/error states; accessibility; end-to-end flows.
- **Acceptance:** screens match approved references; keyboard/screen-reader checks; E2E booking → service → payment → history passes.

---

## 9. Testing and quality

- Pest feature tests for every route group and policy.
- Unit tests for pricing, status transitions, capacity, stock, payments, and loyalty.
- MySQL test database for locking/constraint behavior.
- Factories and seeders for demo data.
- Laravel Pint for formatting; Larastan/PHPStan for static analysis.
- Security checklist: CSRF, XSS escaping, mass-assignment protection, password hashing, rate-limited login, ownership policies, no secrets in logs or frontend.

---

## 10. Prerequisites and open items

1. **PHP upgrade** to 8.3/8.4 for Laravel 13 (recommended), or approve Laravel 12 on PHP 8.2.12.
2. **Capacity values:** operating days/hours, slot duration, capacity per slot or day, and whether capacity is per mechanic/bay. Required before Phase 3.
3. **Figma coverage:** only four screens exist. Approve extending the visual language to the missing screens, or provide additional frames.
4. **Content corrections:** motorcycle-only copy/assets; remove fabricated marketing metrics; align reminder copy with manual reminders.
5. **Deferred policies:** refunds, tax, discounts, loyalty redemption, and payment gateway remain disabled until approved.
6. **Deployment target:** production hosting, domain, TLS, backups, and monitoring are out of scope for Phase 0.

---

## 11. Approval checklist

- [ ] PHP 8.3/8.4 + Laravel 13 (or Laravel 12 fallback)
- [ ] Booking capacity values
- [ ] Extend Figma design language to missing screens
- [ ] Content corrections (motorcycle-only, remove fabricated metrics, manual reminder copy)
- [ ] Proceed to scaffold Phase 0
