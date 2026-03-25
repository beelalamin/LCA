# LC Assets — Luxury Code Asset Management

> **Version:** 1.0
> **Status:** Draft
> **Stack:** FilamentPHP 3.x · Laravel 11 · PostgreSQL · Tailwind CSS
> **Code generation:** Antigravity
> **Audience:** Internal IT Department
> **Supported languages:** English · Arabic (RTL)

---

## 1. Project Overview

LC Assets (Luxury Code Assets) is an internal web application for corporate IT departments to manage the full lifecycle of hardware assets — from procurement through retirement. It replaces spreadsheet-based tracking with a structured, auditable, role-aware system with full English and Arabic language support.

### 1.1 Goals

- Eliminate manual spreadsheet tracking of IT hardware
- Provide a single source of truth for asset ownership and location
- Enforce an auditable, immutable record of every asset change
- Enable rapid asset identification via barcode and QR code scanning
- Allow IT staff to generate, print, and label physical assets from within the system
- Support Arabic-speaking IT staff and employees with full RTL layout and localised UI

---

## 2. Tech Stack

| Layer | Technology | Rationale |
|---|---|---|
| Backend + Frontend | FilamentPHP 3.x on Laravel 11 | Full-stack, minimal code surface, AI-generation-friendly |
| UI framework | Filament panels + Tailwind CSS | Consistent, professional, customizable |
| Database | PostgreSQL | Relational integrity for assignments and audit logs |
| Auth | Laravel Sanctum + Filament Shield | Role-based access, session-based |
| i18n | Laravel `lang/` + Filament i18n + `rtlcss` | English & Arabic, dynamic RTL layout switching |
| Barcode rendering | `milon/barcode` (PHP) | Server-side Code 128 barcode SVG/PNG output |
| QR code rendering | `endroid/qr-code` (PHP) | Server-side QR code SVG/PNG output |
| Barcode scanning | `html5-qrcode` (browser JS) | Camera scanning on mobile and desktop |
| PDF export | `barryvdh/laravel-dompdf` | Barcode/QR sheet PDF export |
| Bulk import | `maatwebsite/excel` (Laravel Excel) | CSV and XLSX import with row-level validation |
| Charts | `leandrocfe/filament-apex-charts` | Dashboard reporting widgets |
| Settings | `spatie/laravel-settings` | Typed system settings with Filament UI |

### 2.1 Why FilamentPHP over React + Spring Boot

- One Resource file covers table, form, filters, and actions — 5–8× less code than a split SPA + API
- Antigravity generates Filament resources predictably from schema descriptions
- Built-in role/permission UI via Filament Shield
- Barcode and QR print views are standard Blade templates — no API layer needed
- Zero frontend build pipeline to maintain

---

## 3. Internationalisation (i18n)

### 3.1 Supported Languages

| Code | Language | Direction |
|---|---|---|
| `en` | English | LTR |
| `ar` | Arabic | RTL |

### 3.2 Implementation

- All UI strings stored in `lang/en/` and `lang/ar/` PHP array files
- Filament's built-in locale switching used for panel UI strings; overridden where needed with custom translation files
- RTL layout activated automatically when `ar` locale is set — Filament 3 natively supports RTL via the `direction` configuration
- `rtlcss` processes Tailwind utility classes for RTL mirror transforms (margins, paddings, flex directions, text alignment)
- Locale preference stored per `app_user` record; switchable via a language toggle in the top navigation bar (globe icon → EN / AR)
- **Arabic numerals vs Western numerals:** configurable in System Settings (default: Western numerals 1, 2, 3 for asset tags and codes)
- **Date formats:** DD/MM/YYYY for both locales (consistent with regional preference); configurable in System Settings
- All Filament form validation messages translated in both languages
- Label print views (Blade) render in the currently active locale — asset names and fields appear in the language the data was entered in

### 3.3 Translatable Content

The following data fields support bilingual entry (English + Arabic side-by-side inputs):

