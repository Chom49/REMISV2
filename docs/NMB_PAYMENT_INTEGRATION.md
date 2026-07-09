# NMB Bank Payment Integration — REMIS

## Overview

REMIS integrates with the **Zalongwa SPG (SARIS Payment Gateway)** to process rent payments through NMB Bank Tanzania. The landlord generates a **NMB control number** for each tenant's outstanding payment. The tenant uses this number to pay via any NMB channel. REMIS polls NMB to detect payment and updates records automatically.

---

## API Documentation Reference

**Source:** `resources/SPG NMB API Manual v2.pdf` — Zalongwa Technologies Limited, 11th May 2026, Version 2.

---

## Architecture

```
Landlord Dashboard (Payments page)
        │
        ▼
PaymentController
  ├─ LeasePaymentSyncService.syncLandlord()   ← auto-creates/updates payment records
  └─ NmbPaymentService                        ← all NMB API calls
          │
          ▼
    Zalongwa SPG  (https://nmb.spg.co.tz)
          │
          ▼
    NMB Bank Tanzania
          │
          ▼ Tenant pays via NMB branch / ATM / Internet Banking / Mobile Banking
          │
    REMIS polls getpayment every 45 s (JS) or on manual ↻ click
          │
          ▼
    Payment record updated → Dashboard shows "Paid"
```

---

## Environment Variables (`.env`)

> **Security rule:** Never put real credentials in this documentation, in source code,
> or in `.env.example`. Real values live **only** in `.env` (git-ignored). The
> `.env.example` file shows the required keys with safe placeholder values.

```env
NMB_SPG_URL=https://nmb.spg.co.tz          # Live SPG base URL (supplied by Zalongwa)
NMB_SPG_USER=<your-spg-username@example.com> # client_usr — obtain from Zalongwa
NMB_SPG_KEY=<your_spg_password>              # client_key — obtain from Zalongwa
NMB_SYSTEM_NAME=REMIS                        # System name registered on SPG
NMB_SYSTEM_CODE=SP1001                       # System code assigned by Zalongwa
NMB_SPG_TIMEOUT=20                           # HTTP timeout in seconds
NMB_CA_BUNDLE=                               # Leave blank to use system CA bundle
```

**How credentials flow (never hardcoded):**

```
.env  (git-ignored, machine-local)
  └─► config/services.php  (env() calls — the only place env() is called)
          └─► config('services.nmb.client_usr')
          └─► config('services.nmb.client_key')
                  └─► NmbPaymentService constructor reads via config()
```

Running `php artisan config:cache` seals this into a compiled cache file so raw
`env()` is never accessible at runtime — even if the `.env` file is accidentally
exposed on disk.

---

## NMB SPG API Endpoints Used

| # | Purpose | Method | Endpoint |
|---|---|---|---|
| 1 | Authenticate | POST | `/api/v1/login` |
| 2 | Generate control number (live) | POST | `/api/v1/generatectlno` |
| 3 | Verify control number | POST | `/api/v1/verification` |
| 4 | Get payment info / poll status | POST | `/api/v1/getpayment` |
| — | (Demo) Generate | POST | `/api/v1/generatedemoctlno` |
| — | (Demo) Verify | POST | `/api/v1/demoverification` |
| — | (Demo) Get payment | POST | `/api/v1/getdemopayment` |

> **Note:** Endpoint 4 (`getpayment`) is used for polling payment status. The `payment`
> endpoint (POST `/api/v1/payment`) in the docs is NMB's internal notification endpoint
> for teller/banking-channel callbacks — REMIS does **not** call it.

---

## Token Authentication Flow

1. REMIS calls `POST /api/v1/login` with `{client_usr, client_key}`.
2. NMB returns `{"token": "..."}`.
3. Token is cached for **14 minutes** (`Cache::put`).
4. All subsequent requests use `Authorization: Bearer <token>`.
5. If any request returns HTTP **401**, the cache is cleared automatically and the token is refreshed (one retry). This handles mid-session token expiry.

---

## Control Number Payload (from API docs)

