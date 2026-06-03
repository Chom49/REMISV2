# REMIS — Real Estate Management Information System

> A full-stack web application for professional property management, built with Laravel 12, Tailwind CSS 4, and MySQL.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Key Features](#2-key-features)
3. [System Requirements](#3-system-requirements)
4. [Technology Stack](#4-technology-stack)
5. [Project Structure](#5-project-structure)
6. [Database Design Overview](#6-database-design-overview)
7. [User Roles and Permissions](#7-user-roles-and-permissions)
8. [Frontend Implementation](#8-frontend-implementation)
9. [Backend Implementation](#9-backend-implementation)
10. [Property Management](#10-property-management)
11. [Tenant Management](#11-tenant-management)
12. [Lease Management](#12-lease-management)
13. [Payment Management](#13-payment-management)
14. [Maintenance Requests](#14-maintenance-requests)
15. [Reports Module](#15-reports-module)
16. [Security Features](#16-security-features)
17. [Email Notifications](#17-email-notifications)
18. [Installation Guide](#18-installation-guide)
19. [Environment Configuration](#19-environment-configuration)
20. [Running the Project](#20-running-the-project)
21. [Useful Artisan Commands](#21-useful-artisan-commands)
22. [Troubleshooting](#22-troubleshooting)

---

## 1. Project Overview

**REMIS** (Real Estate Management Information System) is a comprehensive, multi-role web application designed to streamline the end-to-end management of rental properties. It serves landlords, tenants, and system administrators through three dedicated portals — each with tailored dashboards, workflows, and access controls.

The system was developed to address common pain points in property management: manual lease tracking, inconsistent rent collection, poor communication between landlords and tenants, and lack of structured record-keeping. REMIS digitises these workflows into a single, integrated platform.

### Core Objectives

- Provide landlords with complete visibility and control over their property portfolio.
- Give tenants a transparent view of their lease status, payment history, and maintenance requests.
- Automate rent payment tracking, lease lifecycle management, and notification delivery.
- Generate professional, print-ready PDF reports and lease contracts.
- Enforce security through role-based access, session locking, and input validation.

---

## 2. Key Features

| Category | Features |
|---|---|
| **Authentication** | Secure registration, login, session lock screen, forced password change, rate-limited unlock |
| **Properties** | Single-unit and multi-unit property support, floor/unit management, occupancy tracking |
| **Tenants** | Tenant directory, invitation system, TIN verification, status management |
| **Leases** | Create, view, renew, terminate leases; downloadable PDF contracts; Send Notice warnings |
| **Payments** | Automated rent sync, NMB payment integration, control number generation and delivery |
| **Maintenance** | Request submission (tenant), status tracking and management (landlord) |
| **Reports** | 4 downloadable PDF reports: Rent Payments, Tenants, Overdue Payments, Properties |
| **Notifications** | Email notifications for invitations, lease terminations, warning notices, payment control numbers |
| **Settings** | Profile management, password change, notification preferences per role |
| **Admin Panel** | User management, audit logs, system settings, database backups |

---

## 3. System Requirements

### Server / Local Environment

| Requirement | Minimum Version |
|---|---|
| PHP | 8.2 or higher |
| Laravel | 12.0 |
| MySQL | 5.7 / MariaDB 10.4 |
| Composer | 2.x |
| Node.js | 18.x or higher |
| npm | 9.x or higher |
| XAMPP (Windows) | 8.2+ bundle |
| Web Server | Apache 2.4 (via XAMPP) |

### PHP Extensions Required

- `pdo_mysql` — database connectivity
- `mbstring` — multibyte string handling
- `openssl` — encryption and HTTPS
- `tokenizer` — Blade template parsing
- `gd` or `imagick` — image rendering in PDF generation (CLI PHP only)
- `fileinfo` — MIME type detection
- `curl` — external API requests (NMB integration)

---

## 4. Technology Stack

### Backend

| Technology | Version | Purpose |
|---|---|---|
| **PHP** | ^8.2 | Server-side language |
| **Laravel** | ^12.0 | MVC web framework |
| **MySQL** | 5.7+ | Relational database |
| **barryvdh/laravel-dompdf** | ^3.1 | PDF generation from HTML/Blade |
| **Laravel Tinker** | ^2.9 | Interactive REPL for debugging |
| **Laravel Pint** | ^1.13 | PHP code style fixer (dev) |
| **PHPUnit** | ^11.0 | Unit and feature testing |

### Frontend

| Technology | Version | Purpose |
|---|---|---|
| **Tailwind CSS** | ^4.2 | Utility-first CSS framework |
| **Vite** | ^8.0 | Frontend build tool and dev server |
| **laravel-vite-plugin** | ^3.0 | Laravel–Vite integration |
| **@tailwindcss/vite** | ^4.2 | Tailwind CSS Vite plugin |
| **Vanilla JavaScript** | ES2020+ | Client-side interactivity |

### Services and Integrations

| Service | Purpose |
|---|---|
| **Gmail SMTP** | Outbound email delivery |
| **NMB Bank API** | Payment control number generation and verification |
| **BriqSMS API** | SMS notifications for payment control numbers |
| **TIN Verification API** | Tanzania taxpayer identification validation |

---

## 5. Project Structure

```
RemisV2/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── GenerateLeasePdf.php        # CLI PDF generation for lease contracts
│   │       └── GenerateReportPdf.php       # CLI PDF generation for reports
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php         # Admin panel: users, audit logs, backups, settings
│   │   │   ├── AuthController.php          # Login, register, session lock/unlock, logout
│   │   │   ├── LandlordController.php      # All landlord portal functionality
│   │   │   ├── PaymentController.php       # NMB payment workflow, control numbers
│   │   │   ├── PasswordSetupController.php # Tenant invitation password setup
│   │   │   ├── TenantController.php        # Tenant portal: dashboard, payments, maintenance
│   │   │   └── TinVerificationController.php
│   │   └── Middleware/
│   │       ├── CheckSessionLock.php        # Enforces session lock screen
│   │       ├── EnsureRole.php              # Role-based access control
│   │       └── ForcePasswordChange.php     # Forces password reset after invitation
│   ├── Mail/
│   │   ├── LeaseTerminationNotice.php
│   │   ├── PaymentControlNumber.php
│   │   ├── TenantInvitation.php
│   │   └── TenantWarningNotice.php
│   ├── Models/
│   │   ├── AuditLog.php
│   │   ├── Backup.php
│   │   ├── Lease.php
│   │   ├── MaintenanceRequest.php
│   │   ├── Payment.php
│   │   ├── Property.php
│   │   ├── SystemSetting.php
│   │   ├── Unit.php
│   │   └── User.php
│   └── Services/
│       ├── BriqSmsService.php              # SMS delivery via BriqSMS API
│       ├── LeasePaymentSyncService.php     # Automated rent payment generation and sync
│       └── NmbPaymentService.php           # NMB Bank API integration
├── database/
│   └── migrations/                         # 20+ timestamped schema migrations
├── resources/
│   ├── css/app.css                         # Tailwind CSS entry point
│   ├── js/app.js                           # JavaScript entry point
│   └── views/
│       ├── admin/                          # Admin portal views
│       ├── auth/                           # Login, register, lock screen, password setup
│       ├── components/                     # Shared Blade components
│       ├── landlord/
│       │   ├── dashboard.blade.php
│       │   ├── leases/                     # Lease CRUD + PDF template
│       │   ├── payments/                   # Payment management
│       │   ├── properties/                 # Property CRUD
│       │   ├── reports/pdf/                # 4 PDF report templates
│       │   ├── tenants/                    # Tenant management
│       │   └── settings.blade.php
│       ├── layouts/                        # Base layout templates per role
│       ├── mail/                           # HTML email templates
│       └── tenant/                         # Tenant portal views
└── routes/
    └── web.php                             # All application routes (~80 named routes)
```

---

## 6. Database Design Overview

### Tables

| Table | Description |
|---|---|
| `users` | All users (landlords, tenants, admins). Includes role, TIN, NIDA, preferences (JSON), invitation status |
| `properties` | Property listings with type, category (single/multi-unit), address, status |
| `units` | Individual units within multi-unit properties (floor number, unit number, status) |
| `leases` | Agreements linking landlord, property, unit, and tenant with dates, rent, status, and terms |
| `payments` | Rent payment records with status, NMB fields, and control numbers |
| `maintenance_requests` | Tenant-submitted maintenance requests with status tracking |
| `audit_logs` | Admin-viewable system event log |
| `system_settings` | Key-value store for system-wide configuration |
| `backups` | Metadata for database backup files |
| `cache`, `jobs` | Laravel cache and queue infrastructure tables |

### Key Relationships

```
User (landlord) ──< Property ──< Unit ──< Lease ──< Payment
User (tenant)   ────────────────────────── Lease
Property ──< MaintenanceRequest <── User (tenant)
```

### Notable Design Decisions

- **`users.role`** — discriminates between `landlord`, `tenant`, and `admin` in a single table.
- **`users.landlord_id`** — links a tenant to the landlord who created them.
- **`leases.status`** — `active | renewed | terminated | expired` drives the entire lifecycle.
- **`payments`** — generated automatically by `LeasePaymentSyncService` based on lease configuration.
- **`users.preferences`** — JSON column stores per-user notification settings without extra tables.

---

## 7. User Roles and Permissions

### Landlord — `/landlord/*`

| Module | Permissions |
|---|---|
| Properties | Create, view, manage single and multi-unit properties |
| Units | Add and manage floor/unit layout for multi-unit properties |
| Tenants | Create, invite, edit, deactivate tenants |
| Leases | Create, view, renew, terminate, download PDF, send warning notice |
| Payments | View payment status, generate NMB control numbers, send via email or SMS |
| Reports | Download all 4 PDF reports |
| Maintenance | View and update tenant maintenance requests |
| Settings | Update profile, change password, set notification preferences |

### Tenant — `/tenant/*`

| Module | Permissions |
|---|---|
| Dashboard | View active lease, payment summary, recent maintenance |
| Payments | View full payment history |
| Maintenance | Submit and track maintenance requests |
| Settings | Update profile, change password, notification preferences |

### Admin — `/admin/*`

| Module | Permissions |
|---|---|
| Users | View all users, update roles, delete accounts |
| Audit Logs | View and clear system activity logs |
| System Settings | Update global configuration |
| Backups | Create, download, and delete database backups |

---

## 8. Frontend Implementation

### Design Philosophy

REMIS follows a **clean, professional, and functional** design language. The interface prioritises clarity, whitespace, and intuitive navigation. Every page is built with the primary user in mind — a landlord managing multiple properties who needs quick access to critical information without visual noise.

### UI/UX Principles

- **Role-separated portals** — each role has a distinct sidebar and colour language, preventing confusion between portals.
- **Card-based layouts** — information is grouped into white rounded cards with subtle shadows and borders, consistent across all modules.
- **Progressive disclosure** — detailed actions (terminate, renew, send notice) appear inside modals rather than cluttering the main view.
- **Status-driven colour coding** — green for active/paid, amber for expiring/pending, red for overdue/terminated — applied consistently across badges, buttons, and alerts.
- **Feedback on every action** — success and error flash messages are displayed prominently after every form submission.

### Responsive Design

- Built entirely with **Tailwind CSS v4** utility classes using responsive prefixes (`sm:`, `lg:`).
- Sidebar navigation collapses on mobile with a hamburger toggle.
- Tables scroll horizontally on small screens rather than truncating content.
- PDF layouts use fixed A4 dimensions and are excluded from the responsive grid.

### Pages and Modules

#### Public Pages

| Page | Description |
|---|---|
| Landing (`/`) | Marketing page with embedded login and signup modals |
| Register (`/register`) | Enhanced signup with real-time validation and password strength meter |
| Login | Modal-based login with session persistence |
| Lock Screen | Session timeout lock with rate-limited password re-entry |
| Password Setup | Invited tenant creates their initial password via a secure token link |

#### Landlord Portal

| Page | Description |
|---|---|
| Dashboard | KPI cards, recent payments, expiring leases, upcoming payment alerts |
| Properties Index | Grid of property cards with occupancy status |
| Property Detail | Units overview, active lease summary, maintenance history |
| Tenants Index | Searchable, filterable tenant table with status indicators |
| Tenant Detail | Full profile, lease history, payment summary, invite action |
| Leases Index | All leases with status filters |
| Lease Detail | Full contract view, PDF download, Renew/Send Notice/Terminate actions |
| Payments | Tenant-centric payment view, NMB control number workflow |
| Reports | 4 download cards with live record counts |
| Maintenance | Request list with status update controls |
| Settings | Profile, password, notification preference toggles |

#### Tenant Portal

| Page | Description |
|---|---|
| Dashboard | Active lease card, payment table, maintenance summary |
| Payment History | Full payment history with status badges |
| Maintenance | Submit and track maintenance requests |
| Settings | Profile, password, notification preferences |

#### Admin Portal

| Page | Description |
|---|---|
| Dashboard | System-wide statistics |
| Users | Full user list with role management and account deletion |
| Audit Logs | Paginated event log with bulk clear action |
| System Settings | Global configuration key-value editor |
| Backups | Create, download, and delete database backups |

### Frontend Technologies

| Technology | Usage |
|---|---|
| **Tailwind CSS v4** | All styling — layout, spacing, colour, typography, responsive breakpoints |
| **Vite v8** | Module bundling, asset fingerprinting, hot-module replacement |
| **Vanilla JS (ES2020+)** | Modals, toggles, real-time form validation, AJAX polling, live search filtering |
| **Blade Templates** | Server-side rendering with layout inheritance and component reuse |
| **Inline SVG Icons** | Zero-dependency, sharp iconography throughout all interfaces |
| **CSS Animations** | Float animation on landing illustration; spinner on PDF generation buttons |

---

## 9. Backend Implementation

### Architecture

REMIS follows the standard **MVC (Model–View–Controller)** pattern provided by Laravel 12.

```
HTTP Request → Middleware Stack → Route → Controller → Service/Model → Blade View → HTTP Response
```

- **Models** — Eloquent ORM models with defined relationships, casts, accessors, and helper methods.
- **Controllers** — Thin controllers that validate input, delegate to services, and return views or redirects.
- **Services** — `LeasePaymentSyncService`, `NmbPaymentService`, and `BriqSmsService` encapsulate complex domain logic.
- **Middleware** — `EnsureRole`, `CheckSessionLock`, and `ForcePasswordChange` enforce access rules at the HTTP layer.

### Authentication

- **Session-based auth** via Laravel's built-in `Auth` facade with `remember me` support.
- **Role enforcement** — `EnsureRole` middleware checks `users.role` on every protected route group.
- **Session lock** — `CheckSessionLock` middleware redirects to the lock screen when `auth.locked` is active.
- **Forced password change** — `ForcePasswordChange` middleware redirects invited tenants to change their default password before accessing any other page.
- **Rate limiting** — unlock attempts are throttled via Laravel's `RateLimiter` (5 attempts per 60 seconds).

### Business Logic — LeasePaymentSyncService

The core automation service. Triggered on every page load that involves payment data, it:

1. Reads each lease's `start_date`, `payment_day`, and `payment_frequency`.
2. Generates missing `Payment` records for all elapsed periods up to the current date.
3. Marks `pending` payments as `overdue` when `due_date` has passed.
4. Sets `Lease.status` to `expired` when the end date is exceeded without termination.

### Business Logic — NmbPaymentService

Integrates with the **NMB Bank REST API** for Tanzania-based digital payment collection:

- `generateControlNumber(Payment $payment)` — registers a bill in the NMB billing system.
- `getPaymentInfo(string $controlNumber)` — polls NMB for real-time payment confirmation.
- On confirmation, updates `Payment` fields: `status`, `paid_date`, `nmb_receipt_number`, `nmb_payer_name`, `nmb_paid_at`.

### PDF Generation

PDF generation uses **barryvdh/laravel-dompdf**. The Apache web server's PHP does not have the GD extension enabled, preventing PNG logo rendering. This is resolved through a two-process architecture:

1. The web process spawns a **CLI PHP process** via `Process::run()` — the CLI binary has GD enabled.
2. The CLI process runs an Artisan command (`remis:generate-pdf` or `remis:generate-report-pdf`).
3. The command generates the PDF to a temp file in `storage/app/temp/`.
4. The web process streams the temp file as a download response, then deletes it.
5. A **fallback** (direct generation, logo omitted) is used if the CLI process fails.

All PDF templates are self-contained A4 HTML/CSS files with `28mm` horizontal page margins, `10mm` body padding, and inline REMIS branding.

### Database Interactions

- All queries use **Eloquent ORM** with eager loading (`with()`) to eliminate N+1 query issues.
- Performance indexes on `payments.lease_id`, `payments.tenant_id`, `payments.status`, `payments.due_date`, and `leases.landlord_id`.
- `optional()` helpers guard all nullable relationship chains in views and controllers.

### Asynchronous Email Delivery

Non-critical emails (e.g., warning notices) use `dispatch(fn() => Mail::to(...)->send(...))->afterResponse()`. This returns the HTTP response instantly to the user and sends the email in the background after the response is flushed.

### File Handling

- Property images: `Storage::disk('public')` → `storage/app/public/`.
- Database backups: `storage/app/backups/`.
- Temporary PDF files: `storage/app/temp/` — deleted immediately after streaming.

---

## 10. Property Management

Landlords register two categories of properties:

**Single-unit** — A standalone property occupied by one tenant (house, apartment, shop).

**Multi-unit** — A building with multiple independent units (apartment block, office building). Each unit has:
- A floor number and unit number.
- Its own occupancy status (`vacant` / `occupied`).
- Independent lease and payment history.

The property detail page displays a visual floor/unit grid showing real-time occupancy, with quick-action buttons to create leases for vacant units directly from the property view.

---

## 11. Tenant Management

| Action | Description |
|---|---|
| **Create** | Add tenant with name, email, phone, TIN, NIDA, gender, nationality |
| **TIN Verification** | Real-time Tanzania TIN validation via external API |
| **Invite** | Sends a tokenised email with a password setup link to activate the account |
| **Edit** | Update tenant details at any time |
| **Status** | `active` (current tenant) or `inactive` (former tenant) |
| **Force Password Change** | Tenants created by landlords must change their default password on first login |

---

## 12. Lease Management

| Action | Description |
|---|---|
| **Create** | Link property/unit and tenant, set start/end dates, rent, payment day, and custom terms |
| **View** | Contract detail with progress bar, payment timeline, and key dates |
| **Download PDF** | Professional A4 lease contract with REMIS branding, parties, 10 default terms, and signature blocks |
| **Renew** | Extend with new end date and updated rent; old lease marked `renewed` |
| **Terminate** | Record reason and notes; sends termination email; marks lease `terminated` |
| **Send Notice** | Issue a formal warning email to tenant for lease breaches with selected reason and comments |

### Lease Statuses

`active` → `renewed` / `terminated` / `expired`

### Default Lease Terms (10)

The PDF contract includes 10 standard terms covering: timely rent payment, property maintenance, no subletting, landlord inspection rights, breach consequences, peaceful vacation, legal compliance, damage liability, cleanliness and nuisance prevention, and landlord's right to terminate on repeated breaches.

---

## 13. Payment Management

Payments are generated automatically by `LeasePaymentSyncService`. The landlord payment view is tenant-centric — one row per tenant showing their most urgent outstanding payment.

### NMB Bank Payment Workflow

```
1. Landlord opens the Payments page
2. System syncs all leases (generates missing payments, marks overdue)
3. Landlord clicks "Generate Control Number" for an unpaid payment
4. System calls NMB API → receives a unique control number
5. Landlord sends control number to tenant via Email or SMS
6. Tenant pays at any NMB channel (branch, ATM, mobile banking)
7. System polls NMB API → confirms payment → marks Payment as "paid"
```

### Payment Statuses

| Status | Meaning |
|---|---|
| `pending` | Due date is in the future; payment not yet received |
| `overdue` | Due date has passed; payment not received |
| `paid` | Confirmed payment, optionally with NMB receipt and payer details |

---

## 14. Maintenance Requests

Tenants submit maintenance requests from their dashboard by providing a title and description. The property is automatically linked from their active lease.

Status progression: `open` → `in_progress` → `resolved`

Landlords see all requests in their Maintenance module with full status management. The tenant's dashboard always shows the latest 5 requests and their current status at a glance.

---

## 15. Reports Module

Four professional PDF reports are available to landlords, each generated via the CLI PHP process for reliable image rendering.

| Report | Contents |
|---|---|
| **Rent Payments** | All payments with tenant, property, unit, due date, paid date, amount, status, reference |
| **Tenants** | All tenants with contact info, assigned property/unit, lease end date, monthly rent, lease status |
| **Overdue Payments** | Only overdue/past-due payments with days overdue and outstanding amounts |
| **Properties** | All properties with address, type, category, unit count, occupied/vacant breakdown |

### PDF Report Structure

Each report includes:
- REMIS logo (embedded as inline CID attachment)
- Report title and generation timestamp
- Introductory paragraph explaining the report's purpose
- Summary statistics bar (totals, counts, key figures)
- Colour-coded data table with alternating row shading
- Footer with system name and confidentiality notice

All reports use **A4 portrait** format with `28mm` left/right page margins and `10mm` body padding, consistent with the lease contract PDF.

---

## 16. Security Features

| Feature | Implementation |
|---|---|
| **Authentication** | Laravel session auth with `remember me` cookie |
| **Role-based access control** | `EnsureRole` middleware on all route groups; 403 abort on violations |
| **Session lock** | `CheckSessionLock` middleware; requires password re-entry after inactivity |
| **Rate limiting** | Laravel `RateLimiter` on unlock attempts (5 per 60 seconds) |
| **CSRF protection** | `@csrf` tokens on all POST forms; Laravel's global CSRF middleware |
| **Input validation** | Server-side validation on every form with custom error messages; client-side JS guard |
| **Password policy** | Min 8 characters, uppercase, lowercase, number, and special character (enforced at registration) |
| **Duplicate prevention** | Unique DB constraints and validation rules on `email` and `phone` |
| **Resource ownership** | `abort_if($resource->landlord_id !== Auth::id(), 403)` on all owned-resource operations |
| **XSS prevention** | All output rendered via Blade's `{{ }}` double-escaped syntax |
| **SQL injection prevention** | All queries via Eloquent ORM / parameterised bindings — no raw string interpolation in queries |
| **Forced password change** | Invited tenants are redirected to change their default password on first login |

---

## 17. Email Notifications

| Email | Trigger | Recipient |
|---|---|---|
| **Tenant Invitation** | Landlord creates and invites a tenant | Tenant |
| **Lease Termination Notice** | Landlord terminates an active lease | Tenant |
| **Warning Notice** | Landlord clicks "Send Notice" on an active lease | Tenant |
| **Payment Control Number** | Landlord sends via email channel | Tenant |

All emails are built as self-contained HTML Blade templates featuring:
- REMIS logo embedded as an inline CID attachment (`$message->embed()`) for compatibility with Gmail, Outlook, and all major email clients.
- Branded dark green header (`#1b4332`) with a white logo container.
- Professional body with information boxes, bold key details, and a signed footer.
- Sent via Gmail SMTP using TLS encryption on port 587.

---

## 18. Installation Guide

### Prerequisites

Ensure the following are installed:

- [XAMPP](https://www.apachefriends.org/) (Apache + PHP 8.2+ + MySQL)
- [Composer](https://getcomposer.org/) 2.x
- [Node.js](https://nodejs.org/) 18+ and npm

---

### Step 1 — Clone the Repository

```bash
git clone https://github.com/Chom49/REMISV2.git
cd REMISV2
```

Or place the project folder in `C:\xampp\htdocs\RemisV2`.

---

### Step 2 — Install PHP Dependencies

```bash
composer install
```

---

### Step 3 — Install Frontend Dependencies

```bash
npm install
```

---

### Step 4 — Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

---

### Step 5 — Database Setup

1. Start **Apache** and **MySQL** via XAMPP Control Panel.
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
3. Create a database named `remis_db` with `utf8mb4_unicode_ci` collation.
4. Run migrations:

```bash
php artisan migrate
```

5. (Optional) Seed demo data:

```bash
php artisan db:seed
```

---

### Step 6 — Storage Link

```bash
php artisan storage:link
```

---

### Step 7 — Build Frontend Assets

```bash
# Production build
npm run build

# Development with hot reload
npm run dev
```

---

### Step 8 — Access the Application

```
http://localhost/RemisV2/public
```

---

## 19. Environment Configuration

Edit `.env` with your local settings:

```env
# Application
APP_NAME="REMIS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost/RemisV2

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=remis_db
DB_USERNAME=root
DB_PASSWORD=

# Mail (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="REMIS Estates"

# NMB Payment Integration
NMB_API_URL=https://...
NMB_API_KEY=...
NMB_MERCHANT_ID=...

# SMS (BriqSMS)
BRIQ_SMS_API_KEY=...
BRIQ_SMS_SENDER_ID=REMIS
```

> **Gmail Note:** Enable 2-Factor Authentication and generate an **App Password** at [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords). Use this as `MAIL_PASSWORD` — your regular Gmail password will not work with SMTP.

---

## 20. Running the Project

### Start XAMPP Services

1. Open **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Development (with Vite hot-reload)

```bash
npm run dev
```

Keep this terminal open while developing.

### Production

```bash
npm run build
```

Built assets are output to `public/build/`. No dev server is needed.

---

## 21. Useful Artisan Commands

```bash
# Clear all application caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Database
php artisan migrate                  # Run all pending migrations
php artisan migrate:rollback         # Rollback the last batch
php artisan migrate:fresh            # Drop all tables and re-run all migrations
php artisan db:seed                  # Run database seeders

# Utilities
php artisan route:list               # List all registered routes
php artisan key:generate             # Generate a new application key
php artisan storage:link             # Create public storage symlink

# REMIS custom commands
php artisan remis:generate-pdf {leaseId} {outputPath}
php artisan remis:generate-report-pdf {type} {landlordId} {outputPath}
# type options: rent-payments | tenants | overdue | properties
```

---

## 22. Troubleshooting

### Database connection refused (SQLSTATE[HY000] [2002])

**Cause:** MySQL is not running.
**Fix:** Open XAMPP Control Panel → click **Start** next to **MySQL**.

---

### "Route not defined" error

**Fix:**
```bash
php artisan route:clear
php artisan config:clear
```

---

### Blank page or 500 error after pulling changes

**Fix:**
```bash
composer install
php artisan migrate
php artisan config:clear
php artisan view:clear
npm run build
```

---

### PDF generation fails or logo is missing

**Cause:** The Apache PHP instance does not have the GD extension enabled.
**Fix:** REMIS automatically uses the CLI PHP binary at `C:/Users/Admin/Downloads/php-8.4.7-Win32-vs17-x64/php.exe` which has GD enabled. Ensure this path exists, or update it in `LandlordController::streamReportPdf()` and `LandlordController::leasesDownload()`.

---

### Emails not sending

1. Confirm all `MAIL_*` values in `.env` are correct.
2. Use a **Gmail App Password**, not your regular Gmail password.
3. Run `php artisan config:clear` after editing `.env`.
4. Check `storage/logs/laravel.log` for SMTP error details.

---

### Assets not loading (404 on CSS or JS)

**Fix:**
```bash
npm run build
php artisan view:clear
```

Ensure `public/build/manifest.json` exists. When using `npm run dev`, the Vite dev server must be running in a separate terminal.

---

### Permission denied on storage or cache

**Windows/XAMPP:** Ensure Apache has write permissions on `storage/` and `bootstrap/cache/`.

**Linux/macOS:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## License

This project is proprietary software developed for academic and operational purposes. All rights reserved.

---

## Authors

Developed by the REMIS development team.
Powered by [Laravel](https://laravel.com) · [Tailwind CSS](https://tailwindcss.com) · [DOMPDF](https://github.com/dompdf/dompdf)
