# ADDA Inventory Hub — Technical Plan

> Source: raw requirements provided 2026-05-21.
> Codebase: Laravel 11 + Filament 3 + Spatie Permission + Filament Shield, SQLite (current), `spatie/laravel-translatable` JSON columns, UUID PKs.
> Scope: technical translation of business requirements with no scope changes. Flags raised inline.

---

## 0. Resolved Decisions

- **F-1 · Roles are dynamic, not an enum.** Drop `App\Enums\UserRole` from the casts/forms. Roles live entirely in `spatie/permission`'s `roles` table; the three required roles (`admin`, `asset_manager`, `user`) are seeded but new roles can be added at runtime through the Filament Shield UI. `users.role` column will be removed (or kept nullable as legacy) and authorization checked via `$user->hasRole(...)` / `$user->hasAnyRole(...)`. Data migration: backfill `model_has_roles` from the existing `users.role` string, rename `technician` → `asset_manager`, insert `user`.
- **F-2 · Hide `guard_name`.** Column stays (required by Spatie's unique index). UI: override `RoleResource::form()` / `table()` to drop the field and column; default to `web` via a `Role::saving` listener.
- **F-3 · Single `categories` model with functional subcategories.** Keep the existing self-referencing `categories` table. Implement two Filament navigation entries (`Categories` filtered `parent_id IS NULL`, `SubCategories` filtered `parent_id IS NOT NULL`). The SubCategories page exposes a `parent_id` Select restricted to top-level categories; the Categories page hides `parent_id`. `Asset` form binds `category_id` (parent) and `sub_category_id` (child whose `parent_id` matches the selected category — cascading select).
- **F-4 · Convert free-text fields to lookups.** `assets.manufacturer/model/location`, `employees.department/job_title/location` are migrated to FKs (`manufacturer_id`, `model_id`, `office_location_id`, `department_id`, `job_title_id`). Two-step migration: (a) add FK columns + backfill from existing strings (auto-create lookup rows on first sight); (b) drop legacy string columns once verified.
- **F-5 · Asset status becomes a lookup.** Refactor `assets.status` → `status_id` FK to `statuses`. Delete `App\Enums\AssetStatus`. The state-machine in `AssetService` is rewritten to operate on lookup `code` values (`purchased`, `available`, `assigned`, `in_repair`, `retired`, `disposed`). All `AssetStatus::FOO->value` references in `AssetResource`, `AssetStatsWidget`, `AssetsByStatusChart`, observer, and seeder are replaced.
- **F-6 · Assignment is the source of truth, Asset mirrors the latest.** `Assignment` writes `assignment_status_id`, `maintenance_status_id`, `maintenance_type_id`, `warranty_provider_id`, `return_reason_id`. `AssignmentObserver::saved()` mirrors the latest of each into the parent `Asset` row (`assigned_to_employee_id`, `assignment_status_id`, `assigned_date`, `return_date`, `return_reason_id`, …). Dashboard reads from `Asset` for "current state", from `Assignment` for "history".
- **F-7 · `assigned_by` stays a `users.id` FK.** No polymorphic morph.
- **F-8 · "Disposed" KPI** — uses `disposal_date IS NOT NULL` as authoritative.
- **F-9 / F-10 · Sheet + logos in `static/`.** Confirmed location: `static/Updated_Asset_Management.csv`, `static/ADDA-logo-dark.{svg,png}`, `static/ADDA-logo-light.{svg,png}`. See §1 and §10 for how they are wired. The brand files will be copied to `public/images/` during implementation; the CSV is parsed by `App\Imports\AssetsImport`.

## 0a. New Flags Surfaced from the CSV

- **F-12 · CSV quality.** The supplied sheet (`static/Updated_Asset_Management.csv`, 107 lines, 17 columns) has multi-line cells inside unquoted fields, mixed status values (`AVAILABLE`, `RESERVED`, `DAMAGED`, `PURSHAED` — typo of PURCHASED), free-text mixed into Category (`Admin`, `Accounts/HR`, `Design Team`, `Storage`, `Laptop`) and SubCategory (some rows put the model text under SubCategory). A trailing column header `@` exists with no data. Import must:
  1. Use a proper CSV parser (already via PhpSpreadsheet through `maatwebsite/excel`).
  2. Normalise statuses (`PURSHAED → purchased`, `RESERVED → assigned` or a new `reserved` status — needs confirmation).
  3. Auto-create lookup rows for unseen Category / SubCategory / Manufacturer / Model / Location values.
  4. Treat rows with empty `Asset Name – English` as failures (already enforced).
  5. Skip the trailing `@` column.
- **F-13 · CSV columns missing vs the asset schema in Requirement 12.** The sheet only has 17 of the ~45 Asset fields. All other fields (Department, Condition, Warranty Status, Criticality, Maintenance fields, Disposal fields, Assignment snapshot fields, Ownership, etc.) will be left null on import. Confirm acceptable.
- **F-14 · `Asset Status = RESERVED`.** Not in the original PRD enum (`PURCHASED|AVAILABLE|ASSIGNED|IN_REPAIR|RETIRED|DISPOSED`). Recommend adding `reserved` to the seeded `statuses` lookup. Confirm.
- **F-15 · `Purchased From Store?` column.** Maps to `supplier_id` (Stores/Vendors lookup) in Requirement 12, but the CSV column header phrasing implies a yes/no. Will treat the value as the supplier name and resolve to `suppliers` lookup. Confirm.

---

## 1. Branding & Login Page (Requirement 1)

**Goal:** Replace the "LC Assets" wordmark with the new ADDA Inventory Hub identity on the login screen.

**Brand source files** (already in repo at `static/`):

| Source file | Destination | Used in |
|---|---|---|
| `static/ADDA-logo-light.svg` | `public/images/adda-logo-light.svg` | Light theme · login centre · top bar |
| `static/ADDA-logo-dark.svg` | `public/images/adda-logo-dark.svg` | Dark theme · login centre · top bar |
| `static/ADDA-logo-light.png` | `public/images/adda-logo-light.png` | PNG fallback (4800×2000) |
| `static/ADDA-logo-dark.png` | `public/images/adda-logo-dark.png` | PNG fallback |
| existing `public/images/lca-logo.png` | re-used as the LC mark | login left · top bar right |

> The single `ADDA-logo-*.svg` file contains the full ADDA Inventory Hub identity. The plan currently treats it as one image; if a separate "ADDA group" mark is later needed for the right slot, that file will be added the same way.

- Edit `resources/views/filament/components/brand-logo.blade.php` to render:
  - **Row 1 (logos):** LC mark (left) · ADDA Inventory Hub mark (centre) · ADDA Inventory Hub mark or future group mark (right).
  - **Row 2 (text, centred):**
    - **H1:** "Adda Inventory Hub"
    - **Sublabel:** "ASSET MANAGEMENT PLATFORM"
    - **Description paragraph:** "An advanced platform for registering, tracking, auditing, and managing organizational assets across their full lifecycle — built for precision, security, and scale."
  - **Row 3 (footer):** "Part of the ADDA product line · Crafted by Ideacraft Technology"
- Use `<picture>` + `prefers-color-scheme` (or Filament dark-mode class) to swap between `adda-logo-light.svg` and `adda-logo-dark.svg`.
- Update `AdminPanelProvider::brandLogo()` height/responsive constraints if needed (currently `h-10` on the inner img); centre login is larger (~`h-24`), top bar stays small.
- Translate strings via `lang/en/auth.php` and `lang/ar/auth.php`.

**Affected files:** `resources/views/filament/components/brand-logo.blade.php`, `app/Providers/Filament/AdminPanelProvider.php`, `lang/en/auth.php`, `lang/ar/auth.php`, `public/images/adda-logo-*.*`.

---

## 2. App Banner / Top Bar (Requirement 2)

**Goal:** Top bar shows ADDA Inventory Hub mark on the left and the LC logo (with header action button) on the right.

- Replace the panel `brandLogo` registration with a top-bar layout component (Blade view or `PanelsRenderHook::TOPBAR_START` / `TOPBAR_END`):
  - Render `adda-inventory-hub.svg` at `TOPBAR_START`.
  - Render `lc-logo.png` next to the existing scanner/locale buttons via a new render hook (extend `resources/views/filament/components/header-actions.blade.php`).
- Keep the existing scanner + language switcher buttons; insert LC logo before them.

**Affected files:** `AdminPanelProvider.php`, `resources/views/filament/components/header-actions.blade.php` (or a new `top-bar-left.blade.php`).

---

## 3. Sidebar Information Architecture (Requirements 3 & 4)

**Goal:** Restructure the Filament navigation into role-scoped groups with collapsible lookup section.

### 3.1 Final navigation tree

```
Dashboard                                  [all roles]                navigationGroup = null

MY WORKSPACE                               [User role]
  ├─ My Assets                             AssetResource (scoped to current user’s assignments)
  └─ My Assignments History                AssignmentResource (scoped & read-only)

ASSET MANAGEMENT                           [Asset Manager + Admin]
  ├─ Assets                                AssetResource
  ├─ Assignments                           AssignmentResource
  ├─ Staff                                 EmployeeResource
  └─ Lookups (collapsed)
       ├─ Statuses
       ├─ Categories
       ├─ SubCategories
       ├─ Manufacturers
       ├─ Models
       ├─ Stores / Vendors
       ├─ Departments
       ├─ Job Titles
       ├─ Employment Types
       ├─ Office Locations
       ├─ Asset Conditions
       ├─ Warranty Statuses
       ├─ Ownership Types
       ├─ Criticality Levels
       ├─ Maintenance Statuses
       ├─ Disposal Methods
       ├─ Warranty Providers
       ├─ Asset Assignment Statuses
       ├─ Asset Return Reasons
       ├─ Maintenance Types
       └─ Disposal Reasons

SYSTEM ADMINISTRATION                      [Admin only]
  ├─ Users
  ├─ Roles
  ├─ Activity Logs
  └─ Settings
```

### 3.2 Implementation notes

- Use `getNavigationGroup()` + `getNavigationSort()` per resource. Filament 3 collapsible sub-groups are achieved with `Panel::navigationGroups([NavigationGroup::make('Lookups')->collapsed()])` registered in `AdminPanelProvider`.
- The "Dashboard" entry must render as a top-level link (no group). The existing `App\Filament\Pages\Dashboard` already does this; ensure `navigationSort = 0`.
- "My Assets" and "My Assignments History" are new `Page` classes (or scoped `Resource` variants) that filter on `Assignment::where('employee_id', $currentEmployeeId)` where `currentEmployeeId` resolves via `users.employee_id` (see §6 for the new FK).
- Role-based visibility uses `Resource::shouldRegisterNavigation()` returning `auth()->user()->hasAnyRole(...)`.

**Affected files:** every `App\Filament\Resources\*Resource.php` (set group + sort), `AdminPanelProvider.php` (register navigation groups), new pages/resources for the 18+ lookups, two new "My …" pages/resources.

---

## 4. Role Model Cleanup (Requirements 5, 6, 7)

### 4.1 Drop the enum, go fully dynamic (F-1)

- **Delete** `App\Enums\UserRole`. Authorization moves entirely to Spatie's `roles` + `model_has_roles` tables.
- Remove the `role` cast from `App\Models\User::casts()` and the `'role'` entry from `$fillable`.
- Migration `xxxx_dynamicise_user_roles.php`:
  1. Ensure the three required `roles` rows exist (`admin`, `asset_manager`, `user`) with `guard_name = 'web'`.
  2. For each existing user, read the legacy `users.role` string, normalise (`technician` → `asset_manager`), then attach via `model_has_roles` if not already attached.
  3. Drop the `users.role` column (kept nullable for one release if a rollback window is needed).
- `UserResource` form: replace the `Select::make('role')` with `Select::make('roles')->multiple()->relationship('roles', 'name')->preload()` so any role defined in the DB shows up automatically — new roles created in Filament Shield will appear without code changes.
- `UserResource` table: replace the `role` column with `TextColumn::make('roles.name')->badge()` (multi-badge).
- Filters: `SelectFilter::make('roles')->relationship('roles', 'name')->multiple()`.
- `RolesPermissionsSeeder` (idempotent): re-create or re-sync the three baseline roles with their permission sets every deploy. Custom roles added by admins are left untouched.
- `User::canAccessPanel()` keeps returning `true` (current behaviour) — access control is per-resource via policies that check `hasRole`.

### 4.2 Permission mapping (Requirement 6)

Map Filament Shield permissions per role. Permissions are auto-generated by Shield as `view_any_<resource>`, `view_<resource>`, `create_<resource>`, `update_<resource>`, `delete_<resource>`, etc.

| Capability | admin | asset_manager | user |
|---|---|---|---|
| Dashboard widgets | ✅ | ✅ | ✅ |
| Assets — view_any / create / update / delete | ✅ | ✅ (no delete) | ❌ |
| Assets — view (own assignments only) | ✅ | ✅ | ✅ (scoped query) |
| Assignments — full | ✅ | ✅ | view-own |
| Staff / Employees | ✅ | ✅ | ❌ |
| Lookups (all 21) | ✅ | ✅ | ❌ |
| Users | ✅ | ❌ | ❌ |
| Roles | ✅ | ❌ | ❌ |
| Activity Logs | ✅ | ❌ | ❌ |
| Settings (Filament page) | ✅ | ❌ | ❌ |

- Implement scoping for "view-own" via `getEloquentQuery()` overrides on `AssetResource` and `AssignmentResource` (`->whereHas('activeAssignment', fn ($q) => $q->where('employee_id', $user->employee_id))`).
- Create a `RolesPermissionsSeeder` that idempotently syncs permissions per role on every deploy.
- "Add views in the left as privileges" → Each navigation entry’s visibility is gated by `Resource::canViewAny()` which already calls the policy; verify all `Policies/*.php` honour the three-role matrix (current policies assume `admin/technician`). Update policy classes accordingly.

### 4.3 Roles UI cleanup (Requirement 7 / F-2)

- In `App\Filament\Resources\Shield\RoleResource` (extends Shield’s), override `form()` and `table()` to hide the `guard_name` field/column.
- Default `guard_name = 'web'` via a model observer on `Spatie\Permission\Models\Role` (or `Role::saving` listener registered in `AppServiceProvider`).
- No DB schema change (column still required by Spatie).

**Affected files:** `app/Enums/UserRole.php` (deleted), `database/migrations/xxxx_dynamicise_user_roles.php`, `database/seeders/RolesPermissionsSeeder.php`, `app/Models/User.php`, `app/Filament/Resources/UserResource.php`, `app/Filament/Resources/Shield/RoleResource.php`, all `app/Policies/*.php`, `AppServiceProvider.php`.

---

## 5. Activity Log Filters (Requirement 8)

- Extend `App\Filament\Resources\AuditLogResource::table()` filters with a date-range filter on `performed_at`:
  - Single `Filament\Tables\Filters\Filter` with two `DatePicker`s **and** a `Select` with presets `last_day | last_week | last_month | last_year | custom`.
  - When a preset is chosen, hide the from/to inputs and translate the preset to `performed_at >= now()->sub*`.
  - Custom mode shows both `from` and `to` inputs.
- Default sort already `performed_at desc` — keep.

**Affected files:** `app/Filament/Resources/AuditLogResource.php`.

---

## 6. Employees / Staff Module (Requirements 9 & 11)

### 6.1 Schema migration

`xxxx_extend_employees_table.php`:

| Column | Type | Notes |
|---|---|---|
| `photo_path` | string nullable | Spatie media or simple `FileUpload` to `storage/app/public/employees`. |
| `phone` | exists | keep |
| `department_id` | foreignUuid → departments | replace JSON `department` |
| `job_title_id` | foreignUuid → job_titles | replace string `job_title` |
| `employment_type_id` | foreignUuid → employment_types | new |
| `office_location_id` | foreignUuid → office_locations | replace string `location` |
| `line_manager_id` | foreignUuid → employees (self) nullable | new |
| `status_id` | foreignUuid → statuses nullable | "Staff Status" |
| `joining_date` | date nullable | new |
| `leaving_date` | date nullable | new |
| `emergency_contact_name` | string nullable | new |
| `emergency_contact_phone` | string nullable | new |
| `notes` | text nullable | new |
| `created_at` / `updated_at` | already present | keep |

Backfill: read existing `department` JSON / `job_title` / `location` strings, upsert into the matching lookup table, set FK, then drop the old columns in a follow-up migration after backfill verified.

### 6.2 Model updates

- `App\Models\Employee`:
  - Add `belongsTo` relations: `department`, `jobTitle`, `employmentType`, `officeLocation`, `lineManager` (self), `status`.
  - Remove `HasTranslations` for `department` (now FK).
  - Cast `joining_date` / `leaving_date` as `date`.
- Create lookup models in `App\Models\Lookups\*`.

### 6.3 Filament Resource

Rebuild `App\Filament\Resources\EmployeeResource`:

- Form: full field set above using `Select::relationship()` for each lookup, `FileUpload::make('photo_path')->image()`, two date pickers, toggle `is_active`.
- Table: keep current columns, add `photo` thumbnail, `department.name`, `office_location.name`, `status.name`, `is_active` icon.
- Filters: `SelectFilter` for `department_id`, `job_title_id`, `employment_type_id`, `office_location_id`, `status_id`, `TernaryFilter` for `is_active`, `Filter` for date-range on `joining_date`.
- If column count is too dense, split: keep summary table but enable `ViewAction` opening a structured `Infolist` page (separate `ViewEmployee` page under `Pages/`) — already mentioned in Requirement 9.
- Add `getPages` with `index`, `create`, `view`, `edit`.

### 6.4 Link `users.employee_id`

Add `employee_id` UUID FK on `users` (nullable, unique) so "User" role can be tied to one employee for the "My Assets" view (§3.1).

**Affected files:** new migration, `app/Models/Employee.php`, `app/Models/User.php`, new lookup models, `app/Filament/Resources/EmployeeResource.php` + its `Pages/`, seeder for default lookup values.

---

## 7. Asset Module Schema (Requirement 12)

### 7.1 Migration `xxxx_extend_assets_table.php`

New columns (all FKs nullable except where noted):

| Column | Type | Replaces / Source |
|---|---|---|
| `name` JSON | keep (translatable EN/AR) | existing |
| `serial_number` | unique string | existing |
| `category_id` | exists | existing |
| `sub_category_id` | foreignUuid → categories | new (constrained `parent_id IS NOT NULL`) |
| `manufacturer_id` | foreignUuid → manufacturers | replace string |
| `model_id` | foreignUuid → models | replace string |
| `manufacturer_year` | smallint nullable | new |
| `status_id` | foreignUuid → statuses | **replaces** legacy `status` enum string (F-5) |
| `purchase_date` | exists | existing |
| `purchase_cost` | exists | existing |
| `warranty_expiry` | exists | existing |
| `office_location_id` | foreignUuid → office_locations | replace string `location` |
| `notes` JSON | existing (translatable) | existing |
| `asset_tag` | unique | existing (auto-generated) |
| `qr_code` | string nullable | persisted path to generated SVG (already stored on disk; can be left null) |
| `description` JSON | translatable EN/AR | new |
| `is_active` | boolean default true | new |
| `department_id` | foreignUuid → departments | new |
| `condition_id` | foreignUuid → asset_conditions | new |
| `supplier_id` | foreignUuid → suppliers (stores/vendors) | new |
| `invoice_number` | string nullable | new |
| `purchase_order_number` | string nullable | new |
| `warranty_status_id` | foreignUuid → warranty_statuses | new |
| `image_path` | string nullable | new (Asset Image) |
| `criticality_id` | foreignUuid → criticality_levels | new |
| `last_maintenance_date` | date nullable | new |
| `next_maintenance_date` | date nullable | new |
| `assigned_to_employee_id` | foreignUuid → employees nullable | denormalised current holder (F-6 mirror) |
| `assignment_status_id` | foreignUuid → asset_assignment_statuses | new |
| `assigned_date` | date nullable | new |
| `return_date` | date nullable | new |
| `return_reason_id` | foreignUuid → asset_return_reasons nullable | new |
| `warranty_provider_id` | foreignUuid → warranty_providers nullable | new |
| `ownership_type_id` | foreignUuid → ownership_types | new |
| `maintenance_status_id` | foreignUuid → maintenance_statuses nullable | new |
| `maintenance_type_id` | foreignUuid → maintenance_types nullable | new |
| `disposal_date` | date nullable | new |
| `disposal_method_id` | foreignUuid → disposal_methods nullable | new |
| `disposal_reason_id` | foreignUuid → disposal_reasons nullable | new |

Indexes: composite indexes on `(is_active, status_id)`, `(warranty_expiry)`, `(next_maintenance_date)` for dashboard queries.

### 7.2 Model & Resource updates

- `App\Models\Asset`: add `belongsTo` for every new FK; remove the `status` enum cast; widen `$casts`; add `description` to `$translatable`.
- **Delete** `App\Enums\AssetStatus`. Rewrite `AssetService` so all transitions operate on `status->code` against the seeded `statuses` lookup (`purchased`, `available`, `assigned`, `in_repair`, `retired`, `disposed`, `reserved`). Badge colour helper moves into `Status` model (`getColour()` based on `code`).
- `AssetResource::form()`: regroup into tabs/sections (e.g. *Identification*, *Specs*, *Financial & Warranty*, *Location*, *Assignment*, *Maintenance*, *Disposal*, *Notes EN/AR*).
- `AssetResource::table()`: only show 8–10 essential columns by default with `ToggleColumn`/`toggleableHiddenByDefault()` for the rest.
- `AssetResource::infolist()`: extend with new sections for Maintenance / Disposal / Warranty.
- Update existing Check Out / Check In actions to write to the new assignment-mirror fields on `assets`.

### 7.3 Observer / auto fields

- `AssetObserver::created()` already triggers `LabelGenerationService::generateBoth()`; ensure new `qr_code` column captures the storage path.
- New `creating` hook: if `assignment_status_id` is null, default to the seeded "Available" row.

**Affected files:** `database/migrations/xxxx_extend_assets_table.php`, `app/Models/Asset.php`, `app/Enums/AssetStatus.php` (deleted), `app/Services/AssetService.php`, `app/Filament/Resources/AssetResource.php` (large rewrite), `app/Filament/Widgets/AssetStatsWidget.php` / `AssetsByStatusChart.php` / `AssetsByConditionChart.php` (replace enum filters with FK joins), `app/Observers/AssetObserver.php`, `app/Imports/AssetsImport.php` (status normalisation), lookup model classes, seeder updates.

---

## 8. Assignment Module Schema (Requirement 13)

### 8.1 Migration `xxxx_extend_assignments_table.php`

| Column | Type | Notes |
|---|---|---|
| `assignment_number` | string unique | auto-generated `ASG-{YEAR}-{SEQ}` via observer |
| `asset_id` | exists | keep |
| `employee_id` | exists | keep |
| `department_id` | foreignUuid nullable | snapshot from employee |
| `office_location_id` | foreignUuid nullable | snapshot from employee |
| `assigned_by` | exists (users) | keep (F-7) |
| `checked_out_at` | exists | keep |
| `checked_in_at` | exists | keep |
| `condition_out_id` | foreignUuid → asset_conditions | replace enum |
| `condition_in_id` | foreignUuid → asset_conditions nullable | replace enum |
| `assignment_status_id` | foreignUuid → asset_assignment_statuses | new |
| `return_reason_id` | foreignUuid → asset_return_reasons nullable | new |
| `maintenance_status_id` | foreignUuid → maintenance_statuses nullable | new |
| `maintenance_type_id` | foreignUuid → maintenance_types nullable | new |
| `warranty_provider_id` | foreignUuid → warranty_providers nullable | new |
| `is_active` | exists | keep |
| `notes` JSON translatable EN/AR | replace `notes text` | will require JSON cast |
| `attachment_path` | string nullable | new (handover form) |
| `created_at` / `updated_at` | exist | keep |

Backfill: drop `condition_out` / `condition_in` enum strings after seeding `asset_conditions` rows {GOOD, FAIR, POOR} and re-mapping FKs.

### 8.2 Code updates

- `App\Models\Assignment`: relations for every new FK, `HasTranslations` for `notes`.
- Filament `AssignmentResource`: enable full CRUD if the role allows (currently `canCreate = false`); add the snapshot columns; `FileUpload::make('attachment_path')` with `->disk('public')->directory('assignments')`.
- `AssignmentObserver`: generate `assignment_number`; mirror `assignment_status_id`, `assigned_to_employee_id`, `assigned_date`, `return_date`, `return_reason_id` back to the parent asset (F-6 write-direction).

**Affected files:** new migration, `app/Models/Assignment.php`, `app/Filament/Resources/AssignmentResource.php`, `app/Observers/AssignmentObserver.php`.

---

## 9. Lookup Tables Layer (Requirement 3 + 11 + 12 + 13)

Single shared pattern for all 21 lookups.

### 9.1 Common schema

For every lookup table (`statuses`, `categories` (existing), `manufacturers`, `models`, `suppliers`, `departments`, `job_titles`, `employment_types`, `office_locations`, `asset_conditions`, `warranty_statuses`, `ownership_types`, `criticality_levels`, `maintenance_statuses`, `disposal_methods`, `warranty_providers`, `asset_assignment_statuses`, `asset_return_reasons`, `maintenance_types`, `disposal_reasons`):

```php
$table->uuid('id')->primary();
$table->string('code')->unique();      // machine key e.g. "available"
$table->json('name');                  // translatable EN/AR
$table->json('description')->nullable();
$table->integer('sort_order')->default(0);
$table->boolean('is_active')->default(true);
$table->timestamps();
```

- `models` adds `manufacturer_id` FK.
- `categories` already exists with `parent_id`; reuse for both Categories and SubCategories (F-3).
- `statuses` is a generic table used for both Asset Status and Staff Status; add a `scope` column (`asset|staff`) to keep filters clean.

### 9.2 Code

- Trait `App\Concerns\IsLookup` for the shared `casts`, translatable, `getTranslatedName()` helper.
- Base abstract `App\Filament\Resources\LookupResource` extended by each per-lookup resource. Reduces duplication.
- Each concrete resource sets `navigationGroup = 'Lookups'` and `navigationSort` so the order in §3.1 is preserved.
- Seeder `LookupsSeeder` populates default rows (statuses, conditions, criticalities, common ownership / maintenance / disposal vocabularies).

**Affected files:** 20+ new migrations (one per new table), 20+ new models, 20+ new Filament resources (each ~30 lines via the base class), one seeder.

---

## 10. Excel Upload (Requirement 10)

Source file: `static/Updated_Asset_Management.csv` (107 rows, 17 columns + trailing `@` placeholder).

### 10.1 Column → Schema Map

| CSV column | Asset column | Resolution |
|---|---|---|
| Asset Name – English | `name->en` | required, trim |
| Asset Name – Arabic | `name->ar` | trim, nullable |
| Serial Number | `serial_number` | unique; skip duplicates (existing behaviour) |
| Asset Category | `category_id` | resolve by `name` (EN or AR) on `categories` where `parent_id IS NULL`; auto-create row if not found |
| Asset Sub Category | `sub_category_id` | resolve on `categories` where `parent_id = category_id`; auto-create with the resolved parent |
| Manufacturer / Brand | `manufacturer_id` | resolve / auto-create on `manufacturers` |
| Model | `model_id` | resolve / auto-create on `models`, linked to manufacturer_id |
| Manufacturer Year | `manufacturer_year` | int (4-digit), null if invalid |
| Purchased From Store? | `supplier_id` | resolve / auto-create on `suppliers` (F-15) |
| Asset Status | `status_id` | normalise (`PURSHAED` → `purchased`, `RESERVED` → `reserved`, `AVAILABLE` → `available`, `DAMAGED` → `damaged` — add `damaged` to seeded statuses if missing), then look up |
| Purchase Date | `purchase_date` | parse multiple formats (existing helper) |
| Purchase Cost | `purchase_cost` | strip currency / commas |
| Warranty Expiry Date | `warranty_expiry` | parse |
| Asset Location | `office_location_id` | resolve / auto-create on `office_locations` |
| Notes – English | `notes->en` | trim |
| Notes – Arabic | `notes->ar` | trim |
| `@` (trailing) | — | ignored |

### 10.2 Code changes

1. Rewrite `App\Imports\AssetsImport`:
   - Replace the `categoryCache` array with a `Lookups\LookupResolver` service that supports `resolveOrCreate($table, $name)` for every lookup used above. Caches per import run.
   - Add header aliases for the en-dash characters (`Name – English`) — `WithHeadingRow` lowercases & snake-cases, so map manually if needed.
   - Update `rules()` so only `name_en` is required; status normalisation done inside `model()`.
   - Continue to use `AssetObserver` for `asset_tag` generation + label files.
2. Add `App\Imports\EmployeesImport` modelled on the same pattern for the eventual staff sheet.
3. Build `App\Filament\Pages\BulkImportPage` (or use existing if any) with file upload + summary modal (`N imported · N skipped · N failed`) and an "Audit log" link.
4. Audit-log each batch as `BULK_IMPORTED` with `new_values = { source_filename, total, imported, skipped, failed }`.

### 10.3 Pre-flight (one-shot)

Before running the import in production:

1. Run all schema migrations + lookup seeders (§7, §9).
2. Confirm CSV status mapping with stakeholder (F-12, F-14).
3. Dry-run on staging; review the per-row failure report.
4. Import to production with `--queue` (chunk size 200 already configured).

---

## 11. Dashboard (Dashboard section of requirements)

Replace contents of `App\Filament\Pages\Dashboard::getWidgets()` with the widget set below. Build widgets under `app/Filament/Widgets/`.

### 11.1 KPI row — `AssetKpiWidget` (StatsOverviewWidget)

| Card | Query |
|---|---|
| Total Assets | `Asset::where('is_active', true)->count()` |
| Total Asset Value | `Asset::where('is_active', true)->sum('purchase_cost')` (format as currency) |
| Available | `Asset::whereHas('assignmentStatus', fn($q) => $q->where('code','available'))->count()` |
| Assigned | `… code='assigned'` |
| In Maintenance | `Asset::whereHas('maintenanceStatus', fn($q) => $q->where('code','in_progress'))->count()` |
| Disposed | `Asset::whereNotNull('disposal_date')->count()` |

### 11.2 Alerts widget — `AssetAlertsWidget`

Render as 7 cards (or a table). Queries:

| Alert | Predicate |
|---|---|
| Warranty expiring soon | `warranty_expiry BETWEEN today AND today+30/60/90` (filter chips) |
| Out of warranty | `warranty_status.code = 'expired'` OR `warranty_expiry < today` |
| Maintenance due soon | `next_maintenance_date BETWEEN today AND today+30` |
| Out of maintenance / overdue | `next_maintenance_date < today` |
| Long-overdue returns | `assignments.checked_out_at < today-365 AND checked_in_at IS NULL` |
| Idle assets | `assignment_status.code='available' AND NOT EXISTS assignment in last 90 days` |
| Critical assets in poor condition | `criticality.code IN ('high','critical') AND condition.code IN ('poor','fair')` |

Each card links to the Assets list with the matching pre-applied filter.

### 11.3 Age & lifecycle — `AssetAgeWidget`

Buckets via `purchase_date` arithmetic (see spec). Render as horizontal bar (Apex chart already installed via `leandrocfe/filament-apex-charts`).

### 11.4 Distribution charts (5 widgets)

- `AssetsByCategoryChart` — donut, top 6 + "Other"
- `AssetsByDepartmentChart` — horizontal bar
- `AssetsByOfficeLocationChart` — horizontal bar
- `AssetsByConditionChart` — stacked bar (Excellent → Poor)
- `AssetsByCriticalityChart` — stacked bar (Critical → Low)

Re-use existing `AssetsByCategoryChart` / `AssetsByConditionChart` widget shells, update queries to use the new FK relations.

### 11.5 People widget — `PeopleStatsWidget`

- Total Staff (active)
- Total Active Assignments
- Top-10 staff by `activeAssignments_count` + summed `purchase_cost` of held assets — Filament `TableWidget`.
- Staff with zero assignments — count only.

### 11.6 Recent activity feed — extend `RecentAuditLogsWidget`

- Show last 15 `audit_logs` events with action-specific icon/colour and a link to the related asset/assignment.

**Affected files:** `app/Filament/Pages/Dashboard.php`, several new widgets under `app/Filament/Widgets/`.

---

## 12. Seeders & Data Migration Order

1. `RolesPermissionsSeeder` — admin / asset_manager / user with permission sync.
2. `LookupsSeeder` — seeds all 21 lookups with default values (incl. statuses matching `AssetStatus` enum codes for F-5).
3. Data migration script `xxxx_backfill_lookup_fks.php` — runs after lookup tables are populated; converts existing free-text values (`manufacturer`, `model`, `location`, `department`, `job_title`) into lookup rows and FK references.
4. `xxxx_drop_legacy_string_columns.php` — runs only after backfill verified (two-step migration so rollback is safe).
5. Re-run `DatabaseSeeder` for fresh installs.

---

## 13. Policy & Authorization Audit

For each resource, verify the policy method matrix matches §4.2. Files to update:

- `AssetPolicy`, `AssignmentPolicy`, `CategoryPolicy`, `EmployeePolicy`, `MaintenanceLogPolicy`, `AuditLogPolicy`, `UserPolicy`, `RolePolicy`.
- New policies for every lookup model — simplest path: extend a shared `Policies\LookupPolicy` granting full access to `admin` + `asset_manager`, none to `user`.

---

## 14. i18n Strings

All new UI labels (lookup names, navigation groups, dashboard cards, brand text) must be added to `lang/en/*.php` and `lang/ar/*.php`. Group by concern: `nav.php`, `dashboard.php`, `assets.php`, `employees.php`, `lookups.php`.

---

## 15. Implementation Sequencing

1. Resolve flags F-1 through F-11 with stakeholder.
2. Role/permission cleanup (§4) — unblocks everything else, low surface.
3. Lookup tables + base resource (§9).
4. Employees extension (§6) — depends on lookups.
5. Assets extension (§7) — depends on lookups + employees.
6. Assignments extension (§8) — depends on assets + employees.
7. Sidebar restructure + branding (§§1–3).
8. Activity log filters (§5).
9. Dashboard rebuild (§11).
10. Excel import wiring (§10) once sheet arrives.
11. End-to-end QA in both `en` and `ar` locales.

---

## 16. Testing Plan (high-level)

- Migration test: fresh `migrate --seed` produces working DB with 3 roles, 21 lookups, sample assets/employees.
- Role test: log in as each of three roles; verify visible nav matches §4.2.
- CRUD smoke test on Assets / Employees / Assignments through the new lookup pickers.
- Dashboard widgets render with seeded data and respect filter clicks.
- Bulk import dry-run with the provided sheet.
- RTL render of login screen, top bar, sidebar groups, dashboard.
