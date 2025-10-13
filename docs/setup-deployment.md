# Setup and Deployment Guide

This guide explains how to set up the application locally and basic deployment steps.

## Prerequisites
- PHP 8.x
- Composer
- Node.js (optional if asset pipeline is used)
- MySQL/PostgreSQL

## Local Setup
1. Clone repo: `git clone <repo-url>`
2. Copy env: `cp .env.example .env`
3. Configure DB credentials in `.env`
4. Install dependencies: `composer install`
5. Generate app key: `php artisan key:generate`
6. Run migrations: `php artisan migrate`
7. (Optional) Seed data: `php artisan db:seed`
8. Start dev server: `php artisan serve`
9. Open `http://127.0.0.1:8000` (or provided port)

## Configuration Notes
- Ensure `APP_URL` matches local dev URL
- Set `LOG_CHANNEL` to `stack` (default) for combined logging
- Set `SESSION_DRIVER=file` for local development

## Deployment (General)
- Build artifacts (if using a frontend asset pipeline): `npm run build`
- Use environment-specific `.env` with production DB credentials
- Run `composer install --no-dev --optimize-autoloader`
- Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- Run migrations on deploy: `php artisan migrate --force`
- Set proper file permissions for `storage/` and `bootstrap/cache`

## Post-Deploy Checks
- Verify homepage loads and authentication works
- Check Program/Kegiatan index pages render with normalized status labels
- Create/edit forms validate currency and date/year ranges properly
- Review logs for errors: `storage/logs/laravel.log`

## Maintenance
- Backup DB regularly
- Apply security updates for dependencies via Composer
- Monitor performance metrics and error logs