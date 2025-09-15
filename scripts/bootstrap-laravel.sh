#!/usr/bin/env bash
set -euo pipefail

if ! command -v composer >/dev/null 2>&1; then
  echo "Composer is required. Please install composer and rerun."
  exit 1
fi

if [ ! -f "artisan" ]; then
  echo "Bootstrapping Laravel 12..."
  composer create-project laravel/laravel:^12.0 .
fi

composer require laravel/sanctum --no-interaction
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider" --tag="migrations" --force || true

mkdir -p database
if [ ! -f "database/database.sqlite" ]; then
  touch database/database.sqlite
fi

rsync -a --exclude 'vendor' --exclude 'node_modules' starter-overlay/ ./

php artisan cache:table || true

# Require Larastan (PHPStan) at dev level
composer require --dev nunomaduro/larastan:^2.9 phpstan/phpstan:^1.11 --no-interaction
