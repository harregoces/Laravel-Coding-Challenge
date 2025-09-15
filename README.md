# Laravel Quotes Challenge — Starter (Laravel 12, Sanctum, SQLite, Containerized)

This starter bootstraps **Laravel 12**, **Sanctum**, **SQLite** (DB + database cache), **Larastan** (level 7), and a CI pipeline that runs tests and builds a container on **php:8.3-apache-bookworm**.

> Run the bootstrap script to create a fresh Laravel 12 app in-place and apply the overlay.

## Start Here
- 📖 **Challenge Instructions** → [docs/CHALLENGE.md](docs/CHALLENGE.md)
- ✅ **Self‑review Checklist** → [docs/SELF_REVIEW.md](docs/SELF_REVIEW.md)
- 🧪 **Grader’s Checklist** → [docs/GRADER_CHECKLIST.md](docs/GRADER_CHECKLIST.md)
- 🧭 **ADRs** → [docs/adr](docs/adr)
- 🔌 **Postman Collection** → [starter-overlay/postman/quotes-collection.json](starter-overlay/postman/quotes-collection.json)

## Quick Start

```bash
git clone https://github.com/Priority-Wire/Laravel-Coding-Challenge
cd Laravel-Coding-Challenge

./scripts/bootstrap-laravel.sh

cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
vendor/bin/phpstan analyse
php artisan serve  # http://127.0.0.1:8000
```

## Container

```bash
podman build -t quotes-app:dev -f starter-overlay/Containerfile .
podman run --rm -p 8080:80 -e APP_KEY=base64:dummy quotes-app:dev
# or docker build/run ...
```

## CI / GHCR

The workflow builds and can publish a **public** image to GHCR. Set repo variables:
- `PUBLISH=true` — push to GHCR
- `MAKE_PUBLIC=true` — attempt to set package visibility to **public**

## Notes
- Toggle client mode via `.env` → `QUOTES_CLIENT=stub|real` (default `real`).
- Respect ZenQuotes free tier & attribution (see CHALLENGE.md).
