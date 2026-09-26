---
title: "Reference: Admin Triage Landing (Landing Triage)"
version: 1.0
date_created: 2026-09-26
last_updated: 2026-09-26
status: Active
quadrant: Reference (Diátaxis)
upstream_spec: spec/spec-admin-triage-landing.md (v1.5)
upstream_prd: docs/prd/prd-admin-triage-landing.md (v1.2)
upstream_review: docs/review/code-review-admin-triage-landing-2026-09-25.md (v1.0, 17/17 findings closed)
upstream_architecture: docs/ARCHITECTURE.md (§5, §6, §10 seams S1–S4)
living_examples:
  - tests/Unit/Support/ReportingPeriodTest.php
  - tests/Unit/Services/AdminTriageServiceTest.php
  - tests/Feature/AdminTriageLandingTest.php
  - tests/Feature/SidebarQueueBadgeTest.php
  - tests/Support/ExtractsTriageMarkup.php
generated_by: /tdd-generate-docs
---

<!-- markdownlint-disable -->

# Reference: Admin Triage Landing (Landing Triage)

This document is the factual description of the Admin Triage Landing — the period-scoped, instansi-explicit triage read model at `GET /admin/dashboard`. It maps the delivered code and its
public test seams 1:1 to text. It contains **no instructions and no rationale**: for the task recipes see `docs/how-to/`, for the design reasoning see `docs/explanation/`.

> [!NOTE]
> Business terms are quoted verbatim from `CONTEXT.md`. Where a term is introduced, its ratified `_Avoid_` synonyms are listed so a reader can recognise the rejected vocabulary.
> Every claim below is grounded in the files listed in the front matter; the concrete test case identifiers (`TC-nnn`) refer to
> `docs/checklist/checklist-admin-triage-landing.md`.

## 1. Scope & Sources

| Item | Value |
| --- | --- |
| Interface | `GET /admin/dashboard`, route name `admin.dashboard` (`routes/web.php:200-206`) |
| Feature phases | Phase 1 — triage landing, seams S1–S3; Phase 2 — sidebar queue badge, seam S4 |
| Read/write character | **Read-only**: no migration, no new column, no cached state, therefore no new `AuditLog` obligation |
| Requirements | REQ-001 … REQ-015 (`spec/spec-admin-triage-landing.md` §3.1) |
| Acceptance criteria | AC-001 … AC-039 (same Spec, §5) |
| Verification evidence | Latest run 2026-09-26 (after backlog F1 closed): **151 passed / 0 skipped, 731 assertions**; full suite **12.37 s**; Unit **1.34 s**; Pint PASS **253 files**. Earlier runs and their figures are listed with their commands in `docs/retro/retro-admin-triage-landing-2026-09-26.md` §0 |
| Coverage | Measured 2026-09-26 (PCOV): **8.8 % line over `app/`**, this slice **91.8–100 %**; **branch coverage UNVERIFIED** (Xdebug absent) — `docs/retro/retro-admin-triage-landing-2026-09-26.md` §1. The pre-retro "no driver" note is superseded |

## 2. Route & Authorization

| Aspect | Delivered contract | Evidence |
| --- | --- | --- |
| Path & name | `GET /admin/dashboard` → `admin.dashboard` | `routes/web.php:200-206` |
| Middleware stack | `auth`, `can:admin.dashboard`, `throttle:60,1` — declared on the route group | `routes/web.php:201` |
| Ability | `Gate::define('admin.dashboard', fn (User $user) => $user->isAdmin() || $user->hasPermission('admin.dashboard'))` | `app/Providers/AppServiceProvider.php:94-96` |
| Super Admin | Bypasses every ability through `Gate::before` (`hasRole('Super Admin') ? true : null`) | `app/Providers/AppServiceProvider.php:89-91` |
| Controller-level gate | **None.** The former `can:access-admin-dashboard` middleware was deleted; the ability is referenced nowhere else in the repository | REQ-013; `AdminDashboardController.php:17-22` |
| Failure mode | A viewer without the ability receives `403` and the response leaks no triage copy (`data-triage-region`, `Antrean Verifikasi`, `Semua Instansi` all absent) | TC-036 / AC-016 |
| Success mode | The permitted viewer is served directly — never redirected | TC-037 / AC-017 |
| Rate limit | 60 requests per minute per viewer | `routes/web.php:201`; TC-066 (suite regression) |

