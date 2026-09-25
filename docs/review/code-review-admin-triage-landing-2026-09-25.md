---
title: Code Review & Test-Suite Audit — Admin Triage Landing (Landing Triage)
version: 1.0
date_created: 2026-09-25
status: Complete (findings open — refactoring plan issued)
reviewed_scope: 11 tickets of plan/plan-admin-triage-landing.md v1.0 — Phase 1 (T1–T9) and Phase 2 (T10–T11)
upstream_spec: spec/spec-admin-triage-landing.md (v1.3)
upstream_plan: plan/plan-admin-triage-landing.md (v1.0)
upstream_checklist: docs/checklist/checklist-admin-triage-landing.md (70 cases)
upstream_prd: docs/prd/prd-admin-triage-landing.md (v1.2)
refactoring_plan: plan/plan-refactor-admin-triage-landing-v1.0.md
reviewer: /tdd-code-review (TDD Code & Test Reviewer)
---

<!-- markdownlint-disable -->

# Code Review & Test-Suite Audit: Admin Triage Landing

> **This document is also the Phase-1 review artifact** required by `spec/spec-admin-triage-landing.md` §9.4 obligations 2 and 4 and by
> `plan/plan-admin-triage-landing.md` §6 ("Accepted decisions recorded in the review artifact"). `docs/review/` did not exist before this review, so obligation 4 is discharged here; obligation 2
> (the three manual measurements) is discharged **as unverified**, see `[SPEC-B-05]`.

## 1. Fixed Point, Scope & Method

- **Fixed point:** `9c3eaee` (repository baseline snapshot, pre-feature). **Reviewed range:** `9c3eaee..c494ad4`.
- **Commits in scope:** `0c6fc64`, `4bd7291`, `0fb08c1`, `df57eb8`, `2f6236f`, `10bc90c`, `365e4b7`, `01675b6`, `6937515` (concurrent/external author), `7e8642e`, `cf08e72`, `c494ad4`.
- **Working tree at review time:** clean (`git status --porcelain` empty).
- **Method:** Two-Axis review — **Axis A** (Clean Code / SOLID / Clean Architecture / Fowler Code Smells / test efficacy / security) and **Axis B** (PRD ↔ Spec ↔ Plan ↔ Checklist ↔ Code conformance) — following
  `.agents/skills/tdd-code-review/references/` (`FIVE-AXIS-REVIEW.md`, `CLEAN-CODE-ARCHITECTURE.md`, `SECURITY-HARDENING.md`, `CODE-SMELLS.md`). Every finding below carries `file:line` evidence produced in this session.
- **Known findings excluded by instruction** (recorded, not re-derived): (1) Spec §5.0 F-6 fixture impossibility vs the `assessments.performance_data_id` UNIQUE index; (2) `ReportFactory` metadata columns absent from `reports`
  (`Report::forceCreate` workaround, pre-existing); (3) finding F-1 — `manage-sakip` referenced by two `@can` blocks but defined and granted nowhere, making the sidebar owner badge effectively Super-Admin-only; (4) the
  unfalsifiable `(int)` cast residual of AC-004 in a non-strict-types repository; (5) accepted divergences D-S7/D-S8/D-S9 already asserted through AC-037/AC-038.

## 2. Verified Baseline Evidence (reproduced in this session, not inherited)

| Gate | Command | Result |
| --- | --- | --- |
| Full suite | `/opt/homebrew/opt/php@8.3/bin/php artisan test` | **144 passed / 2 skipped, 656 assertions, 11.12 s** — identical to the declared baseline |
| The 2 skips | `grep -rn 'markTestSkipped' tests/` | `tests/Feature/RateLimitingTest.php:116,189` only — the documented pre-existing backlog **F1**, not this feature |
| Unit suite | `php artisan test --testsuite=Unit` | **54 passed, 1.65 s** — inside the `< 10 s` target of `CONSTRAINTS.md` §1 |
| Seams S1–S4 | `php artisan test --filter='AdminTriageLandingTest\|SidebarQueueBadgeTest\|AdminTriageServiceTest\|ReportingPeriodTest'` | **57 passed, 348 assertions, 2.47 s** |
| Linter | `php vendor/bin/pint --test --no-interaction` | **PASS — 251 files** |
| Migrations | reviewed diff `9c3eaee..c494ad4` | **0 migrations** added or altered |
| Floor-guard greps | `withoutGlobalScope\|withoutInstansiScope\|whereRaw` over `app/Support`, `AdminTriageService`, `app/View/Composers`, the controller, the blade and the four seam files | **0 hits** |
| Floor-guard greps | `@phpstan-ignore\|@noinspection\|eslint-disable\|phpcs:ignore\|noqa`, `.skip`, `markTestIncomplete` in changed files | **0 hits** |
| Coverage | `php artisan test --coverage --min=75` | **UNVERIFIED** — no Xdebug/PCOV in this environment. **Not reported green.** |

