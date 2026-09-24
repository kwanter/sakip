---
title: Project Discovery & Idea Assessment: Admin Triage Landing (period-scoped, instansi-explicit)
status: DRAFT (Phase 0)
date_analyzed: 2026-09-24
target_slug: admin-triage-landing
verdict: GO
---

# Project Discovery & Idea Assessment: Admin Triage Landing (period-scoped, instansi-explicit)

> Phase 0 artifact produced by `/tdd-explore-ideas` on 2026-09-24. Every claim below was verified against the working tree at `95ef4cc`.
> Verdict: **GO** — with four open product decisions handed to `/tdd-prd` (section 7).

## 1. Problem Statement & Business Justification

**Who.** The HQ landing surface `GET /admin/dashboard`, aimed at `Super Admin` and any user holding the `admin.dashboard` permission — the platform administrators `PRODUCT.md` names as the admin panel's primary users.

**The problem.** The page answers *"how much data exists"* instead of *"what needs my attention first"*. `PRODUCT.md` states the expectation verbatim: admins "arrive after login expecting a health-triage
landing: what needs attention first (pending submissions, verification queue, audit anomalies)". Today the landing delivers only the first of those three, and without a frame of reference.

Measured in code:

| Symptom | Evidence |
| --- | --- |
| Three figures with three different time dimensions, presented as equal peers | `AdminDashboardController.php:24` all-time `submitted`; `:26-28` period-filtered `validated`; `:29` all-time indicator inventory |
| No instansi frame at all — the counts are agency-blind | The controller never states whose data it counts; tenancy filtering stays implicit inside `InstansiScope` |
| A triage slot spent on telemetry | `:32-34` counts `AuditLog` `action = 'login'` events over 7 days, labelled "Login 7 Hari Terakhir" |
| Only one of three existing domain queues is surfaced | `PerformanceData::submitted()` (`Models/PerformanceData.php:197`), `Assessment::pending()` (`:171`), and `Report::submitted()` (`:183`) all exist; the latter two are unused on this page |
| The third named signal does not exist anywhere | No audit-anomaly concept exists in `app/` |
| Zero automated coverage | No test references `admin/dashboard` or `admin.dashboard` |

**Why the existing features are not enough.** The queue pages already exist and are filterable — `Sakip/DataCollectionController.php:168-171` honours `?validation_status=submitted`, and the landing's CTA
already deep-links there (impeccable critique P1, fixed 2026-08-29). What is missing is not a list but a **decision-ready signal**: comparable numbers, a declared reporting period, a declared instansi scope,
and the other two queues that require HQ attention. The SAKIP-side dashboard (`SakipDashboardService`) serves the agency-side personas (executive, assessor, auditor, data collector), not HQ triage.

## 2. Technology Stack & Infrastructure Grounding

Referencing `docs/ARCHITECTURE.md` (2026-09-24):

- **Core framework / language:** Laravel 12, PHP `^8.3` (CI matrix 8.3/8.4; local verification ran 8.5.10), Blade + Bootstrap 5.3 via CDN styled by `public/css/modern-sakip.css`.
- **State management / data layer:** server-rendered Blade with no client state store; Eloquent with global scopes. Production datastore is MySQL 8; tests run on SQLite `:memory:`.
- **Authorization:** Spatie Laravel Permission plus gates in `AppServiceProvider.php:88-120`; `Gate::before` grants `Super Admin` every ability (`:88-90`).
- **Key dependencies & existing public seams:**
  - Period vocabulary: `SakipDashboardService::getDateRange()` (`:608-649`) accepts `current_year` (default), `current_quarter`, `last_quarter`, `current_month`, `last_month`, and returns a `[start, end]` Carbon pair.
  - Queue state: `PerformanceData::submitted()`, `Assessment::pending()`, `Report::submitted()`.
  - Deep-link target: `Sakip/DataCollectionController.php:168-171` (`?validation_status=`).
  - Caching precedent: `SakipDashboardService.php:46` uses `Cache::remember(..., now()->addMinutes(15))` with per-user keys, invalidated by `clearCache()` (`:651-655`).
  - Indexes already present: `idx_perf_data_instansi_period` (`2026_01_23_add_performance_data_indexes.php:18`), `idx_targets_year` and `idx_targets_indicator_year` (`2026_01_23_add_targets_indexes.php:18-21`).
- **Missing seams this idea must create:** no service owns admin metrics; no view composer owns the cross-page sidebar badge; there is no first-class "active reporting year" concept anywhere (`config/sakip.php` has no year key; `SystemSettingsSeeder` seeds only `app.name` and `app.description`).

## 3. Current Architecture Assessment & Tech Debt

### Strengths