## 3. Periode Pelaporan Query Contract

`Periode Pelaporan` is *the annual, quarterly, or monthly window that scopes a triage figure*. `_Avoid_: periode, periode data, bulan berjalan, date range, rentang waktu`.

### 3.1 The `period` request parameter

| Input | Resolved key | Evidence |
| --- | --- | --- |
| `period=current_year` (also the absent or empty case) | `current_year` — 1 January … 31 December of the anchor year | TC-028 / AC-005 |
| `period=current_quarter` | `current_quarter` — the quarter containing the anchor date | TC-003 / AC-003 |
| `period=last_quarter` | `last_quarter` — the immediately preceding quarter | TC-003 |
| `period=current_month` | `current_month` — the anchor month | TC-003 |
| `period=last_month` | `last_month` — the immediately preceding month | TC-003 |
| `period=not-a-key`, `period=`, no parameter, non-string (`period[]=current_month`) | `current_year`, **without raising** | TC-002, TC-029, TC-030 / AC-002, AC-006, AC-007 |
| Hostile payload (`current_year'--"<b>boom</b>`) | `current_year`; the payload is never reflected into the page | TC-031 / SEC-003 |

The parameter is untrusted (SEC-003): the controller accepts it only when `is_string()`, and the resolver whitelists it against `ReportingPeriod::KEYS`. Re-requesting the same URL renders the
same period and the same figures (TC-032 / AC-008).

### 3.2 Resolved windows

| Key | Window | Indonesian label | `performancePeriodRange()` |
| --- | --- | --- | --- |
| `current_year` | 1 Jan 00:00:00 → 31 Dec 23:59:59 of the anchor year | `Tahun {year}` | `['YYYY-01', 'YYYY-12']` |
| `current_quarter` | quarter start → quarter end | `Triwulan {I–IV} {year}` | the two months that bound the quarter |
| `last_quarter` | previous quarter start → previous quarter end | `Triwulan {I–IV} {year}` | the two months that bound that quarter |
| `current_month` | month start → month end | `{Bulan} {year}` (Indonesian month name) | `['YYYY-MM', 'YYYY-MM']` — an equal pair |
| `last_month` | previous month start → previous month end | `{Bulan} {year}` | `['YYYY-MM', 'YYYY-MM']` — an equal pair |

Bounds are always inclusive and always `00:00:00` → `23:59:59` (TC-007); the range pair is lexicographically ordered (TC-006); every key resolves inside the anchor year (TC-005).
`isSingleMonth()` is true for exactly `current_month` and `last_month` (TC-004) and is the only shape predicate the class exposes.

### 3.3 The anchor year and its configuration seam

| Precedence | Condition | Result | Evidence |
| --- | --- | --- | --- |
| 1 | A clock is injected (`ReportingPeriod::anchor($now)`) | the injected clock wins; the configuration seam is not consulted | TC-010 / AC-039 |
| 2 | No clock injected, `sakip.reporting.active_year` holds a whole year inside `1970…9999` | the configured year replaces the clock year | TC-011, TC-008 / AC-039, AC-004 |
| 3 | No clock injected, the key is `null` | the server clock | TC-009 / AC-004 |
| — | No clock injected, the value is `'abc'`, `'0'`, `'-5'`, `'2026.5'` or `'99999'` | rejected: the server clock (a malformed value never moves year-scoped queries) | TC-071 / review `STD-A-06` |

`ReportingPeriod::activeYear()` returns an `int` in every branch (TC-008 asserts `assertIsInt`). The seam is a trust boundary: the configured value is validated with
`FILTER_VALIDATE_INT` plus the `1970…9999` window inside `anchor()`, so no second "current year" implementation exists (`app/Support/ReportingPeriod.php:59-74`).

## 4. Rendered Contract: Triage Region and Machine Handles