```json
{
  "systemName":  "REMIS",
  "systemCode":  "SP1001",
  "payerID":     "TENANT-{user.id}",
  "firstName":   "Khadija",
  "lastName":    "Omary",
  "email":       "tenant@example.com",
  "payerMobile": "255754002200",
  "currency":    "TZS",
  "amount":      40000,
  "amountType":  "EXACT",
  "paymentType": "RENT",
  "paymentDesc": "RENT payment – Property Name / Unit"
}
```

**Success response:**
```json
{ "std_reg_number": "TENANT-42", "reference_number": "777138535002", "status": "Success" }
```

---

## Database Schema (payments table additions)

Migration: `2026_06_02_000001_add_nmb_fields_to_payments_table`

| Column | Type | Purpose |
|---|---|---|
| `control_number` | string, nullable | NMB reference number returned by `generatectlno` |
| `control_number_generated_at` | timestamp, nullable | When generated (used for 7-day expiry logic) |
| `control_number_sent_at` | timestamp, nullable | When sent to tenant |
| `control_number_sent_via` | string(10), nullable | `email` or `sms` |
| `nmb_transaction_id` | string, nullable | NMB `transactionRef` after payment |
| `nmb_receipt_number` | string, nullable | NMB `receipt` after payment |
| `nmb_payer_name` | string, nullable | Payer name from NMB transaction |
| `nmb_payer_mobile` | string, nullable | Payer mobile from NMB transaction |
| `nmb_paid_at` | timestamp, nullable | Exact timestamp of NMB payment confirmation |

---

## REMIS Routes

All routes under `auth` + `role:landlord` + `locked` middleware.

| Method | URL | Controller Action | Description |
|---|---|---|---|
| GET | `/landlord/payments` | `index` | Tenant-centric payments page |
| POST | `/landlord/payments/{payment}/generate` | `generateControlNumber` | Call NMB to generate control number |
| POST | `/landlord/payments/{payment}/send` | `sendControlNumber` | Send via email or SMS |
| GET | `/landlord/payments/{payment}/status` | `checkStatus` | AJAX: poll single payment status on NMB |
| GET | `/landlord/payments/poll-all` | `pollAll` | AJAX: batch-check all unpaid payments |
| GET | `/landlord/payments/upcoming-count` | `upcomingCount` | AJAX: badge count for top-bar bell |

---

## Key Service Classes

| File | Responsibility |
|---|---|
| `app/Services/NmbPaymentService.php` | All NMB API calls. Token caching + retry. |
| `app/Services/LeasePaymentSyncService.php` | Auto-generates/updates Payment records from active leases. Determines if control number can be generated or is still active. |
| `app/Http/Controllers/PaymentController.php` | Orchestrates sync → query → precompute → view. |
| `app/Mail/PaymentControlNumber.php` | Mailable for email delivery of control number. |

---

## LeasePaymentSyncService — What It Does

Called on every `/landlord/payments` page load. For each active lease under the landlord:

1. **syncLease**: Ensures exactly one pending/overdue payment exists per lease. Creates one if absent. Updates `status` to `overdue` if `due_date` is past.
2. **canGenerateControlNumber**: Returns `true` when the payment is unpaid, the lease is active, and no **active** control number exists.
3. **activeControlNumber**: A control number is "active" when it exists, the payment is not yet paid, the lease is still active, and the number was generated within the last 7 days (or due date has not passed).

---

## Landlord Workflow — Step by Step

### 1 — Navigate to Payments
Click **Payments** in the left sidebar. The page auto-syncs payment records for all active leases.

### 2 — Read the stat cards
- **Pending** — tenants awaiting payment
- **Overdue** — tenants past due date
- **Due Soon** — payments due within 7 days
- **Total** — all tenants

### 3 — Filter tenants
Use the pill tabs: All | Pending | Overdue | Due Soon | Paid | Previous (inactive tenants).

### 4 — Generate a Control Number
- Click the green **Generate** button in the Actions column.
- The button is **active** only when:
  - There is an outstanding (unpaid) payment
  - The lease is active and not expired
  - No valid control number already exists
- REMIS calls NMB, stores the returned `reference_number`, and displays it in the Control Number column.