> [!IMPORTANT]
> `SAKIP_ACTIVE_YEAR` is unset in the tested environment, so every period assertion implicitly exercises the `config === null` → clock branch. No test in the suite sets `active_year` through the real `env()` path; the config seam is
> only ever exercised by `config()->set()`. This is acceptable for the seam's unit contract but is recorded here as residual evidence (see `[STD-A-06]`).


## 3. Axis A — Standards (Code Quality, Test Efficacy & Security)

Overall health: **good**. The slice respects the repository's layering (`app/Support/` pure value objects, `app/Services/` read model, thin controller, layout-owned composer), the service is read-only, every deep link is
`route()`-built, no suppression/skip exists in any changed file, and the seam tests assert behaviour rather than mocks. No `[CRITICAL]` finding. The findings below are hygiene-level except where marked.

### `[OPTIONAL] [STD-A-01]` Duplicated markup-extraction helpers across three test files

- **Category:** Test Efficacy / Duplicated Code.
- **Location:** `tests/Feature/AdminTriageLandingTest.php:496-502` (`figureValue`) and `:423-429` (`figureHref`); `tests/Feature/SidebarQueueBadgeTest.php:121-127` (`verificationFigure`) and `:114-119` (`badgeValue`).
  `figureValue()` and `verificationFigure()` are behaviourally identical, and `figureHref()` duplicates the inline regex at `SidebarQueueBadgeTest.php:123`.
- **Why it matters:** the render contract is exactly what these helpers pin; three copies can drift so that S3 and S4 disagree about what "the figure" is while both stay green.
- **Remedy:** extract one trait, e.g. `tests/Support/ExtractsTriageMarkup.php`, exposing `figureValue()`, `figureHref()`, `badgeValue()` and `renderedRowCount()`, and `use` it in both feature classes. The same trait can carry the
  `superAdminViewer()`/`permittedViewer()` factories, currently re-declared in three files and duplicating `tests/Feature/ReportIndexRendersTest.php:19-27`.

### `[OPTIONAL] [STD-A-02]` Fully-qualified class names inline, against the file's own import style

- **Category:** Readability.
- **Location:** `tests/Feature/AdminTriageLandingTest.php:219,229,325-335,395,414,485-490,507-512,534-535,550-551` (`\App\Models\Instansi`, `\App\Support\ReportingPeriod`,
  `\Illuminate\Support\Facades\DB`, `\App\Constants\SystemRoles`); `tests/Unit/Services/AdminTriageServiceTest.php:324-344,366-388`.
- **Why it matters:** the same files already import `App\Models\User` and `Carbon\CarbonImmutable`, so the reader must hold two conventions at once and diffs grow noisier than the change they express.
- **Remedy:** promote the repeatedly used FQCNs to `use` statements in the three new test files.

### `[OPTIONAL] [STD-A-03]` Unused public API — `ReportingPeriod::isYearScoped()`

