# 🔍 TDD Clarification Report [Review Iteration 1]

<!-- markdownlint-disable -->

> [!SUCCESS]
> **REMEDIATION STATUS: RESOLVED** — authoring agent `/tdd-spec`, 2026-09-24 (remediation of Iteration 1, delivered as Spec **v1.1**, then refined to **v1.2**).
>
> All ten mandatory corrections in §4 are applied, and the Iteration-2 refinements (D-1…D-8) are applied on top:
>
> | §4 correction | Status | Where it lives now |
> | --- | --- | --- |
> | 1. C-1 — rescope AC-033 | RESOLVED | AC-033 (three-part) + §4.4 `data-triage-region` + §1.2 A2 carve-out + §6.3 + §8 "Never do" |
> | 2. C-2 — S2/S3 contradiction | RESOLVED | §6.1 S2 row + §6.2 "S3 owns the ambient-auth invariant" |
> | 3. C-3 — S2 fixture matrix | RESOLVED | new §5.0 (F-1…F-12) + AC-009/AC-011 |
> | 4. C-4 / C-5 / C-6 — missing ACs | RESOLVED | §4.3 disclosures, A8/A9, D-S7…D-S9, AC-036…AC-038 |
> | 5. C-7 — anchor precedence | RESOLVED (AC-039 added in v1.2) | §4.1 docblock + AC-039 + §6.1 S1 row |
> | 6. C-8 — config seam wording and type | RESOLVED (AC-004 hardened in v1.2) | REQ-015, §7.2, AC-004 |
> | 7. C-9 — correct §4.6 | RESOLVED (needle aligned in v1.2) | §4.6 table + §4.6 paragraph + CON-003 + §4.4 + §6.3 snippet |
> | 8. §6.4 replaced-assertion claim | RESOLVED | §6.4 correction bullet |
> | 9. Negative-assertion idiom named | RESOLVED | CON-007 + §6.3 snippet |
> | 10. S4 viewer named | RESOLVED | AC-023, AC-024, AC-026 + §6.1 S4 row |
>
> **Iteration-2 refinement list:** see `docs/audit/clarification-report-spec-admin-triage-landing-iteration-2-2026-09-24.md` §4 (items) and §5 (Decision Log: user chose REFINE on 2026-09-24).
>
> **Projected Readiness Score: ≈ 97/100** (Completeness 39/40, Clarity & Testability 29/30, Alignment 29/30; Critical Flaw Veto not triggered). Calculation recorded in the authoring session output.

**Target Document:** `spec/spec-admin-triage-landing.md` (v1.0, status Draft)  
**Readiness Score:** 78/100  
**Status:** Below Threshold (< 80)  
**Audit Date:** 2026-09-24  
**Interrogator:** TDD Clarification Analyst

**Score Breakdown (Quality Gate Rubric):**

- **Completeness (max 40):** 34 — Fifteen requirements, 35 acceptance criteria, four declared seams with a required-case list, created/modified file tables, a frozen copy table, and a per-viewer query
  budget are all present — an unusually complete Spec. Deduction: the S2 required-case list contradicts its own isolation boundary (C-2), the S2 fixture matrix is incomplete and clock-dependent (C-3),
  and three real behaviours have no AC at all (C-4, C-5, C-6).
- **Clarity & Testability (max 30):** 22 — Concretely testable nearly everywhere (typed value objects, explicit labels, explicit handles, injected clock, exact per-viewer query counts). Deduction:
  AC-033 is unsatisfiable as written (C-1), one required S2 case is vacuously green by construction (C-2), the `anchor()` contract is undefined when `$now` and `config('sakip.reporting.active_year')`
  are both present (C-7), and the `active_year` value type is unspecified (C-8).
- **Alignment & Constraints (max 30):** 22 — Strong on the load-bearing facts: `InstansiScope` semantics, the three model scopes, all three deep-link route names, and the cited `file:line` anchors for
  `AdminDashboardController`, `AssessmentController`, `getDateRange()`, `ForYearScope`/`ForYearTrait`, and the factory states were all re-verified as accurate. Deduction: `AuditLog` is documented as
  soft-deletable but is not (C-9), the table is `instansis` and not `instansi` (C-9), `config/sakip.php` already has a `reporting` block (C-8), and the figure-versus-target coverage-column divergence
  (C-5) is not recorded anywhere.
