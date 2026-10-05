# ROLE

You are a senior Laravel engineer who writes code that a FRESHER can read, understand and edit easily. Build a production-ready, mobile-first PWA called **Argil Leads** (a lead / contact book for Argil Group, argiltiles.com) inside my EXISTING Laravel project. Inspect the current project first (Laravel version, composer.json, existing migrations, User model) and extend it. Do not recreate the project and do not break existing migrations.

# BEGINNER-FRIENDLY CODE RULES (MOST IMPORTANT)

-   Simple beats clever. If there are two ways to write something, always choose the one a beginner understands faster.
-   Use only basic, well-known Laravel features: resource controllers, Eloquent models, relationships (belongsTo, hasMany, belongsToMany), Form Requests, Blade, routes, middleware, seeders.
-   Simple ORM only: `Lead::with('country', 'departments')->where(...)->paginate(10)`. No raw SQL, no repository pattern, no Service/Action classes, no API Resources, no DTOs, no traits for business logic, no custom collections, no events/listeners, no jobs/queues.
-   Keep logic in the controller methods (short and readable) or in one small model method. A controller method should read top to bottom like a story.
-   Filters are written as plain `if ($request->filled('country_id')) { $query->where(...); }` blocks, not dynamic or generic filter classes.
-   Basic Blade only: `@extends`, `@section`, `@include`, `@foreach`, `@if`, `@csrf`, `@method`, `old()`, `@error`. Use a few simple `@include` partials (navbar, flash messages, lead card, pagination). Avoid anonymous components, slots, Livewire and Alpine.
-   Bootstrap 5 only for the UI (grid, cards, forms, buttons, badges, modal, offcanvas, navbar). Write very little custom CSS (one `public/css/app.css` file for the brand colors). Write very little JavaScript, plain vanilla JS only, in small commented snippets.
-   Use clear, full names: `$lead`, `$leads`, `$selectedDepartmentIds`, `canDeleteLeads()`. No abbreviations, no one-letter variables.
-   Add a short plain-English comment above each controller method and each non-obvious block explaining WHAT and WHY.
-   One responsibility per file. Keep files short. Follow Laravel naming conventions exactly (LeadController, Lead model, leads table, `leads.index` route names).
-   No PHP 8 fancy features that confuse beginners (no match chains, no readonly classes, no attributes magic, no first-class callable syntax). Plain `if/else` and `foreach` are fine.
-   Run Laravel Pint for consistent formatting.
-   Add a `docs/CODE_GUIDE.md` that explains in simple words: folder structure, how a request flows (route -> controller -> model -> view), how to add a new field to a lead (migration, model, form, validation, view), how roles work, and how scoping works.

# SCOPE

IN SCOPE:

-   Authentication
-   Lead create / edit / delete / list
-   Department-wise and country-wise lead management with filters and search
-   Excel import and Excel download of leads
-   Simple role-based access (roles only, no permission tables)
-   Department and country access per user
-   PWA

# TECH STACK

-   Laravel already in the project, PHP 8.2+
-   Blade templates + Bootstrap 5 (installed with npm and built with Vite, or CDN if the project has no Vite) + vanilla JS
-   `Paginator::useBootstrapFive()` for pagination links
-   maatwebsite/excel for import/export (the ONLY extra package)
-   Laravel Form Requests for validation
-   Authentication: Laravel Breeze (Blade version) if no auth exists; restyle its views with Bootstrap
-   MySQL; basic PHPUnit feature tests
-   NO Tailwind, NO Alpine, NO Livewire, NO Inertia, NO spatie packages, NO queues

# DATA MODEL (new migrations, keep the existing starter ones)

-   users: add `role` string (default `staff`), `is_active` boolean (default true), `phone` nullable. No soft deletes (use is_active to deactivate).
-   departments: id, name (unique), is_active, timestamps. Seed: Export, Marketing, Sales, Accounts, Management, Domestic.
-   countries: id, name, iso2 (unique), dial_code, is_active. Seed a country list with ISO2 and dial codes (include UK, Australia, India, UAE, USA, Canada, Germany, France and other common markets).
-   department_user pivot: which departments a user can access.
-   country_user pivot: which countries a user can access (permitted territory).
-   lead_statuses: id, name, color, timestamps. Seed: Lead Received, Quartz Inquired, Sample Dispatched, Crate Dispatch Review.
-   leads: id, full_name, phone_country_code, phone_number, email, company_name, address (nullable), city, country_id (FK), lead_status_id (FK nullable), created_by (FK users), timestamps, soft deletes.
    -   Indexes on country_id, city, email.
-   department_lead pivot: a lead belongs to MANY departments.
-   Proper foreign keys with simple cascade rules.
-   Models define `$fillable` and simple relationships only.

# ROLES (simple `role` column on users, no permission tables)

