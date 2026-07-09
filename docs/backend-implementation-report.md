# REMIS V2 — Backend Implementation Report

**Project:** Rental Management Information System (REMIS)  
**Framework:** Laravel 12 (PHP 8.2+)  
**Database:** MySQL 8 via XAMPP  
**Report Date:** June 2026  
**Author:** Development Team

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Architecture Overview](#3-architecture-overview)
4. [Directory Structure](#4-directory-structure)
5. [Database Design](#5-database-design)
6. [Models and Relationships](#6-models-and-relationships)
7. [Migrations — Database Evolution](#7-migrations--database-evolution)
8. [Authentication and Authorization](#8-authentication-and-authorization)
9. [Middleware](#9-middleware)
10. [Routing](#10-routing)
11. [Controllers](#11-controllers)
12. [Service Layer](#12-service-layer)
13. [Mail System](#13-mail-system)
14. [External API Integrations](#14-external-api-integrations)
15. [PDF Report Generation](#15-pdf-report-generation)
16. [Artisan Console Commands](#16-artisan-console-commands)
17. [Configuration Files](#17-configuration-files)
18. [Testing](#18-testing)
19. [Security Implementation](#19-security-implementation)
20. [Performance Considerations](#20-performance-considerations)
21. [Deployment Notes](#21-deployment-notes)
22. [Component Interaction Summary](#22-component-interaction-summary)

---

## 1. Project Overview

REMIS V2 is a full-stack web application built for the Tanzanian rental property market. It provides landlords with a complete platform to manage properties, tenants, leases, and rent payments — with deep integration into Tanzanian financial and regulatory infrastructure.

### Core Capabilities

| Domain | Capability |
|---|---|
| Property Management | Single-unit and multi-unit (multi-floor) property registration |
| Tenant Management | Tenant onboarding, identity verification (TIN/NIDA), invitations |
| Lease Lifecycle | Create, assign tenant, terminate, renew, download as PDF |
| Rent Payments | NMB Bank SPG integration — generate control numbers, send via email/SMS, poll status |
| Notifications | SMTP email (Gmail-ready) + BRIQ bulk SMS (Tanzania) |
| TIN Verification | Live lookup against Tanzania Revenue Authority (TRA) API |
| Reports | PDF exports: rent payments, tenants, overdue, properties |
| Admin Panel | System settings, audit logs, backups, user management |
| Roles | Landlord, Tenant, Admin — each with isolated dashboard and permissions |

---

## 2. Technology Stack

### Backend Dependencies (`composer.json`)

```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/tinker": "^2.9",
    "barryvdh/laravel-dompdf": "^3.1"
},
"require-dev": {
    "fakerphp/faker": "^1.23",
    "laravel/pint": "^1.13",
    "mockery/mockery": "^1.6",
    "phpunit/phpunit": "^11.0"
}
```

| Component | Technology | Purpose |
|---|---|---|
| Framework | Laravel 12 | MVC framework, routing, ORM, auth, mail |
| Language | PHP 8.2+ | Nullable types, named args, enums, fibers |
| Database | MySQL 8 (XAMPP) | Primary data store |
| ORM | Eloquent | Model-to-table mapping, relationships, query builder |
| Authentication | Laravel Auth (session-based) | Login, remember-me, session lock |
| PDF | barryvdh/laravel-dompdf 3.1 | Lease and report PDF generation |
| HTTP Client | Laravel HTTP (Guzzle wrapper) | NMB, BRIQ, TRA API calls |
| Frontend Build | Vite + Tailwind CSS | Asset bundling (separate from backend) |
| Testing | PHPUnit 11 | Unit and feature tests |
| Linting | Laravel Pint | PSR-12 code style |

---

## 3. Architecture Overview

REMIS follows the standard **Laravel MVC architecture** with an additional **Service Layer** for business logic that spans multiple models. The request lifecycle is:

```
HTTP Request
     │
     ▼
┌─────────────────────────────────────────────────────────┐
│  MIDDLEWARE PIPELINE                                      │
│  web (sessions, CSRF) → auth → role:X → locked →        │
│  force.password.change                                    │
└─────────────────────────┬───────────────────────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │  CONTROLLER │  (AuthController, LandlordController,
                    └──────┬──────┘   TenantController, AdminController,
                           │          PaymentController, ...)
                           │
              ┌────────────┼─────────────────┐
              │            │                 │
              ▼            ▼                 ▼
         ┌─────────┐  ┌─────────┐     ┌──────────────┐
         │  MODEL  │  │ SERVICE │     │ EXTERNAL API │
         │(Eloquent│  │  LAYER  │     │ NMB/BRIQ/TRA │
         │   ORM)  │  │         │     └──────────────┘
         └────┬────┘  └────┬────┘
              │            │
              ▼            ▼
         ┌─────────────────────┐
         │      DATABASE       │
         │   (MySQL via PDO)   │
         └─────────────────────┘
              │
              ▼
         ┌─────────┐
         │  VIEW   │  (Blade templates → HTML response)
         └─────────┘
```

### Design Patterns Used

- **Repository-like pattern** — Controllers use Eloquent models directly, but business logic is extracted into Services.
- **Service Objects** — `LeasePaymentSyncService`, `NmbPaymentService`, `BriqSmsService` encapsulate domain logic.
- **Mailable classes** — `TenantInvitation`, `PaymentControlNumber`, `LeaseTerminationNotice`, `TenantWarningNotice`.
- **Middleware pipeline** — Route protection, role enforcement, session lock, and force-password-change flow.
- **Named routes** — All routes use Laravel's named route system (`landlord.payments.index`, etc.) for clean URL generation.

---

## 4. Directory Structure

```
RemisV2/
├── app/
│   ├── Console/Commands/          # Artisan CLI commands
│   │   ├── GenerateLeasePdf.php
│   │   └── GenerateReportPdf.php
│   ├── Http/
│   │   ├── Controllers/           # Request handlers (one per domain)
│   │   │   ├── AuthController.php
│   │   │   ├── LandlordController.php
│   │   │   ├── TenantController.php
│   │   │   ├── AdminController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── TinVerificationController.php
│   │   │   └── PasswordSetupController.php
│   │   └── Middleware/            # HTTP middleware
│   │       ├── EnsureRole.php
│   │       ├── ForcePasswordChange.php
│   │       └── CheckSessionLock.php
│   ├── Mail/                      # Mailable classes
│   │   ├── TenantInvitation.php
│   │   ├── PaymentControlNumber.php
│   │   ├── LeaseTerminationNotice.php
│   │   └── TenantWarningNotice.php
│   ├── Models/                    # Eloquent ORM models
│   │   ├── User.php
│   │   ├── Property.php
│   │   ├── Unit.php
│   │   ├── Lease.php
│   │   ├── Payment.php
│   │   ├── MaintenanceRequest.php
│   │   ├── AuditLog.php
│   │   ├── SystemSetting.php
│   │   └── Backup.php
│   ├── Providers/                 # Laravel service providers
│   └── Services/                  # Domain service objects
│       ├── LeasePaymentSyncService.php
│       ├── NmbPaymentService.php
│       └── BriqSmsService.php
├── config/
│   ├── auth.php                   # Authentication guards and providers
│   ├── database.php               # DB connection config
│   ├── mail.php                   # Mail transport config
│   └── services.php               # Third-party service credentials
├── database/
│   ├── migrations/                # Database schema versions (28 files)
│   └── seeders/                   # Data seeders
├── routes/
│   └── web.php                    # All application routes (no api.php)
├── resources/
│   ├── views/                     # Blade templates (frontend)
│   ├── css/app.css                # Tailwind CSS
│   └── js/app.js                  # Alpine.js / JS
├── tests/
│   ├── Unit/LeasePaymentSyncServiceTest.php
│   └── Feature/BriqSmsServiceTest.php
├── composer.json
└── .env.example
```

---

## 5. Database Design

### Entity-Relationship Overview

```
┌──────────────┐         ┌──────────────┐         ┌───────────────┐
│     users    │◄────────│  properties  │────────►│     units     │
│  (landlord,  │ 1     * │  (landlord's │ 1     * │ (unit_number, │
│  tenant,     │         │  portfolio)  │         │  floor_number)│
│  admin)      │         └──────┬───────┘         └──────┬────────┘
└──────┬───────┘                │                        │
       │                        │                        │
       │ tenant          1      ▼                        │
       │    *──────────►┌───────────────┐◄───────────────┘
       │                │    leases     │  (unit_id nullable
       │                │  (lifecycle)  │   for legacy data)
       │                └──────┬────────┘
       │                       │
       │                       │ 1
       │                       ▼  *
       │                ┌───────────────┐
       └───────────────►│   payments    │
                        │ (NMB fields)  │
                        └───────────────┘

┌──────────────┐     ┌──────────────────┐
│  properties  │────►│maintenance_reqs  │
└──────────────┘     └──────────────────┘

┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  audit_logs  │     │system_settings│    │   backups    │
└──────────────┘     └──────────────┘     └──────────────┘
```

### Tables Summary

| Table | Rows (typical) | Purpose |
|---|---|---|
| `users` | hundreds | Landlords, tenants, admins (unified table, role column) |
| `properties` | tens per landlord | Physical property records |
| `units` | hundreds per landlord | Individual rentable units within properties |
| `leases` | hundreds | Tenant-unit contracts (lifecycle tracked) |
| `payments` | thousands | Rent payment records with NMB integration |
| `maintenance_requests` | hundreds | Tenant-submitted maintenance tickets |
| `audit_logs` | tens of thousands | Admin action trail |
| `system_settings` | ~8 | Application-wide configuration |
| `backups` | small | Backup metadata records |
| `sessions` | per-user | Laravel session storage |
| `password_reset_tokens` | transient | Password setup/reset tokens |

---

## 6. Models and Relationships

### 6.1 User Model (`app/Models/User.php`)

The `User` model serves all three roles in one table, differentiated by the `role` column. This design avoids three separate user tables while still supporting role-specific fields.

```php
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'phone', 'role', 'password', 'landlord_id',
        'tenant_status', 'tin', 'nida_number', 'gender', 'nationality',
        'force_password_change', 'default_password_hint', 'invitation_status',
        'profile_picture', 'preferences'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'preferences'       => 'array',   // JSON column deserialized automatically
        ];
    }

    public function isLandlord(): bool { return $this->role === 'landlord'; }
    public function isTenant(): bool   { return $this->role === 'tenant'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }

    // Self-referential: landlord creates tenants (landlord_id FK on tenants)
    public function createdByLandlord()   { return $this->belongsTo(User::class, 'landlord_id'); }
    public function createdTenants()      { return $this->hasMany(User::class, 'landlord_id'); }

    // Cross-role relationships
    public function properties()          { return $this->hasMany(Property::class, 'landlord_id'); }
    public function leasesAsLandlord()    { return $this->hasMany(Lease::class, 'landlord_id'); }
    public function leasesAsTenant()      { return $this->hasMany(Lease::class, 'tenant_id'); }
    public function payments()            { return $this->hasMany(Payment::class, 'tenant_id'); }
    public function maintenanceRequests() { return $this->hasMany(MaintenanceRequest::class, 'tenant_id'); }
}
```

**Key design decisions:**
- `landlord_id` on a user row identifies a tenant's owning landlord (self-referential FK).
- `preferences` is a JSON column cast to PHP array, enabling extensible per-user settings without new migrations.
- `force_password_change` = `true` forces tenants to set a new password before they can use the app.
- Tanzania-specific fields (`tin`, `nida_number`, `nationality`) are stored directly on the user.

---

### 6.2 Property Model (`app/Models/Property.php`)

```php
class Property extends Model
{
    protected $fillable = [
        'landlord_id', 'name', 'address', 'city', 'county', 'total_area',
        'type', 'bedrooms', 'bathrooms', 'rent_amount', 'description', 'status',
        'property_category', 'number_of_units', 'floor_layout', 'image',
    ];

    public function units()       { return $this->hasMany(Unit::class); }
    public function leases()      { return $this->hasMany(Lease::class); }
    public function activeLease() { return $this->hasOne(Lease::class)->where('status', 'active'); }

    public function isMultiUnit(): bool { return $this->property_category === 'multi'; }

    public function occupiedUnitsCount(): int
    {
        return $this->units()->where('status', 'occupied')->count();
    }
}
```

**`property_category`** values:
- `single` — one rentable unit (legacy/simple properties).
- `multi` — multiple units (apartments, commercial blocks, multi-floor buildings).

**`floor_layout`** values:
- `single_floor` — all units on one floor.
- `multi_floor` — units spread across named floors (e.g., Ground, Floor 1, Floor 2).

---

### 6.3 Unit Model (`app/Models/Unit.php`)

Units represent individual rentable spaces within a property. They were introduced in the April 2026 refactor to support multi-unit buildings.

**Fields:** `property_id`, `unit_number` (e.g. "A1", "Unit 3"), `floor_number` (nullable string), `status` (`vacant`|`occupied`|`maintenance`), `notes`.

Each unit has exactly one active lease at a time via:
```php
public function activeLease()
{
    return $this->hasOne(Lease::class)->where('status', 'active')->latestOfMany();
}
```

---

### 6.4 Lease Model (`app/Models/Lease.php`)

The lease is the central business entity binding a tenant, a unit, and a landlord together.

```php
class Lease extends Model
{
    protected $fillable = [
        'property_id', 'unit_id', 'tenant_id', 'landlord_id',
        'start_date', 'end_date', 'monthly_rent', 'security_deposit',
        'payment_day', 'payment_frequency', 'lease_expiry_reminder_days',
        'lease_terms', 'status',
        'termination_reason', 'termination_notes', 'terminated_at',
        'renewal_of_id',          // Self-FK: points to the lease this renews
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function renewedFrom() { return $this->belongsTo(Lease::class, 'renewal_of_id'); }
    public function renewals()    { return $this->hasMany(Lease::class, 'renewal_of_id'); }

    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->end_date, false);
    }
}
```

**Lease status values:** `active`, `renewed`, `terminated`, `expired`.

**`payment_day`** — if set, the sync service generates payments due on this day of month (e.g., day 5 = 5th of every month). If null, defaults to the start_date's day.

**`renewal_of_id`** enables a linked-list of lease renewals, allowing the full history of a tenancy to be reconstructed.

---

### 6.5 Payment Model (`app/Models/Payment.php`)

```php
class Payment extends Model
{
    protected $fillable = [
        'lease_id', 'tenant_id', 'amount', 'due_date', 'paid_date',
        'status', 'reference', 'notes',
        // NMB payment gateway fields:
        'control_number', 'control_number_generated_at',
        'control_number_sent_at', 'control_number_sent_via',
        'nmb_transaction_id', 'nmb_receipt_number',
        'nmb_payer_name', 'nmb_payer_mobile', 'nmb_paid_at',
    ];
}
```

**Status lifecycle:**
```
pending ──► overdue     (if due_date passes without payment)
pending ──► paid        (confirmed by NMB)
overdue ──► paid        (confirmed by NMB)
```

NMB fields are populated when the tenant pays through NMB Bank — the `checkStatus` / `pollAll` endpoints query the NMB SPG API and write transaction confirmation back to this record.

---

### 6.6 AuditLog Model (`app/Models/AuditLog.php`)

All admin actions are recorded using a static helper:

```php
class AuditLog extends Model
{
    public static function log(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null
    ): void {
        static::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
```

Called throughout `AdminController` whenever a setting is changed, a user's role is modified, or a backup is deleted. Indexed on `(action, created_at)` and `(model_type, model_id)` for fast filtering.

---

### 6.7 SystemSetting Model

A simple key-value store for admin-configurable application settings. Seeded on migration with defaults:

```php
public static function get(string $key, mixed $default = null): mixed
{
    return static::where('key', $key)->value('value') ?? $default;
}

public static function set(string $key, mixed $value): void
{
    static::updateOrCreate(['key' => $key], ['value' => $value]);
}
```

Pre-seeded keys: `app_name`, `app_description`, `maintenance_mode`, `max_login_attempts`, `session_timeout_minutes`, `allow_tenant_registration`, `support_email`, `currency_symbol`.

---

## 7. Migrations — Database Evolution

Migrations are named with timestamps that reflect the development phases.

### Phase 1: Foundation (January 2024)

| Migration File | What It Creates |
|---|---|
| `0001_01_01_000000_create_users_table` | `users`, `password_reset_tokens`, `sessions`, `cache` tables |
| `2024_01_01_000010_create_properties_table` | Properties with landlord FK |
| `2024_01_01_000011_add_county_area_to_properties_table` | Adds `county`, `total_area` columns |
| `2024_01_01_000020_create_leases_table` | Leases with property, tenant, landlord FKs |
| `2024_01_01_000021_add_payment_fields_to_leases_table` | Adds `payment_day`, `payment_frequency`, `lease_expiry_reminder_days` |
| `2024_01_01_000030_create_payments_table` | Payments with lease and tenant FKs |
| `2024_01_01_000040_create_maintenance_requests_table` | Maintenance requests |
| `2024_01_01_000041_update_maintenance_requests_table` | Adds `viewable_by` column |

### Phase 2: Multi-Unit Support (April–May 2026)

| Migration File | Change |
|---|---|
| `2026_04_25_..._make_tenant_id_nullable_on_leases_table` | Allows leases to exist before a tenant is assigned |
| `2026_04_27_..._add_landlord_id_to_users_table` | Self-referential FK (landlord creates tenants) |
| `2026_05_07_000001_add_property_category_to_properties_table` | `property_category`, `number_of_units`, `image` |
| `2026_05_07_000002_create_units_table` | New `units` table with `unit_number`, `floor_number`, `status` |
| `2026_05_07_000003_add_unit_id_and_terms_to_leases_table` | `unit_id` FK on leases, `lease_terms` text field |
| `2026_05_07_100001_add_tenant_status_to_users_table` | `tenant_status` (active/inactive) |
| `2026_05_07_100002_add_lifecycle_fields_to_leases_table` | `termination_reason`, `termination_notes`, `terminated_at`, `renewal_of_id` |
| `2026_05_07_200001_add_identity_fields_to_users_table` | `tin`, `nida_number`, `gender`, `nationality` |

### Phase 3: Security & Admin (May 2026)

| Migration File | Change |
|---|---|
| `2026_05_11_000001_add_force_password_change_to_users_table` | `force_password_change` bool, `default_password_hint` |
| `2026_05_11_000002_add_invitation_status_to_users_table` | `invitation_status` for tracking tenant onboarding |
| `2026_05_12_083721_add_performance_indexes_to_payments_leases` | Composite indexes on `payments` and `leases` for query optimization |
| `2026_05_12_125641_add_floor_fields_to_properties_and_units` | `floor_layout` on properties, `floor_number` on units |
| `2026_05_29_000001_add_admin_role_to_users_table` | Extends `role` enum to include `'admin'` |
| `2026_05_29_000002_create_audit_logs_table` | `audit_logs` with indexed action and model fields |
| `2026_05_29_000003_create_system_settings_table` | `system_settings` key-value table (pre-seeded) |
| `2026_05_29_000004_create_backups_table` | `backups` metadata table |

### Phase 4: User Profiles & Payments (May–June 2026)

| Migration File | Change |
|---|---|
| `2026_05_31_000001_add_profile_settings_to_users` | `profile_picture`, `preferences` (JSON) |
| `2026_06_02_000001_add_nmb_fields_to_payments_table` | All NMB control number and transaction fields |

---

## 8. Authentication and Authorization

### 8.1 Session-Based Authentication

Laravel's built-in session guard handles authentication. The `AuthController` manages the full auth flow:

**Login (`POST /login`):**
```php
public function login(Request $request)
{
    // 1. Validate email/password presence
    // 2. Attempt authentication with remember-me always on
    if (Auth::attempt($request->only('email', 'password'), true)) {
        $request->session()->regenerate();   // Prevent session fixation
        return $this->redirectByRole();       // Route to correct dashboard
    }
    // 3. Return error without revealing which field is wrong
    return back()->withErrors(['email' => 'These credentials do not match our records.']);
}
```

**Registration (`POST /register`):**

Strict validation enforces password strength at sign-up time:
```php
'password' => [
    'required', 'confirmed',
    Password::min(8)->mixedCase()->numbers()->symbols(),
],
```

Custom validation messages are defined for every rule to provide user-friendly feedback.

**Role-based redirect after login:**
```php
public function redirectByRole()
{
    $user = Auth::user();
    if ($user->isAdmin())    return redirect()->route('admin.dashboard');
    if ($user->isLandlord()) return redirect()->route('landlord.dashboard');
    return redirect()->route('tenant.dashboard');
}
```

### 8.2 Session Lock Screen

When a user has a persistent session (remember-me token) and reopens their browser, they are redirected to a lock screen (`/lock`) before accessing the dashboard. This provides a second layer of security beyond the initial login.

```php
public function unlock(Request $request)
{
    $throttleKey = 'session-unlock:' . Auth::id();

    if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
        $seconds = RateLimiter::availableIn($throttleKey);
        return back()->withErrors(['password' => "Too many failed attempts. Try again in {$seconds} seconds."]);
    }

    if (!Hash::check($request->password, Auth::user()->password)) {
        RateLimiter::hit($throttleKey, 60);  // 60-second decay window
        $remaining = 5 - RateLimiter::attempts($throttleKey);
        return back()->withErrors(['password' => "Incorrect password. {$remaining} attempts remaining."]);
    }

    RateLimiter::clear($throttleKey);
    session()->forget('auth.locked');
    $request->session()->regenerate();
    return $this->redirectByRole();
}
```

### 8.3 Role-Based Authorization

Authorization is enforced at the **route level** by the `EnsureRole` middleware:

```php
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (! Auth::check() || Auth::user()->role !== $role) {
            abort(403, 'Unauthorized.');
        }
        return $next($request);
    }
}
```

This middleware is registered in `bootstrap/app.php` as `role` and applied to route groups:

```php
Route::middleware(['auth', 'role:landlord', 'locked'])->prefix('landlord')->group(...)
Route::middleware(['auth', 'role:tenant', 'force.password.change', 'locked'])->prefix('tenant')->group(...)
Route::middleware(['auth', 'role:admin', 'locked'])->prefix('admin')->group(...)
```

### 8.4 Force Password Change (Tenants)

When a landlord creates a tenant account and sends an invitation, the tenant's `force_password_change` flag is set to `true`. The `ForcePasswordChange` middleware intercepts every tenant request and redirects to the password change page until the flag is cleared:

```php
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::user();

        if ($user && $user->force_password_change) {
            if (! $request->routeIs('tenant.password.change', 'tenant.password.update', 'logout')) {
                return redirect()->route('tenant.password.change')
                    ->with('warning', 'You must set a new password before continuing.');
            }
        }

        return $next($request);
    }
}
```

### 8.5 Payment Authorization

Within `PaymentController`, each action verifies the requesting landlord owns the property tied to the payment:

```php
private function authorizePayment(Payment $payment): void
{
    $landlordId  = Auth::id();
    $propertyIds = Property::where('landlord_id', $landlordId)->pluck('id');

    $allowed = $payment->lease
        ? $propertyIds->contains($payment->lease->property_id)
        : false;

    abort_if(! $allowed, 403);
}
```

---

## 9. Middleware

### Registered Middleware Aliases

| Alias | Class | Description |
|---|---|---|
| `role` | `EnsureRole` | Checks `Auth::user()->role` against the required role |
| `locked` | `CheckSessionLock` | Redirects to lock screen for remembered/locked sessions |
| `force.password.change` | `ForcePasswordChange` | Enforces first-login password change for tenants |

All three are registered in `bootstrap/app.php` via:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'                  => EnsureRole::class,
        'locked'                => CheckSessionLock::class,
        'force.password.change' => ForcePasswordChange::class,
    ]);
})
```

### Middleware Pipeline Per Route Group

```
Landlord routes:  auth → role:landlord → locked
Tenant routes:    auth → role:tenant → force.password.change → locked
Admin routes:     auth → role:admin → locked
Lock routes:      auth  (only — prevents redirect loop)
Public routes:    (none — guest accessible)
```

---

## 10. Routing

All routes are defined in `routes/web.php`. There is no `api.php` — AJAX endpoints (TIN verification, payment status checks) return JSON from within the `web.php` file, protected by the same session-based auth middleware.

### Route Groups

**Public (no auth required):**
```
GET  /                          → Landing page with login/register modals
GET  /login                     → Redirect to / with login modal
GET  /register                  → Redirect to / with signup modal
POST /login                     → AuthController::login
POST /register                  → AuthController::register
GET  /create-password/{token}   → PasswordSetupController::show
POST /create-password           → PasswordSetupController::store
```

**Auth only (lock screen — no role check):**
```
GET  /lock     → AuthController::showLock
POST /lock     → AuthController::unlock
POST /logout   → AuthController::logout
```

**Landlord routes (`/landlord/*`):**
```
GET  /dashboard
GET  /properties                   # List all properties
GET  /properties/create            # Create form
POST /properties                   # Store new property
GET  /properties/{property}        # Property detail

# Unit-based lease workflow (new):
GET  /properties/{property}/units/{unit}/leases/create
POST /properties/{property}/units/{unit}/leases
POST /properties/{property}/units/{unit}/leases/{lease}/assign-tenant

# Lease management:
GET  /leases                        # List all leases
GET  /leases/{lease}                # Lease detail
GET  /leases/{lease}/download       # Download PDF
POST /leases/{lease}/send-notice    # Email/SMS notice
POST /leases/{lease}/terminate
POST /leases/{lease}/renew

# Tenants CRUD + invite:
GET  /tenants
GET  /tenants/create
POST /tenants
POST /tenants/verify-tin            # AJAX: TRA TIN verification
GET  /tenants/{user}
GET  /tenants/{user}/edit
PATCH /tenants/{user}
POST /tenants/{user}/invite

# Payments (NMB):
GET  /payments
POST /payments/{payment}/generate   # Generate NMB control number
POST /payments/{payment}/send       # Send via email or SMS
GET  /payments/{payment}/status     # AJAX: poll NMB status
GET  /payments/poll-all             # AJAX: batch poll all pending
GET  /payments/upcoming-count       # AJAX: badge count

# Reports (PDF):
GET  /reports/pdf/rent-payments
GET  /reports/pdf/tenants
GET  /reports/pdf/overdue
GET  /reports/pdf/properties

# Maintenance, Settings (CRUD)
```

**Admin routes (`/admin/*`):**
```
GET  /dashboard
GET  /settings, POST /settings
GET  /audit-logs, DELETE /audit-logs
GET  /backups, POST /backups, DELETE /backups/{backup}
GET  /backups/{backup}/download
GET  /users
PATCH /users/{user}/role
DELETE /users/{user}
```

**Tenant routes (`/tenant/*`):**
```
GET  /dashboard
GET  /change-password, POST /change-password  # Mandatory on first login
GET  /payments/history
GET  /payments/{payment}
POST /payments/{payment}/pay
GET  /maintenance, POST /maintenance
GET  /settings, POST /settings/profile, etc.
```

---

## 11. Controllers

### 11.1 AuthController (`app/Http/Controllers/AuthController.php`)

Handles all authentication flows: login, registration, session lock/unlock, and logout. Described fully in [Section 8](#8-authentication-and-authorization).

---

### 11.2 LandlordController (`app/Http/Controllers/LandlordController.php`)

The largest controller (~600+ lines), responsible for the landlord's entire workflow.

**Dashboard method — key metrics computed:**
- Total properties, active leases, monthly revenue (sum of active monthly rents)
- Upcoming rent total (due within 7 days)
- Overdue amount
- Rent collection rate (paid / total expected × 100)
- Active maintenance requests count
- 6-month income chart data (sum of payments by month)

**Property creation — three workflows:**

```
property_category = 'single'
   └── Creates 1 Unit automatically (unit_number = "Unit 1")

property_category = 'multi' + floor_layout = 'single_floor'
   └── Creates N Units: "Unit 1", "Unit 2", ..., "Unit N"

property_category = 'multi' + floor_layout = 'multi_floor'
   └── Reads floor_config JSON from request:
       [{ floor: "Ground", count: 4 }, { floor: "Floor 1", count: 6 }, ...]
       Generates: A1, A2, A3, A4 (Ground), B1-B6 (Floor 1), ...
```

**Tenant invitation flow:**

When `tenantInvite()` is called, the controller:
1. Generates a password reset token (using Laravel's `PasswordBroker`).
2. Builds a signed URL pointing to `/create-password/{token}?email=...`.
3. Sends `TenantInvitation` mailable with the URL and a temporary password hint.
4. Sets `invitation_status = 'invited'` on the tenant record.

**Lease notice flow (`leasesSendNotice`):**

```php
// Sends either a termination notice or a warning notice
// based on the `notice_type` request field:
if ($request->notice_type === 'termination') {
    Mail::to($tenant->email)->send(new LeaseTerminationNotice($lease, $loginUrl));
    // Optionally also mark lease as terminated
} else {
    Mail::to($tenant->email)->send(new TenantWarningNotice($lease, $reason, $comments));
}
```

**Report PDF generation:**

Each report method queries the relevant data, renders a dedicated Blade view, and returns a DomPDF response:

```php
public function reportRentPaymentsPdf()
{
    $payments = Payment::whereHas('lease', fn($q) => $q->where('landlord_id', Auth::id()))
        ->with(['tenant', 'lease.property'])
        ->orderBy('due_date', 'desc')
        ->get();

    $pdf = Pdf::loadView('landlord.reports.rent-payments-pdf', compact('payments'));
    return $pdf->stream('rent-payments-' . now()->format('Y-m-d') . '.pdf');
}
```

---

### 11.3 TenantController (`app/Http/Controllers/TenantController.php`)

The tenant's portal controller. All methods are scoped to `Auth::user()` — tenants can only see their own data.

**Dashboard:** Loads active lease, calculates upcoming/overdue/paid payment counts, shows recent maintenance requests.

**Force password change:** On first login, the `force_password_change` flag routes the tenant here before anything else. After updating the password:
```php
Auth::user()->update([
    'password'              => Hash::make($validated['password']),
    'force_password_change' => false,
]);
```

---

### 11.4 AdminController (`app/Http/Controllers/AdminController.php`)

**Dashboard:** Counts total users, landlords, tenants, and shows recent audit log entries.

**Settings management:**
```php
public function settingsUpdate(Request $request)
{
    $booleanKeys = ['maintenance_mode', 'allow_tenant_registration'];

    foreach ($request->except(['_token', '_method']) as $key => $value) {
        // Handle unchecked booleans (not sent by browser)
        $val = in_array($key, $booleanKeys)
            ? ($request->boolean($key) ? '1' : '0')
            : $value;

        SystemSetting::set($key, $val);
    }

    AuditLog::log('settings_updated', 'Admin updated system settings');
    return back()->with('success', 'Settings saved.');
}
```

**Audit logs:** Paginated with filters by action, user, and date range. Old logs (>90 days) can be bulk-cleared.

---

### 11.5 PaymentController (`app/Http/Controllers/PaymentController.php`)

This controller implements the complete NMB payment workflow. Its `index()` method is the most complex in the application:

**Payment index overview:**
1. Sync all lease payments for the landlord (via `LeasePaymentSyncService`).
2. Build a base query of all tenants tied to this landlord's properties.
3. Compute stats: total, pending, overdue, upcoming (next 7 days).
4. Apply the selected filter (all / pending / overdue / upcoming / paid / previous).
5. Paginate tenants (25 per page).
6. For each tenant, load their most-relevant payment (overdue > pending > paid).
7. Pre-compute `can_generate` and `active_control` flags for the UI.

**Generate control number flow:**
```
PaymentController::generateControlNumber($payment)
  │
  ├── Authorize: payment belongs to landlord's property
  ├── Sync payment status (may flip pending→overdue)
  ├── Check LeasePaymentSyncService::canGenerateControlNumber()
  │     ├── NOT paid
  │     ├── Lease active and not expired
  │     └── No current active control number
  │
  └── NmbPaymentService::generateControlNumber($payment)
        ├── getToken() → cached 14-min bearer token
        ├── Build payload: payerID, firstName, lastName, email, mobile, amount, desc
        ├── POST /api/v1/generatectlno
        └── Returns { control_number: "..." }
  │
  └── payment.update({ control_number, control_number_generated_at })
```

**Send control number flow:**
```
PaymentController::sendControlNumber($payment, channel=email|sms)
  │
  ├── Validate active control number exists
  │
  ├── channel = 'email'
  │     └── Mail::to(tenant.email)->send(new PaymentControlNumber($payment, $tenant))
  │
  └── channel = 'sms'
        └── BriqSmsService::send(tenant.phone, message)
              "Hi {name}, your rent of TZS {amount} for {property} is due {date}.
               Pay via NMB control number: {ctlNo}."
```

---

### 11.6 TinVerificationController (`app/Http/Controllers/TinVerificationController.php`)

AJAX endpoint for real-time TIN (Taxpayer Identification Number) verification against the Tanzania Revenue Authority API.

```php
public function verify(Request $request)
{
    $request->validate(['tin' => 'required|string|max:20']);
    $tin = trim($request->tin);

    if (config('services.tra.dev_mode')) {
        // Return realistic mock data in development
        return response()->json([
            'success'       => true,
            'taxpayer_id'   => $tin,
            'taxpayer_name' => 'JOHN DOE (MOCK)',
            'has_vat'       => false,
        ]);
    }

    // Production: POST to TRA API
    $response = Http::withToken(config('services.tra.api_key'))
        ->timeout(config('services.tra.timeout', 15))
        ->get(config('services.tra.base_url') . '/api/CheckVatStatusFull/' . $tin);

    if ($response->status() === 404) {
        return response()->json(['success' => false, 'error' => 'TIN not found in TRA records.']);
    }

    // Normalize response keys to snake_case before returning
    return response()->json(array_merge(['success' => true], $this->normalizeResponse($response->json())));
}
```

---

## 12. Service Layer

### 12.1 LeasePaymentSyncService (`app/Services/LeasePaymentSyncService.php`)

This service is the heart of the payment system. It is called at the start of every payment-related request to ensure payment records are up-to-date.

**`syncLandlord(int $landlordId)`** — Entry point for batch sync:
```php
public function syncLandlord(int $landlordId): void
{
    Lease::where('landlord_id', $landlordId)
        ->with('payments')
        ->chunkById(100, function (Collection $leases) {
            $leases->each(fn (Lease $lease) => $this->syncLease($lease));
        });
}
```

`chunkById(100)` prevents loading all leases into memory at once, processing them in batches of 100.

**`syncLease(Lease $lease)`** — Core logic for one lease:
```php
public function syncLease(Lease $lease): ?Payment
{
    // 1. Update statuses of all existing payments for this lease
    $this->syncPaymentStatuses($lease->payments);

    // 2. Only create/update payment if lease can receive one
    if (! $this->leaseCanReceivePayment($lease)) {
        return null;   // No tenant, or lease inactive/expired
    }

    // 3. Find existing open payment
    $payment = $lease->payments
        ->whereIn('status', ['pending', 'overdue'])
        ->sortByDesc('due_date')
        ->first();

    // 4. Calculate due date from payment_day or start_date day
    $dueDate = $this->currentDueDate($lease);
    $status  = $dueDate->isPast() && ! $dueDate->isToday() ? 'overdue' : 'pending';

    // 5. Create if none exists
    if (! $payment) {
        return Payment::create([...]);
    }

    // 6. Update if changed (but don't move due_date if a control number is active)
    $updates = ['tenant_id' => $lease->tenant_id, 'amount' => $lease->monthly_rent, 'status' => $status];
    if (empty($payment->control_number)) {
        $updates['due_date'] = $dueDate->toDateString();
    }

    if ($this->hasChanged($payment, $updates)) {
        $payment->update($updates);
    }

    return $payment->refresh();
}
```

**`currentDueDate(Lease $lease)`** — Calculates the current month's due date:
```php
private function currentDueDate(Lease $lease): Carbon
{
    $today = now()->startOfDay();
    $day = $lease->payment_day ?: $lease->start_date->day;

    // Clamp to days-in-month (e.g., day 31 in February → 28)
    $dueDate = $today->copy()->day(min($day, $today->daysInMonth));

    // Don't go before lease start
    if ($dueDate->lt($lease->start_date)) {
        $dueDate = $lease->start_date->copy()->startOfDay();
    }

    // Don't go after lease end
    if ($dueDate->gt($lease->end_date)) {
        $dueDate = $lease->end_date->copy()->startOfDay();
    }

    return $dueDate;
}
```

**Control number expiry logic:**
```php
private function controlNumberExpiresAt(Payment $payment): ?Carbon
{
    // If generated, expires 7 days after generation
    if ($payment->control_number_generated_at) {
        return $payment->control_number_generated_at->copy()->addDays(7)->startOfDay();
    }
    // Fallback to due_date
    return $payment->due_date?->copy()->startOfDay();
}
```

---

### 12.2 NmbPaymentService (`app/Services/NmbPaymentService.php`)

Handles all communication with NMB Bank's Zalongwa SPG (Service Payment Gateway).

**Token management:**
```php
private const CACHE_KEY     = 'nmb_spg_token';
private const CACHE_MINUTES = 14;  // NMB tokens are valid ~15 min

public function getToken(bool $forceRefresh = false): array
{
    if (! $forceRefresh && Cache::has(self::CACHE_KEY)) {
        return ['token' => Cache::get(self::CACHE_KEY)];
    }

    $response = Http::post($this->baseUrl . '/api/v1/login', [
        'client_usr' => $this->clientUsr,
        'client_key' => $this->clientKey,
    ]);

    $token = $response->json('token');
    Cache::put(self::CACHE_KEY, $token, now()->addMinutes(self::CACHE_MINUTES));

    return ['token' => $token];
}
```

**Automatic token refresh on 401:**
```php
private function postWithAuth(string $endpoint, array $payload, callable $handler): array
{
    foreach ([false, true] as $retry) {     // Try once normally, once with fresh token
        $auth = $this->getToken(forceRefresh: $retry);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->post($this->baseUrl . $endpoint, $payload);

        if ($response->status() === 401 && ! $retry) {
            Cache::forget(self::CACHE_KEY);  // Force token refresh
            continue;
        }

        return $handler($response->json());
    }
}
```

**Control number generation payload:**
```php
$payload = [
    'systemName'  => $this->systemName,   // "REMIS"
    'systemCode'  => $this->systemCode,   // "SP1001"
    'payerID'     => 'TENANT-' . $tenant->id,
    'firstName'   => $firstName,
    'lastName'    => $lastName,
    'email'       => $tenant->email,
    'payerMobile' => $this->normalizeMobile($tenant->phone ?? ''),
    'currency'    => 'TZS',
    'amount'      => (float) $payment->amount,
    'amountType'  => 'EXACT',
    'paymentType' => 'RENT',
    'paymentDesc' => 'RENT payment – PropertyName / UnitNumber',
];
```

**Available API endpoints used:**

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/api/v1/login` | Authenticate, get bearer token |
| `POST` | `/api/v1/generatectlno` | Generate live control number |
| `POST` | `/api/v1/generatedemoctlno` | Generate demo control number (testing) |
| `POST` | `/api/v1/verification` | Verify a control number |
| `POST` | `/api/v1/getpayment` | Get payment info / confirm payment |
| `POST` | `/api/v1/getdemopayment` | Get demo payment info |

---

### 12.3 BriqSmsService (`app/Services/BriqSmsService.php`)

Sends SMS messages via the BRIQ bulk SMS API (Tanzania).

**Phone normalization:**
```php
private function normalizePhone(string $phone): array
{
    $digits = preg_replace('/\D/', '', $phone);

    // Resolve to 9-digit subscriber portion
    if (strlen($digits) === 9)                              $subscriber = $digits;
    elseif (strlen($digits) === 10 && str_starts_with($digits, '0'))
        $subscriber = substr($digits, 1);                   // 0712345678 → 712345678
    elseif (strlen($digits) === 12 && str_starts_with($digits, '255'))
        $subscriber = substr($digits, 3);                   // 255712345678 → 712345678
    else
        return ['valid' => false, 'error' => 'Invalid Tanzanian mobile number.'];

    // Must start with 6 or 7 (Tanzanian mobile prefix)
    if (!preg_match('/^[67]\d{8}$/', $subscriber)) {
        return ['valid' => false, 'error' => 'Not a valid Tanzanian mobile number.'];
    }

    return ['valid' => true, 'number' => '+255' . $subscriber];
}
```

**SMS request payload:**
```php
Http::withHeaders(['X-API-Key' => $this->apiKey])->post($endpoint, [
    'content'    => $message,
    'recipients' => [$normalizedNumber],  // Without the '+' prefix
    'sender_id'  => $this->senderId,      // "REMIS"
]);
```

---

## 13. Mail System

All transactional emails are sent via Laravel's `Mail` facade using dedicated Mailable classes. Each mailable constructs a rich HTML email from a Blade template.

### 13.1 TenantInvitation

Sent when a landlord invites a tenant to set up their account.

```php
class TenantInvitation extends Mailable
{
    public function __construct(
        public User   $tenant,
        public string $plainPassword,
        public string $loginUrl        // Signed URL: /create-password/{token}?email=...
    ) {}
}
```

**Subject:** `Your REMIS Account is Ready – Login Credentials Inside`

**Content:** Tenant name, temporary credentials, and a button linking to the password setup page.

### 13.2 PaymentControlNumber

Sent to tenants when a landlord pushes a control number via email.

```php
class PaymentControlNumber extends Mailable
{
    public function __construct(
        public Payment $payment,
        public User    $tenant
    ) {}
}
```

**Subject:** `Your Rent Payment Control Number – [Property Name]`

**Content:** Control number, amount, due date, property details, NMB payment instructions.

### 13.3 LeaseTerminationNotice

```
Subject: Important: Your Lease Has Been Terminated – REMIS
Content: Lease details, termination date, reason, landlord contact
```

### 13.4 TenantWarningNotice

```
Subject: Official Warning Notice Regarding Your Lease Agreement
Content: Warning reason, comments, remedial actions required
```

### Mail Configuration (`.env`)

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=app-specific-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@remis.tz
MAIL_FROM_NAME="REMIS Rental System"
```

In development, `MAIL_MAILER=log` writes emails to `storage/logs/laravel.log` instead of sending them.

---

## 14. External API Integrations

### 14.1 NMB Bank SPG (Zalongwa Payment Gateway)

**Purpose:** Tanzanian bank payment processing. Tenants pay rent by going to any NMB branch, ATM, or mobile/internet banking and entering the control number.

**Configuration:**
```ini
NMB_SPG_URL=https://nmb.spg.co.tz
NMB_SPG_USER=client_email@example.com
NMB_SPG_KEY=client_password
NMB_SYSTEM_NAME=REMIS
NMB_SYSTEM_CODE=SP1001
NMB_SPG_TIMEOUT=20
NMB_CA_BUNDLE=/path/to/ca-bundle.crt  # Optional custom SSL cert
```

**Flow:**
```
1. Landlord clicks "Generate Control Number"
2. System calls NMB /api/v1/generatectlno → receives 10-digit control number
3. Landlord sends control number to tenant (email or SMS)
4. Tenant visits any NMB channel, enters control number, pays
5. System polls /api/v1/getpayment → sees paid=true
6. System updates Payment record: status=paid, receipt, payer info
```

**SSL handling:** The service respects `NMB_CA_BUNDLE` for custom certificate authorities, with fallback to the system CA bundle.

---

### 14.2 BRIQ SMS (Tanzania Bulk SMS)

**Purpose:** Sends SMS notifications to tenants with Tanzanian mobile numbers.

**Configuration:**
```ini
BRIQ_API_KEY=your_api_key
BRIQ_BASE_URL=https://karibu.briq.tz
BRIQ_SENDER_ID=REMIS
BRIQ_BEARER_TOKEN=optional_bearer_token
```

**Usage:** Currently used to send payment control numbers. The service normalizes Tanzanian phone numbers to E.164 format before sending.

---

### 14.3 Tanzania Revenue Authority (TRA) TIN Verification

**Purpose:** Validates tenant TIN (Taxpayer Identification Number) before storing it.

**Configuration:**
```ini
TRA_API_BASE_URL=https://api.tra.go.tz
TRA_API_KEY=your_api_key
TRA_API_TIMEOUT=15
TRA_DEV_MODE=true  # Returns mock data in development
```

**Response fields returned to frontend:**
```json
{
    "success": true,
    "taxpayer_id": "100-123-456",
    "taxpayer_name": "JOHN DOE",
    "has_vat": false,
    "vrn": null,
    "business_name": null,
    "registration_date": "2020-01-15"
}
```

The frontend uses this AJAX response to auto-fill the taxpayer name field and display a validation indicator before the form is submitted.

---

## 15. PDF Report Generation

PDF generation uses `barryvdh/laravel-dompdf`, which renders Blade templates to A4 PDFs.

### Available Reports

| Report | Route | Content |
|---|---|---|
| Rent Payments | `GET /landlord/reports/pdf/rent-payments` | Payment history with status, dates, amounts |
| Tenant Inventory | `GET /landlord/reports/pdf/tenants` | All tenants with contact info and lease status |
| Overdue Payments | `GET /landlord/reports/pdf/overdue` | Tenants with overdue balances |
| Property Inventory | `GET /landlord/reports/pdf/properties` | All properties with unit counts and occupancy |

### Lease PDF (`leasesDownload`)

The lease PDF is a formatted legal document containing:
- Landlord and tenant details
- Property address and unit number
- Lease dates, monthly rent, security deposit
- Payment schedule
- Full lease terms text
- Signatures section

Generated via:
```php
$pdf = Pdf::loadView('landlord.leases.pdf', compact('lease'));
$pdf->setPaper('A4', 'portrait');
return $pdf->download('lease-' . $lease->id . '.pdf');
```

### CLI PDF Generation

Two Artisan commands support server-side PDF generation outside of HTTP context:

```bash
# Generate a single lease PDF
php artisan remis:generate-pdf 42 /tmp/lease-42.pdf

# Generate a batch report
php artisan remis:generate-report-pdf rent-payments 7 /tmp/report.pdf
```

These use GD image processing for landlord property images embedded in the PDF.

---

## 16. Artisan Console Commands

| Command | Signature | Purpose |
|---|---|---|
| GenerateLeasePdf | `remis:generate-pdf {leaseId} {outputPath}` | Generate a single lease as PDF to a file |
| GenerateReportPdf | `remis:generate-report-pdf {type} {landlordId} {outputPath}` | Generate a batch report PDF to a file |

---

## 17. Configuration Files

### `config/auth.php`

```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
],
'providers' => [
    'users' => ['driver' => 'eloquent', 'model' => App\Models\User::class],
],
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table'    => 'password_reset_tokens',
        'expire'   => 10080,   // 7 days (tenant invitation tokens)
        'throttle' => 60,
    ],
],
```

### `config/services.php`

Centralizes all third-party API credentials:
```php
'tra'  => ['base_url' => env('TRA_API_BASE_URL'), 'api_key' => env('TRA_API_KEY'), 'dev_mode' => env('TRA_DEV_MODE', true)],
'briq' => ['api_key' => env('BRIQ_API_KEY'), 'base_url' => env('BRIQ_BASE_URL'), 'sender_id' => env('BRIQ_SENDER_ID', 'REMIS')],
'nmb'  => ['base_url' => env('NMB_SPG_URL'), 'client_usr' => env('NMB_SPG_USER'), 'client_key' => env('NMB_SPG_KEY'), ...],
```

### `.env.example`

Key environment variables:

```ini
APP_NAME=REMIS
APP_ENV=local
APP_KEY=                    # Generated by: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=remis_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log             # Use 'smtp' in production
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=

TRA_DEV_MODE=true           # Set false in production with real TRA credentials
NMB_SPG_URL=https://nmb.spg.co.tz
NMB_SYSTEM_CODE=SP1001
BRIQ_SENDER_ID=REMIS
```

---

## 18. Testing

### Test Files

| File | Type | Coverage |
|---|---|---|
| `tests/Unit/LeasePaymentSyncServiceTest.php` | Unit | Payment sync logic, status transitions, control number expiry |
| `tests/Feature/BriqSmsServiceTest.php` | Feature | SMS API integration, phone normalization, error handling |

### BriqSmsServiceTest (9 test cases)

```
✓ it requires api key to be configured
✓ it normalizes 07XXXXXXXX format to E.164
✓ it normalizes 7XXXXXXXX (9-digit) format
✓ it normalizes 255XXXXXXXXX (12-digit) format
✓ it rejects non-Tanzanian numbers
✓ it rejects numbers starting with 08
✓ it sends correct payload to BRIQ endpoint
✓ it returns success on HTTP 200
✓ it returns error on HTTP 4xx/5xx
```

### LeasePaymentSyncServiceTest

Tests the core sync logic without database interaction using lightweight model stubs:

```
✓ pending payment stays pending if due date is today
✓ payment becomes overdue if due date has passed
✓ paid payment remains paid regardless
✓ payment on inactive lease becomes overdue
✓ control number is active within 7 days of generation
✓ control number is expired after 7 days
✓ cannot generate control number when one is active
✓ can generate control number when previous has expired
```

### Running Tests

```bash
php artisan test                    # Run all tests
php artisan test --filter BriqSms   # Run specific test class
composer test                       # Clears config cache first, then runs tests
```

---

## 19. Security Implementation

### Authentication Security

| Threat | Mitigation |
|---|---|
| Brute-force login | Rate limiter: 5 unlock attempts, 60-second decay |
| Session fixation | `$request->session()->regenerate()` on login |
| CSRF attacks | Laravel's `VerifyCsrfToken` middleware on all POST/PUT/DELETE routes |
| Password exposure | Bcrypt hashing via `'password' => 'hashed'` cast |
| Unauthorized access | Role-based middleware on every route group |
| Stale sessions | Session lock screen for remembered sessions |

### Authorization

- **Route-level:** `EnsureRole` middleware prevents cross-role access.
- **Object-level:** `authorizePayment()` verifies landlord owns the payment's property before any NMB action.
- **Tenant isolation:** All tenant queries are scoped to `Auth::user()->id` — tenants cannot view other tenants' data.
- **Landlord isolation:** Landlords only see data created under their `landlord_id`.

### API Security

- **NMB credentials** are never logged; only error status codes and truncated response bodies are written to the log.
- **BRIQ API key** is never logged; recipients are logged with a sanitized number only.
- **TRA API key** is passed as a Bearer token and not exposed in responses.
- **SSL/TLS:** All external API calls use HTTPS. Custom CA bundle path supported for corporate environments.

### Input Validation

All user input is validated at the controller level:
- Email: RFC-validated (`email:rfc`), uniqueness check.
- Password: Minimum 8 chars, mixed case, numbers, symbols.
- Phone: Optional, regex-validated E.164 format.
- Name: Regex-validated (letters, spaces, hyphens, apostrophes only).
- SQL injection: All queries go through Eloquent's PDO parameter binding.

---

## 20. Performance Considerations

### Database Indexes

Performance indexes were added in `2026_05_12_083721_add_performance_indexes_to_payments_leases`:

**Payments table:**
- `(lease_id, status)` — fast filter of payments by lease and status
- `(tenant_id, status)` — fast tenant payment lookups
- `(due_date, status)` — fast upcoming/overdue date queries

**Leases table:**
- `(landlord_id, status)` — fast landlord lease queries
- `(property_id, status)` — fast property active-lease lookup
- `(tenant_id, status)` — fast tenant lease lookup

**Audit logs table:**
- `(action, created_at)` — fast filtering by action type and date
- `(model_type, model_id)` — fast lookup of logs for a specific record

### Query Optimization

- **Eager loading:** All controllers use `->with([...])` to avoid N+1 queries.
- **Chunking:** `LeasePaymentSyncService::syncLandlord()` processes leases 100 at a time.
- **Token caching:** NMB bearer tokens are cached for 14 minutes (Laravel `Cache` facade).
- **Pagination:** Payment index paginates at 25 tenants per page.
- **Scoped queries:** All landlord queries use `->where('landlord_id', Auth::id())` at the ORM level.

### Pre-computed UI Data

In `PaymentController::index()`, control number flags are pre-computed in PHP after a single bulk payment query, rather than calling `canGenerateControlNumber()` in a Blade loop (which would trigger N+1 database hits):

```php
$paymentMap = Payment::whereIn('tenant_id', $tenantIds)
    ->with(['lease.property', 'lease.unit'])
    ->orderByRaw("FIELD(status,'overdue','pending','paid')")
    ->get()
    ->groupBy('tenant_id');

// Build plain-PHP data map — all flags computed once, not per-row in Blade
$rowData = [];
foreach ($tenants as $tenant) {
    $payment = $paymentMap->get($tenant->id)?->first();
    $rowData[$tenant->id] = [
        'payment'        => $payment,
        'can_generate'   => $payment !== null && $sync->canGenerateControlNumber($payment),
        'active_control' => $payment !== null && $sync->activeControlNumber($payment),
    ];
}
```

---

## 21. Deployment Notes

### XAMPP (Local Development)

The application runs on XAMPP for Windows with:
- Apache 2.4 with `mod_rewrite` enabled (required for Laravel's pretty URLs).
- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `curl`.
- MySQL 8 for the database.

### Setup Script

The `composer.json` includes a setup script:
```bash
composer run setup
# Equivalent to:
composer install
php artisan key:generate
php artisan migrate --force
npm install --ignore-scripts
npm run build
```

### Environment Requirements

| Requirement | Value |
|---|---|
| PHP | ≥ 8.2 |
| MySQL | ≥ 8.0 |
| Extensions | pdo_mysql, mbstring, openssl, fileinfo, gd, curl, zip |
| `storage/` permissions | Writable by web server |
| `bootstrap/cache/` permissions | Writable by web server |
| Apache `mod_rewrite` | Enabled |
| `AllowOverride All` | On the document root |

### Production Checklist

```bash
APP_ENV=production
APP_DEBUG=false
TRA_DEV_MODE=false              # Enable live TRA API
NMB_SPG_URL=https://nmb.spg.co.tz  # Live NMB endpoint
MAIL_MAILER=smtp                # Enable real email delivery

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 22. Component Interaction Summary

The diagram below shows how all backend components interact during the most complex request — a landlord generating and sending a rent control number:

```
Browser (Landlord)
     │  POST /landlord/payments/{id}/generate
     ▼
web.php route (named: landlord.payments.generate)
     │
     ▼
Middleware: auth → role:landlord → locked
     │
     ▼
PaymentController::generateControlNumber($payment)
     │
     ├── authorizePayment()
     │       └── Property::where('landlord_id') → abort_if(403) if not owner
     │
     ├── $payment->fresh(['lease', 'tenant'])
     │
     ├── LeasePaymentSyncService::syncPaymentStatuses()
     │       └── statusForPayment() → check due_date + lease.end_date
     │           └── Payment::update(['status' => 'overdue'|'pending'])
     │
     ├── LeasePaymentSyncService::canGenerateControlNumber()
     │       ├── payment.status != 'paid'
     │       ├── lease.status == 'active'
     │       ├── lease.end_date >= today
     │       └── no active control number
     │
     ├── NmbPaymentService::generateControlNumber($payment)
     │       ├── getToken() → Cache::get('nmb_spg_token')
     │       │               └── if miss: POST /api/v1/login → Cache::put()
     │       │
     │       ├── Build payload with tenant details
     │       │
     │       └── postWithAuth('/api/v1/generatectlno', $payload)
     │               ├── HTTP::post(NMB_SPG_URL)
     │               ├── On 401: Cache::forget() → retry with fresh token
     │               └── Return ['control_number' => '1234567890']
     │
     ├── Payment::update(['control_number' => '...', 'control_number_generated_at' => now()])
     │
     └── return back()->with('success', 'Control number generated: 1234567890')
           │
           ▼
       Browser (redirect with flash message)

---

Next: Landlord clicks "Send via Email"

     │  POST /landlord/payments/{id}/send  (channel=email)
     ▼
PaymentController::sendControlNumber($request, $payment)
     │
     ├── Validate active control number
     ├── loadMissing(['tenant', 'lease.property', 'lease.unit'])
     │
     └── sendViaEmail($payment, $tenant)
             └── Mail::to(tenant.email)->send(new PaymentControlNumber($payment, $tenant))
                     └── SMTP → Gmail → Tenant's inbox

OR if channel=sms:
     └── sendViaSms($payment, $tenant)
             └── BriqSmsService::send(tenant.phone, message)
                     ├── normalizePhone('+255712345678') → '+255712345678'
                     └── HTTP::post(BRIQ_API + '/v1/message/send-instant', payload)
                             └── SMS delivered to tenant's mobile
```

---

*End of Backend Implementation Report*

*Report covers REMIS V2 codebase as of commit `2d39813` (June 2026).*
