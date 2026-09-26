---
goal: Repository Architecture and Structure Documentation
date_created: 2026-09-24
last_updated: 2026-09-24
status: 'Active'
---

<!-- markdownlint-disable -->

# Architecture Documentation

![Status: Active](https://img.shields.io/badge/status-active-green)

This document is the canonical architectural map of SAKIP. It records the repository structure, runtime and test toolchain, request flow, public test seams, and the constraints relevant to making safe changes.

## 1. Project Overview

SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah) is a Laravel 12 web application that operates the Indonesian government performance-accountability pipeline. It models the performance cascade `Instansi` → `SasaranStrategis` → `Program` → `Kegiatan` → `PerformanceIndicator` → `Target`, collects and validates
`PerformanceData` through a `draft → submitted → validated → approved` workflow, runs `Assessment` against criteria with linked `EvidenceDocument`s, and generates `Report`s exported to PDF, Excel, CSV, and JSON.

Its intended users are Indonesian government agency staff — Super Admin, Admin, Assessor, Auditor, and Data Collector — scoped to their own `Instansi` (agency). The repository is a single Laravel monolith with server-rendered Blade pages, a thin controller layer
over a service layer, policy-based authorization, Eloquent global scopes for tenancy and filtering, and a Vite-managed frontend asset pipeline. Business workflows are meaningful enough that the service layer (`app/Services/`) is the primary place where domain rules live;
the project is not organized as a strict Clean Architecture or a package-level modular monolith.

## 2. High-Level Architecture & Tech Stack

- **Primary Language:** PHP `^8.3` (CI matrix: 8.3 and 8.4); JavaScript ES modules for the Vite frontend pipeline.
- **Frameworks/Libraries:** Laravel 12, Blade, Eloquent, Spatie Laravel Permission, DOMPDF, PhpSpreadsheet, Vite 7 + Tailwind CSS 4 + Chart.js 4 (bundled), Bootstrap 5.3 + jQuery + DataTables loaded from CDN.
- **Architectural Pattern:** Laravel MVC monolith with a service layer for business logic (`app/Services/`), the strategy pattern for export formats (`app/Services/Export/`), policy classes for authorization (`app/Policies/`), and Eloquent global scopes for cross-cutting query constraints (`app/Models/Scopes/`).
- **Build/Tooling:** Composer, npm, Vite, Laravel Pint, PHPUnit 11, GitHub Actions, Docker Compose (app, queue, scheduler, nginx, MySQL 8.0, Redis 7).
- **Frontend reality check:** Bootstrap 5.3 via CDN styled by `public/css/modern-sakip.css` is what actually renders. Tailwind 4 and the Vite pipeline exist in the toolchain but are not loaded by the Bootstrap layouts.
- **PHP quality commands:** `php artisan test --profile` (the `--profile` flag lists the ten slowest tests beside the totals) and `./vendor/bin/pint --test --no-interaction`. The same gates run on the host without Docker through `make gates-local`, `make test-local`, `make test-unit-local`, `make lint-local`, and `make php-version`: the Makefile resolves the binary itself (`PHP ?= $(shell command -v php 2>/dev/null || echo /opt/homebrew/opt/php@8.3/bin/php)`), because non-interactive shells do not source `~/.zshrc` and may have no `php` on `PATH`.
- **Frontend commands:** `npm run dev` and `npm run build`. `package.json` defines no JavaScript test, lint, or format script.

## 3. Data Flow & Layer Dependencies

A typical request enters through `public/index.php` and `bootstrap/app.php`, matching a route in `routes/web.php` (which requires `routes/web_sakip.php`). It passes the global `SecurityHeadersMiddleware`, then the route's middleware stack
(`auth`, `verified`, and `role` / `permission` aliases, `secure.file.upload` for uploads, and named throttles). A controller in `app/Http/Controllers/{Admin,Sakip}` validates input through a Form Request in `app/Http/Requests/{Admin,Sakip}`,
authorizes through a policy or gate, delegates domain work to a service in `app/Services/`, and returns a Blade view, a redirect, or a generated export.