- **The triage pattern already exists and is right:** a conditional attention strip that renders only when work is pending, paired with a CTA that carries a real filter (`?validation_status=submitted`). Empty states are honest ("Belum ada aktivitas tercatat").
- **Tenant isolation is a genuine guardrail**, not a convention: `InstansiScope` is applied to `instansi_id`-bearing models and is pinned by `TargetTenantIsolationTest` and `EvidenceUpdateIsolationTest`.
- **The database is ready for period + instansi slicing** — the composite indexes already exist, so this is not a schema problem.
- **A vetted design baseline exists:** three `.impeccable/critique/` runs against `admin/dashboard.blade.php`; the newest scores 25/40 with 1×P1 and 0×P0.

### Tech debt & coupling risks

**C1 — Divergent authorization on the same route (functional defect).** `routes/web.php:201` guards the group with `can:admin.dashboard`, while `AdminDashboardController.php:14` adds
`$this->middleware('can:access-admin-dashboard')`. That second permission is defined nowhere: it is absent from `RolesAndPermissionsSeeder.php:31` (which seeds `admin.dashboard`) and is referenced only in
that one line. Super Admins survive it thanks to `Gate::before` (`AppServiceProvider.php:88-90`), so the page *appears* to work; a non-Super-Admin holding `admin.dashboard` passes the route guard and is then
rejected by the controller. The `isAdmin()` branch of the `admin.dashboard` gate (`AppServiceProvider.php:93-94`) is dead code for the same reason, since `User::isAdmin()` is exactly `hasRole('Super Admin')`.
No test pins any of this.

**C2 — Period vocabulary is duplicated and hand-rolled.** The controller builds `now()->format('Y-m')` (`:25`) and compares it to `performance_data.period`, a `string(7)` "YYYY-MM" column
(`2025_10_14_080002_create_performance_data_table.php:31`), while the codebase already ships `getDateRange()` and a year-filtering capability that is itself **duplicated across two files**:
`trait ForYear` inside `app/Models/Scopes/ForYearScope.php` and `trait ForYearTrait` in `ForYearTrait.php`, the first using `date('Y')` and the service using `Carbon::now()`. Meanwhile `Target.year` is an
indexed `integer` (`2025_10_14_080001_create_targets_table.php:23,37`) — the domain is annual with quarterly breakdowns, so a calendar month is not a decision unit.

**C3 — Incomplete signal set; telemetry occupies the triage slot.** Two of three approval queues are invisible (see section 1), and `audit anomalies` — explicitly named in `PRODUCT.md` — has no implementation. The space instead goes to a 7-day login count, which contradicts `PRODUCT.md` product principle #1: *"screens answer questions about the performance pipeline, not the server."*

**C4 — Asymmetric dashboard architecture; the newest code drifts from the charter.** `SakipDashboardService` (657 lines) is service-layered, cached, and period-aware. `AdminDashboardController` (50 lines)
holds raw Eloquent, has **no caching** (verified: zero `Cache::` or `remember(` occurrences), and no period abstraction. Three conflicting stories about caching: `config/sakip.php` declares
`dashboard.cache_ttl_minutes => 5`, the service hard-codes **15** minutes (`:46`), and the admin controller ignores caching entirely. `docs/ARCHITECTURE.md` §6 and `CONSTITUTION.md` Prinsip V both require
thin controllers delegating to services, so this is a governance deviation in the newest layer of code.

**C5 — Inverted coupling for cross-page chrome.** `layouts/modern.blade.php:91-92` renders the sidebar badge from `isset($pendingDataCount)`, and the only producer is `AdminDashboardController` (`:24`,
consumed at `resources/views/admin/dashboard.blade.php:20,26,46`). Any other page rendered with that layout silently loses the queue badge; the badge cannot be tested without booting that controller.

**C6 — Testability friction.** With no service seam and no view composer, the only way to test this behaviour today is an HTTP feature test on a controller that mixes authorization, querying, and date formatting. There is no place to assert "counts for period X are Y" without also asserting the whole page.

**C7 — Dead affordances erode the signal** (impeccable critique, P2, parked): the header search has no handler and the notification bell keeps a permanent red dot. On a triage surface, a fake alarm trains users to ignore real ones. Layout-shell scope — noted, deliberately not part of this increment.

## 4. Solution Shaping & Trade-Offs

### Option A (Recommended) — Period-scoped, scope-explicit triage read-model

One vertical slice that turns the landing from a museum of totals into a defensible signal:

1. **Introduce a period value object** with the vocabulary that already exists (`current_year` default, `current_quarter`, `last_quarter`, `current_month`, `last_month`) and a single `range()` resolver.
   `SakipDashboardService::getDateRange()` becomes a consumer of it, and the duplicated `trait ForYear` / `trait ForYearTrait` pair is consolidated onto it. Net effect: one source of truth for
   "what does this period mean", with the `date('Y')` vs `Carbon::now()` drift removed.