- **Category:** Speculative Generality (Fowler smell #9).
- **Location:** `app/Support/ReportingPeriod.php:85-88`; the only reference in the repository is its own assertion at `tests/Unit/Support/ReportingPeriodTest.php:87`.
- **Evidence:** `grep -rn 'isYearScoped' app/ tests/ resources/` returns exactly those two lines. The service decides the deep-link degradation from `isSingleMonth()` (`app/Services/AdminTriageService.php:168`), not from this flag.
- **Why it matters:** a public method that exists only to be tested is a contract the reader must maintain for no behaviour.
- **Remedy:** either consume it (use `isYearScoped()` as the negative branch of the deep-link rule, which is the semantic it was written for) or delete it from the class **and** from Spec §4.1 in the same commit. Do not delete from
  code alone — the Spec pins it.

### `[OPTIONAL] [STD-A-04]` One literal, two constants, two near-identical docblocks

- **Category:** Duplicated Code / magic-string hygiene.
- **Location:** `app/Services/AdminTriageService.php:23-30` — `VERIFICATION_SUBMITTED_STATUS = 'submitted'` and `REPORT_SUBMITTED_STATUS = 'submitted'`.
- **Why it matters:** keeping them separate is *defensible* (they name two different target query parameters, `validation_status` vs `status`), but as written the two constants are indistinguishable to a reader and will drift if one
  target ever renames its filter.
- **Remedy:** keep the separation and make it explicit — rename to `VERIFICATION_FILTER_VALUE` / `REPORT_FILTER_VALUE` and name the target parameter in each docblock, or collapse to one constant with a comment that both targets accept
  `submitted`. No behavioural change.

### `[OPTIONAL] [STD-A-05]` Config read on a path that discards it

- **Category:** Readability / redundant computation.
- **Location:** `app/Support/ReportingPeriod.php:57-61` — `$configuredYear = config('sakip.reporting.active_year')` executes before the `$now !== null` early return, so the deterministic branch pays for and ignores the lookup.
- **Remedy:** move the assignment below the early return. Cosmetic; no behaviour change.

### `[OPTIONAL] [STD-A-06]` An unvalidated configuration seam can silently move the application to year 0

- **Category:** Correctness / robustness at a trust boundary (Axis 4 input validation applied to *configuration* input).
- **Location:** `app/Support/ReportingPeriod.php:65` — `$anchor->setYear((int) $configuredYear)`; `config/sakip.php:127` — `env('SAKIP_ACTIVE_YEAR', null)`.
- **Why it matters:** `env()` returns whatever the operator typed. `SAKIP_ACTIVE_YEAR=abc` casts to `0`, so `activeYear()` returns `0` and every year-scoped query (`ForYearTrait::scopeForCurrentYear`,
  `SakipDashboardService::getDateRange`, `ReportingPeriod::fromKey`) silently switches to year 0 instead of failing loudly or falling back to the clock; `2026.5` silently becomes `2026`. No test covers a non-numeric or non-positive value,
  and the Spec only pins the `(int)` cast (REQ-015, AC-004).
- **Remedy:** treat the seam as untrusted input — resolve once, e.g. `$year = (int) $configuredYear;` then `if ($year < 1970 || $year > 9999) { return $anchor; }` — and add one S1 case per rejected shape (non-numeric, `0`, negative,
  out-of-range). A stricter alternative keeps the resolver pure: filter inside `config/sakip.php` with `is_numeric(env('SAKIP_ACTIVE_YEAR')) ? (int) env('SAKIP_ACTIVE_YEAR') : null`.

### `[OPTIONAL] [STD-A-07]` A source-file assertion paired with the rendered assertion it duplicates

- **Category:** Test Efficacy (shallow/tautological pairing).
- **Location:** `tests/Feature/AdminTriageLandingTest.php:294-296` — asserts the rendered page contains `Indikator Kinerja`, that the triage region does not, **and** that `layouts/modern.blade.php` on disk contains it.
- **Why it matters:** the third assertion restates the first two for a template that renders that string statically; it can only fail in the way its message claims by construction. The meaningful half is the region-scoped pair.
- **Remedy:** keep lines 294-295 and drop line 296, or replace it with a real boundary check — assert the rendered page exposes the sidebar label **and** that no element inside `data-triage-region` references a sidebar route.

### `[FYI] [STD-A-08]` Badge renders the raw integer while the figure renders a formatted one

- **Location:** `resources/views/layouts/modern.blade.php:92` (`{{ $pendingDataCount }}`) vs `resources/views/admin/dashboard.blade.php:81` (`number_format($figure['count'])`).
- **Assessment:** AC-023 requires "the same integer", which holds numerically, and Spec §4.5 forbids changing the badge markup. Recorded for whoever next touches the badge: above 999 the sidebar reads `1234` beside the figure's `1,234`.

### `[FYI] [STD-A-09]` The selector rebuilds five period objects per render

- **Location:** `resources/views/admin/dashboard.blade.php:31-33` — `ReportingPeriod::fromKey($key)->label()` inside the `@foreach` over `KEYS` (5 × `config()` + 5 × `CarbonImmutable::now()`), plus a second `fromKey()` for the
  selected key's `data-triage-period-label` at `:32`.
- **Assessment:** equivalent to the controller's anchor today only because both resolve with a `null` clock. The resolver deliberately supports an injected clock (AC-039); if any future caller injects one, the selector labels would silently
  drift from the rendered period. Remedy when the template is next touched: expose one `ReportingPeriod::options()` (or pass a prebuilt label map anchored to `$summary->period`) and let the blade render values rather than periods.

### Axis 4 — Security review (STRIDE / OWASP Top 10): no findings

| Check | Evidence | Verdict |
| --- | --- | --- |
| Input validation at the boundary | `AdminDashboardController.php:28-29` and `SidebarQueueBadgeComposer.php:32-33` both guard with `is_string()` before the five-key whitelist; AC-030 (`?period[]=`) and AC-031 (hostile payload containing `<b>boom</b>`) prove the fallback and the absence of reflection | Pass |
| Output encoding | every emitted value uses `{{ }}`; `data-triage-period` carries a whitelisted constant, never user text | Pass |
| Injection | no raw SQL, no `whereRaw` / `orderByRaw` / concatenated identifier anywhere in the diff | Pass |
| Authorization / trust boundary | the route group (`routes/web.php:201`) is the single gate; `access-admin-dashboard` is referenced nowhere (`grep` = 0 hits); the 403 path renders no triage copy (TC-036) | Pass |
| Tenancy (STRIDE: Information Disclosure) | no `withoutGlobalScope` / `withoutInstansiScope`; `InstansiScope` intact (`app/Models/Scopes/InstansiScope.php:11-18`); the only `withTrashed()` is the label-only lookup at `AdminTriageService.php:113`, and TC-027 asserts every statement the service issues is a `select` | Pass |
| Secret exposure | the diff adds no credential, URL or token; `config/sakip.php:127` reads an env var, not a hardcoded value | Pass |
| Error messaging | no new error surface; invalid input degrades to the default period instead of raising | Pass |
| Supply chain | no Composer or npm manifest changed across the range | Pass |
| Prompt-injection surface | none — no model calls; all ingested log/test/template text was handled as inert data during this review | Pass |


## 4. Axis B — Spec Axis (Functional Compliance)

The eleven tickets deliver the Spec's substance: the three figures, the scope indicator, the period envelope, the deep-link rules, the attention strip, the empty state, the single authorization gate, the period resolver and the
layout-owned badge all behave as §4.1–§4.5 specify, and the accepted divergences are asserted rather than assumed. Every finding below is a **traceability or documentation-fidelity defect**: something the Spec or the PRD required
that no artifact — Spec, Plan, Checklist, code or test — currently carries, so the next agent cannot see that it was dropped.

### `[REQUIRED] [SPEC-B-01]` The Phase-1 badge-continuity clause of Spec §4.5 was violated in T4 and only repaired in T10

- **Spec reference:** Spec §4.5 "Controller (Phase 1)" contract — the Phase-1 controller must pass `'pendingDataCount' => $summary->verificationCount`, justified verbatim as: *"Phase 2 removes this variable in the same commit that
  introduces the composer below, so no intermediate state loses the badge."* Also `plan/plan-admin-triage-landing.md` Ticket-010 Step 2 ("drop the dead `pendingDataCount` view variable from the controller"), which presupposes the
  variable exists until T10.
- **Evidence:**
  - Baseline `9c3eaee:app/Http/Controllers/Admin/AdminDashboardController.php` passed `pendingDataCount` from `PerformanceData::where('status','submitted')->count()`.
  - `df57eb8` (**T4**) rewrote the controller to `return view('admin.dashboard', compact('summary', 'recentLogs'));` — and `git show df57eb8:resources/views/admin/dashboard.blade.php | grep -n pendingDataCount` returns **no match**.
  - The badge is read only from the layout (`resources/views/layouts/modern.blade.php:91-92`), which was untouched, so from `df57eb8` until `7e8642e` (**T10**) **no `layouts.modern` page — the landing included — rendered
    `sidebar-link-badge` at all**.
  - `7e8642e`'s own message states it: *"AdminDashboardController needed no change: the previous ticket had already dropped the pendingDataCount view variable"*; memory records the same fact at
    `.agents/instructions/memory.instructions.md:342`.
- **Impact:** a five-commit, user-visible regression window on the surface this feature owns. No test could see it — the S4 badge seam existed only from Phase 2 — so the Phase-1 VERIFY gate (`php artisan test` green) passed straight
  over it. **Not live at HEAD**: the composer restores the badge everywhere and TC-057/TC-058/TC-059 now pin it. The durable defect is that the Spec's own continuity rule was overridden without a Spec amendment, so Ticket-004's GREEN
  step will keep teaching the wrong behaviour to the next reader.
- **Remedy:** amend Spec §4.5 so the Phase-1 controller contract matches the delivered design (the composer is the single source from Phase 1 onward, or state explicitly that the badge is briefly absent and why), and record the
  deviation here and in memory as an *accepted deviation* rather than a neutral observation. No production change is required.

### `[REQUIRED] [SPEC-B-02]` Spec §4.4 requires the handle `data-triage-period-select`; the landing never renders it and no case asserts it

- **Spec reference:** Spec §4.4 required-elements table, line 416: `data-triage-period-select` on the `<select name="period">` inside the GET form, with "five `<option>` values in `ReportingPeriod::KEYS` order with the resolved key
  marked `selected`; a visible submit control labelled `Terapkan`; no inline event handler".
- **Evidence:**
  - `grep -rn 'data-triage' resources/views/admin/dashboard.blade.php` returns `data-triage-region` (:20), `data-triage-period`/`data-triage-scope` (:20), `data-triage-scope-label` (:22), `data-triage-attention` (:44),
    `data-triage-signal` (:52), `data-triage-empty` (:62), `data-triage-figure` (:74), `data-triage-period-label` (:83). **`data-triage-period-select` is absent.**
  - `grep -rn 'data-triage-period-select' spec/ plan/ docs/checklist/` returns **one hit — the Spec line itself.** The Plan's Ticket-004 and the Checklist's TC-042/TC-043/TC-051 never carry it, so the element was dropped between
    Spec and Plan and never re-detected.
- **Impact:** §4.4 exists so that "every CI-enforced PRD §6 metric is assertable"; one of the nine required handles is unassertable, and TC-051 substitutes prose-level assertions (`Periode Pelaporan`, `Terapkan`, `method="GET"`) for
  the declared machine handle. Behaviour is correct today; the *contract* is partially unimplemented, so a future selector rewrite can pass every existing test while breaking the declared seam.
- **Remedy:** add `data-triage-period-select` to the `<select>` at `resources/views/admin/dashboard.blade.php:30` and extend TC-051 (or add a case) to assert the handle appears exactly once inside the triage region — a one-line
  production change plus one assertion. Do **not** delete the handle from the Spec: the declared handles are the seam other tools rely on.


### `[REQUIRED] [SPEC-B-03]` AC-028 / TC-045 were never assigned to a ticket and never written — a Spec→Plan→Code traceability break

- **Spec reference:** Spec §5.6 `AC-028 (S3)`: *"Given datasets in two calendar years, When the viewer switches the period, Then the verification count changes accordingly while the assessment and report counts stay identical and remain
  labelled `Tidak dibatasi periode`."* Checklist §3: `TC-045 test_switching_period_moves_only_the_verification_figure — AC-028 · S3 · invariant across two datasets`.
- **Evidence:**
  - `grep -o 'AC-0[0-9][0-9]' plan/plan-admin-triage-landing.md | sort -u` yields `AC-005 AC-008 AC-009 AC-012 AC-013 AC-014 AC-015 AC-017 AC-018 AC-022 AC-023 … AC-039` — **AC-028 is not among them**, and no ticket's TC range
    covers TC-045 either (Ticket-004: TC-028…TC-032, TC-035…TC-037, TC-042, TC-043, TC-051; Ticket-005: TC-033, TC-034, TC-044, TC-049, TC-050; Ticket-006: TC-038…TC-041, TC-056; Ticket-007: TC-046…TC-048).
  - `grep -o 'AC-0[0-9][0-9]'` across all six seam test files yields `AC-001…AC-027, AC-029…AC-039` — **AC-028 is the single unreferenced criterion in the entire delivery.**
  - Method inventory: `AdminTriageLandingTest` has **28** test methods against the Checklist's **29** S3 cases; the missing one is exactly `test_switching_period_moves_only_the_verification_figure`.
- **Impact:** the only AC with no test at any seam. Its substance is *partially* covered — TC-048 (`AdminTriageLandingTest.php:465-480`) proves the verification figure moves between periods, and TC-022 (`AdminTriageServiceTest.php:201-216`)
  proves assessment/report period-invariance at the service level — but **nothing asserts, in rendered output, that a period switch leaves the assessment and report figures and their basis labels unchanged.** That is precisely the US-006
  honesty contract this feature exists to protect.
- **Remedy:** add `test_switching_period_moves_only_the_verification_figure` to `tests/Feature/AdminTriageLandingTest.php`, seeded with a two-calendar-year dataset (the existing `agencyWithSubmittedData()` helper accepts arbitrary periods, so
  the calendar-year dimension only needs the periods to span two years), asserting: the verification figure changes across the switch, the assessment and report figures are identical across both renders, and both keep `Tidak dibatasi periode`.
  Also patch the Plan's ticket mapping so the gap cannot recur.

### `[REQUIRED] [SPEC-B-04]` The Spec's query-budget numbers are now factually wrong and were never amended

- **Spec reference:** Spec §4.4 budget table (Phase 1 `4 / 5 / 1`, Phase 2 `5 / 6 / 2`) and §9.4 obligation 3 — *"After Phase 2, update the §4.4 exact query expectations from 4/5/1 to 5/6/2 in the same commit as the composer."*
- **Evidence:** the delivered and asserted values are **`5 / 6 / 1`** (`tests/Feature/AdminTriageLandingTest.php:516-518`). The unassigned column is `1`, not `2`, because `AdminTriageService::verificationCountFor()` returns `0` for an
  unassigned viewer **before issuing any query** (`app/Services/AdminTriageService.php:69-71`), so the badge adds no statement. The measurement is right and the test comment documents it — but the **Spec still reads `2`**, and §9.4 still
  instructs an update that, taken literally, would have written a false assertion.
- **Impact:** the Spec is the project's declared executable truth; a downstream reader (or the next review) re-deriving the Phase-2 expectation from §4.4 gets the wrong number and may "correct" a passing test.
- **Remedy:** amend §4.4's Phase-2 Unassigned cell to `1` with the reason ("the badge short-circuits and issues no statement for an unassigned viewer"), rewrite §9.4 obligation 3 as discharged, and cross-reference this review. Purely
  documentary.


### `[REQUIRED] [SPEC-B-05]` Spec §9.4 obligations 2 and 4 had no artifact: `docs/review/` did not exist and the manual measurements were never captured

- **Spec reference:** Spec §9.4 obligations 2 and 4 — record the manually verified metrics (above-the-fold placement at 1280×800, the five-participant ≤10 s usability check, the p95 < 500 ms measurement) and the accepted decisions D-S7/D-S8/D-S9
  "in the Phase-1 review artifact"; `plan/plan-admin-triage-landing.md` §6 final gate — "Accepted decisions recorded in the review artifact … and the manual metrics … that create no CI obligation"; Checklist §8 — "Out of this inventory
  (manual, no CI obligation — Spec §9.4)".
- **Evidence:** `find docs -type f` before this review returns no `docs/review/**` and no execution or review report for this feature; `plan/` contained only `plan-admin-triage-landing.md`; the Plan's `APPROVAL PHASE 1` and `APPROVAL PHASE 2`
  boxes are unticked and no walkthrough artifact records either gate. `grep -rn 'p95|usability|1280' docs/ plan/` matches only the PRD and the earlier clarification report — never a measurement record.
- **Impact:** the CI-invisible half of the PRD §6 metric set (three metrics) has **no evidence at all**, and by definition the suite cannot falsify it. Obligation 4 is discharged by *this* document; obligation 2 remains **unverified** and must
  not be reported green — the same honesty rule the team already applies to coverage (Spec §7.4).
- **Remedy:** perform and record the three measurements in an addendum to this file (or `docs/review/phase-1-metrics-*.md`), or formally re-scope them as `[Assumed / Backlog]` in the PRD with the product owner's agreement. Do not leave them as
  an unticked implicit obligation.

### `[REQUIRED] [SPEC-B-06]` The PRD's long-agency-name criterion vanished without an allocation or a deferral

- **Spec reference:** PRD §5.3 "Long agency names": *"the scope indicator truncates with an ellipsis once the agency name exceeds 40 characters, and the header neither wraps nor reflows at a 1280 px viewport (finding F-05 replaced
  'degrades gracefully' with this measurable threshold)."*