The triage block is one `<section data-triage-region>` (`resources/views/admin/dashboard.blade.php:20`). The region is the unit of assertion for every content criterion: negative criteria are
asserted inside it, never against the whole body, because the layout shell renders its own copy (`finding C-1`).

| Handle | Element | Carries | Cardinality |
| --- | --- | --- | --- |
| `data-triage-region` | `<section>` | the whole triage block | exactly 1 (TC-042) |
| `data-triage-period` | the same `<section>` | the resolved key, e.g. `current_year` | exactly 1 |
| `data-triage-scope` | the same `<section>` | the `TriageScope` value: `cross_agency`, `agency`, `unassigned` | exactly 1 |
| `data-triage-scope-label` | `<span>` (the chip) | the rendered scope label, with the label also in `title` | 1 per render (TC-033 presence, TC-072 markup contract) |
| `data-triage-period-select` | `<select name="period">` | the five `<option value>` keys in `ReportingPeriod::KEYS` order, one marked `selected` | exactly 1 (TC-051) |
| `data-triage-figure="verification"` | `<a>` | the count in `.stat-value`, the name `Antrean Verifikasi`, the basis label | exactly 1 |
| `data-triage-figure="assessment"` | `<a>` | the count, the name `Antrean Asesmen`, the period-independent basis label | exactly 1 |
| `data-triage-figure="report"` | `<a>` | the count, the name `Antrean Laporan`, the period-independent basis label | exactly 1 |
| `data-triage-period-label` | `<small>` inside the verification figure only | the selected period's Indonesian label | exactly 1 (TC-042, TC-044) |
| `data-triage-attention` | the strip card | rendered **only** when at least one of the three counts is greater than zero | 0 or 1 (TC-046…TC-048) |
| `data-triage-signal="<handle>"` | one row per non-empty queue | exactly one named action per signal | 0…3 |
| `data-triage-empty` | the empty-state card | rendered **only** when all three counts are zero | 0 or 1 |

Figure order is fixed as Verifikasi → Asesmen → Laporan and each figure name renders exactly once inside its own anchor (TC-043 / AC-029). The selector is a plain
`<form method="GET">` whose `action` is `route('admin.dashboard')`, with an explicit `Terapkan` submit control and **no** inline event handler (`onchange=` / `onsubmit=` are asserted absent) — TC-051 / CON-004.

## 5. Figure Contract

`Antrean Verifikasi` is *the performance-data records awaiting a decision at the verification step of the reporting workflow* (`_Avoid_: inbox, pending list, tugas saya, daftar tunggu`).
`Antrean Asesmen` is *the assessments awaiting their first decision, under the same agency coverage as the performance data they assess* (`_Avoid_: antrean lain, menunggu, pending asesmen`).
`Antrean Laporan` is *the reports awaiting HQ review before publication* (`_Avoid_: antrean lain, menunggu, pending laporan`).

| Figure | Population | Period basis | Period-scoped? |
| --- | --- | --- | --- |
| Antrean Verifikasi | `PerformanceData::submitted()` inside the selected range, restricted to the viewer's `Cakupan Instansi` | the selected `Periode Pelaporan`, rendered as `Tahun 2026` / `Triwulan III 2026` / `September 2026` | **Yes** (TC-028, TC-044) |
| Antrean Asesmen | `Assessment::pending()` whose parent `PerformanceData` row is **not** soft-deleted, restricted to the viewer's `Cakupan Instansi` through `performance_data.instansi_id` | `Tidak dibatasi periode` | **No** (A1 ratified; TC-039, TC-044) |
| Antrean Laporan | `Report::submitted()`, restricted to the viewer's `Cakupan Instansi` | `Tidak dibatasi periode` | **No** (D2/D9; TC-040, TC-044) |

`Tidak dibatasi periode` is the single shared constant `AdminTriageSummary::PERIOD_INDEPENDENT_BASIS_LABEL` — a period-independent figure states its limitation on screen rather than implying a
window it does not have. A period switch therefore moves the verification figure only (TC-045 / AC-028).

Each figure is exactly one anchor built with `route()`. No figure is rendered as a non-link and no URL is assembled by string concatenation (REQ-009).

## 6. Cakupan Instansi — the Viewer States

