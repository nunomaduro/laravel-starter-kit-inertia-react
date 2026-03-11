#!/usr/bin/env bash
# Reset the repo to a "fresh clone" state for manual testing: no DB, no install
# progress, minimal .env. Run from repo root: ./scripts/fresh-clone-reset.sh
set -e

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_ROOT"

echo "→ Fresh-clone reset (repo root: $REPO_ROOT)"
echo ""

# 1. Minimal .env so app can boot; installer will add the rest
echo "→ Writing minimal .env..."
printf 'APP_ENV=local\nAPP_KEY=\n' > .env
php artisan key:generate --force --no-interaction
echo "  .env has APP_ENV and APP_KEY only."
echo ""

# 2. Clear Laravel caches (config/route/view/event always; cache only if DB exists)
echo "→ Clearing Laravel caches..."
php artisan config:clear  2>/dev/null || true
if [[ -f database/database.sqlite ]]; then
  php artisan cache:clear  2>/dev/null || true
fi
php artisan route:clear  2>/dev/null || true
php artisan view:clear  2>/dev/null || true
php artisan event:clear  2>/dev/null || true
echo "  Caches cleared."
echo ""

# 3. Remove SQLite DB so installer starts from "no database" / "migrate" step
if [[ -f database/database.sqlite ]]; then
  rm -f database/database.sqlite
  echo "→ Removed database/database.sqlite"
else
  echo "→ No database/database.sqlite to remove"
fi
echo ""

# 4. Clear storage (keep structure)
echo "→ Clearing storage..."
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
find storage/logs -maxdepth 1 -name "*.log" -delete 2>/dev/null || true
echo "  Storage cache/sessions/views/logs cleared."
echo ""

# 5. Clear bootstrap cache
echo "→ Clearing bootstrap/cache..."
rm -f bootstrap/cache/config.php bootstrap/cache/routes-*.php bootstrap/cache/packages.php bootstrap/cache/services.php 2>/dev/null || true
echo "  Bootstrap cache cleared."
echo ""

# 6. Remove install progress so CLI installer can run from scratch
if [[ -f storage/app/.install-progress.json ]]; then
  rm -f storage/app/.install-progress.json
  echo "→ Removed storage/app/.install-progress.json"
fi
echo ""

echo "Done. State is like a fresh clone: minimal .env, no DB, no install progress."
echo ""
echo "Next: start the app (e.g. composer run dev or Herd), then either:"
echo "  • Visit /install for the web installer, or"
echo "  • Run: php artisan app:install"
echo ""