- **Evidence:** `grep -rn -i 'ellipsis|truncat' spec/spec-admin-triage-landing.md docs/checklist/checklist-admin-triage-landing.md plan/plan-admin-triage-landing.md resources/views/admin/dashboard.blade.php` returns **no match anywhere**.
  The blade renders the label as a plain Bootstrap badge (`:22`, `class="badge text-bg-light border"`) with no truncation utility, the Checklist has no case, and PRD §9 allocates Phase 2 to "the layout-owned sidebar badge" and Phase 3 to
  "period-scoped report figures … caching … trend visualisation" — the criterion is in **no phase at all**. Spec §1.1 Out-of-Scope does not mention it either.
- **Impact:** an accepted, measurable PRD requirement was dropped silently between PRD and Spec. The implementation is a live UX risk for the exact persona FEAT-002 exists for (a long `nama_instansi` will wrap or overflow the chip), and no
  artifact tells a future reader it was ever required.
- **Remedy:** choose one explicitly — (a) implement: a truncation class carrying `text-overflow: ellipsis` with the 40-character threshold plus a `title` attribute, and one S3 case with a >40-character `nama_instansi`; or (b) defer: add the
  criterion to Spec §1.1 and PRD §9 Phase 3 marked "backlog — no AC in v1.3". Either is acceptable; silence is not.