`Cakupan Instansi` is *the agency coverage of a figure or screen — either exactly one agency, or every agency in the system* (`_Avoid_: tenant, tenant scope, scope instansi, wilayah`).
Exactly one scope indicator is always rendered, resolved to one of three states (REQ-004).

| State | `data-triage-scope` | Label rendered | Count population | Evidence |
| --- | --- | --- | --- | --- |
| Cross-agency | `cross_agency` | `Semua Instansi` | every agency: no `instansi_id` restriction | TC-017, TC-018 / AC-011, AC-012 |
| Agency-bound | `agency` | the agency's `nama_instansi`, verbatim | `performance_data.instansi_id = users.instansi_id` | TC-015, TC-033 / AC-009, AC-013 |
| Unassigned | `unassigned` | `Instansi Belum Ditetapkan` | **no count query is issued**: all three figures are `0` | TC-016, TC-034 / AC-010, AC-014 |

`Semua Instansi` is *the coverage label shown to a viewer entitled to figures that are not limited to any single agency* (`_Avoid_: all tenants, global scope, lintas instansi, semua unit`) and is
reserved for `cross_agency`: the label is rendered if and only if the scope is cross-agency (TC-018 / AC-012). `Instansi Belum Ditetapkan` is *the state of a viewer account that carries no agency
assignment and is therefore entitled to no agency-scoped data* (`_Avoid_: semua instansi, instansi kosong, instansi null, instansi global`); the legacy `InstansiScope` default-deny remains intact,
so no screen may label that viewer `Semua Instansi` (TC-034 asserts the string appears nowhere in the response).

Agency resolution has two documented edges: a **soft-deleted** agency still yields its name (label-only `withTrashed()` lookup, never inside a count — TC-023) and when the name cannot resolve at all
the viewer is treated as `unassigned`, so a scope label is never empty (TC-024). A long agency name is truncated by CSS, not by the server: the chip carries `triage-scope-chip`
(`max-width: 49ch`, `text-overflow: ellipsis`) and keeps the untruncated name in `title` (TC-072 / PRD §5.3).

The canonical fixture matrix of Spec §5.0 produces these counts, and the cross-agency count equals the sum of the agency-bound counts for every queue (TC-017):

| Viewer | Antrean Verifikasi | Antrean Asesmen | Antrean Laporan |
| --- | --- | --- | --- |
| Cross-agency | 5 | 4 | 3 |
| Agency-bound — Dinas A | 2 | 3 | 1 |
| Agency-bound — Dinas B | 3 | 1 | 2 |
| Unassigned | 0 | 0 | 0 |

## 7. Deep-Link Contract

Every figure is a single anchor to a named queue route. Sending a parameter a target does not honour is forbidden (REQ-009 / D-S4); the "never sent" column is asserted by TC-056.

| Figure | Target route | Always sent | Conditionally sent | Never sent | Evidence |
| --- | --- | --- | --- | --- | --- |
| Antrean Verifikasi | `sakip.data-collection.index` | `validation_status=submitted` | `period=YYYY-MM`, **only** when the selection is a single month | `instansi`, `category`, `type`, `priority` | TC-038 / AC-018 |
| Antrean Asesmen | `sakip.assessments.index` | `status=pending` | nothing | `period`, `instansi`, … | TC-039 / AC-019 |
| Antrean Laporan | `sakip.reports.index` | `status=submitted` | nothing | `period`, `instansi`, … | TC-040 / AC-020 |

The period is sent to exactly one target because only that target's filter can express the selection exactly: `DataCollectionController@index` accepts an exact `YYYY-MM`. The assessment index reads a
calendar year from `?period=` and the report index reads its own quarter-coded string, so for those two the figure degrades to its status-filtered index instead of claiming a window it cannot express
(D-S4). Every `href` with the query string stripped resolves to a reachable page — `200` or `302`, never `404`/`500` (TC-041 / AC-021).

## 8. Query Budget

The landing's domain statements are counted per viewer state (`db-performance_data`, `assessments`, `reports`, `instansis`, `audit_logs`) and asserted as an **exact** value with a ceiling of `≤ 6`
(TC-052 / AC-034 / CON-003). The ceiling covers the whole render, including the sidebar badge that the layout composes.

