#!/usr/bin/env bash
# Copy DataTable React files from vendor (machour/laravel-data-table) into the app.
# Run after updating the package (e.g. composer update machour/laravel-data-table).
# The repo already commits these files so a fresh clone works without this script.
set -e

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
VENDOR="$REPO_ROOT/vendor/machour/laravel-data-table/react/src/data-table"
DEST="$REPO_ROOT/resources/js/components/data-table"

if [[ ! -d "$VENDOR" ]]; then
  echo "→ Vendor path not found: $VENDOR"
  echo "  Run: composer install"
  exit 1
fi

echo "→ Syncing DataTable files from vendor to $DEST"
cp "$VENDOR/data-table.tsx" "$DEST/"
cp "$VENDOR/data-table-column.tsx" "$DEST/"
cp "$VENDOR/data-table-column-header.tsx" "$DEST/"
cp "$VENDOR/data-table-pagination.tsx" "$DEST/"
cp "$VENDOR/data-table-row-actions.tsx" "$DEST/"
cp "$VENDOR/data-table-quick-views.tsx" "$DEST/"
cp "$VENDOR/i18n.ts" "$DEST/"
cp "$VENDOR/types.ts" "$DEST/"
echo "  ✓ All DataTable component files synced."