2. **Extract an admin triage service** (read model) returning a small structured result: the period descriptor (key plus Indonesian label), the scope descriptor (`Semua Instansi` for HQ users vs the
   resolved instansi name), and the counts for the **three** queues — `PerformanceData` `submitted`, `Assessment` `pending`, `Report` `submitted` — all resolved on the same period range. The controller
   keeps only authorization plus view binding.
3. **Make the frame visible in the UI:** a period selector (default `current_year`, toggle month/quarter) and an always-visible scope chip. Every count carries the same period; the login-count card leaves the triage block and becomes, at most, secondary activity content.
4. **Every count deep-links to an already-filtered queue**, reusing `?validation_status=` and the equivalent parameters on the assessment/report indexes.
5. **Fix C1 while the seam is open:** remove the phantom `access-admin-dashboard` middleware so route and controller agree on `admin.dashboard`. This is pinned by a RED test first.
6. **Move the sidebar badge off the controller** into a view composer (or dedicated component) so the layout owns its own data (C5), with the same period/scope semantics as the landing.

**Pros:** every number becomes comparable and reportable; the domain's own annual/quarterly vocabulary is reused instead of reinvented; two pure seams appear — the period resolver (unit-testable) and the triage read model (unit-testable with factories) — leaving only a thin HTTP test for the page and its authorization. It also clears a latent 403 bug and a governance deviation in one slice.

**Cons / costs:** the increment needs a decision about the default period and about empty-period behaviour, plus a little more UI state (selector and chip). It touches three call sites (`AdminDashboardController`, the layout badge, the period resolver consumers), so the blast radius is wider than a cosmetic fix — mitigated by the fact that the existing test suite is GREEN and these seams are new.

**Why it facilitates clean TDD:** the hypothesis in section 5 can be proven at two seams without any HTTP: (a) period key → date range, (b) user plus period → counts. The authorization defect is provable at the HTTP seam with a synthetic `Admin` user, and it fails today — a genuine legitimate RED.

### Option B (Alternative) — Honest labels only (rejected)

Keep `now()->format('Y-m')` and timeless counts; only fix the wording ("per <bulan> <tahun>", "s.d. hari ini"). Near-zero cost and it does answer part of impeccable findings #2 and #10.

**Rejected because:** SAKIP's decision unit is the reporting year with quarterly breakdowns (`targets.year` is an indexed integer; `Target` carries quarterly columns), so a calendar month is the wrong
frame; agency-blindness and the incomparable cumulative-vs-period mix remain; and the impeccable critique's central verdict stands: *"period context and instansi slicing are not charts — they are the
information architecture of the counts themselves."* It also leaves C1, C4, and C5 untouched, so the debt keeps compounding.

### Option C (Alternative) — Full queue inbox with bulk actions (deferred, not rejected forever)

Replace the stat cards with one actionable, age-sorted queue across the three statuses, with bulk assignment.

**Rejected for this increment because:** it duplicates a surface that already exists and is already filterable (`sakip.data-collection.index` with `?validation_status=`), and bulk write paths would
require new policies, audit entries, and their own authorization tests — a scope explosion that violates `CONSTITUTION.md` Prinsip V (simplicity / Rule 0) and creates two sources of truth for the same list.
It remains a credible follow-up once the read-model exists.

### Explicitly out of scope for this increment

- **Audit-anomaly rules.** "What counts as an anomaly" is a rule-design problem of its own and overlaps idea **I2** (anomaly detection before `submitted → validated`). It must not be smuggled into this slice.
- Charts or trend visualisation (the SAKIP service already owns chart data; HQ triage needs counts and links first).
- Bulk actions (Option C).
- Layout-shell P2 items: duplicate H1, inert search, fake bell dot, `--warning` token unification, dark-mode token remapping, mobile `.page-header` margins.
- The two known backlog items **F1** (2 skipped rate-limit tests) and **F2** (63 PHPUnit 12 deprecations) — both are *HOW* concerns routed to `/tdd-bug-report` and `/tdd-prd`/`/tdd-spec`, not Discovery ideas.

## 5. TDD Verification Hypothesis

> **Hypothesis:** If the admin triage landing derives every figure from one period-scoped, instansi-explicit read model, then an HQ administrator can answer *"what needs my attention first, for which agency, in which reporting period"* without leaving the page — and each figure is defensible in a formal report.

**Core BDD verification scenario**

- **Given** an HQ user holding the `admin.dashboard` permission (explicitly including a non-Super-Admin `Admin`), and seeded performance data spread across two `Instansi` and two reporting periods,
- **When** the user opens `GET /admin/dashboard` and the period defaults to the current reporting year,
- **Then** every displayed figure is derived from that single period range, the instansi scope is stated explicitly on the page, and each figure links to its queue pre-filtered to the same period and scope.