| Viewer state | Exact | Composition |
| --- | --- | --- |
| Cross-agency | 5 | 3 counts + 1 badge count + 1 recent-activity list |
| Agency-bound | 6 | 3 counts + 1 agency-name lookup + 1 badge count + 1 recent-activity list |
| Unassigned | 1 | 0 counts (short-circuit, badge included) + 1 recent-activity list |

The badge short-circuits for an unassigned viewer and issues no statement, which is why the unassigned column is `1` rather than `2`. The landing also keeps its pre-existing `limit(10)` recent-activity
list, so the page cannot silently regress into an unbounded list.

## 9. Frozen Indonesian Copy

These strings are a CI contract: changing any of them requires a Spec change and a test change (CON-005).

| Location | String |
| --- | --- |
| Page title / breadcrumb (`admin.dashboard`) | `Panel Admin`, subtitle `Antrean verifikasi dan aktivitas sistem` |
| Region — period selector label | `Periode Pelaporan` |
| Region — submit control | `Terapkan` |
| Region — scope chip | `Cakupan: {label}` where `{label}` is `Semua Instansi`, `Instansi Belum Ditetapkan`, or the agency name |
| Figure name — verification | `Antrean Verifikasi` |
| Figure name — assessment | `Antrean Asesmen` |
| Figure name — report | `Antrean Laporan` |
| Basis label — period-scoped figure | `Tahun 2026` / `Triwulan III 2026` / `September 2026` / `Agustus 2026` (from `ReportingPeriod::label()`) |
| Basis label — period-independent figures | `Tidak dibatasi periode` (`AdminTriageSummary::PERIOD_INDEPENDENT_BASIS_LABEL`) |
| Attention signal — verification | `{n} data kinerja menunggu tindakan` + action `Tinjau Antrean Verifikasi` |
| Attention signal — assessment | `{n} asesmen menunggu tindakan` + action `Tinjau Antrean Asesmen` |
| Attention signal — report | `{n} laporan menunggu tindakan` + action `Tinjau Antrean Laporan` |
| Empty state | `Belum ada pekerjaan tertunda pada periode ini.` |
| Secondary content (retained) | `Aktivitas Terbaru`, `Belum ada aktivitas tercatat`, `Aksi Cepat`, `Lihat Semua Aktivitas` |

Two strings that the triage block **used to** own are asserted absent inside the region: `Aktivitas Login (7 Hari)` (login telemetry, removed by FEAT-006) and `Tervalidasi` (the removed inventory card
was labelled `Tervalidasi (<bulan>)`, and only its fixed part is asserted). The identical `Indikator Kinerja` string that the layout shell renders for its own sidebar link is deliberately **out of scope**: the
region-scoped negative assertion never touches the layout (TC-049, TC-050 / finding C-1).

## 10. Value-Object & Contract Reference

### 10.1 `App\Support\ReportingPeriod` (seam S1)

`final readonly` value object. Constructor is private; instances come from the factories below.

| Member | Type | Contract |
| --- | --- | --- |
| `DEFAULT_KEY` | `string` | `'current_year'` |
| `KEYS` | `list<string>` | `['current_year', 'current_quarter', 'last_quarter', 'current_month', 'last_month']` — the only legal vocabulary |
| `$key`, `$start`, `$end` | readonly props | resolved key; inclusive window bounds (`CarbonImmutable`) |
| `fromKey(?string $key, ?CarbonImmutable $now = null): self` | factory | unknown/empty/null/non-listed key → `DEFAULT_KEY`, never throws |
| `default(?CarbonImmutable $now = null): self` | factory | shorthand for `fromKey(DEFAULT_KEY, $now)` |
| `anchor(?CarbonImmutable $now = null): CarbonImmutable` | factory | the anchor instant under the precedence rules of §3.3 |
| `activeYear(?CarbonImmutable $now = null): int` | static | calendar year of the anchor; `int` in every branch |
| `label(): string` | instance | Indonesian label of the resolved window |
| `isSingleMonth(): bool` | instance | true for `current_month` and `last_month` only |
| `performancePeriodRange(): array{0: string, 1: string}` | instance | `['YYYY-MM', 'YYYY-MM']`, lexicographically ordered and matching the `string(7)` column |