### `[REQUIRED] [SPEC-B-07]` `docs/ARCHITECTURE.md` is stale in exactly the places this feature moved

- **Spec reference:** AGENTS.md Living Architecture Map Mandate — new directories, modules and **public test seams** must be recorded; Spec §9.4 obligation 1; Plan Tickets T9/T11.
- **Evidence:**
  - Recorded correctly: `app/Support/` (§5 tree and §6 row) and `app/View/Composers/` (§5 tree and §6 row).
  - `docs/ARCHITECTURE.md:85` still reads `app/ # Application code (131 PHP files)`; the actual count is **135** — `app/Support/` (+3) and `app/View/Composers/` (+1) are precisely this feature's contribution.
  - `:119` reads `tests/Feature/ # 12 HTTP/workflow tests` while `:152` reads `13 tests`; the actual count is **14**. `:120` reads `tests/Unit/ # 5 tests`; the actual count is **9**.
  - §6's `tests/Unit/` row (`:153`) lists only `ArchitectureGuardTest`, `SecurityHeadersTest`, the export-injection test and the service test — the four new unit files are unlisted, and the new **`tests/Unit/Support/`** and
    **`tests/Unit/Scopes/`** directories appear in no tree.
  - §10 "Existing public test seams" (`:206-211`) still lists only the six pre-existing patterns; **S1–S4 — the four seams this feature introduced and the Spec pre-agreed — are absent.**
  - Commit `6937515`'s message claims it refreshed "the moved counts (35 services, 13 feature test files)"; services (35) is correct, the test counts are not — the document now carries three different numbers for one quantity.