- `categories.name`
- `categories.description`
- `assets.name`
- `assets.notes`
- `employees.full_name` (stored as `full_name_en` + `full_name_ar`)
- `employees.department`

Implemented via `spatie/laravel-translatable` — stored as JSON in the database column, e.g. `{"en": "Laptop", "ar": "حاسوب محمول"}`.

---

## 4. User & People Model

LC Assets separates **users** (people who log in) from **employees** (people assets are assigned to). This is a deliberate architectural decision.

### 4.1 Users — who logs in

Only IT staff have login credentials. Two roles:

| Role | Description |
|---|---|
| **Admin** | Full system access — register assets, manage employees, configure settings, view all reports |
| **Technician** | Operational access — check out/in assets, open maintenance tickets, scan barcodes, print labels |

### 4.2 Employees — who assets are assigned to

Employees are the full company directory. They **do not** log in to LC Assets. They exist purely as assignment targets — "who has this laptop." This keeps the auth system lean and allows the employee list to scale to the full company headcount without creating dummy credentials.

Employees can be:

- Added manually via the Employees section (Admin only)
- Bulk imported via CSV

An employee record contains enough detail to identify a person and their organisational placement — no password, no role, no login.

### 4.3 Roles & Permissions

| Permission | Admin | Technician |
|---|---|---|
| Register / edit assets | ✅ | ❌ |
| Bulk import assets | ✅ | ❌ |
| Retire / dispose assets | ✅ | ❌ |
| Check out / check in assets | ✅ | ✅ |
| Open maintenance tickets | ✅ | ✅ |
| Generate & print barcodes / QR codes | ✅ | ✅ |
| View audit log | ✅ | ✅ |
| View reports & dashboard | ✅ | ✅ |
| Manage employees (directory) | ✅ | ❌ |
| Manage app users | ✅ | ❌ |
| System settings | ✅ | ❌ |

Implemented via **Filament Shield** (`bezhansalleh/filament-shield`).

---

## 5. Domain Model

### 5.1 Core Tables

#### `categories`

```
id (uuid, PK)
name (json)                        -- translatable: {"en":"...", "ar":"..."}
parent_id (uuid, FK → categories, nullable)  -- null = top-level; set = subcategory
created_at, updated_at
```

Categories support **one level of nesting** (parent → subcategory). A category with `parent_id = null` is a top-level category. A category with a `parent_id` is a subcategory of that parent. Subcategories cannot have children of their own (max depth: 2). Enforced at the service layer.

#### `users` (login accounts — IT staff only)

```
id (uuid, PK)
full_name (string)
email (string, unique)
role (enum: admin | technician)
preferred_locale (enum: en | ar, default: en)
is_active (boolean, default true)
password (hashed)
created_at, updated_at
```

#### `employees` (company directory — assignment targets, no login)

```
id (uuid, PK)
employee_number (string, unique)
full_name_en (string)
full_name_ar (string, nullable)
email (string, nullable)
phone (string, nullable)
department (json)                  -- translatable
job_title (string, nullable)
location (string, nullable)
is_active (boolean, default true)
created_at, updated_at
```

#### `assets`

```
id (uuid, PK)
asset_tag (string, unique)              -- auto-generated: LC-{YEAR}-{SEQUENCE}
serial_number (string, unique)
name (json)                             -- translatable: {"en":"...", "ar":"..."}
category_id (uuid, FK → categories)
manufacturer (string)
model (string)
status (enum: PURCHASED|AVAILABLE|ASSIGNED|IN_REPAIR|RETIRED|DISPOSED)
purchase_date (date, nullable)
purchase_cost (decimal 10,2, nullable)
warranty_expiry (date, nullable)
location (string, nullable)
notes (json, nullable)                  -- translatable
created_by (uuid, FK → users)
created_at, updated_at
```

Both a barcode and a QR code are generated for every asset on creation. No per-asset format preference — both are always available and both are always printable.

#### `assignments`

