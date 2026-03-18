# Contributing

Thanks for contributing to the Laravel Starter Kit. This document helps you get set up and follow the project’s checks.

## Setup

1. Clone the repo and install dependencies:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --force
   bun install
   bun run build
   ```

   Or use the one-liner:

   ```bash
   composer setup
   ```

2. **(Optional)** Install the pre-commit hook so commits are validated automatically:

   ```bash
   cp scripts/pre-commit .git/hooks/pre-commit && chmod +x .git/hooks/pre-commit
   ```

   The hook runs: Rector (PHP), Pint (PHP style), model/seeder checks, documentation check, and TypeScript type check. Skip with `git commit --no-verify` when necessary.

## Verify your environment

Run a quick sanity check:

```bash
php artisan app:health && composer test
```

- `php artisan app:health` checks database, cache, queue, mail, search, etc.
- `composer test` runs the fast test suite (Pest in parallel).

For the full suite (type coverage, coverage, lint, static analysis):

```bash
composer test:full
```

## Commands

| Command | Description |
|--------|-------------|
| `composer dev` | Start server, queue, logs, and Vite together |
| `composer test` | Fast tests (used by pre-commit) |
| `composer test:full` | Full suite: type coverage, coverage, lint, PHPStan, TS |
| `composer test:types` | PHPStan + `bun run test:types` |
| `composer lint` | Fix PHP (Rector, Pint) and JS/TS (ESLint, Prettier) |
| `composer docs:check` | Ensure Actions/Controllers/Pages are documented |
| `composer docs:sync` | Sync documentation manifest |

## Documentation

New **Actions**, **Controllers**, and **Pages** must be documented. The pre-commit hook and CI will fail if documentation is missing.

- Run `php artisan docs:sync` to sync the manifest.
- Run `php artisan docs:sync --generate` to create stubs for undocumented items.
- See [docs/developer/](docs/developer/) and the [documentation guidelines](.cursor/rules/laravel-boost.mdc) in the repo.

## Branching and PRs

- Open a branch for your change and run `composer test` (and optionally `composer test:full`) before pushing.
- CI runs on push/PR to `main` and includes tests, lint, docs, permissions, and route checks.

## Troubleshooting

- **Pre-commit fails (docs)**  
  Run `php artisan docs:sync` and, if needed, `php artisan docs:sync --generate`.

- **`composer test:types` fails**  
  Fix reported PHPStan or TypeScript errors. Run `bun run test:types` for TS only.

- **Vite manifest missing**  
  Run `bun run build` or `bun run dev` so assets are built.

- **PHP version**  
  This project requires **PHP 8.4+**. See `composer.json` and the main [README](README.md).