- **Impact:** the map is the entry point every agent is told to read first; three wrong counts and four missing seams will mislead the next reader about what is tested where.
- **Remedy:** one documentation commit — correct the two counts (drop the duplicate figure), list the four new unit files and the two new unit subdirectories, and add S1–S4 to §10 with their boundaries (`ReportingPeriod` pure; `AdminTriageService`
  DB without `actingAs()`; `admin.dashboard` HTTP; `layouts.modern` badge). Re-run the "every directory under `app/` listed exactly once" check the T9/T11 steps prescribe.

### `[OPTIONAL] [SPEC-B-08]` TC-070 (the suppression/skip static guard) was never written, and the inventory was not reconciled

- **Checklist reference:** Checklist §5 `TC-070 test_no_suppression_or_skip_annotation_exists_in_changed_files — CONSTRAINTS §3 rules 1 & 3 · static guard`, and §6 which maps `SEC-001` onto "TC-027, TC-070 + pre-flight grep".
- **Evidence:** `grep -rn 'suppression|noqa|phpstan-ignore|eslint-disable' tests/` returns **no match** — no such test exists. The rule is satisfied *by inspection* (this review re-ran the greps: zero hits; the only skips remain the two documented
  `RateLimitingTest` ones from backlog F1), but the automated guard the inventory promised is missing and, unlike the F-6 fixture deviation, the omission is recorded nowhere.