**Target public test seams**

| # | Seam | Boundary | Why it is testable without HTTP |
| --- | --- | --- | --- |
| 1 | Period resolver (value object) | `App\Support\...` domain boundary | Pure mapping: period key → `[start, end]`; asserts quarter boundaries, month cases, and the `current_year` fallback for unknown keys |
| 2 | Triage read model (service) | Application service boundary | `user + period → counts`; uses existing factories (`PerformanceDataFactory`, `AssessmentFactory`, `ReportFactory`) with `RefreshDatabase` + `$seed` |
| 3 | `GET /admin/dashboard` | HTTP route boundary | Pins authorization (C1) and that the rendered page carries the period label and scope chip |
| 4 | Layout badge (view composer) | View rendering boundary | Read the badge on a page other than the admin dashboard — this is the assertion that proves C5 is fixed |

**The legitimate RED that exists today:** seam 3 with a synthetic non-Super-Admin `Admin` user holding `admin.dashboard` fails with 403, because of `AdminDashboardController.php:14`. Pin it with a failing test before touching the middleware.

## 6. Domain Vocabulary (proposed for `CONTEXT.md`)

Proposed — `CONTEXT.md` is created lazily, only once you approve these terms (no file is created yet).

- **Periode Pelaporan** (Reporting Period): the annual, quarterly, or monthly window that scopes every triage figure, expressed with the canonical keys `current_year` (default), `current_quarter`, `last_quarter`, `current_month`, `last_month`.
  (`_Avoid_: "periode", "periode data", "bulan berjalan", "date range"`)
- **Cakupan Instansi** (Instansi Scope): the explicit statement of which `Instansi` a figure covers — all agencies (HQ) or exactly one.
  (`_Avoid_: "tenant", "tenant filter", "wilayah"`)
- **Antrean Verifikasi** (Verification Queue): records awaiting a decision at a workflow step; canonical instance is `PerformanceData` with status `submitted`.
  (`_Avoid_: "inbox", "pending list", "tugas saya"`)
- **Antrean Asesmen** and **Antrean Laporan**: the `Assessment` `pending` and `Report` `submitted` queues; in the UI they must stay distinct from the verification queue, never merged into a single anonymous number.
  (`_Avoid_: "antrean lain", "menunggu"`)
- **Landing Triage** (Triage Landing): the HQ landing whose purpose is to direct attention before administration.
  (`_Avoid_: "dashboard admin", "beranda admin", "overview"` — "dashboard admin" collides with the agency-side SAKIP dashboards)

## 7. Handoff Notes for Product Manager (`/tdd-prd`)

**Product decisions the PRD must close**

1. **What is the "active reporting year"?** Server-clock calendar year, or a first-class setting (e.g. `sakip.active_year`)? `[ASSUMPTION]` I recommend clock-derived for this increment *behind a config seam*, so it can later become a setting with fiscal-year semantics without touching call sites.
2. **Which column scopes `Assessment` and `Report` by period?** `performance_data.period` is a `string(7)` "YYYY-MM"; `targets.year` is an integer. The other two models have no documented period field. `[ASSUMPTION]` I do **not** assume `created_at`/`updated_at` is acceptable — the Spec phase must confirm the real anchor before the PRD promises a period-scoped count for those queues.
3. **Empty-period behaviour and copy** — what a zero-work period says, and whether the attention strip hides entirely (today it is conditional).
4. **Attention-strip composition** — does it stay `PerformanceData`-only or carry all three signals?

**Constraints and risks**

- **No destructive migration expected.** But the mapping period → `performance_data.period` (`YYYY-MM` string) versus period → `targets.year` (integer) must be defined **once** in the period object, not per call site.
- **Caching:** `clearCache()` today forgets three hard-coded keys (`SakipDashboardService.php:653-655`). The new read model must either define its own invalidation or deliberately skip caching. `[ASSUMPTION]` skip caching in this increment; revisit with real query timings.
- **Authorization scope creep:** while fixing C1, do **not** silently widen access; the PRD should state precisely who may see the landing (Super Admin plus `admin.dashboard` holders) and the spec must pin it with a test.
- **Local verification requires the CI-parity environment** (the agent shell exports production-like `.env` values: MySQL host `mysql`, `phpredis`). See the Session 3 dead-end in `.agents/instructions/memory.instructions.md`.
- **ADR:** none created. The Triple-Gate is not met — the period-model choice is reversible, would not surprise a new engineer, and was a normal product call rather than a paradigm shift. Revisit only if the active year becomes a first-class setting with fiscal-year semantics.

**Next step**

```text
/tdd-prd Create a PRD based on the approved discovery draft in @docs/discovery/idea-admin-triage-landing.md
```