### 5 — Send the Control Number to the Tenant
- Click the blue **Send** button (active only after a control number is generated).
- A modal appears: choose **Email** or **SMS**.
- Both channels send identical content: control number, amount, due date, and payment instructions.
- **Email**: branded HTML via REMIS Gmail SMTP.
- **SMS**: via BRIQ Tanzania (requires `BRIQ_API_KEY`).

### 6 — Tenant Pays via NMB
The tenant uses the control number to pay via:
- NMB branch (teller)
- NMB ATM
- NMB Internet Banking (`ibank.nmbtz.com`)
- NMB Mobile Banking (NMB Mkononi app)

### 7 — Automatic Payment Detection (Real-Time)
- The payments page polls NMB's `getpayment` endpoint **every 45 seconds** for all rows with unpaid control numbers.
- When NMB confirms payment (`"paid": true`), the table row flashes green instantly and the status updates to **Paid**.
- The **↻** icon on each row triggers an immediate manual check for that specific payment.
- On confirmation: `status`, `paid_date`, `nmb_receipt_number`, `nmb_transaction_id`, `nmb_payer_name` are all written to the database.

---

## Dashboard Notifications

- **Bell icon** (top bar): shows an amber badge with the count of payments due within 7 days. Clicking goes directly to the "Due Soon" filter.
- **Dashboard notification card**: amber alert listing all tenants with payments due within 7 days, showing property, unit, amount, due date, and whether a control number has been generated.

---

## SMS Message Format

```
Hi {FirstName}, your rent payment of TZS {Amount} for {PropertyName} / {Unit}
is due on {DueDate}. Pay via NMB using control number: {ControlNumber}.
Visit any NMB branch, ATM, or use NMB internet/mobile banking.
```

---

## Error Handling

| Scenario | Behavior |
|---|---|
| NMB unreachable | User-friendly error; details logged to `storage/logs/laravel.log` |
| Invalid credentials | Auth error shown; token cache cleared |
| HTTP 401 on API call | Cache cleared, token refreshed, request retried once automatically |
| Non-JSON response | Logged + user-facing error shown |
| Control number already active | Generate button disabled with tooltip "Control number already exists" |
| Lease expired | Generate button disabled with tooltip "Lease expired or payment already settled" |
| Tenant has no phone | SMS button disabled in modal; warning shown |
| Payment already paid | Actions column shows "Collected"; no buttons rendered |

---

## Going Live — Checklist

- [ ] Set `NMB_SPG_URL` in `.env` to the live gateway URL (confirm with Zalongwa)
- [ ] Set `NMB_SPG_USER` in `.env` to your SPG credential email (from Zalongwa)
- [ ] Set `NMB_SPG_KEY` in `.env` to your SPG credential password (from Zalongwa)
- [ ] Confirm `NMB_SYSTEM_NAME` and `NMB_SYSTEM_CODE` match what Zalongwa registered
- [ ] Run `php artisan config:cache` after updating `.env` to seal values into cache
- [ ] Test control number generation using the demo endpoints first:
      change `$demo = true` in `NmbPaymentService::generateControlNumber()` temporarily
- [ ] Set `BRIQ_API_KEY` in `.env` for SMS delivery (from karibu.briq.tz)
- [ ] Confirm REMIS Gmail SMTP is active — test with `php artisan tinker`:
      `Mail::raw('test', fn($m) => $m->to('you@example.com')->subject('REMIS test'))`
- [ ] Verify NMB SPG account is activated and `system_code` is whitelisted by Zalongwa
- [ ] Verify CA bundle is up to date:
      `php -r "echo date('Y-m-d', filemtime(ini_get('curl.cainfo')));"`
      Re-download from `https://curl.se/ca/cacert.pem` if more than 12 months old

---

## Logs

All NMB API interactions are written to `storage/logs/laravel.log`:

| Log Level | Event |
|---|---|
| `info` | Token refreshed, control number generated, payment confirmed |
| `warning` | Control number generation failed, 401 retry triggered |
| `error` | Connection failure, HTTP error, unexpected response format |
