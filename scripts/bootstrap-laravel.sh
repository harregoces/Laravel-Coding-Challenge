#!/usr/bin/env bash
set -euo pipefail

# Fail early if composer isn't available
if ! command -v composer >/dev/null 2>&1; then
  echo "Composer is required. Please install Composer and rerun."
  exit 1
fi

# Pick rsync if present; fall back to cp -a for portability
if command -v rsync >/dev/null 2>&1; then
  CP="rsync -a"
else
  CP="cp -a"
fi

# If artisan is missing, we need to install a Laravel skeleton.
if [ ! -f "artisan" ]; then
  echo "Bootstrapping Laravel 12 into a temporary directory..."
  TMP_DIR="$(mktemp -d 2>/dev/null || echo ./laravel-tmp)"
  composer create-project laravel/laravel:^12.0 "$TMP_DIR"

  # Copy the freshly-created Laravel skeleton into the repo root.
  # Exclude vendor to speed things up; 'composer require' will install deps anyway.
  $CP --exclude 'vendor' "$TMP_DIR"/ ./ || true

  # Clean up the temp dir if we created a named fallback
  if [ -d "./laravel-tmp" ]; then rm -rf ./laravel-tmp || true; fi
fi

# Core dependencies
composer require laravel/sanctum --no-interaction

# Sanctum migrations (ok if already published)
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --tag="migrations" --force || true

# Ensure SQLite DB file exists for local/dev runs
mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

# Overlay our starter files on top of the skeleton
# (exclude vendor/node_modules from overlay)
if [ -d "starter-overlay" ]; then
  rsync -a --exclude 'vendor' --exclude 'node_modules' starter-overlay/ ./ 2>/dev/null || $CP starter-overlay/. ./
fi

# Create cache table for database cache driver (ok if already exists)
php artisan cache:table || true

# Static analysis (Larastan) — enforced at level 7 in phpstan.neon.dist
# Detect Laravel major version; pick Larastan accordingly
LARAVEL_MAJ=$(php -r "echo preg_replace('/[^0-9].*$/','', json_decode(file_get_contents('composer.json'), true)['require']['laravel/framework'] ?? '12');")
if [ "${LARAVEL_MAJ:-12}" -ge 12 ]; then
  composer require --dev nunomaduro/larastan:^3 --no-interaction
else
  composer require --dev nunomaduro/larastan:^2.11 --no-interaction
fi