Eloquent models apply global scopes automatically: `InstansiScope` (tenant isolation), plus `ForYearScope`, `RecentScope`, `SearchScope`, and `WithStatusScope` for filtering. Multi-write workflows use the `WithDatabaseTransactions` trait and, where relevant, pessimistic or optimistic locking. Every state-changing action is expected to write an `AuditLog` row.

```mermaid
flowchart TD
    Browser[Browser] --> Entry[public/index.php + bootstrap/app.php]
    Entry --> MW[SecurityHeadersMiddleware (global)]
    MW --> Routes[Routes: web.php -> web_sakip.php]
    Routes --> AuthMW[auth / verified / role / permission / throttle]
    AuthMW --> Controller[Http Controller: Admin or Sakip]
    Controller --> FormRequest[Form Request validation]
    Controller --> Policy[Policy / Gate authorization]
    Controller --> Service[Service layer]
    Service --> Model[Eloquent models + global scopes]
    Model --> DB[(MySQL / SQLite)]
    Model --> Audit[AuditLog]
    Service --> Export[Export services: PDF / Excel / CSV / JSON]
    Controller --> View[Blade view or redirect]
```

## 4. Dependencies & External Services

- **MySQL 8.0 / MariaDB 10.3+:** primary datastore in production and Docker; tests run against in-memory SQLite configured in `phpunit.xml`.
- **Redis 7:** cache, queue, and session backend in the Docker Compose stack (`app`, `queue`, `scheduler`, `nginx` services).
- **MySQL + Redis containers and Nginx:** provisioned by `docker-compose.yml`; `Makefile` wraps the common operations.
- **DOMPDF (`dompdf/dompdf`):** PDF report and letter generation.
- **PhpSpreadsheet (`phpoffice/phpspreadsheet`):** Excel and CSV export.
- **Spatie Laravel Permission:** roles and permissions (`config/permission.php`); roles are Super Admin, Admin, Assessor, Auditor, and Data Collector.
- **Private filesystem disk:** evidence-document uploads (PDF/DOC/DOCX/XLS/XLSX/JPG/JPEG/PNG, max 10 MB) served through authorized routes rather than the public directory.
- **Laravel Telescope:** development-only request/query inspection (`config/telescope.php`), disabled during tests.
- **GitHub Actions:** the CI system of record (`.github/workflows/ci.yml`).

## 5. Directory Tree Map