- **Remedy:** either add `tests/Unit/FloorGuardTest.php` asserting the repository contains no suppression token and no `markTestSkipped` outside the documented F1 allow-list, or mark TC-070 as `[Assumed / Backlog]` in the Checklist and Spec §6.4 so the
  70-case inventory reconciles with the delivered cases (see §5 below).


## 5. Verifying the Verification (Test Efficacy & Inventory Reconciliation)

### 5.1 Inventory reconciliation — Checklist 70 cases vs delivered

Counts obtained with `grep -c 'public function test_'` per file in this session.

| Checklist group | Inventory | Delivered | Delta |
| --- | --- | --- | --- |
| S1 — `ReportingPeriod` unit | 13 (TC-001…TC-013) | 12 in `ReportingPeriodTest` + TC-013 in `ForYearTraitTest` | 0 |
| S2 — `AdminTriageService` unit with DB | 14 (TC-014…TC-027) | 14 | 0 |
| S3 — `GET /admin/dashboard` | 29 (TC-028…TC-056) | **28** | **−1 — TC-045 absent (`[SPEC-B-03]`)** |
| S4 — sidebar badge | 3 (TC-057…TC-059) | 3 | 0 |
| Cross-cutting guards | 11 (TC-060…TC-070) | TC-060…TC-067 satisfied by pre-existing suites; TC-068 delivered as 4 cases in `DashboardDateRangeAdapterTest`; TC-069 delivered in `ForYearTraitTest`; **TC-070 absent** | **−1 (`[SPEC-B-08]`)** |
| **Total delivered** | **70 cases** | **63 new test methods in 6 files** (12 + 14 + 4 + 2 + 28 + 3) | −2 cases, both documented above |

Only two of the seventy inventoried cases are missing, and both are named — which is why `SPEC-B-03` is treated as a required *fix* rather than a systemic process failure.

### 5.2 Test-efficacy audit — tautology, shallowness and over-mocking

