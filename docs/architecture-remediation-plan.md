# Architecture Remediation Plan v3 — 2026-08-26 (second refinement)

v2 closed all "verify later" items. This pass additionally ran the full view/route contract audit and resolved the remaining execution-time checks. Every list below is verified against `main` (a6b23eb); nothing is deferred to execution except where explicitly marked *candidate*.

## Objective

One coherent architecture: one service generation, one frontend generation, one tenant-isolation mechanism, docs that match reality, CI gates that can fail, and zero broken route references in rendered views.

**Global definition of done**

1. No duplicate short class names; no dead controllers/services/models/JS modules; no orphan views.
2. Every `route()` call and runtime `fetch()` in *rendered* code resolves to a defined route.
3. `instansi_id` tenancy enforced by a shared global scope on every directly-scoped model.
4. CLAUDE.md, the sakip skills, and README match the real system.
5. Pint enforced in CI; the vacuous tsc step removed.

## Assumptions and constraints (verified facts)

- Monolith app; no external consumers of provider `publishes()`. Existing migrations never rewritten.
- Tests pass today (75 / 2 skipped / 1 risky); every phase keeps them passing.
- Pint fails on **205 files**; style-fix commit must land first.
- **No `tsconfig.json`, zero `.ts`/`.tsx`** — the CI tsc step checks nothing; `typescript` dep is unused.
- `.gitignore` ignores `*.md` globally except `CHANGELOG.md` — this plan file needs `!docs/**/*.md` to be committable.
- **[v3]** The `debug` permission is used by three route groups (`sakip-test`, `healthz`, `debug/session`) — keep the permission; delete only the `sakip-test` group.
- **[v3]** `report_templates.instansi_id` is nullable → templates can be global; `ReportTemplate` stays **unscoped** (documented decision).
- **[v3]** Both seeders give null-instansi users the **Super Admin** role (`AdminUserSeeder`, `UserSeeder`) → the default-deny scope for non-superadmin null-instansi users has zero seeded casualties. v2's R5 risk is retired.
- Each phase = one branch + PR; small commits; plain-git rollback.

## Verified inventory

| ID | Item | Evidence |
|----|------|----------|
| F1 | Two live `SakipService` classes | root (29 methods) vs `Sakip\SakipService` (50 methods) |
| F2 | Dead service | `Calculation\PerformanceCalculationService` — 0 refs |
| F3 | Dead controllers | `Api/Sakip/SakipApiController`, `Api/Sakip/SakipDataTableController`, `Sakip/SakipController` (unrouted); `SakipTestController` (dev-only). All 7 `Admin/*` controllers are routed — dead list is complete |
| F4 | Orphan domain layer | `IndikatorKinerja`/`LaporanKinerja` + policies, no tables; 0 seeder/factory refs |
| F5 | **[v3] 13 orphan views** (~16% of 80) | verified: `sakip/dashboard.blade.php`, `sakip/test.blade.php`, `sakip/programs/index`, `sakip/activities/index`, `sakip/dashboard/{assessor,executive,data-collector,auditor}`, `sakip/dashboard-modern`, `sakip/data-collection/create-modern`, `sakip/components/{dashboard,notification}`, `welcome` (no route renders it). `SakipDashboardController` renders only `sakip.dashboard.index`. Convention-resolved and **not** orphans: `errors.403`, `components/modern/stat-card` (live via `<x-modern.stat-card>` ×4) |
| F6 | `SakipServiceProvider` fully removable | after F3: root `SakipService`/`SakipDataTableService`/`SakipNotificationService` have 0 consumers; all `@sakip*` directives and `$sakipConfig`/`$currentUser` composers 0 view usages |
| F7 | Three frontend generations | Gen A live: Blade layouts + Bootstrap/jQuery/DataTables CDN + `public/js/{custom-scripts,helpers,admin-settings}.js` + Vite `app.js` → 5 vanilla modules + chart.js + Tailwind. Gen B dead: `resources/js/sakip/{assessment,audit-trail,report,data-collection}.js` (built, imported by nothing; `window.SAKIP_*` assignments exist only in app.js for the 5 live modules and in dead views/`public/js/sakip/`). Gen C phantom: React/Inertia deps + vite react plugin + react chunks, zero code |
| F8 | **[v3] Two live 500s (hotfix-grade)** | `sakip/reports/index.blade.php:143` → `route('sakip.reports.edit')` missing (view rendered by `ReportController@index`; any report row makes the page 500); `sakip/profile/show.blade.php:110` → `route('institution.profile')` missing (view rendered by `ProfileController@show`) |
| F9 | `public/js/sakip/` stale third copy | no view or live `public/js` root file references it |
| F10 | Tenancy manual | only `PerformanceIndicator.php:264` scoped |
| F11 | Tenant semantics | live scope: no-auth → no filter; SUPER_ADMIN → no filter; others → `WHERE instansi_id = user's` (null → sees nothing). Dead `SakipApiController` guard's "null-instansi may request any" semantics are discarded (see F12) |
| F12 | `SakipApiTenantIsolationTest` reflectively tests the dead controller | delete test with controller; its 2 cases become mandatory P4 tests |
| F13 | Fat controllers | DataCollection 1184, Assessment 998, PerformanceMeasurement 956, PerformanceIndicator 919, Report 884, SakipAudit 863 |
| F14 | **[v3] Legacy `/api` redirect block is broken** | `routes/web.php:465–492` redirects call `route('sakip.api.datatables.{program,kegiatan,indicator}')` — none exist → those endpoints 500 instead of redirecting. Delete the block |
| F15 | Docs phantom claims | CLAUDE.md: Repositories, `SanitizeInputMiddleware`, Sanctum, api.php CRUD, Inertia+React frontend |
| F16 | CI gates advisory | `pint --test \|\| true` (failing on 205 files); `tsc --noEmit \|\| true` (vacuous) |