```text
[SAKIP Project Root]
├── .agents/
│   ├── instructions/            # Agent instruction files (clean code, markdown, memory)
│   ├── rules/SDLCOrchestrator.md# SDLC orchestration rules
│   ├── skills/                  # 21 skills: tdd-* (20) + memory-manager
│   ├── skills/sakip/            # Project-specific SAKIP skill
│   ├── skills/standards/        # ADR / CONSTITUTION / CONSTRAINTS / CONTEXT templates
│   └── standards/               # Same standards set at the top level of .agents/
├── .claude/                     # Claude Code mirror of the .agents scaffolding
├── .github/workflows/ci.yml     # CI: lint-check, unit-feature-tests, security-audit, build-assets
├── app/                         # Application code (135 PHP files)
│   ├── Constants/               # Status, ReportStatus, AssessmentStatus, SystemRoles, ValidationRules, Pagination
│   ├── Console/Commands/        # AddSakipPermissions, CheckMissingClasses, RemoveTestUsers
│   ├── Http/
│   │   ├── Controllers/         # 31 files: Admin/, Auth/, Sakip/, plus top-level utility controllers
│   │   ├── Middleware/          # SecurityHeadersMiddleware, SecureFileUploadMiddleware
│   │   └── Requests/            # 11 Form Requests under Admin/ and Sakip/
│   ├── Models/                  # 17 Eloquent models
│   │   └── Scopes/              # InstansiScope, ForYearTrait, RecentScope, SearchScope, WithStatusScope
│   ├── Policies/                # 15 authorization policies
│   ├── Providers/               # AppServiceProvider, RateLimitServiceProvider, TelescopeServiceProvider
│   ├── Services/                # Business logic (35 files): Export/, Import/, Sakip/, Validation/
│   ├── Support/                 # Read-model contracts (seam S1): ReportingPeriod, TriageScope, AdminTriageSummary
│   ├── Traits/                  # ClearsCacheByKey, SortsSafely, WithDatabaseTransactions
│   └── View/                    # View composition
│       └── Composers/           # SidebarQueueBadgeComposer (layouts.modern queue badge, seam S4)
├── bootstrap/app.php            # Laravel 12 wiring: routing, global middleware, aliases, throttles
├── config/                      # sakip.php, sakip_templates.php, permission.php, telescope.php, ...
├── database/
│   ├── factories/               # 11 model factories
│   ├── migrations/              # 54 migrations (UUID PKs, soft deletes, index migrations)
│   └── seeders/                 # DatabaseSeeder, RolesAndPermissions, Instansi, SystemSettings, AdminUser, User
├── docs/                        # ARCHITECTURE.md + SDLC artifacts (discovery/, prd/, audit/, checklist/, review/, retro/) + the Diátaxis set (tutorials/, how-to/, reference/, explanation/) + plans/, history/, adr/ (ADRs), security/
├── public/                      # Web root, css/modern-sakip.css design system, js helpers, js/app bundles
├── resources/
│   ├── css/                     # app.css, sakip-styles.css (Vite inputs)
│   ├── js/ + js/sakip/          # Vite entry + SAKIP modules (dashboard, data-tables, notification, helpers)
│   └── views/                   # Blade: sakip/ (44), admin/ (15), layouts/ (2), auth/ (2), errors/ (1)
├── routes/
│   ├── web.php                  # Auth, profile, admin routes; requires web_sakip.php at the end
│   ├── web_sakip.php            # /sakip/* resource + workflow routes and /sakip/api/* AJAX endpoints
│   ├── api.php                  # /api/health and /api/csp-reports only (no CRUD API)
│   └── console.php              # Artisan command definitions
├── tests/
│   ├── Feature/                 # 14 HTTP/workflow tests (incl. AdminTriageLandingTest, SidebarQueueBadgeTest)
│   ├── Unit/                    # 10 tests: 5 guards at the root (incl. FloorGuardTest), 3 under Services/, 1 each under Scopes/ and Support/
│   │   ├── Scopes/              # ForYearTraitTest (the active-year seam, REQ-014)
│   │   ├── Services/            # PerformanceCalculationServiceTest, AdminTriageServiceTest, DashboardDateRangeAdapterTest
│   │   └── Support/             # ReportingPeriodTest (seam S1: pure unit, no DB, HTTP or auth)
│   └── TestCase.php             # RefreshDatabase + $seed = true + Spatie permission cache reset
├── AGENTS.md                    # Agent operating contract and SDLC map
├── CONSTITUTION.md              # Non-negotiable engineering principles
├── CONSTRAINTS.md               # Quality bars and floor-guard anti-cheat rules
├── phpunit.xml                  # Unit + Feature suites; SQLite in-memory testing environment
├── docker-compose.yml           # app, queue, scheduler, nginx, mysql:8.0, redis:7-alpine
├── Dockerfile, Makefile         # Container build and operator shortcuts
└── composer.json, package.json  # PHP and frontend dependency manifests
```

## 6. Directory Purposes & Responsibilities