| Test-efficacy risk | Verdict | Evidence |
| --- | --- | --- |
| Tautological tests (asserting the implementation's own expression) | Clean | counts are asserted against the §5.0 matrix literals (`AdminTriageServiceTest.php:109-112`), not re-derived; `TC-014` asserts the fixture population **before** any count, so a fixture defect cannot masquerade as an implementation defect (Checklist Sequencing hazard 1) |
| Mock-asserting tests | Clean | no `Mockery` / `$this->mock(` in the four seam files, as §6.2 mandates; every seam drives the real DB, router and Blade |
| Shallow assertions | One instance | `[STD-A-07]` — the `file_get_contents()` assertion in TC-050 is the only pairing that cannot fail independently. Every other negative case asserts inside an extracted region (`AdminTriageLandingTest.php:277-282`, `:292-296`) rather than against the whole body, exactly as CON-007 requires |
| Mutation-resistance | Good, with one residual | `TC-008` (`assertIsInt` plus the string-config fixture) kills removal of the `(int)` cast; `TC-010`/`TC-011` pin both precedence directions, so reversing `anchor()` breaks a case; `TC-052` asserts **exact** per-state counts plus the ceiling, so one added or removed statement fails it. Residual: the unfalsifiable cast already recorded as known finding 4 — correctly not claimed as covered |
| Over-specification / brittleness | Acceptable | `domainStatementsFor()` filters on table-name substrings rather than a query-log length; TC-034's `stat-value">0<` count is formatting-coupled but fails loudly when the markup moves |
| Assertion honesty on accepted defects | Exemplary | TC-055 pins the D-S8 empty cross-agency queue as a positive equality (`assertSame(0, …)`), and TC-054's comment states which of exact-equality vs containment it asserts — the C-4/C-5 requirement, genuinely satisfied |
| Floor-guard cleanliness | Clean | zero suppressions, zero new skips, `phpunit.xml` and CI untouched, six pre-existing regression suites green |


### 5.3 Axis 1–5 statements (no further findings)

- **Axis 1 Correctness:** happy paths, boundaries (five keys, hostile keys, array input, month/year edges) and error paths (fallback instead of exception) are all asserted; `InstansiScope`-induced default-deny is preserved; the only robustness gap is the
  unvalidated config seam (`[STD-A-06]`). Return contracts hold in every branch (`AdminTriageSummary` is `final readonly`; `verificationCountFor()` returns `int` on the short-circuit path).
- **Axis 2 Readability:** the resolver, the service and the composer are small, single-purpose and named for their domain terms; nesting never exceeds two levels; no dead code beyond `isYearScoped()` (`[STD-A-03]`); no `TODO`/`FIXME`/placeholder in any changed file.
- **Axis 3 Architecture:** dependency direction is correct (controller → service → model; view → DTO/composer); `app/Support/` is pure — no DB, HTTP or auth access, as ARCHITECTURE §6 now records; the composer removed the inverted controller-owned layout coupling the
  Spec called C5; `SakipDashboardService::getDateRange()` was changed by expand–contract so its five call sites and its `array{0: Carbon, 1: Carbon}` contract are untouched. The defect here is documentary only (`[SPEC-B-07]`).
- **Axis 4 Security:** clean; see the table in §3.
- **Axis 5 Performance:** the domain-query budget is asserted exactly per viewer state with a `≤ 6` ceiling, so the page cannot silently regress into N+1; the only unbounded list (`recentLogs`) keeps its pre-existing `limit(10)`; the badge adds exactly one count per
  `layouts.modern` render and short-circuits to zero queries for an unassigned viewer; `whereBetween('period', …)` stays index-friendly (`idx_perf_data_instansi_period`) instead of a `whereYear()` scan.

## 6. Final Verdict

- **Total findings:** **9 Standards issues** (0 `[CRITICAL]`, 0 `[REQUIRED]`, 6 `[OPTIONAL]`, 3 `[FYI]`) and **8 Spec issues** (0 `[CRITICAL]`, **7 `[REQUIRED]`**, 1 `[OPTIONAL]`).
- **Worst Standards issue:** `[OPTIONAL] [STD-A-06]` — an unvalidated `SAKIP_ACTIVE_YEAR` can silently move every year-scoped query to year 0.
- **Worst Spec issue:** `[REQUIRED] [SPEC-B-03]` — AC-028 (US-006's honesty invariant) has no test at any seam and was never assigned to a ticket.
- **Most surprising Spec issue:** `[REQUIRED] [SPEC-B-01]` — the Spec's explicit "no intermediate state loses the badge" clause was overridden by T4 and repaired five commits later, with no Spec amendment and no test able to see it.
- **Recommendation:** **Proceed to Refactoring Plan.** The implementation is functionally sound and the suite is honest; the outstanding work is traceability closure (one missing test, one missing render handle, one dropped PRD criterion) plus documentation
  fidelity (Spec §4.4/§4.5/§9.4 and ARCHITECTURE.md). Nothing requires a redesign, and no single finding blocks merge on its own — but none should be left as folklore either.

## 7. Outstanding Manual Evidence (must stay unverified until measured)

| Metric (PRD §6 / Spec §9.4 obligation 2) | Status |
| --- | --- |
| First meaningful figure visible without scrolling at 1280×800 | **UNVERIFIED** — no browser harness in this repository and no measurement recorded |
| Largest queue identified within 10 s by 4 of 5 participants | **UNVERIFIED** — no session record exists |
| Landing p95 < 500 ms against the seeded dataset | **UNVERIFIED** — no performance harness (PRD finding F-05); the CI substitute is the exact query budget, which *is* asserted |
| Test coverage (line ≥ 80% target / ≥ 75% floor, branch ≥ 75% / ≥ 70%) | **UNVERIFIED** — no Xdebug/PCOV, so `php artisan test --coverage --min=75` cannot run here |

## 8. Handoff

1. The refactoring plan for these findings is `plan/plan-refactor-admin-triage-landing-v1.0.md` — execute it with `/tdd-write-code`.
2. Spec-side amendments (`SPEC-B-01`, `SPEC-B-04`, `SPEC-B-06`) and the architecture-map corrections (`SPEC-B-07`) are documentation edits; they belong in the same tickets as their behavioural siblings so the Spec never trails the code again.
3. `SPEC-B-05` (manual metrics) can only be closed by a human observer; schedule it as Phase-1 sign-off, not as code work.
4. After the plan is executed, re-run all gates in §2 and re-measure the Unit runtime — the new S3 case adds a second HTTP render per assertion set.