There is no other shape predicate: `isYearScoped()` was removed because no production call site existed (review `STD-A-03`), so the deep-link rule is driven by `isSingleMonth()`.

### 10.2 `App\Support\TriageScope`

| Case | Value | `defaultLabel()` |
| --- | --- | --- |
| `TriageScope::CrossAgency` | `cross_agency` | `'Semua Instansi'` |
| `TriageScope::Agency` | `agency` | `null` — the agency name is supplied by the caller |
| `TriageScope::Unassigned` | `unassigned` | `'Instansi Belum Ditetapkan'` |

### 10.3 `App\Support\AdminTriageSummary`

`final readonly` DTO returned by `summaryFor()`.

| Field | Type | Meaning |
| --- | --- | --- |
| `period` | `ReportingPeriod` | the resolved period object |
| `periodLabel` | `string` | `$period->label()` |
| `scope` | `TriageScope` | the resolved `Cakupan Instansi` |
| `scopeLabel` | `string` | never empty: canonical label, or the agency name |
| `verificationCount`, `assessmentCount`, `reportCount` | `int` | the three figures |
| `verificationUrl`, `assessmentUrl`, `reportUrl` | `string` | absolute URLs of the named queue routes |
| `PERIOD_INDEPENDENT_BASIS_LABEL` | `string` const | `'Tidak dibatasi periode'` |

## 11. `App\Services\AdminTriageService` (seam S2)

The instansi-scoped triage read model. Read-only: every statement it issues is a `select` (TC-027). It receives the viewer explicitly and applies `Cakupan Instansi` explicitly, so its coverage holds
even with no authenticated user.

| Member | Contract |
| --- | --- |
| `summaryFor(User $user, ReportingPeriod $period): AdminTriageSummary` | resolves the scope, counts the three queues, and builds the three deep links |
| `verificationCountFor(User $user, ?ReportingPeriod $period = null): int` | shares the verification query with `summaryFor()`; a `null` period means the default period; returns `0` for an unassigned viewer without issuing a statement |
| `ASSESSMENT_PENDING_STATUS` | `'pending'` — the `?status=` value the assessment index honours |
| `VERIFICATION_FILTER_VALUE` | `'submitted'` — the `?validation_status=` value the data-collection index honours |
| `REPORT_FILTER_VALUE` | `'submitted'` — the `?status=` value the report index honours |

Scope resolution runs in a fixed order and always yields one of the three states: `Super Admin → CrossAgency`; otherwise a non-null `instansi_id` → `Agency`; otherwise `Unassigned`
(`app/Services/AdminTriageService.php:89-100`). `summaryFor()` then adds the label (agency name lookup with the label-only `withTrashed()` carve-out) and falls back to `Unassigned` when no name
resolves; `verificationCountFor()` deliberately skips the label lookup, which is why a badge render spends one statement fewer.

The three count forms are the only permitted ones:

| Queue | Query form |
| --- | --- |
| Verification | `PerformanceData::query()->whereBetween('period', $period->performancePeriodRange())->submitted()` + explicit `where('instansi_id', …)` when agency-bound. Index-backed by `idx_perf_data_instansi_period` |
| Assessment | `Assessment::query()->pending()->whereHas('performanceData', …)` — the parent constraint carries the agency (`performance_data.instansi_id`); `Assessment` has no `instansi_id` of its own |
| Report | `Report::query()->submitted()` + explicit `where('instansi_id', …)` when agency-bound |

`whereHas('performanceData')` is not optional: an assessment whose parent `PerformanceData` row is soft-deleted is **not** pending work, and both viewer states must count the same population
(TC-020, TC-021 / AC-036 / D-S9). No count re-declares a status literal inline, and no count uses `whereYear('period', …)`.

## 12. HTTP Layer, Badge & Configuration

### 12.1 `App\Http\Controllers\Admin\AdminDashboardController`

