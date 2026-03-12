# Manual testing: fresh install (TUI and web)

Use this when you want to test the app as if you had just cloned the repo and are running the installer from scratch.

---

## Part 1: Reset to “fresh clone” state

Run this from the repo root so all old data and caches are cleared:

```bash
./scripts/fresh-clone-reset.sh
```

What it does:

- Writes a minimal `.env` (only `APP_ENV=local` and `APP_KEY`).
- Deletes `database/database.sqlite` so there is no existing database.
- Clears Laravel caches (config, cache, routes, views, events).
- Clears `storage/framework` (cache, sessions, views) and log files.
- Clears `bootstrap/cache`.
- Removes `storage/app/.install-progress.json` so the CLI installer can start from scratch.

After this, the app is in a clean state with no DB and no install progress.

---

## Part 2: Start the app

From the repo root:

```bash
composer run dev
```

Or, if you use Laravel Herd, the site is already served (e.g. `https://laravel-starter-kit-inertia-react.test`). Ensure the frontend is built:

```bash
bun run dev
# or: npm run dev
```

Keep this running in a separate terminal for the steps below.

---

## Part 3a: Test the **web installer** (`/install`)

1. **Open the installer**  
   In the browser go to:  
   `http://localhost:8000/install`  
   (or your Herd URL, e.g. `https://laravel-starter-kit-inertia-react.test/install`).

2. **Database**  
   - If the first step is “Database”, choose **SQLite** (or MySQL if you prefer) and submit.  
   - If the first step is “Run migrations”, use the button to run migrations.

3. **Admin user**  
   - Enter name, email, and password for the super-admin.  
   - Submit.

4. **App**  
   - Set site name, URL, timezone, locale.  
   - Submit.

5. **Optional steps**  
   - Go through each optional step (tenancy, infrastructure, mail, search, AI, social, storage, broadcasting, SEO, monitoring) or use **Skip** where you don’t want to configure anything.

6. **Demo data**  
   - Choose “No demo data” for a minimal app, or select modules (users, organizations, billing, content, etc.) and run the seeders.

7. **Finish**  
   - You should be redirected to `/admin` (or the dashboard).  
   - Confirm you can log in with the admin account and that the UI loads.

8. **Installer locked**  
   - Visit `/install` again. You should be redirected to `/admin` (EnsureNotInstalled).

**Note:** The web installer and express install are only available when `APP_ENV=local` (or `testing`). In production or staging, install routes return 404.

### Express install with options

On the **Database** step you can use **Express install** (SQLite + defaults) or **Express with options**:

- **Preset**: None, SaaS, Internal tool, AI-first — sets tenancy and demo defaults.
- **Tenancy**: Multi- or single-organization.
- **Demo data**: None, minimal (users/orgs/content), or full.
- **Organization name**: When single-tenant, the default org name.

After express completes you are redirected to `/install/complete?token=...` and automatically logged in as the created admin, then sent to `/admin`. Progress files are removed once status is `done` or `error`.

---

## Part 3b: Test the **CLI installer** (optional, from a second reset)

If you want to test the TUI instead of (or after) the web installer, reset again then use the CLI:

1. **Reset again**  
   ```bash
   ./scripts/fresh-clone-reset.sh
   ```

2. **Run the CLI installer**  
   ```bash
   php artisan app:install
   ```  
   - Go through the prompts (database, admin, app name, optional services, demo data).  
   - Or run non-interactive:  
     ```bash
   php artisan app:install --non-interactive \
     --admin-email=superadmin@example.com \
     --admin-password=secret \
     --admin-name="Admin" \
     --site-name="My App" \
     --url=https://example.test
   ```

3. **Check result**  
   - Visit the app in the browser and log in as the admin user.  
   - Visit `/install` again and confirm you are redirected to `/admin`.

---

## Part 4: Smoke checks after install

- **Dashboard**  
  Open the dashboard and confirm it loads without errors.

- **Users**  
  Open the users list (e.g. `/users` or Filament) and confirm the admin user exists.

- **Settings**  
  In Filament go to **System → Settings** (or `/system/settings`) and confirm you can open at least one settings group (e.g. App, Infrastructure).

- **Health**  
  Run:  
  ```bash
  php artisan app:health
  ```  
  and confirm there are no critical failures.

---

## Quick reference

| Goal              | Command / action                          |
|-------------------|-------------------------------------------|
| Reset to fresh    | `./scripts/fresh-clone-reset.sh`          |
| Web installer     | Visit `/install` (only when `APP_ENV=local`) |
| Express install   | POST `/install/express` with optional body (tenancy, demo, preset, single_org_name, locale) |
| CLI installer     | `php artisan app:install`                 |
| Health check      | `php artisan app:health`                 |
| Re-run migrations | `php artisan migrate:fresh` (destructive) |
| Full app reset    | `php artisan app:reset --force` (drops DB and re-migrates) |

`app:reset --force` is for when the app is already installed and you want to wipe the DB and run migrations again; it does not restore a “no .env / no DB” state. For “fresh clone” testing, use `./scripts/fresh-clone-reset.sh` first.