## Phase dependency graph

```
P0 (CI + baseline)
 ├─► P1a (live 500 hotfix) ─► P1b (frontend/view cleanup) ─► P2 (dead PHP) ─► P3 (guards) ─► P6 (docs)
 └─► P4 (InstansiScope)  ← independent track; merge after P2
P5 (controller slimming)  ← after P2; one PR per controller
```

---

## P0 — CI truth and baseline  *(effort: M — dominated by the 205-file pint commit)*

1. `.gitignore`: add `!docs/**/*.md`; commit this plan file.
2. `./vendor/bin/pint` (fix mode) — standalone commit, ~205 files. Land before starting other branches.
3. Remove `|| true` from the CI pint step; **delete** the tsc step and the `typescript` devDependency.
4. Fix the 1 risky + 2 deprecated tests.
5. Archive untracked root session `.md` artifacts into `docs/history/` or delete; move tracked `fix-onclick.sh` / `extract-scripts.sh` to `scripts/`.
6. Baselines: `php artisan route:list --json > storage/route-baseline.json`; view file list.

**DoD:** CI green with a real pint gate; zero risky/deprecated; baselines recorded.

## P1a — Hotfix live 500s  *(effort: S — ship first, own PR)*

1. `sakip/reports/index.blade.php:143` — the edit button. Decision: if `ReportController` has `edit()`/`update()` methods and the product wants editable reports, add the missing `sakip.reports.{edit,update}` routes; otherwise remove the button. Verify with a feature test hitting `/sakip/reports` with ≥1 report.
2. `sakip/profile/show.blade.php:110` — repoint `route('institution.profile')` to the existing `profile.show` route (it links to the same page's context).
3. Feature tests for both pages (currently uncovered).

**DoD:** both pages render with seeded data; tests green.

## P1b — Frontend reconciliation  *(effort: M)*

1. Delete the 13 verified orphan views (F5). *Candidates requiring one precise grep each before deletion:* `components/modern/data-table` (check `@component`/`@include` usage — the `<x-*>` tag route is already ruled out). Never delete `errors.*` or tag-resolved components.
2. Delete Gen B JS modules (`assessment.js`, `audit-trail.js`, `report.js`, `data-collection.js`) + their `vite.config.js` inputs. Pre-check: `window.SAKIP_` in remaining views comes back empty after step 1 (verified: only orphan views use those globals).
3. Delete Gen C: react plugin, `sakip-vendor`/`sakip-components` manualChunks; from `package.json` remove `react`, `react-dom`, `@inertiajs/react`, `recharts`, `lucide-react`, `@types/react*`. Keep `chart.js` (used by live modules).
4. Delete `public/js/sakip/` (F9).
5. Live contract re-check: `data-api-url` values existed only in orphan views (deleted); cascade-dropdown fetches in `indicators/create+edit` point at real routes (verified). Re-run the extraction after deletions as a regression gate (see P3).
6. `npm run build` green; `php artisan view:clear && view:cache`.

**DoD:** build green; no rendered view references a missing route; dashboard + indicators create/edit (cascade dropdowns) smoke-tested.

## P2 — Dead PHP removal  *(effort: M)*

Precondition: `SakipApiTenantIsolationTest` deleted with the dead controller; its 2 cases recorded as P4 requirements.

1. `app/Services/Calculation/PerformanceCalculationService.php`.
2. Controllers: `Api/Sakip/SakipApiController`, `Api/Sakip/SakipDataTableController`, `Sakip/SakipController`, `SakipTestController` + its route block (`routes/web.php:397–420`). **Keep** the `debug` permission (`healthz`, `debug/session` use it).
3. Legacy `/api` redirect block (`routes/web.php:465–492`) — targets don't exist (F14).
4. Orphan domain layer: `IndikatorKinerja`/`LaporanKinerja` models + policies + `AppServiceProvider` registrations + `Kegiatan::indikatorKinerjas()`.
5. Root services: `app/Services/{SakipService,SakipDataTableService,SakipNotificationService}.php`.
6. `app/Providers/SakipServiceProvider.php` + its `bootstrap/providers.php` entry (F6). `SakipDashboardService` class stays.
7. After each batch: `composer dump-autoload`, pint, full tests, `route:list` diff.

**DoD:** one `SakipService` FQCN; greps for deleted FQCNs / `app('sakip')` / `app('sakip.*')` empty; tests green.

## P3 — Regression guards  *(effort: S)*

1. **Duplicate class-name test:** scan `app/` for two classes sharing a short name.
2. **Route-name existence test:** scan `resources/views/**/*.blade.php` for `route('…')` (excluding `request()->route(`) and assert each name exists; also scan `routes/` itself (would have caught F14).

**DoD:** both tests in CI, green.

## P4 — InstansiScope  *(effort: M–L; independent track)*

Semantics (fixed; matches the only live implementation plus seeder evidence):

| Context | Behavior |
|---------|----------|
| No authenticated user (console/queue/seeder) | No filter |
| `SUPER_ADMIN` role | No filter |
| Any other authenticated user | `WHERE instansi_id = user.instansi_id`; null instansi sees nothing (default-deny; no seeded user is affected — both null-instansi users are Super Admin) |
| Escape hatch | `withoutInstansiScope()` for admin reports/jobs |

1. `app/Models/Scopes/InstansiScope.php`; refactor `PerformanceIndicator`'s inline scope onto it.
2. Apply to direct-column models: `Program`, `SasaranStrategis`, `PerformanceData`, `Report`, `AuditLog`. `ReportTemplate` explicitly **excluded** (nullable column = shared/global templates; docblock the decision).
3. Indirect models (`Kegiatan`, `Target`, `Assessment`, `EvidenceDocument`): no scope; verify policies + add route-model-binding tests (404, not 403, for foreign IDs).
4. Tests: the 2 ported tenant cases + one per scoped model (foreign rows invisible incl. via binding; superadmin bypass; unauthenticated context unfiltered).
5. Keep per-query `where('instansi_id', …)` filters for now (defense-in-depth).

**DoD:** tests green; `db:seed` green; one queued job runs.

## P5 — Controller slimming  *(effort: L, incremental)*

Order: DataCollection (1184) → Assessment (998) → PerformanceMeasurement (956) → PerformanceIndicator (919) → Report (884) → SakipAudit (863). Per controller: characterization tests first, then move logic into the existing services. Behavior-preserving; no business-logic privates; ≤ ~300 lines.

## P6 — Documentation truth  *(effort: S)*

Correct CLAUDE.md, `.agents/.claude` sakip skills, `.codex/AGENTS.md`, README, `docs/technical-documentation.md`: remove Repositories / `SanitizeInputMiddleware` / Sanctum / api.php CRUD / Inertia+React claims; document the real stack (Blade + Bootstrap/jQuery/DataTables CDN + Tailwind 4 + Vite vanilla-JS modules + chart.js; `/sakip/api/*` session-auth AJAX; `InstansiScope` + policies tenancy).

---

## Risks

| # | Risk | Mitigation |
|---|------|------------|
| R1 | 205-file pint commit conflicts with in-flight branches | Land P0 first |
| R2 | Orphan-view sweep false positives (convention-resolved views) | F5 tiering: verified list vs candidates; `errors.*` and `<x-*>` components never auto-deleted |
| R3 | `public/js/helpers.js` (live) overlaps `resources/js/sakip/helpers.js` | Only `public/js/sakip/` is deleted in P1b |
| R4 | Provider deletion breaks a dynamic container key | Re-grep `app('sakip` at execution; tests + `view:cache` after |
| R5 | ~~Default-deny blinds null-instansi users~~ | Retired — seeders give such users the Super Admin role |
| R6 | Broken inheritance hidden by compiled-view cache | `view:clear` + `view:cache` in every phase's verification |
| R7 | Route removal breaks unnoticed consumers | `route:list` baseline diff in each PR |

## Execution summary

| Phase | Effort | Can start after | Ships |
|-------|--------|-----------------|-------|
| P0 | M | now | CI gates, baselines, hygiene |
| P1a | S | P0 | two live 500s fixed |
| P1b | M | P1a | one frontend generation, 13 orphan views gone |
| P2 | M | P1b | one PHP service generation |
| P3 | S | P2 | recurrence guards |
| P4 | M–L | P0 (merge after P2) | structural tenancy |
| P5 | L | P2, incremental | thin controllers |
| P6 | S | P3 | truthful docs |

## Verification per phase PR

1. `composer test` green; `pint --test` clean; `npm run build` green (P1+).
2. `route:list --json` diff vs baseline — only intended changes.
3. `php artisan view:clear && view:cache` succeeds.
4. Manual smoke: login → dashboard → master-data CRUD → data collection submit → report export → profile page.
5. P4 additionally: `db:seed` + one queued job.

## Rollback

Independent branches; deletions revert via git. No schema changes in any phase.