| Directory/File | Primary Purpose | Contains | Rules / Constraints |
| --- | --- | --- | --- |
| `app/Http/Controllers/` | HTTP orchestration | `Admin/`, `Auth/`, `Sakip/`, plus Health/Docs/Feedback/Profile controllers | Keep thin: validate, authorize, delegate to services, return a view/redirect/export. No workflow business rules. |
| `app/Http/Requests/` | Input validation | Form Requests for `Admin/` and `Sakip/` operations | Validate here, not in controllers; authorization may live in the request's `authorize()`. |
| `app/Http/Middleware/` | Cross-cutting HTTP concerns | `SecurityHeadersMiddleware`, `SecureFileUploadMiddleware` | Registered in `bootstrap/app.php`; header middleware is global, the upload middleware is an alias. |
| `app/Services/` | Domain and workflow logic | 35 services, including `Export/`, `Import/`, `Sakip/`, `Validation/` | Performance-data transitions and calculations belong here. Wrap multi-write operations in `WithDatabaseTransactions`. |
| `app/Support/` | Period vocabulary and read-model contracts | `ReportingPeriod`, `TriageScope`, `AdminTriageSummary` | One source of truth for "what does this period mean" and for the agency-coverage labels. Keep it pure: no database, HTTP, or auth access, or the seam it exists to specify is hidden. |
| `app/Models/` | Persistence and relationships | 17 Eloquent models | UUID primary keys, soft deletes on data-tracking models, `instansi_id`-bearing models keep `InstansiScope`. |
| `app/Models/Scopes/` | Query constraints | Tenancy, year, recent, search, and status scopes | Do not remove `InstansiScope` to make a test pass; bypassing it requires an authorized path plus a test. |
| `app/Policies/` | Authorization | 15 policies bound in `AppServiceProvider` | Authorization decisions live in policies, not in Blade conditionals alone. |
| `app/Constants/` | System-wide values | Status, ReportStatus, AssessmentStatus, SystemRoles, ValidationRules, Pagination | Prefer these constants over inline magic strings. |
| `app/Traits/` | Reusable behaviors | `ClearsCacheByKey`, `SortsSafely`, `WithDatabaseTransactions` | Use `SortsSafely` for any user-supplied sort parameter. |
| `app/View/Composers/` | Layout-owned view state | `SidebarQueueBadgeComposer` | Composes state that every page of a layout needs (the sidebar queue badge) from the request, so no controller owns it. Registered beside the global nonce composer in `AppServiceProvider`. |
| `config/` | Runtime configuration | `sakip.php`, `sakip_templates.php`, `permission.php`, `telescope.php`, and Laravel defaults | Performance thresholds and upload limits are configuration, not code; use `env()` only here. |
| `database/migrations/` | Schema evolution | 54 migrations, including index migrations for hot tables | Follow UUID PK and soft-delete conventions. |
| `database/seeders/` | Baseline data | Roles/permissions, instansi, system settings, admin user | Tests seed via `DatabaseSeeder` because `TestCase::$seed = true`. |
| `resources/views/` | Server-rendered UI | `sakip/`, `admin/`, `layouts/`, `auth/`, `errors/` Blade templates | Bootstrap CDN + `modern-sakip.css` components; user-facing strings in Bahasa Indonesia; WCAG AA. |
| `resources/js/` | Frontend assets | Vite entry plus `sakip/` modules | Vite bundles are not loaded by every Bootstrap layout today; verify at runtime before relying on them. |
| `routes/` | HTTP surface | `web.php`, `web_sakip.php`, `api.php`, `console.php` | `web_sakip.php` is required by `web.php`; SAKIP AJAX endpoints live under `/sakip/api/` with session auth. |
| `tests/Feature/` | End-to-end HTTP behavior | 14 tests covering auth, dashboards, data collection, isolation, rendering, rate limiting, and the triage landing | Assert HTTP status, redirects, rendered content, and persisted rows. |
| `tests/Unit/` | Guards and isolated logic | Root: `ArchitectureGuardTest`, `SecurityHeadersTest`, `FloorGuardTest`, the export-injection guard, `ExampleTest`. `Services/`: `PerformanceCalculationServiceTest`, `AdminTriageServiceTest` (seam S2 — real DB, no `actingAs()`), `DashboardDateRangeAdapterTest`. `Scopes/`: `ForYearTraitTest`. `Support/`: `ReportingPeriodTest` (seam S1) | `ArchitectureGuardTest` prevents duplicate class names and missing route names from recurring. `FloorGuardTest` statically forbids suppression tokens, skipped tests, and doc-comment test metadata across `app/`, `config/`, `resources/views/` and `tests/` (it excludes itself, whose string literals name the tokens). Seams S3 and S4 live in `tests/Feature/AdminTriageLandingTest.php` and `tests/Feature/SidebarQueueBadgeTest.php`. |
| `docs/` | Project documentation | This map, plans, history, security notes, ADRs | ADRs follow `.agents/standards/ADR-FORMAT.md` and live in `docs/adr/`. |
| `.agents/`, `.claude/` | Agent governance scaffolding | Instructions, rules, 21 skills, standards | Governance artifacts only; never place application code here. |

