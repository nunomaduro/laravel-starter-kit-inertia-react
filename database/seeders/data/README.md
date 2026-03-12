# Seeder data

JSON files in this directory are loaded by Development seeders (e.g. `users.json` by `UsersSeeder`).

**Testing credentials** (after `php artisan migrate:fresh --seed`): all fixed users use password **`password`**. Full table and use cases: [Testing credentials (Development)](../../../docs/developer/backend/database/seeders.md#testing-credentials-development).

| Email | Role | Password |
|-------|------|----------|
| superadmin@example.com | super-admin | password |
| test@example.com | user | password |
| admin-app@example.com | admin | password |
| owner@example.com | user | password |
| unverified@example.com | user | password |
| onboarding@example.com | user | password |
| multi@example.com | user | password |
| member@example.com | user | password |
| mixed@example.com | user | password |