Roles stored as plain strings: `super_admin`, `admin`, `manager`, `staff`, `viewer`.
Put the role rules in ONE place, as simple methods on the User model, for example:

```php
public function isSuperAdmin() { return $this->role === 'super_admin'; }
public function canManageUsers() { return in_array($this->role, ['super_admin', 'admin']); }
public function canCreateLeads() { return in_array($this->role, ['super_admin', 'admin', 'manager', 'staff']); }
public function canDeleteLeads() { return in_array($this->role, ['super_admin', 'admin', 'manager']); }
public function canImportLeads() { ... }
public function canExportLeads() { ... }
```

Role abilities:

-   **super_admin**: everything, ignores department/country limits.
-   **admin**: manage users, departments, statuses, and all leads in their departments/countries; import, export, delete.
-   **manager**: create, edit, delete, import, export leads in their departments/countries; can see the users list (read-only).
-   **staff**: create, edit, view, export leads in their departments/countries; no delete, no import.
-   **viewer**: view leads only.

Use these methods in Blade (`@if(auth()->user()->canDeleteLeads())`) and in controllers (`abort_unless(auth()->user()->canDeleteLeads(), 403);`). Add one tiny `CheckRole`-style middleware only if it keeps routes cleaner. Do not use Gates or Policies unless really needed.

Scoping rules (enforced on the SERVER, not only by hiding buttons):

-   Non-super-admins only see leads that belong to at least one of their departments AND have a country they are allowed.
-   Write this as one simple, well-commented method used by the controller (for example `Lead::visibleTo($user)` as a single easy query scope).
-   On the Create Lead form, departments the user cannot use are shown disabled with a "SCOPE LOCK" badge. The country dropdown lists only the user's countries, with a "Permitted territory: UK, Australia" note.
-   Validation must reject any department or country outside the user's access, even if the request is tampered with.
-   `role` must never be mass-assignable from a public form.

# PAGES (match the screenshots, built with Bootstrap)

Layout: top navbar with Argil logo and a user dropdown (profile, logout). Mobile: fixed bottom nav (Leads, Depts, Users, Settings). Desktop: same links in the navbar or a simple left sidebar. Hide links the role cannot use. Show flash messages (success/error) with Bootstrap alerts.

1. **Leads list** ("Contacts / Leads", subtitle "Manage and route all Argil international surface leads.")

    - Buttons: "+ Create Lead", "Import", "Download Excel" (shown by role).
    - One plain GET filter form: search box (name, phone, email, company, city), Department select, Country select, City input, Status select, Search and Clear buttons. Filters stay in the URL, and pagination keeps them with `->withQueryString()`.
    - Text "Showing X of Y leads" and Bootstrap pagination (10 per page).
    - Checkbox on each lead card plus "Select page" checkbox. A small vanilla JS snippet shows "2 Selected" and a Clear button. Bulk actions: Delete selected (if allowed) and Download selected.
    - Lead card (Bootstrap card): name, country badge, company, phone, email, address, department badges (peach), status dot + label, edit and delete icons (Bootstrap Icons or inline SVG). Delete uses a Bootstrap confirm modal.
    - Mobile: one card per row. Tablet: two columns. Desktop: three columns or a Bootstrap table. Use the Bootstrap grid (`row`, `col-12 col-md-6 col-lg-4`).
    - Friendly "No leads found" empty message.

2. **Create / Edit Lead** (ONE shared form partial used by both pages)

    - Card 1 "Contact Particulars": Full Name*, Contact Number* (dial-code select + number), Email*, Company / Business Name*.
    - Card 2 "Dispatch & Territory": Physical Address, City*, Country*.
    - Card 3 "Department Routing": department checkboxes styled as chips (at least one required), locked ones disabled with SCOPE LOCK badge.
    - Status select (shown on the edit page).
    - Create Lead / Update Lead and Cancel buttons. Disable the submit button after the first click (tiny JS) to avoid double submit.
    - Show `@error` messages under each field (`is-invalid` + `invalid-feedback`) and keep input with `old()`.

3. **Import / Export** (simple and synchronous)

    - Import: upload .xlsx, .xls or .csv (max 2 MB, max 1000 rows). Provide a "Download sample template" link. Validate each row with the same rules as the form (country by ISO2 code, departments by name separated with commas). Skip bad rows, import the good ones, and show a summary: imported count, skipped count, and a list of errors with row numbers.
    - Export: download the currently filtered leads (or the selected leads) as .xlsx, limited to leads the user is allowed to see.

4. **Departments** page (admin, super_admin): table with lead count, create, edit, activate/deactivate.

5. **Users** page (admin, super_admin manage; manager read-only): list, create, edit, activate/deactivate, choose role, departments and countries. Rules: cannot deactivate yourself; cannot remove or demote the last super_admin; only a super_admin can create or edit a super_admin.

6. **Settings**: my profile (name, email, change password) and lead statuses CRUD (admin, super_admin).