| Step | Behaviour |
| --- | --- |
| Constructor | receives `AdminTriageService` through dependency injection |
| Read input | `$request->query('period')`, accepted only when `is_string()`, then whitelisted by `ReportingPeriod::fromKey()` |
| Delegate | `summaryFor($viewer, $period)` — no counting logic in the controller |
| Secondary data | `AuditLog::with('user')->latest()->limit(10)->get()` |
| Bind | `view('admin.dashboard', compact('summary', 'recentLogs'))` |
| Authorization | none: the route group owns the single gate (REQ-013) |

### 12.2 `App\View\Composers\SidebarQueueBadgeComposer` (seam S4)

| Step | Behaviour |
| --- | --- |
| Registration | `View::composer('layouts.modern', SidebarQueueBadgeComposer::class)` — `app/Providers/AppServiceProvider.php:186`, beside the existing `csp_nonce_value` composer |
| Guest guard | a guest request returns without composing anything (login and verification pages render no badge) |
| Period | resolved from the request with the same whitelist as the landing, so the badge agrees with the landing figure on a non-default period |
| Value | `pendingDataCount = AdminTriageService::verificationCountFor($user, $period)` |

The badge markup is owned by the layout and is unchanged by this feature: inside `@can('manage-sakip')`, on the `sakip.data-collection.index` sidebar link, the layout renders
`<span class="sidebar-link-badge">{{ $pendingDataCount }}</span>` only when the value is greater than zero (`resources/views/layouts/modern.blade.php:88-93`). Never a zero-value badge (TC-058).

> [!IMPORTANT]
> That sidebar section sits inside `@can('manage-sakip')`, and `manage-sakip` is defined and granted nowhere in the repository, so the badge — and the whole section — is effectively
> Super-Admin-only today through `Gate::before` (`docs/ARCHITECTURE.md` §10; review known finding F-1, deliberately out of this slice). The badge renders the raw integer while the landing figure
> renders `number_format()`ed digits: numerically identical, formatted differently above 999 (review `STD-A-08`).

### 12.3 Configuration — `sakip.reporting.active_year`

| Aspect | Value |
| --- | --- |
| Location | `config/sakip.php:127`, inside the existing `reporting` block (no new block) |
| Definition | `env('SAKIP_ACTIVE_YEAR', null)` |
| Meaning | the calendar year treated as the active reporting year; `null` means "derive it from the server clock" |
| Consumers | `ReportingPeriod::anchor()` / `activeYear()`; `ForYearTrait::scopeForCurrentYear()` (`app/Models/Scopes/ForYearTrait.php:32-35`); `SakipDashboardService::getDateRange()` (`app/Services/SakipDashboardService.php:609-617`) |
| Validation | a non-numeric, zero, negative, fractional or out-of-range value is rejected at the single read site and falls back to the clock (TC-071) |
| Reach | because `ForYearTrait::scopeForCurrentYear` delegates to the resolver, one configuration key moves every year-scoped query in the application — there is exactly one "current year" implementation |

## 13. Public Test Seams

| Seam | Boundary | Test file | Level & constraints | Case range |
| --- | --- | --- | --- | --- |
| **S1** | `App\Support\ReportingPeriod` | `tests/Unit/Support/ReportingPeriodTest.php` | unit — no DB, no HTTP, no auth | TC-001…TC-012, TC-071 |
| **S2** | `AdminTriageService::summaryFor()` / `verificationCountFor()` | `tests/Unit/Services/AdminTriageServiceTest.php` | unit with a real database (`RefreshDatabase`, factories) and **no `actingAs()`** | TC-014…TC-027 |
| **S3** | `GET /admin/dashboard` | `tests/Feature/AdminTriageLandingTest.php` | HTTP feature (`actingAs`, seeded permissions, driven through the router) | TC-028…TC-056, TC-072 |
| **S4** | the sidebar badge on `layouts.modern` | `tests/Feature/SidebarQueueBadgeTest.php` | HTTP feature over two routes owned by two controllers | TC-057…TC-059 |

