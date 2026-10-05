# Argil Leads code guide

This app is a normal Laravel project. The folders below are the ones you will edit. You can ignore `vendor` and `node_modules`.

## Folder structure

- `routes/web.php` — every page URL and the controller method it calls.
- `app/Http/Controllers` — what happens when a form is submitted or a page is opened.
- `app/Http/Requests` — the rules that check form input before it is saved.
- `app/Models` — one class per table, plus the relationships between them.
- `app/Imports` and `app/Exports` — the Excel upload and download.
- `database/migrations` — the table changes. Do not edit a migration that has already been run on a shared database. Add a new one.
- `database/seeders` — the starter departments, countries, statuses, users, and sample leads.
- `resources/views` — the HTML. `layouts` is the frame, `partials` are the reused pieces, and the other folders match the pages.
- `public/css/app.css` — the Argil colours. Change these when the brand changes.
- `resources/css/app.css` and `resources/js/app.js` — Bootstrap, built by Vite.
- `public/service-worker.js` and `public/manifest.webmanifest` — the installable app.

## How a request flows

1. The browser asks for a URL, for example `GET /leads`.
2. `routes/web.php` finds that URL and calls `LeadController@index`. The `auth` middleware sends guests to the login page first. The `active` middleware logs out a deactivated user.
3. The controller asks the `Lead` model for the rows this person is allowed to see, then loads the country, departments, and status in the same query.
4. The controller returns a Blade view. The view prints the data with `{{ }}`, which escapes it so typed text cannot become HTML.
5. A form posts back to another route. A Form Request checks the fields. If they fail, Laravel sends the person back and the view shows `@error` under each field. If they pass, the controller saves the model and redirects with a flash message.

Read a controller method from top to bottom. The permission check is the first line. The query or the save is next. The redirect is last.

## How to add a new field to a lead

Use "website" as the example.

1. Create a migration: `php artisan make:migration add_website_to_leads_table`. In `up()`, add `$table->string('website')->nullable();`. Run `php artisan migrate`.
2. Add `'website'` to the `$fillable` array on `app/Models/Lead.php`.
3. Add the rule in `app/Http/Requests/LeadRequest.php`, inside `rulesFor()`, for example `'website' => ['nullable', 'string', 'max:190']`. Add a plain sentence to `messageList()` if you want a custom error.
4. If the Excel file should include it, add the column in `app/Imports/LeadsImport.php` and in both export classes under `app/Exports`.
5. Add the input to `resources/views/partials/lead-form.blade.php` with `old('website', $lead->website)`, `is-invalid`, and `@error('website')`.
6. Show it on the card in `resources/views/partials/lead-card.blade.php` if people need to see it in the list.

The create page and the edit page share that one form partial, so you only add the field once.

## How roles work

The role is a string column on `users`: `super_admin`, `admin`, `manager`, `staff`, or `viewer`. There is no permissions table.

All of the yes/no questions live on the `User` model: `canCreateLeads()`, `canDeleteLeads()`, `canImportLeads()`, `canExportLeads()`, `canManageUsers()`, and the others next to them. Views use those methods to hide buttons. Controllers call `abort_unless(...)` so hiding a button is not the only protection.

`role` is not in `$fillable`. The user controller sets `$user->role = ...` on its own line. A tampered form cannot change a role through mass assignment.

Only a super admin can create or edit a super admin. You cannot deactivate yourself, change your own role, or demote or deactivate the last active super admin.

## How scoping works

A super admin can see every lead. So can anyone with All departments or All countries ticked.

Everyone else can see a lead when either of these is true:

- the lead belongs to at least one of their departments, in any country, or
- the lead's country is one of the countries on their user record, in any department.

That rule is `Lead::visibleTo($user)` in `app/Models/Lead.php`. The list, the edit page, delete, and the Excel download all start from that query. A lead outside that access returns a 404, not an empty edit form.

On the create form, a department right unlocks every country for those departments. A country right unlocks every department in those countries. Departments the user still cannot use are disabled and marked SCOPE LOCK. `LeadRequest` rejects a combination that matches neither side.

Super admins, and users with both All checkboxes, skip the limits. Their department and country checkboxes are cleared when they are saved, because those limits do not apply to them.
