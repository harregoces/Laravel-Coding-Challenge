# Laravel Quotes Challenge — Starter (Laravel 12, Sanctum, SQLite, Containerized)

This is a **cohesive starter** for your coding challenge. It bootstraps **Laravel 12**, **Sanctum**, **SQLite** (DB + database cache),
**Larastan** (PHPStan) at **level 6**, a CI workflow, and a container image on **php:8.3-apache-bookworm**.

> Fork the repo. The Laravel scaffold itself is not committed. Run the bootstrap script to create Laravel 12 in-place and apply this overlay.

## Start Here
- 📖 **Challenge Instructions** → [docs/CHALLENGE.md](docs/CHALLENGE.md)
- 🧭 **Architectural Decision Records (ADRs)** → [docs/adr](docs/adr)
- 🔌 **Postman Collection** → [starter-overlay/postman/quotes-collection.json](starter-overlay/postman/quotes-collection.json)
- ✅ **Self‑review Checklist** → [docs/SELF_REVIEW.md](docs/SELF_REVIEW.md)
- 🧪 **Grader’s Checklist** → [docs/GRADER_CHECKLIST.md](docs/GRADER_CHECKLIST.md)
- <img width="20" height="20" alt="image" src="https://github.com/user-attachments/assets/1cbde537-d430-4209-9f22-1b8cd5ce0544" />  **AI Use Transparency** - If you used any AI tool, document it in [AI_USAGE.md](AI_USAGE.md)

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

The workflow runs tests + Larastan and can publish a **public** image to GHCR. Set repo variables:
- `PUBLISH=true` — push to GHCR
- `MAKE_PUBLIC=true` — attempt to set package visibility to **public**

## Notes
- Toggle client mode via `.env` → `QUOTES_CLIENT=stub|real` (default `real`).
- Respect ZenQuotes free tier & attribution (see CHALLENGE.md).
