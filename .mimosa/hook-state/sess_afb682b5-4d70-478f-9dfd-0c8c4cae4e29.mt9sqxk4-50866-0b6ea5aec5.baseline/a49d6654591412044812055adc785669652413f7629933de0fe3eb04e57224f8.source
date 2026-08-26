---
name: sakip-conventions
description: Development conventions and patterns for kwanter/sakip. PHP Laravel 12 monolith — government performance accountability system.
---

# SAKIP Conventions

## Tech Stack

- **Language**: PHP 8.3, Laravel 12
- **Frontend**: Blade layouts + Bootstrap 5 / jQuery / DataTables (CDN) + Vite-bundled vanilla JS modules + chart.js + Tailwind 4
- **DB**: SQLite (dev/test), MySQL/PostgreSQL (production)
- **Auth**: Session-based, Spatie Laravel Permission for roles/permissions
- **Tenant isolation**: `InstansiScope` global scope (applied to Program, SasaranStrategis, PerformanceData, Report, AuditLog, PerformanceIndicator) + per-model policies

## Key Architecture

- **Service layer**: Business logic in `app/Services/`. Controllers delegate to services.
- **No repositories** — Eloquent models used directly.
- **No Inertia/React** — removed in 2026 architecture cleanup. The frontend is Blade + Bootstrap + Vite-bundled vanilla JS.
- **API surface**: `/sakip/api/*` AJAX endpoints in `routes/web_sakip.php` (session auth, not Sanctum). No `routes/api.php` CRUD API.
- **UUID primary keys**, soft deletes on all tracked models.
- **Instansi tenancy**: `InstansiScope` global scope on direct-column models. Use `Model::withoutGlobalScope(InstansiScope::class)` for admin reports.

## Route Naming

- All SAKIP routes live in `routes/web_sakip.php`, prefixed `/sakip`, middleware `auth` + `verified`.
- Admin routes in `routes/web.php`, prefixed `/admin`.
- API-like endpoints under `/sakip/api/` (web middleware, not api middleware).
- Route names follow `sakip.{resource}.{action}` pattern.

## Code Style

- Laravel Pint enforcement (CI gate). No `|| true` escape.
- Use `booted()` for model global scopes; `boot()` when `parent::boot()` is needed.
- New models with `instansi_id` column must add the `InstansiScope` global scope.