# Argil Leads

A mobile-first lead book for Argil Group. It is a Laravel app with Bootstrap, simple roles, and Excel import/export.

## Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Copy the environment file and generate an app key if you have not already:

```bash
copy .env.example .env
php artisan key:generate
```

3. Create a MySQL database named `argil_leads` (or the name in `DB_DATABASE`). The app user in `.env.example` is `root` with an empty password. Change that for a real server.

4. Set `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` in `.env` before you seed. The example password is only for local use.

5. Create the tables and the starter data:

```bash
php artisan migrate --seed
```

6. Install the front-end packages and build Bootstrap:

```bash
npm install
npm run build
```

7. Create the public storage link (uploads are not stored in `public`, but the link is part of a normal Laravel setup):

```bash
php artisan storage:link
```

8. Start the app:

```bash
php artisan serve
```

Open `http://127.0.0.1:8000` and log in.

## Local logins

After seeding:

| Who | Email | Password |
| --- | --- | --- |
| Super admin | the `SUPER_ADMIN_EMAIL` in `.env` | the `SUPER_ADMIN_PASSWORD` in `.env` |
| Admin | admin@argil.test | Password123 |
| Manager | manager@argil.test | Password123 |
| Staff | staff@argil.test | Password123 |
| Viewer | viewer@argil.test | Password123 |

Change these passwords before anyone else uses the app.

## Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database. They do not touch MySQL.

## Replace the placeholder logo

The brown "A" mark is a stand-in. Replace these files with the real Argil logo, then change `STATIC_CACHE` in `public/service-worker.js` so installed phones download the new files:

- `public/images/argil-logo.svg`
- `public/icons/icon-192.png`
- `public/icons/icon-512.png`
- `public/icons/icon-maskable-192.png`
- `public/icons/icon-maskable-512.png`

The maskable icons need extra padding around the mark so Android can crop them into a circle.

## Production checklist

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` set to the real HTTPS address
- A strong `SUPER_ADMIN_PASSWORD`, and new passwords for the demo users (or delete the demo users)
- HTTPS. The install button and service worker only work in a secure browser. `localhost` is the only HTTP exception.
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `npm run build` on the server, or copy the built `public/build` folder
- MySQL backups

Mail uses the `MAIL_*` values in `.env`. Password reset emails follow that mailer. `log` writes them to the Laravel log, which is fine locally and not fine in production.

## Where to read the code

See `docs/CODE_GUIDE.md` for the folder map, how a request moves, how to add a lead field, and how roles and territory limits work.