```
id (uuid, PK)
asset_id (uuid, FK → assets)
employee_id (uuid, FK → employees)      -- assigned TO an employee (not a user)
assigned_by (uuid, FK → users)          -- assigned BY a technician/admin
checked_out_at (timestamp)
checked_in_at (timestamp, nullable)
condition_out (enum: GOOD|FAIR|POOR)
condition_in (enum: GOOD|FAIR|POOR, nullable)
notes (text, nullable)
is_active (boolean)
-- partial unique index: UNIQUE (asset_id) WHERE is_active = true
```

#### `maintenance_logs`

```
id (uuid, PK)
asset_id (uuid, FK → assets)
technician_id (uuid, FK → users)
type (enum: REPAIR|UPGRADE|INSPECTION|WARRANTY_CLAIM)
status (enum: OPEN|IN_PROGRESS|COMPLETED|CANCELLED)
description (text)
scheduled_date (date, nullable)
completed_date (date, nullable)
cost (decimal 10,2, nullable)
vendor (string, nullable)
created_at, updated_at
```

#### `audit_logs` (insert-only — enforced via PostgreSQL RLS)

```
id (uuid, PK)
asset_id (uuid, FK → assets, nullable)
performed_by (uuid, FK → users)
action (string)                    -- REGISTERED, CHECKED_OUT, CHECKED_IN,
                                   -- STATUS_CHANGED, BULK_IMPORTED, LABEL_PRINTED
entity_type (string)
old_values (jsonb, nullable)
new_values (jsonb, nullable)
ip_address (string)
performed_at (timestamp)
```

---

## 6. Functional Requirements

### 6.1 Asset Registration

- Admin registers assets via a Filament form with bilingual name/notes fields
- Asset tag auto-generated on save: `LC-{YEAR}-{SEQUENCE}` (e.g. `LC-2025-00142`), manually overridable
- On successful save, the system generates both a Code 128 barcode and a QR code for the asset — both stored as SVG files in `/storage/app/labels/{asset_id}/barcode.svg` and `/storage/app/labels/{asset_id}/qrcode.svg`
- After saving, a toast notification appears with a "Print Labels" shortcut action

### 6.2 Asset Lifecycle State Machine

Valid transitions (enforced in `AssetService`, invalid transitions return a validation error):

```
PURCHASED  → AVAILABLE
AVAILABLE  → ASSIGNED | IN_REPAIR | RETIRED
ASSIGNED   → AVAILABLE (on check-in) | IN_REPAIR
IN_REPAIR  → AVAILABLE | RETIRED
RETIRED    → DISPOSED
```

Every transition is written to `audit_logs`.

### 6.3 Check-Out / Check-In

**Check-Out:**

1. Select asset (or scan barcode/QR to auto-fill)
2. Search and select employee from the directory (name, employee number, department shown in results — supports Arabic name search)
3. Select condition (Good / Fair / Poor)
4. Optional notes
5. Confirm → asset `ASSIGNED`, assignment record created, audit entry written

**Check-In:**

1. Select asset or scan
2. Condition on return + optional notes
3. Confirm → `checked_in_at` stamped, `is_active = false`, asset → `AVAILABLE`

### 6.4 Label Generation — Barcode & QR Code

Every asset always has both a barcode and a QR code. There is no "choose one" — both are generated at registration and both are always available for printing.

#### 6.4.1 Generation on Registration

```php
// AssetObserver::created()
LabelGenerationService::generateBoth($asset);

// LabelGenerationService
public function generateBoth(Asset $asset): void
{
    // Code 128 barcode (encodes asset_tag)
    $barcode = (new DNS1D())->getBarcodeSVG($asset->asset_tag, 'C128');
    Storage::put("labels/{$asset->id}/barcode.svg", $barcode);

    // QR code (encodes asset_tag)
    $qr = QrCode::format('svg')->size(200)->generate($asset->asset_tag);
    Storage::put("labels/{$asset->id}/qrcode.svg", $qr);
}
```

#### 6.4.2 Label Content

Each printed label includes:

- Asset name (in active locale)
- Asset tag (human-readable text)
- Both barcode and QR code images side by side (or stacked — configurable in System Settings)
- Optional: company logo (set in System Settings)

#### 6.4.3 Single Asset Label Print

Available from the Asset Detail page and the Asset List row action menu. Opens a **Print Label** modal with:

- Live label preview (barcode + QR side by side, asset tag, name)
- Label size selector: `38×13mm` | `62×29mm` | `100×50mm` | Custom
- Print method selector (see §6.4.5)

#### 6.4.4 Bulk Label Printing

Available from:

- Asset List → checkbox selection → "Print Labels" bulk action
- Dedicated **Print Labels** page under the Assets section

**Workflow:**

1. Select assets (checkbox table with "Select All Filtered" option)
2. Choose layout: labels per row (1 / 2 / 3 / 4) on A4 or Letter
3. Choose print method (see §6.4.5)
4. Preview rendered label sheet before printing/exporting

#### 6.4.5 Print Methods

| Method | Description | Best for |
|---|---|---|
| **Browser Print** | Renders a print-optimised Blade view, triggers `window.print()`. `@media print` CSS strips all UI chrome. Supports RTL label layout when Arabic locale is active. | Standard office printers |
| **Export as PDF** | Generates a PDF via dompdf with precise label dimensions. Downloadable file. | Saving label sheets, print queues |
| **Label Printer (ZPL)** | Generates ZPL code for Zebra/Dymo thermal label printers. Displayed in a code block for copy-paste into printer utility software. | Dedicated barcode label printers |

### 6.5 Barcode & QR Scanning (Input)

- **USB HID scanner:** Global keydown listener (Alpine.js) captures keystroke bursts ending in Enter. Works on any page.
- **Camera scanner:** `html5-qrcode` opens device camera in a modal overlay. Works on mobile and desktop browsers.
- **Lookup:** searches `asset_tag` OR `serial_number` (internal Laravel route)
  - **Found** → asset summary card with action buttons (Check Out / Check In / View)
  - **Not found** → "Not registered" with "+ Register" CTA pre-filled with scanned value

### 6.6 Bulk Import

- Accepts `.csv` and `.xlsx`
- Downloadable template (bilingual column headers: English required, Arabic optional)
- Row-level validation with inline error display
- Summary on completion: N registered · N skipped (duplicate) · N failed
- All rows written to `audit_logs` as `BULK_IMPORTED`
- On completion: redirects to filtered asset list showing the newly imported batch

### 6.7 Employee Directory

- Managed under a dedicated **Employees** section (Admin only)
- Fields: Employee Number, Full Name (EN), Full Name (AR), Email, Phone, Department, Job Title, Location, Active status
- Searchable by name (English or Arabic), employee number, department
- Bulk import via CSV
- When an employee is deactivated, their active assignments are flagged with a warning (not automatically checked in — requires manual resolution)

### 6.8 Audit Log

- Laravel Model Observers on `Asset`, `Assignment`, `MaintenanceLog` fire `AuditLogger` service
- Stored in insert-only `audit_logs` table
- Filament table: filterable by asset, user, action type, date range
- No edit or delete actions exposed — read-only resource

### 6.9 Reporting & Dashboard

**Dashboard widgets:**

- **KPI cards:** Total Assets · Assigned · Available · In Repair · Warranty Alerts
- **Asset status distribution** (donut chart)
- **Warranty expiring ≤ 90 days** (configurable threshold) — table widget
- **Recent audit activity** — timeline widget

**Report pages:**

- Warranty Expiry Report
- Aging Hardware Report (assets older than N years by category)
- Asset Distribution Report (by department, location, category)
- Employee Asset Report (all assets currently assigned to a given employee)

All reports: exportable to CSV and PDF, rendered in active locale.

---

## 7. Branding & Visual Design

### 7.1 Brand Identity