## 7. Key Configuration Files

- `bootstrap/app.php`: registers routing files, appends `SecurityHeadersMiddleware` globally, defines the `role`, `permission`, `role_or_permission`, `secure.file.upload`, `throttle.login`, and `throttle.api.strict` aliases, and prepends the `api` throttle group.
- `config/sakip.php`: SAKIP domain configuration — performance thresholds (excellent 100, good 80, satisfactory 60; max achievement 200%), validation rules, upload limits (10 MB with MIME/extension allowlist), reporting options, assessment options (`require_evidence`, assessor bounds), export defaults, audit settings (`retention_days` 365), and dashboard caching (5 minutes).
- `config/sakip_templates.php`: built-in report template definitions.
- `config/permission.php`: Spatie permission tables, guards, and cache configuration.
- `phpunit.xml`: Unit and Feature suites, coverage source directory (`app`), and the testing environment (SQLite `:memory:`, array cache/mail/session, sync queue).
- `vite.config.js`: Vite inputs for `resources/css` and `resources/js`, including Tailwind and Chart.js.
- `docker-compose.yml` / `Dockerfile` / `Makefile`: container topology (app, queue, scheduler, nginx, MySQL 8.0, Redis 7) and operator shortcuts (`make test`, `make lint`, `make migrate`, `make fresh`).
- `.github/workflows/ci.yml`: the authoritative quality gate — see section 9.

## 8. Entry Points

- **App Initialization:** `public/index.php` bootstraps the framework; `bootstrap/app.php` configures routing, middleware, and exception handling.
- **HTTP Routing:** `routes/web.php` (auth, profile, admin, utility pages) `require`s `routes/web_sakip.php` (all `/sakip/*` resources, workflow routes, and `/sakip/api/*` AJAX endpoints). `routes/api.php` exposes only `/api/health` and `/api/csp-reports`.
- **Authorization Entry:** `app/Providers/AppServiceProvider.php` binds model policies and registers the CSP nonce singleton.
- **Rate Limiting Entry:** `app/Providers/RateLimitServiceProvider.php` defines the `login`, `guest`, and `api_strict` limiters. The throttle aliases map `throttle.login` and `throttle.api.strict`; `throttle:guest` (30/min per IP, answering `Limit::none()` for a resolved user) guards the guest-facing routes `/`, `GET /login`, `POST /login` and `POST /logout`.
- **CLI Entry:** `artisan`, with commands defined in `app/Console/Commands/` and `routes/console.php`.
- **Navigation Entry:** `/` redirects guests to `login`, unverified users to `verification.notice`, and verified users to `sakip.dashboard`; `/admin/dashboard` is the admin landing surface.

## 9. Environment & Deployment

- **Environments:** local development (Docker Compose stack via `make up`, or a local PHP/MySQL setup), testing (SQLite `:memory:` plus array/sync drivers from `phpunit.xml`), and production (Nginx + PHP-FPM container with MySQL 8.0 and Redis 7).
- **CI/CD:** GitHub Actions (`.github/workflows/ci.yml`) with five jobs:
  1. `lint-check` — `composer validate --strict --no-check-all`, `npm ci`, `./vendor/bin/pint --test --no-interaction`.
  2. `unit-feature-tests` — PHP matrix 8.3/8.4, `php artisan config:clear`, `php artisan test`.
  3. `security-audit` — `composer audit --locked`, `npm audit --omit=dev`.
  4. `build-assets` — `npm ci`, `npm run build`.
  5. `deploy` — placeholder step on `main`.