Two invariants belong to specific seams: the **ambient-auth invariant** is asserted at S3 (a `Super Admin` render must equal a direct `summaryFor()` call made with no authenticated user — TC-053),
because `InstansiScope` is a documented no-op without an authenticated user and S2 could not falsify it; the **recent-activity table** is what makes the unassigned viewer's budget `1` rather than `0`.
`tests/Support/ExtractsTriageMarkup.php` is the single markup boundary shared by S3 and S4 (`figureValue`, `figureHref`, `badgeValue`, `renderedRowCount`, viewer factories) so both seams read the
rendered handles the same way.

Focused commands (from `spec/spec-admin-triage-landing.md` §7.4):

```bash
php artisan test --filter=ReportingPeriodTest
php artisan test --filter=AdminTriageServiceTest
php artisan test --filter=AdminTriageLandingTest
php artisan test --filter=SidebarQueueBadgeTest
```

## 14. Accepted Divergences & Unverified Items

These are **documented and asserted** behaviours, not defects to "fix" silently. Each is pinned by a test that states the expectation positively.

| # | Behaviour | Why it is accepted | Asserted by |
| --- | --- | --- | --- |
| D-S7 | The assessment figure is period-independent while its target index is year-bounded | the figure states how much assessment work exists; scoping it to a year the data does not claim would violate the Periode honesty rule | TC-054 (figure↔target agreement, scoped to the §5.0 matrix) |
| D-S8 | For the cross-agency viewer the verification figure renders while its target queue is empty | `DataCollectionController@index` derives coverage from `performance_indicators.instansi_id` and has no cross-agency branch; repairing it is outside this slice | TC-055 (`assertSame(0, …)` on the rendered rows) |
| D-S9 | An assessment whose parent `PerformanceData` is soft-deleted leaves the assessment queue | a trashed performance-data row is not pending work; without the rule the two viewer states would count different populations | TC-020, TC-021 |
| — | The three manual metrics (above-the-fold placement at 1280×800, the five-participant ≤10 s usability check, p95 < 500 ms) | re-scoped to `[Assumed / Backlog]` by the product owner on 2026-09-25 — no harness in this repository can produce them | `docs/review/code-review-admin-triage-landing-2026-09-25.md` §7; PRD §6 |
| — | Rendered ellipsis for a >40-character agency name | the markup contract is CI-enforced (TC-072); the rendered appearance needs a browser | review §7 |
| — | The period selector resolves its five option labels on every render (5 × `config()` + 5 × `CarbonImmutable::now()`) | cosmetic and equivalent to the controller's anchor today; the remedy is deferred until the template is next touched (review `STD-A-09`) | review §3 |
| — | Test coverage (line ≥ 80% target / ≥ 75% floor; branch ≥ 75% / ≥ 70%) | Line coverage is **measured and unmet at project scale** (8.8 % over `app/`; 91.8–100 % for this slice); branch coverage stays **UNVERIFIED** (Xdebug absent). The scope of the threshold is a pending product-owner decision (retro A5) | `CONSTRAINTS.md` §1; `docs/retro/retro-admin-triage-landing-2026-09-26.md` §1 |

## 15. Related Documents

| Document | Use |
| --- | --- |
| `docs/explanation/explanation-triage-landing-decisions.md` | why the figures, links and the badge are shaped this way — including the decisions summarised in §14 |
| `docs/how-to/how-to-configure-the-active-reporting-year.md` | the task recipe for the configuration seam of §12.3 |
| `docs/how-to/how-to-add-a-queue-figure-to-the-triage-landing.md` | the task recipe for extending the landing with a further figure |
| `docs/tutorials/tutorial-observe-the-triage-figures.md` | the guided exercise that builds the matrix of §6 from scratch |
| `spec/spec-admin-triage-landing.md` | the executable specification (requirements, acceptance criteria, seam contracts) |
| `docs/checklist/checklist-admin-triage-landing.md` | the 70-case inventory plus TC-071/TC-072, with the requirement → criterion → case map |
| `docs/review/code-review-admin-triage-landing-2026-09-25.md` | the five-axis review, its 17 findings and the closure log |
| `docs/ARCHITECTURE.md` | repository map, layer rules, and the seam list in §10 |
| `CONTEXT.md` | the ratified domain vocabulary quoted throughout this document |