| Token | Value |
|---|---|
| App name | LC Assets |
| Full name | Luxury Code Assets |
| Primary color | `#FFC06A` (warm amber-gold) |
| Primary dark | `#E6A050` (hover/active states) |
| Primary light | `#FFF0D6` (subtle highlights, selected rows) |
| Neutral base | Slate (`slate-50` bg · `slate-900` text) |
| Font | Inter (LTR) · Noto Sans Arabic (RTL) |

### 7.2 Filament Theme Configuration

```php
// AppPanelProvider.php
->brandName('LC Assets')
->brandLogo(asset('images/lc-assets-logo.svg'))
->colors([
    'primary' => Color::hex('#FFC06A'),
])
->font('Inter')
->darkMode(false) // light mode default; dark mode opt-in per user
```

### 7.3 Design Principles

- Clean, professional enterprise aesthetic — information-dense without feeling cluttered
- Warm amber-gold primary conveys the "Luxury Code" brand without being decorative
- Generous whitespace, `rounded-lg` cards, `shadow-sm` elevation
- Status badges: pill shape, semantic color (green / blue / amber / red / slate)
- RTL layout mirrors correctly — navigation, tables, forms, modals all direction-aware

---

## 8. FilamentPHP Resource Map

| Filament Resource | Model | Key features |
|---|---|---|
| `AssetResource` | Asset | Bilingual form, status filter, actions: Check Out, Check In, Print Labels, View History |
| `CategoryResource` | Category | Bilingual CRUD |
| `EmployeeResource` | Employee | Bilingual directory, CSV import, active toggle |
| `UserResource` | User | CRUD + role assignment, active toggle |
| `AssignmentResource` | Assignment | Read-only history table |
| `MaintenanceLogResource` | MaintenanceLog | Full CRUD, status transitions |
| `AuditLogResource` | AuditLog | Read-only, advanced filters |

**Custom Filament Pages:**

- `DashboardPage` — KPI widgets + charts
- `BulkPrintPage` — asset selection, layout config, print/export
- `BulkImportPage` — file upload, validation preview, import execution
- `ReportsPage` — tabbed report views with export

**Custom Actions (on `AssetResource`):**

- `CheckOutAction` — slide-over form, employee search
- `CheckInAction` — confirmation modal (shown only when active assignment exists)
- `PrintLabelsAction` — print modal with preview (single asset)
- `StatusChangeAction` — modal showing only valid next states

---

## 9. System Settings

| Setting | Type | Default |
|---|---|---|
| Default locale | EN / AR | EN |
| Asset tag prefix | String | `LC` |
| Asset tag sequence padding | Integer | 5 digits |
| Company name | String | Luxury Code |
| Company logo | Image upload | — |
| Label layout | Barcode + QR side by side / stacked | Side by side |
| Default label size | Small / Medium / Large | Medium |
| Default page size (PDF) | A4 / Letter | A4 |
| Warranty warning threshold | Integer (days) | 90 |
| Numeral style | Western (1,2,3) / Arabic-Indic (١,٢,٣) | Western |
| Date format | DD/MM/YYYY / MM/DD/YYYY | DD/MM/YYYY |
| Dark mode | On / Off (per user) | Off |

---

## 10. Antigravity Generation Guide

Generate in this order to avoid dependency issues:

1. **Migrations** — all tables from §5 schema, in dependency order (`categories` → `employees` → `users` → `assets` → `assignments` → `maintenance_logs` → `audit_logs`)
2. **Models** — one at a time; include `$fillable`, casts, translatable fields (`spatie/laravel-translatable`), and relationships
3. **Filament Resources** — one at a time; specify columns, form fields, filters, and actions explicitly
4. **Service classes** — `AssetService` (lifecycle), `LabelGenerationService` (barcode + QR), `AuditLogger`
5. **Custom Actions** — `CheckOutAction`, `CheckInAction`, `PrintLabelsAction` as standalone Filament action classes
6. **Custom Pages** — `BulkPrintPage`, `BulkImportPage`, `ReportsPage`
7. **i18n** — generate `lang/en/*.php` and `lang/ar/*.php` translation files last, after all UI strings are finalised