- **Critical Flaw Veto:** **Triggered (Max 79)** — AC-033 is a CI-enforced criterion that cannot go green without either editing a file §1.1 declares out of scope or weakening the assertion
  (`CONSTRAINTS.md` §3 rule 2). 78 is below the veto ceiling of 79, so the veto does not change the arithmetic outcome.

---

## 1. 🚨 Critical Findings (Untestable Blockers)

### C-1 — AC-033 is unsatisfiable: the forbidden string lives in the layout shell

- **Requirement / Section:** "**AC-033 (S3)** Given any landing render, When the body is inspected, Then it contains exactly three `data-triage-figure` anchors, and it contains none of the removed
  strings `Aktivitas Login (7 Hari)`, `Tervalidasi`, or `Indikator Kinerja` (REQ-011, ASSUMPTION A2)."
- **Verified evidence:** `resources/views/layouts/modern.blade.php:86` renders `<span class="sidebar-link-text">Indikator Kinerja</span>` inside the always-rendered `@can('manage-sakip')` sidebar
  section. `resources/views/admin/dashboard.blade.php:1` is `@extends('layouts.modern')`, so **every** landing body contains that string. §1.1 puts layout-shell defects out of scope and §4.4 leaves
  the sidebar untouched.
- **Testability Gap:** the assertion can never pass. The implementer is forced into one of three bad options: edit an out-of-scope file, delete the assertion (floor-guard violation), or weaken it —
  and §8's "Never do" list forbids the third.
- **Proposed Seam:** S3 — scope the negative assertion to the triage region (extract the element carrying `data-triage-period` from `$content`, then assert the forbidden strings are absent **inside
  that region only**) and reduce the forbidden set to the strings the triage block actually owned (`Aktivitas Login (7 Hari)`, `Tervalidasi`). Record explicitly that the sidebar copy
  `Indikator Kinerja` is out of scope, so no future agent "fixes" the layout to satisfy a test.
- **Decision Required:** narrow AC-033 to the triage region, or rename the sidebar label (which pulls `layouts.modern` into scope)?

### C-2 — The required S2 case contradicts the S2 isolation boundary, producing a tautological test

- **Requirement / Section:** §6.1 S2 requires "an invariant case proving a cross-agency call is not narrowed by ambient auth"; §6.2 states "**S2 must not use `actingAs()`**".
- **Verified evidence:** `app/Models/Scopes/InstansiScope.php:24-26` — `if (! auth()->check()) { return; }`. With no authenticated user the global scope is a documented no-op, so a cross-agency call at
  S2 **cannot** be narrowed by ambient auth under any implementation. The case passes for every possible production implementation, including a broken one.