- **Required configuration:** copy `.env.example` to `.env` and run `php artisan key:generate`. `AppServiceProvider` refuses to boot in production when `APP_KEY` is missing or shorter than 32 characters.
- **Domain environment variables:** `SAKIP_CALCULATION_METHOD`, `SAKIP_MAX_FILE_SIZE`, `SAKIP_AUDIT_ENABLED`, `SAKIP_DASHBOARD_CACHE`, plus standard `DB_*`, `REDIS_*`, `MAIL_*`, and `SESSION_*` settings.
- **Operational caveat:** per `PRODUCT.md`, as of 2026-08-29 the docker-compose stack is torn down and its MySQL instance is not volume-backed. Local verification therefore starts with `make up` (or an equivalent local MySQL/SQLite setup) rather than assuming a running database.

## 10. Testing Strategy

- **Framework:** PHPUnit 11 (`phpunit/phpunit ^11.5.3`) executed through Laravel's Artisan test runner. This repository has no JavaScript test runner in `package.json` and no Playwright configuration.
- **Location:** `tests/Unit` (guards and isolated logic) and `tests/Feature` (HTTP and workflow behavior), declared as two suites in `phpunit.xml`. The coverage source directory is `app`.
- **Base test case:** `tests/TestCase.php` applies `RefreshDatabase`, sets `$seed = true` so `DatabaseSeeder` runs before each test, and clears Spatie's cached permissions after reseeding.
- **Commands:**

```bash
php artisan test --profile               # full suite + the ten slowest tests
php artisan test --testsuite=Unit        # unit runtime SLA check
php artisan test --filter=TestName       # focused seam during Red-Green-Refactor
composer test                            # config:clear + artisan test
make test                                # same command inside the Docker app container
make gates-local                         # pint + test --profile on the host (Makefile resolves PHP)
php artisan test --coverage              # line coverage (PCOV is installed; branch coverage needs Xdebug)
```

- **Coverage reality (measured 2026-09-26):** PCOV is installed, so line coverage is measurable — **8.8 %** over `app/` and **91.8–100 %** for the admin-triage slice. `CONSTRAINTS.md` §1 measures it in two scopes: the **changed files** against a >= 80 % target / >= 75 % floor, and the whole-`app/` total as a **ratchet** that may only be raised. Branch coverage requires Xdebug and is reported **unverified**.
- **Suite size (measured 2026-09-26):** **152 tests** — 89 named `test_*` plus 63 driven by the `#[Test]` attribute (doc-comment test metadata was migrated away, backlog F2) — with **732 assertions**, **0 skips** and **0 PHPUnit deprecations**. Unit: 57 tests in ~1.3 s (SLA < 10 s). Full suite: ~12.2–12.6 s (SLA < 15 s target / < 20 s hard).