7. **Auth**: login, logout, forgot/reset password. Inactive users cannot log in. Limit login attempts (Laravel default throttle).

# VALIDATION (Form Requests, with simple custom messages)

-   full_name: required, string, min 2, max 120.
-   phone_country_code: required, must exist in `countries.dial_code`.
-   phone_number: required, digits only after removing spaces and dashes, 6 to 15 digits (simple regex, no extra package).
-   email: required, `email`, max 190, saved in lowercase, no duplicate for the same country (clear message).
-   company_name: required, string, max 150.
-   address: nullable, string, max 500.
-   city: required, string, max 100.
-   country_id: required, exists, and one of the user's allowed countries.
-   department_ids: required, array, min 1, each exists and is one of the user's departments.
-   lead_status_id: nullable, exists.
-   User form: name required, email required and unique, role must be one of the five roles (and one the acting user may assign), password min 8 with letters and numbers (required on create, optional on edit), department_ids and country_ids required unless the role is super_admin.
-   Trim inputs. Rely on Blade `{{ }}` escaping, never `{!! !!}` for user data.
-   Optional HTML5 attributes (`required`, `maxlength`, `type="email"`) for quick feedback, but the server is the source of truth.

# UI DESIGN (match the screenshots)

-   Override Bootstrap with CSS variables in `public/css/app.css` (or Vite-compiled Sass variables):
    -   Page background: soft cream #FDF6EF
    -   Cards: white, soft shadow, 12-16px radius
    -   Primary button: tan/clay #C4A58D with white text
    -   Navbar avatar / dark accent: deep brown #6B5442
    -   Selected chips and checkboxes: near-black #1F1F1F with white text
    -   Department badge: peach background #FDDCC0 with burnt-orange text
    -   Neutral badge: warm grey #EFE7DF
-   Font: Plus Jakarta Sans with a system fallback.
-   Responsive on mobile, tablet and desktop using only Bootstrap grid and utilities. No horizontal scroll at 320px. Buttons and checkboxes at least 44px tall on mobile. Include `viewport-fit=cover` and safe-area padding for the bottom nav.
-   Basic accessibility: labels for every input, visible focus states, sufficient contrast, `aria-label` on icon buttons.

# PWA (simple, hand-written)

-   `public/manifest.webmanifest`: name "Argil Leads", short_name "Argil", start_url "/leads", display "standalone", theme_color #6B5442, background_color #FDF6EF, icons 192x192 and 512x512 plus maskable (create placeholders and tell me where to replace them with the real Argil logo).
-   `public/service-worker.js`, hand-written and commented in simple words: cache only static files (CSS, JS, fonts, icons) and an `offline.html` page. Never cache logged-in pages or lead data. Show `offline.html` when the network is down.
-   Register the service worker in the main layout with a small script. Add the meta tags (theme-color, apple-touch-icon, manifest link).
-   Add an "Install App" button in Settings using `beforeinstallprompt`, plus a small iOS "Add to Home Screen" hint.
-   Works over HTTPS only.

# PRODUCTION-READINESS (keep it simple)

-   Prevent N+1 with `with('country', 'departments', 'status')`.
-   Authorization checked in EVERY controller method that changes or shows data; routes protected with `auth` middleware.
-   CSRF on all forms (`@csrf`), mass-assignment protection with `$fillable`, hashed passwords, validated file uploads, uploaded files not stored in the public folder.
-   Friendly custom 403, 404, 419 and 500 error pages using the same layout.
-   `.env.example` updated. README with setup steps: install, `.env`, migrate, seed, `npm run build`, `storage:link`, and a short production checklist (`config:cache`, `route:cache`, `view:cache`, HTTPS, `APP_DEBUG=false`).
-   Seeders: departments, countries, statuses, one Super Admin (email and password from `.env`), and optional demo leads (UK/AU samples like in the screenshots) and one demo user per role.
-   Tests (basic PHPUnit feature tests, easy to read): login works, inactive user cannot log in, a user cannot see or create leads outside their departments/countries, each role can or cannot create/delete/import/export as expected, lead validation errors, Excel import with a bad row.

# WORKING METHOD

Work in small phases. After each phase, list the files created or changed and give simple steps for me to test it before moving on:

1. Inspect project, install Bootstrap and maatwebsite/excel, base layout, navbar, bottom nav, flash messages, app.css theme.
2. Migrations, models, relationships, seeders.
3. Auth, `role` helper methods on User, middleware, scope logic.
4. Leads list with filters and search, then create/edit/delete, then bulk select.
5. Import / Export.
6. Departments, Users, Settings pages.
7. PWA (manifest, service worker, offline page, install button).
8. Tests, error pages, README, `docs/CODE_GUIDE.md`, production checklist.

Ask me only if something blocks progress; otherwise make sensible assumptions and list them at the end.