- **Testability Gap:** a vacuously green test is precisely what `CONSTRAINTS.md` §3 rule 4 ("tautological tests") forbids, and it means the SEC-002 claim ("counts stay correct even when no
  authenticated user is bound") is never independently falsified.
- **Proposed Seam:** S3 — assert that a `Super Admin` landing render yields the same figures as a direct unscoped service call (falsifiable), and drop the impossible S2 case; alternatively broaden the
  S2 boundary to permit `actingAs()` and document why.
- **Decision Required:** which seam owns the "ambient auth does not narrow a cross-agency count" invariant — S3 (add a case) or S2 (change the boundary)?

### C-3 — The S2 fixture matrix is incomplete and clock-dependent

- **Requirement / Section:** "**AC-009 (S2)** Given agencies A and B (A: 2 submitted `PerformanceData`, 3 pending `Assessment`; B: 3 submitted) … Then `verificationCount === 2`, `assessmentCount === 3`,
  `reportCount` counts only A's submitted reports" and "**AC-011 (S2)** … the cross-agency viewer's `verificationCount === 5` with `scopeLabel === 'Semua Instansi'`, the agency-bound viewer's is `2`,
  and the unassigned viewer's is `0`."
- **Verified evidence (three independent defects).**
  1. `database/factories/AssessmentFactory.php:35` defaults `'performance_data_id' => PerformanceData::inRandomOrder()->first()->id ?? PerformanceData::factory()`. AC-009 never states that the three
     pending assessments are attached via `forPerformanceData()` to **A's** data, so `assessmentCount === 3` holds only by luck of insertion order. AC-011 asserts the cross-agency
     verification count but is silent on the cross-agency assessment and report expectations, which the Givens never fix.
  2. `database/factories/PerformanceDataFactory.php:30-38` pins `period` to `date('Y').'-01' … '-06'` (current calendar year, first half only). Neither AC pins `forPeriod()`, so a period-scoped
     expectation of `2` against a `current_year` range becomes clock-dependent for any month after June — contradicting §5's promise that "no criterion depends on wall-clock time".
  3. Neither Given states the submitted-report fixtures that `reportCount` is asserted against.
- **Testability Gap:** three of the ACs the Spec calls authoritative cannot be written deterministically from their own Givens.
- **Proposed Seam:** S2 — restate the Given as an explicit queue × viewer-state matrix with `forInstansi()`, `forPeriod()`, and `forPerformanceData()` applied to **every** fixture, and assert all three
  counts for all three viewer states in AC-011 (AC-012's invariant then becomes checkable).
- **Decision Required:** accept the explicit fixture matrix as the canonical S2 Given?

### C-4 — The assessment figure and its deep-link target disagree on the period frame, and no AC owns it

- **Requirement / Section:** §4.3 row 2 ("Antrean Asesmen … Never sent: `period` for **every** selection") read together with AC-027's period-honesty assertion and ASSUMPTION A4 ("the target index
  additionally hardcodes the current calendar year for its default query … which is the viewer's to interpret").
- **Verified evidence:** `app/Http/Controllers/Sakip/AssessmentController.php:53` resolves `$currentYear = Carbon::now()->year` and `:60` hard-filters `->whereYear('created_at', $currentYear)` **before**
  any request filter is applied; the request's own `period` filter at `:92-94` merely re-applies `whereYear('created_at', …)`. The target queue can therefore only ever show the current calendar year,
  while the figure is labelled `Tidak dibatasi periode`.
- **Testability Gap:** US-006's honesty rule holds for the figure's label but not for the figure → target *pair*: a viewer whose pending assessments are all from last year sees a **non-zero** figure
  and an **empty** queue. AC-019 checks only that the href contains `status=pending` and omits `period=`; nothing asserts figure/target agreement, so the divergence ships silently.
- **Proposed Seam:** S3 — add one criterion that either forces the decision (assert the figure equals the target's row count for the same viewer and fixture) or freezes the mismatch as intentional
  (assert the labelled period-independent figure beside a year-bounded target and record the accepted risk in §9.1).
- **Decision Required:** may the assessment figure legitimately exceed its target's current-year frame, or must the pair be made consistent (changing either the label or the target)?

### C-5 — The figure and its target compute agency coverage through different columns; the landing's primary viewer gets an empty verification queue

- **Requirement / Section:** §4.2's count forms (agency-bound `PerformanceData::…->where('instansi_id', $id)`; agency-bound `Assessment::pending()->whereHas('performanceData', …)`) together with §4.3's
  claim that "All three indexes derive coverage from the authenticated viewer … verified: none of the three `index` methods reads an instansi query parameter".
- **Verified evidence (three independent defects).**
  1. `app/Http/Controllers/Sakip/DataCollectionController.php:84-95` builds its list from `PerformanceIndicator::where('instansi_id', $instansiId)` with `$instansiId = $user->instansi_id` and **no
     `Super Admin` branch**. For the cross-agency viewer (`instansi_id === null`, while `app/Models/Scopes/InstansiScope.php:31-33` returns early for `Super Admin`) the queue resolves to
     `instansi_id = null` → **no rows**, while the landing's verification figure is non-zero. The landing's principal persona receives a correct figure and an empty queue.
  2. The two columns are independent: `database/migrations/2025_10_14_080002_create_performance_data_table.php:24` (`performance_data.instansi_id`) versus
     `database/migrations/2025_10_14_080000_create_performance_indicators_table.php:20` (`performance_indicators.instansi_id`), and `PerformanceDataFactory::definition()` sets them independently — so an
     agency-bound figure and its target list may diverge too.
  3. The assessment target shows the same divergence: `AssessmentController.php:67-73` filters by `whereHas('indicator')` → `performance_indicators.instansi_id`, while the Spec counts assessments through
     `performance_data.instansi_id` (F-01).
- **Testability Gap:** AC-018 and AC-021 verify the query string and a `200`/`302` status, never that the linked queue holds the rows the figure counted. US-004 scenario 1 ("reach the specific queue
  pre-filtered") is therefore asserted only at URL level, never at row-count level.
- **Proposed Seam:** S3 — add a figure/target agreement criterion for at least the agency-bound viewer (row count of the linked queue equals the figure over the same fixture), plus a recorded decision for
  the cross-agency viewer, whose target controller has no cross-agency path today.
- **Decision Required:** is the cross-agency viewer's empty verification queue accepted and recorded (target-controller defect, tracked outside this Spec), or does the landing stop deep-linking that figure
  for that viewer?

### C-6 — Soft-deleted parents make the two assessment populations non-comparable, and AC-011 assumes they are comparable

- **Requirement / Section:** §4.2's two assessment forms — cross-agency `Assessment::query()->pending()->count()` versus agency-bound
  `->whereHas('performanceData', fn ($q) => $q->where('performance_data.instansi_id', $id))` — read together with AC-011's implied viewer consistency.
- **Verified evidence:** `app/Models/Assessment.php:75-77` declares `performanceData()` as `belongsTo(PerformanceData::class)` and `app/Models/PerformanceData.php:24` applies `SoftDeletes`. The `whereHas`
  therefore excludes assessments whose parent is soft-deleted, while the plain cross-agency `count()` includes them. `Assessment` itself soft-deletes (`app/Models/Assessment.php:18`), so the difference is
  only ever the parent row.
- **Testability Gap:** one dataset yields two differently-defined populations, so "the cross-agency count is the sum of the agency counts" is undefined for any dataset containing a soft-deleted
  `PerformanceData`. §5 never pins which population is canonical, and §4.6's blanket `withTrashed()` prohibition covers counts without resolving the question.
- **Proposed Seam:** S2 — state the canonical population explicitly (parent-trashed assessments counted or not) and add one case with a soft-deleted parent asserting the chosen number for **both** viewer
  states.
- **Decision Required:** is an assessment whose `PerformanceData` is soft-deleted still pending work, or does it leave the queue?

## 2. 🧩 Resolved Items & Pre-Agreed Boundaries

Each item below was re-verified against the working tree in this session and is hereby **pre-agreed as correct**; no further clarification is needed. Where a boundary was missing, the resolution is
recorded in the same bullet.

- **Original Ambiguity (PRD-level): "is the claimed `403` a legitimate RED?"** — Resolution: yes. `illuminate/routing/Controller` still exposes `middleware()` and `getMiddleware()`
  (`vendor/laravel/framework/src/Illuminate/Routing/Controller.php:23-43`), so `app/Http/Controllers/Admin/AdminDashboardController.php:14` really registers `can:access-admin-dashboard`;
  `access-admin-dashboard` occurs exactly once in the repository (repository-wide grep), and `Gate::before` (`app/Providers/AppServiceProvider.php:88-90`) only bypasses `Super Admin`. A verified
  `admin.dashboard` holder therefore meets `403` today. Target seam: S3 (AC-015).
- **Original Ambiguity: "which gate governs the landing?"** — Resolution: `Gate::define('admin.dashboard', …)` at `app/Providers/AppServiceProvider.php:93-94` (`isAdmin() || hasPermission(...)`), the
  permission is seeded at `database/seeders/RolesAndPermissionsSeeder.php:31`, and the route group gate is `routes/web.php:201`. Deleting the controller-level middleware cannot widen access beyond
  these two gates. Target seam: S3 (AC-015/AC-016).
- **Original Ambiguity: "does the S2 no-auth design survive `InstansiScope`?"** — Resolution: yes. `app/Models/Scopes/InstansiScope.php:24-26` returns early when `auth()->check()` is false, so a
  service called without an authenticated user reads unscoped data; S2's DB-without-`actingAs()` design is viable (and is the root cause of C-2 for the *auth-interaction* case only). Target seam: S2.
- **Original Ambiguity: "is `whereHas('performanceData')` implementable on `Assessment`?"** — Resolution: yes. `app/Models/Assessment.php:75-77` declares `performanceData()` as
  `belongsTo(PerformanceData::class)`; `performance_data_id` is the unique column at
  `database/migrations/2025_10_14_080004_create_assessments_table.php:43`. Target seam: S2.
- **Original Ambiguity: "do the three queue scopes and route names exist?"** — Resolution: yes. `PerformanceData::scopeSubmitted` (`app/Models/PerformanceData.php:195-197`),
  `Assessment::scopePending` (`app/Models/Assessment.php:169-171`), `Report::scopeSubmitted` (`app/Models/Report.php:181-183`); `sakip.data-collection.index` (`routes/web_sakip.php:140`),
  `sakip.assessments.index` (`:206`), `sakip.reports.index` (`:252`). Target seam: S2/S3.
- **Original Ambiguity: "is `getDateRange()` really re-usable as an adapter?"** — Resolution: yes. `app/Services/SakipDashboardService.php:608-649` already carries the identical five-key whitelist and
  returns `[Carbon, Carbon]`; delegating to `ReportingPeriod` while preserving the mutable `Carbon` return contract keeps its five call sites (`:67, :87, :256, :392, :444`) untouched. Target seam: S1.
- **Original Ambiguity: "is the dead year-scope file safe to delete?"** — Resolution: yes. `app/Models/Scopes/ForYearScope.php` contains both a `ForYearScope implements Scope` class whose `apply()` body
  is empty and an unused `ForYear` trait; no `use` site exists for either (repository-wide grep shows only the file itself). `ForYearTrait` is used by `PerformanceIndicator`, `PerformanceData`, and
  `Program`. Target seam: `ForYearTrait` unit case (§6.4).
- **Original Ambiguity: "are the fixtures the Spec demands available?"** — Resolution: yes. `PerformanceDataFactory::submitted()` (`:74`), `forInstansi()` (`:136`), `forPeriod()` (`:146`);
  `AssessmentFactory::pending()` (`:61`), `forPerformanceData()` (`:145`); `ReportFactory::submitted()` (`:162`), `forInstansi()` (`:241`). Target seam: S2/S3.
- **Original Ambiguity: "does the Phase-2 badge really need a composer?"** — Resolution: yes. `resources/views/layouts/modern.blade.php:91-92` guards with `isset($pendingDataCount) && $pendingDataCount > 0`
  and `pendingDataCount` is currently produced **only** by `AdminDashboardController`, so the composer genuinely introduces the badge on the other `layouts.modern` pages. Target seam: S4.
- **Boundary gap resolved here — who is the S4 viewer?** AC-023 requires two pages owned by two controllers (`sakip.dashboard`, `admin.dashboard`). `SakipDashboardController::index` calls
  `Gate::authorize('sakip.dashboard.view')`, whose policy requires the `view-sakip-dashboard` permission (`app/Policies/SakipDashboardPolicy.php:17-25`), and it always returns
  `sakip.dashboard.index`, which extends `layouts.modern`. **Pre-agreed resolution:** S4 uses a `Super Admin` viewer (satisfies both gates through `Gate::before`), and AC-023/AC-024/AC-026 must state
  that viewer explicitly, because a collector-role viewer would never reach `layouts.modern` on that route (`resources/views/sakip/dashboard/collector.blade.php:1` extends `layouts.app`).

## 3. ⚠️ Assumed / Auto-Resolved / Out of Scope (The 20% Tail)

- **Scenario / Question:** `ReportingPeriod::anchor()` when `$now` is supplied **and** `config('sakip.reporting.active_year')` is set — does the configured year override the injected clock, or does an
  explicit `$now` win?
  - **Handling:** `[Assumed / Out of Scope]` — needs one sentence in §4.1 (C-7). Recommended: the configured year applies only when `$now === null`; an explicitly injected clock is always authoritative, so
    S1 stays deterministic.
- **Scenario / Question:** the value type of `config('sakip.reporting.active_year')`.
  - **Handling:** `[Assumed / Out of Scope]` — needs one clause in REQ-015 (C-8). `env()` returns a **string**, so `activeYear(): int` must cast, and AC-004 must state whether it sets the config as
    `'2025'` or `2025`.
- **Scenario / Question:** must the period selector preserve any other query parameters (for example a future `page`) when it posts to `route('admin.dashboard')`?
  - **Handling:** `[Assumed / Auto-Resolved]` — no other triage parameter exists in Phase 1; the form carries `period` only, and CON-006 plus `ArchitectureGuardTest::every_blade_route_name_exists` cover
    the route name.
- **Scenario / Question:** the `tests/Unit` runtime floor after adding ~8 DB-backed S2 cases (only `tests/Unit/Services/PerformanceCalculationServiceTest.php` uses `RefreshDatabase` today).
  - **Handling:** `[Assumed / Out of Scope]` — no local PHP toolchain is installed in the audit environment (`php: command not found`), so the impact on the `< 10 s` target / `< 20 s` hard floor
    (`CONSTRAINTS.md` §1) is **unverified**; §7.4 already obliges the implementer to report a blocked gate as unverified rather than passed.
- **Scenario / Question:** p95 < 500 ms (PRD §6, F-05) remains a manual measurement.
  - **Handling:** `[Assumed / Out of Scope]` — already recorded in §1.2; the CI-enforced substitute is the §4.4 domain-query budget. No harness is introduced.
- **Scenario / Question:** the negative content-assertion idiom. CON-007 pre-agrees only `$response->getContent()` + `assertStringContainsString(...)`, yet AC-019, AC-020, AC-027, AC-030, AC-033 and
  AC-034 all require the absence of a string.
  - **Handling:** `[Assumed / Auto-Resolved]` — the paired house idiom is `assertStringNotContainsString(...)` on the same `$content` value; §6.3's "Seam Idioms" list should name it so no third idiom is
    invented (correction 9 in §4).

---

## 4. 📝 Next Steps

**Mandatory corrections for the authoring agent (`/tdd-spec`), required before `/tdd-plan-tasks`.** Items 1–6 close the §1 blockers; items 7–10 are factual corrections that must ship in the same
revision because they change what is asserted.

1. **C-1 — rescope AC-033.** Either scope the forbidden-string assertion to the triage region extracted from `$content`, or drop `Indikator Kinerja` from the forbidden set and record in §1.1 that the
   sidebar label at `resources/views/layouts/modern.blade.php:86` is intentionally out of scope. State the choice so the implementer cannot "fix" the layout to satisfy a test.
2. **C-2 — resolve the S2/S3 contradiction.** Delete the impossible S2 "not narrowed by ambient auth" case (its premise is falsified by `InstansiScope.php:24-26`) and move the equivalent invariant to S3
   as a `Super Admin` HTTP case, or explicitly broaden §6.2 to permit `actingAs()` at S2 with the reason recorded.
3. **C-3 — rewrite the S2 Given as an explicit fixture matrix** with `forInstansi()`, `forPeriod()`, and `forPerformanceData()` applied to every fixture, and assert all three counts for all three viewer
   states in AC-011.
4. **C-4 / C-5 / C-6 — add the three missing acceptance criteria**, or record each explicitly as an accepted risk in §9.1 **with** an AC that asserts the accepted behaviour. C-5 is the priority: it
   concerns the landing's primary persona, and it currently ships a non-zero figure that links to an empty queue.
5. **C-7 — define `anchor()` precedence** between an injected `$now` and `config('sakip.reporting.active_year')`.
6. **C-8 — fix the configuration-seam wording and type.** `config/sakip.php:103-116` **already contains** a `reporting` block (report-generation settings), so REQ-015 and §7.2 must say "new
   `reporting.active_year` key inside the existing `reporting` block"; add the `(int)` cast and fix AC-004's Given value type.
7. **C-9 — correct §4.6.** The agency table is `instansis` (`database/migrations/2025_08_05_120351_create_instansis_table.php:14`), not `instansi`, and the query-count needle list must say so explicitly
   (today it matches only as a substring). Also `AuditLog` has **no** `SoftDeletes` (`app/Models/AuditLog.php:16`), so the blanket "soft deletes are respected on … `AuditLog`" claim and its
   `withTrashed()` prohibition must be narrowed to `PerformanceData`, `Assessment`, and `Report`.
8. **§6.4 — restate the replaced-assertion claim.** `grep -rn 'admin.dashboard' tests/` returns **no hits**: no test covers `/admin/dashboard` today, so there is no "previous landing assertion" to
   replace. State instead that AC-015 is a brand-new RED with no incumbent test, and keep `SakipDashboardAccessTest` in the "must stay GREEN" list (it covers `sakip.dashboard` only, and its
   status-only assertions are unaffected by the Phase-2 badge).
9. **CON-007 / §6.3 — name the negative idiom** (`assertStringNotContainsString`) alongside the positive one.
10. **AC-023 / AC-024 / AC-026 — name the S4 viewer** (`Super Admin`) per the boundary resolved in §2, and state that the `layouts.modern` rendering is verified through `sakip.dashboard.index`.

**Projected score after remediation.** With items 1–4 resolved, Completeness returns to ≈38/40 and Clarity & Testability to ≈28/30, while Alignment reaches ≈27/30 once items 7–10 are applied →
**≈93/100**, above the threshold. This projection is a mental calculation, not a re-audit; the re-audit is Iteration 2.

> **No User Decision Prompt is issued.** The readiness score is **78/100 — below the 80-point threshold**, and the Critical Flaw Veto is triggered, so this document must not advance to
> `/tdd-plan-tasks` yet. Remediate §1 (C-1 … C-6) through `/tdd-spec`, then re-invoke `/tdd-clarify` for Iteration 2. A human override ("proceed anyway") remains available, but it would carry the six
> unresolved blockers above straight into the Plan and the RED step.