- **Existing public test seams** (patterns to reuse when adding tests):
  - Named HTTP routes exercised with `actingAs()` — `tests/Feature/SakipDashboardAccessTest.php`, `tests/Feature/ReportIndexRendersTest.php`, `tests/Feature/RedirectFlowTest.php`.
  - Role/permission-gated endpoints — `tests/Feature/AdminRoleEscalationTest.php`, `tests/Feature/DataCollectionControllerTest.php`.
  - Tenant isolation — `tests/Feature/TargetTenantIsolationTest.php`, `tests/Feature/EvidenceUpdateIsolationTest.php` assert that cross-instansi access is denied or absent.
  - Service contracts — `tests/Unit/Services/PerformanceCalculationServiceTest.php` and `tests/Feature/PerformanceCalculationServiceTest.php` call services with factory-built models.
  - Architecture guards — `tests/Unit/ArchitectureGuardTest.php` walks `app_path()` to prevent duplicate short class names and missing route names.
  - Floor-guard scan — `tests/Unit/FloorGuardTest.php` walks `app/`, `config/`, `resources/views/` and `tests/` and fails on any suppression token, any skipped test, or any doc-comment test metadata (backlogs F1 and F2 are closed, so none of the three has an allow-list today).
  - Security invariants — `tests/Unit/SecurityHeadersTest.php`, `tests/Unit/ExportFormulaInjectionTest.php`, `tests/Feature/RateLimitingTest.php`.
  - **Admin triage landing seams S1–S4** (pre-agreed in `spec/spec-admin-triage-landing.md` §6.1):
    - **S1** `app/Support/ReportingPeriod` — pure value object, `tests/Unit/Support/ReportingPeriodTest.php`. No database, no HTTP, no auth: a query or an `actingAs()` here would hide the boundary the seam exists to specify.
    - **S2** `app/Services/AdminTriageService::summaryFor()` / `verificationCountFor()` — `tests/Unit/Services/AdminTriageServiceTest.php`, real database and factories but **no `actingAs()`**, because the service applies agency coverage explicitly. The ambient `InstansiScope` is a documented no-op without an authenticated user, so this seam can prove nothing about ambient auth; S3 owns that invariant.
    - **S3** `GET /admin/dashboard` — `tests/Feature/AdminTriageLandingTest.php` (`actingAs()`, seeded permissions, driven through the router). It owns the ambient-auth invariant: a `Super Admin` render must equal a direct `summaryFor()` call made with no authenticated user.
    - **S4** the sidebar queue badge on `layouts.modern` — `tests/Feature/SidebarQueueBadgeTest.php`, driven through two routes owned by two controllers. **The badge sits inside `@can('manage-sakip')`** (`resources/views/layouts/modern.blade.php:81,88-93`) and that permission is defined and granted nowhere, so the seam is effectively Super-Admin-only today: never assert a badge without first establishing that the viewer may open its link.
- **Fixtures:** 11 model factories exist (`User`, `Instansi`, `Program`, `Kegiatan`, `PerformanceIndicator`, `Target`, `PerformanceData`, `Assessment`, `AssessmentCriterion`, `EvidenceDocument`, `Report`). Seeders provide roles, permissions, instansi, and system settings. There is no factory for `SasaranStrategis`; create records explicitly or add a factory when a test needs one.

## 11. AI Agent Boundaries

- **Governance first:** before changing code, read `AGENTS.md`, `CONSTITUTION.md`, `CONSTRAINTS.md`, `.agents/instructions/`, and the active memory file (`.agents/instructions/memory.instructions.md`).
- **Test-first only:** no functional code without a failing test at an agreed public seam; tests assert state and outcomes, never private internals.
- **Layer discipline:** workflow and calculation logic belongs in `app/Services/`; controllers stay thin; authorization belongs in `app/Policies/`; system-wide values belong in `app/Constants/`.
- **Non-negotiable invariants:** UUID primary keys, soft deletes on data-tracking models, `InstansiScope` on `instansi_id`-bearing models, and `AuditLog` entries for state-changing actions.
- **Never cheat the floor:** do not weaken assertions, skip tests, suppress diagnostics, remove tenancy scoping, or silence audit logging to reach green (see `CONSTRAINTS.md` §3).
- **Language policy:** user-facing strings in Bahasa Indonesia; identifiers, code comments, commit messages, and SDLC documentation in English.
- **Upload safety:** evidence documents go to the private disk through the `secure.file.upload` middleware and `SecureFileUploadMiddleware`; never write uploads to `public/`.
- **Frontend discipline:** reuse `public/css/modern-sakip.css` components and the incumbent Bootstrap CDN pipeline; verify at runtime whether a Vite bundle is actually loaded on the layout you are touching.
- **Verification before claiming done:** `php artisan test` (or `make test`) plus `./vendor/bin/pint --test`; report any gate blocked by toolchain, database, or network availability as unverified rather than passed.
- **Out of bounds:** never edit `vendor/`, `node_modules/`, or generated build output; never run destructive database commands (`migrate:fresh`, `db:wipe`) against a non-testing database.
