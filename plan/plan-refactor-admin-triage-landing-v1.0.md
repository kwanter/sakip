---
goal: Close the Admin Triage Landing review findings — restore full Spec↔Code traceability (one missing AC, one missing render handle), amend the Spec where the delivered behaviour is correct but the document is stale, make the architecture map truthful, harden the configuration seam, and remove the test-hygiene duplication.
version: 1.0
date_created: 2026-09-25
last_updated: 2026-09-25
status: Planned
upstream_review: docs/review/code-review-admin-triage-landing-2026-09-25.md
upstream_spec: spec/spec-admin-triage-landing.md (v1.3)
upstream_plan: plan/plan-admin-triage-landing.md (v1.0)
upstream_checklist: docs/checklist/checklist-admin-triage-landing.md (70 cases)
upstream_prd: docs/prd/prd-admin-triage-landing.md (v1.2)
target_executor: /tdd-write-code
tags: ["refactor", "clean-code", "architecture", "security", "tdd", "traceability"]
---

<!-- markdownlint-disable -->

# Refactoring & Remediation Plan: Admin Triage Landing (Landing Triage)

> **Origin.** Every task below traces to a finding in `docs/review/code-review-admin-triage-landing-2026-09-25.md` (9 Standards + 8 Spec findings, 0 `[CRITICAL]`). No task redesigns the feature; the slice is functionally sound, and its
> suite reproduced green in the review session (144 passed / 2 skipped, 656 assertions, 11.12 s; Pint PASS 251 files; Unit 54 passed in 1.65 s).
>
> **Execution honesty.** This workspace has no PHP in `PATH`. Every PHP command below is run as `/opt/homebrew/opt/php@8.3/bin/php <file>`, and any gate that cannot execute is reported **unverified** — never green (Spec §7.4,
> `CONSTRAINTS.md` §2). Coverage stays **unverified** (no Xdebug/PCOV) and must not be claimed.
>
> **Floor-Guard.** No task may add a suppression, skip a test, weaken an assertion, delete an existing case, lower a threshold, or touch `phpunit.xml` / CI (`CONSTRAINTS.md` §3 rules 1–5). Two tasks deliberately mutate production code
> to *prove* a new test is falsifiable; both mutations are reverted before the commit and must never be staged.

## 1. Traceability: Requirements & Constraints

| ID | Requirement / principle this plan must satisfy | Finding | Task |
| --- | --- | --- | --- |
| **REQ-001** | AC-028 must be verified at the S3 seam: a period switch moves the verification figure while the assessment and report figures and their basis labels stay identical (Spec §5.6) | `SPEC-B-03` | TASK-101, TASK-102 |
| **REQ-002** | Spec §4.4 declares `data-triage-period-select` a required machine handle; the landing must render it and a case must assert it | `SPEC-B-02` | TASK-103, TASK-104 |
| **REQ-003** | The `SAKIP_ACTIVE_YEAR` seam must treat its value as untrusted: a non-numeric, zero, negative or out-of-range year falls back to the clock instead of silently switching the application to year 0 | `STD-A-06` | TASK-301, TASK-302 |
| **REQ-004** | `ReportingPeriod::isYearScoped()` must either drive the deep-link rule it was written for or leave the class **and** Spec §4.1 | `STD-A-03` | TASK-304 |
| **PRN-001** | **Spec is the executable truth:** when the delivered behaviour is right and the Spec is stale, the Spec is amended in the same ticket — never only in a code comment | `SPEC-B-01`, `SPEC-B-04` | TASK-105 |
| **PRN-002** | Seam tests own one shared markup-extraction boundary; two spellings of "the figure" must not exist | `STD-A-01` | TASK-303 |
| **PRN-003** | A configuration seam is a trust boundary like any other input boundary | `STD-A-06` | TASK-301, TASK-302 |
| **SEC-001** | No task may introduce `withoutGlobalScope` / `withoutInstansiScope`, a raw tenancy clause, a new permission, or any write | all | every VERIFY step |
| **TEST-001** | Every new case must be falsifiable; a pin added over already-correct behaviour must be RED-proofed by a temporary mutation that is reverted before the commit | `STD-A-07` | TASK-101, TASK-202 |
| **TEST-002** | The Checklist's 70-case inventory must reconcile to the delivered set: TC-045 written, TC-070 either written or explicitly deferred | `SPEC-B-03`, `SPEC-B-08` | TASK-101, TASK-201 |
| **DOC-001** | Spec §4.4's Phase-2 budget column, §4.5's Phase-1 continuity clause and §9.4's obligations must state the delivered facts | `SPEC-B-01`, `SPEC-B-04` | TASK-105 |
| **DOC-002** | The PRD's measurable long-agency-name criterion must be implemented or explicitly deferred with the product owner — not silently absent | `SPEC-B-06` | TASK-401 |
| **DOC-003** | `docs/ARCHITECTURE.md` must be truthful for counts, directories and public test seams (Living Architecture Map Mandate) | `SPEC-B-07` | TASK-201 |
| **DOC-004** | Spec §9.4 obligation 2 (three manual measurements) must be recorded or re-scoped; it cannot be closed by code | `SPEC-B-05` | TASK-402 |

---

## 2. Implementation Steps

> **⚠️ EXECUTION DIRECTIVE FOR AI AGENTS (`/tdd-write-code`):**
> Execute this plan phase by phase through strict TDD. Write the failing test (RED), implement the fix (GREEN), verify the full gate set, then **STOP AND WAIT** for explicit user approval before starting the next phase. One commit per task. Where a task is a
> documentation edit with no behavioural sibling, state in the commit message that no RED applies and why.

### Implementation Phase 1: Spec Traceability Closure

- **GOAL-001:** Close the two behavioural gaps the Spec declares but the delivery does not carry (AC-028's rendered invariant, and the `data-triage-period-select` handle), and amend the Spec where its text is factually stale.
- **PHASE 1 EXECUTED 2026-09-25** — commits `86235e0` (TASK-101/102) and `775a201` (TASK-103/104/105). Evidence: the AC-028 pin passed (10 assertions) and was RED-proofed by a reverted mutation that failed exactly at the cross-render equality (`-'1'` vs `+'0'`); TC-051 failed at the missing handle before the blade change; the full suite went 144 → **145 passed / 2 skipped, 674 assertions**; `AdminTriageLandingTest` now carries all **29** checklist S3 cases; Pint clean on 251 files; Unit **1.63 s** against the 10 s floor; `app/` shows no diff, so no mutation leaked. Coverage stays **unverified** (no Xdebug/PCOV).

| Task ID | Description (Exact File Paths & TDD Steps) | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-101 | **Step 1 (RED-proof by mutation, no commit):** temporarily add a period filter to `assessmentQuery()` in `app/Services/AdminTriageService.php:141-153` — for example `->whereBetween('assessments.created_at', ['2026-01-01 00:00:00', '2026-12-31 23:59:59'])` — run the new case of TASK-102 and confirm it **fails**, then remove the mutation in full (`git diff app/Services/AdminTriageService.php` must be empty afterwards). This proves the pin is falsifiable (TEST-001). | REQ-001, TEST-001 | [x] | 2026-09-25 |
| TASK-102 | **Step 2 (PIN):** add `test_switching_period_moves_only_the_verification_figure` to `tests/Feature/AdminTriageLandingTest.php`. Seed an agency with submitted rows in **two calendar years** (the existing `agencyWithSubmittedData(array $periods)` helper at `:483-494` already accepts arbitrary periods, so pass e.g. `['2026-03', '2025-03']`) plus one pending `Assessment` and one submitted `Report`. Render `current_year`, then re-render after switching to a period whose verification window is empty. Assert: (a) the verification figure differs between the two renders, (b) `figureValue($content,'assessment')` and `figureValue($content,'report')` are **identical** across both renders, (c) both anchors still contain `Tidak dibatasi periode`. Comment the docblock `/** TC-045 / AC-028 … */` and state that it is a pin over already-correct behaviour, RED-proofed by the TASK-101 mutation. Confirm the case passes and that the full suite stays green. | REQ-001, TEST-002 | [x] | 2026-09-25 |
| TASK-103 | **Step 1 (RED):** extend `test_selector_is_a_get_form_without_inline_event_handlers` (`tests/Feature/AdminTriageLandingTest.php:188-199`) — or add `test_period_selector_exposes_its_declared_handle` — asserting the rendered triage region contains `data-triage-period-select` **exactly once**, that the `<form>` is `method="GET"` with `action` pointing at `admin.dashboard`, and that exactly five `<option>` values appear in `ReportingPeriod::KEYS` order with the resolved key carrying `selected`. Run `/opt/homebrew/opt/php@8.3/bin/php artisan test --filter=AdminTriageLandingTest` and record the failure (the handle is absent today — a genuine RED). | REQ-002 | [x] | 2026-09-25 |
| TASK-104 | **Step 2 (GREEN):** add `data-triage-period-select` to the `<select name="period">` at `resources/views/admin/dashboard.blade.php:30`. Change nothing else — the `<form method="GET">`, the explicit `Terapkan` submit control, the `@selected` marking and the absence of inline handlers already satisfy CON-004. Re-run the focused filter, then Pint, then the full suite. | REQ-002 | [x] | 2026-09-25 |
| TASK-105 | **Step 3 (DOC, same commit as TASK-104 is acceptable):** amend three Spec locations so no reader re-derives a wrong fact. (a) §4.5 "Controller (Phase 1)": replace the `'pendingDataCount' => $summary->verificationCount` continuity clause with the delivered design — the badge is composed by `SidebarQueueBadgeComposer` from the first release, because the layout owns its sidebar data (finding C5) and a controller-owned variable could not serve every `layouts.modern` page; state the deviation from the original Phase-1/Phase-2 split explicitly. (b) §4.4 budget table: change the Phase-2 Unassigned cell from `2` to `1` and add the reason ("the badge short-circuits and issues no statement for an unassigned viewer"), citing `tests/Feature/AdminTriageLandingTest.php:516-518`. (c) §9.4 obligation 3: mark it discharged with the measured values `5 / 6 / 1`, noting that the originally predicted `5 / 6 / 2` would have been a false assertion. Add a v1.4 remediation note to the front matter summarising all three, and add AC-028's pin to §9.3's traceability table if it is absent. | PRN-001, DOC-001 | [x] | 2026-09-25 |
| TASK-10X | **VERIFY:** `/opt/homebrew/opt/php@8.3/bin/php artisan test` (0 failures, 0 new skips) · `/opt/homebrew/opt/php@8.3/bin/php vendor/bin/pint --test --no-interaction` (0 violations) · `/opt/homebrew/opt/php@8.3/bin/php artisan test --testsuite=Unit` (**record the runtime** against the `< 10 s` target / `< 20 s` floor) · confirm `git diff --stat` touches only the files named in TASK-102, TASK-104 and TASK-105 · confirm no test count decreased. | SEC-001 | [x] | 2026-09-25 |
| TASK-10Y | **APPROVAL:** 🛑 Stop and wait for explicit user confirmation before Phase 2. | - | [x] | 2026-09-25 |


### Implementation Phase 2: Architecture Map Fidelity & Floor-Guard Automation

- **GOAL-002:** Make `docs/ARCHITECTURE.md` truthful for the counts, directories and seams this feature moved, and convert the manual floor-guard grep into an automated guard.
- **PHASE 2 EXECUTED 2026-09-25** — commits `5cce6fa` (TASK-201/202) and `17947ff` (TASK-203). Evidence: every figure in the map is now **measured** — `app/` **135**, `tests/Unit/` **9** across three subdirectories, `tests/Feature/` **14** — with **zero stale hits left** (`grep` for all four old numbers returns nothing) and the four seams findable by name (8 references). The new `FloorGuardTest` walks 4 roots, fails on a suppression token injected into a scratch file under `app/` with that exact path in the offender list, and passes again once the scratch is deleted. Suite 145 → **147 passed / 2 skipped, 683 assertions**; Pint 251 → **252 files**; Unit **1.70 s** against the 10 s floor.

| Task ID | Description (Exact File Paths & TDD Steps) | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-201 | **Step 1 (RED — evidence of the gap):** run and capture `grep -n '131 PHP files\|tests, including ArchitectureGuardTest\|HTTP/workflow tests\|13 tests' docs/ARCHITECTURE.md`, plus `find app -name '*.php' \| wc -l`, `find tests/Unit -name '*.php' \| wc -l`, `ls tests/Feature/*.php \| wc -l`. The numbers disagree with each other and with the document (131 vs 135 actual; 5 vs 9; 12 and 13 vs 14). Also grep §10 for any of `ReportingPeriod`, `AdminTriageService`, `AdminTriageLandingTest`, `SidebarQueueBadgeTest` — zero hits today, so the four public test seams S1–S4 are unrecorded. | DOC-003 | [x] | 2026-09-25 |
| TASK-202 | **Step 2 (GREEN, documentation):** edit `docs/ARCHITECTURE.md` only — (a) correct the `app/` file count and the `tests/Unit` / `tests/Feature` counts, and remove the duplicated, contradictory figure between §5 and §6; (b) list the four new unit test files and the two new unit subdirectories (`tests/Unit/Support/`, `tests/Unit/Scopes/`) in §5 and in §6's `tests/Unit/` row; (c) add S1–S4 to §10 "Existing public test seams" with their boundaries — `ReportingPeriod` pure (no DB/HTTP/auth), `AdminTriageService` with real DB and **no** `actingAs()`, `admin.dashboard`, and the `layouts.modern` badge; (d) keep the explicit note that the badge lives inside `@can('manage-sakip')` (finding F-1) so a future reader does not misread the seam as tenant-agnostic. | DOC-003 | [x] | 2026-09-25 |
| TASK-203 | **Step 3 (PIN + falsifiability proof):** add `tests/Unit/FloorGuardTest.php` implementing Checklist `TC-070` — walk `app/`, `config/`, `resources/views/` and `tests/` (excluding `vendor/` and `node_modules/`) and assert no file contains `@phpstan-ignore`, `@noinspection`, `phpcs:ignore`, `eslint-disable`, `@ts-ignore` or `noqa`; separately assert `markTestSkipped` appears only in `tests/Feature/RateLimitingTest.php` and only twice (the documented backlog F1). Make the scanner assert its own reachability by asserting it scanned a non-zero file count, so a broken iterator cannot pass vacuously. Prove falsifiability by temporarily adding `// @phpstan-ignore` to a scratch file under `app/`, running the case, observing the failure, deleting the scratch file, and re-running green — the scratch file must never be committed. | TEST-002, SEC-001 | [x] | 2026-09-25 |
| TASK-20X | **VERIFY:** re-run the TASK-201 greps and confirm the map now reports `135` / `9` / `14` with no third number for any of them, and that all four seams are findable by name · focused `--filter=FloorGuardTest` green · full suite green · Pint clean · `git status --short` shows only `docs/ARCHITECTURE.md` and `tests/Unit/FloorGuardTest.php` as changes. | SEC-001 | [x] | 2026-09-25 |
| TASK-20Y | **APPROVAL:** 🛑 Stop and wait for explicit user confirmation before Phase 3. | - | [x] | 2026-09-25 |


### Implementation Phase 3: Configuration-Seam Hardening & Test Hygiene

- **GOAL-003:** Treat the active-year configuration as untrusted input, settle the unused public flag, and remove the duplicated test plumbing. No behaviour outside `ReportingPeriod` may change.
- **PHASE 3 EXECUTED 2026-09-25** — commits `ee8ba2a` (TASK-301/302), `b2876cb` + `79b67c1` (TASK-303, split so test plumbing and the production rename stay reviewable apart), `5c94ed7` (TASK-304). Evidence: the hostile-config case failed first with `0 is identical to 2026` and passes now; the fractional value is probed a second time under a 2027 clock so that rule is falsifiable rather than decorative; `tests/Support/ExtractsTriageMarkup.php` is the single markup boundary for S3 and S4; `isYearScoped()` is gone from the class **and** from Spec §4.1 — TASK-304 took the **delete** branch, not the plan's "consume" branch, because that branch documented why a *different* predicate operates instead of consuming the flag. Suite 147 → **148 passed / 2 skipped, 688 assertions** (six fewer than before are deliberate: one deleted source-file probe plus five from the removed per-key flag assertion); Pint clean **253 files**; Unit **57 passed in 1.34 s** against the 10 s floor.

| Task ID | Description (Exact File Paths & TDD Steps) | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-301 | **Step 1 (RED):** add to `tests/Unit/Support/ReportingPeriodTest.php` one case per rejected value — `test_non_numeric_or_out_of_range_active_year_falls_back_to_the_clock`. Sweep `['abc', '0', '-5', '2026.5', '99999']` with `config()->set('sakip.reporting.active_year', $value)` and assert `ReportingPeriod::activeYear()` equals the clock year (`2026` under the frozen clock) for every entry, and that `fromKey('current_year')->start->year` agrees. Today `'abc'` yields year `0` (`(int) 'abc' === 0` → `setYear(0)`), so the case is a **genuine RED**. Run the filter and record the failure. | REQ-003, PRN-003 | [x] | 2026-09-25 |
| TASK-302 | **Step 2 (GREEN):** harden `ReportingPeriod::anchor()` (`app/Support/ReportingPeriod.php:55-66`) — read the config **after** the `$now !== null` early return (also closing `STD-A-05`), then accept the configured year only when it is a whole number inside a sane window, e.g. `$year = (int) $configuredYear;` and `return ($year >= 1970 && $year <= 9999) ? $anchor->setYear($year) : $anchor;`. Do **not** introduce a second "current year" implementation and do **not** move the fallback into `SakipDashboardService` (Spec §3.2 REQ-015 forbids a second source). Re-run the S1 filter, then the S2/S3 filters (the year seam feeds `ForYearTrait` and `getDateRange()`), then Pint, then the full suite. | REQ-003, PRN-003 | [x] | 2026-09-25 |
| TASK-303 | **Step 3 (REFACTOR, behaviour-neutral):** (a) extract `tests/Support/ExtractsTriageMarkup.php` holding `figureValue()`, `figureHref()`, `badgeValue()` and `renderedRowCount()` plus one `superAdminViewer()` / `permittedViewer()` factory pair, and `use` it from `tests/Feature/AdminTriageLandingTest.php` and `tests/Feature/SidebarQueueBadgeTest.php`; delete the duplicated private methods (including the identical `verificationFigure()`). (b) Promote the inline FQCNs to `use` statements in the three new test files. (c) Delete the `file_get_contents(resource_path('views/layouts/modern.blade.php'))` assertion at `tests/Feature/AdminTriageLandingTest.php:296`, keeping the two rendered assertions (TEST-001 forbids replacing it with a weaker check). (d) Rename the two same-valued constants in `app/Services/AdminTriageService.php:23-30` to `VERIFICATION_FILTER_VALUE` / `REPORT_FILTER_VALUE` and name the target query parameter in each docblock. Run the full suite after each sub-step; the assertion count must not decrease. | PRN-002, STD-A-01/02/04/07 | [x] | 2026-09-25 |
| TASK-304 | **Step 4 (DECISION + REFACTOR):** settle `ReportingPeriod::isYearScoped()` (zero production call sites today, `grep -rn 'isYearScoped' app/ resources/` = 1 hit which is the declaration). Preferred: consume it — express the deep-link rule in `AdminTriageService::verificationUrl()` as "send `period` only when the selection is addressable", using `isSingleMonth()` as the positive branch and documenting why `isYearScoped()` is not the operative test. If instead the method is to be deleted, delete it from `app/Support/ReportingPeriod.php:85-88`, from `tests/Unit/Support/ReportingPeriodTest.php:87` and from **Spec §4.1** in the same commit (PRN-001). Either way the suite stays green and the Spec matches the class exactly. | REQ-004, PRN-001 | [x] | 2026-09-25 |
| TASK-30X | **VERIFY:** focused S1 (`--filter=ReportingPeriodTest`) green with the new cases · full suite green with an assertion count **higher** than the review baseline (656) · Pint clean · `--testsuite=Unit` runtime recorded · grep proves no `withoutGlobalScope`, no new permission, no migration (`git status --short` shows no `database/migrations/**`). | SEC-001 | [x] | 2026-09-25 |
| TASK-30Y | **APPROVAL:** 🛑 Stop and wait for explicit user confirmation before Phase 4. | - | [x] | 2026-09-25 |

### Implementation Phase 4: Human-Gated Closures (not agent-executable)

- **GOAL-004:** Close the two findings that require a product owner or a human observer. No code may be written for these until the decision is recorded.
- **PHASE 4 — EXECUTED 2026-09-25** (commits `b49a117` for TASK-403, `195fd66` for the decision-1A implementation, `672f42f` for both decisions recorded). **Decision 1A (`SPEC-B-06`):** the long-agency-name criterion is **delivered** — the scope chip truncates with an ellipsis beyond 40 characters (`triage-scope-chip`, `max-width: 49ch`), keeps the untruncated name in `title`, and TC-072 pins that markup contract at S3 (the rendered ellipsis needs a browser and joins the deferred manual set). **Decision 2B (`SPEC-B-05`):** the three manual metrics are formally re-scoped to `[Assumed / Backlog]` by the product owner, so §9.4 obligation 2 is discharged as a deliberate deferral instead of staying unverifiable; **coverage is explicitly not part of that re-scope and remains unverified.** Suite 148 → **149 passed / 2 skipped, 693 assertions**; Pint clean **253 files**; Unit **57 passed in 1.56 s**; the inventory now names **72 cases** across **68 measured seam methods**.

| Task ID | Description | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-401 | **Decision — the PRD's long-agency-name criterion.** Present both options of `SPEC-B-06` to the product owner: (a) implement the 40-character ellipsis truncation on the scope indicator plus one S3 case, or (b) defer it to PRD §9 Phase 3. Whichever is chosen, record it in `docs/prd/prd-admin-triage-landing.md` §5.3/§9 and in `spec/spec-admin-triage-landing.md` §1.1 so the criterion is traceable. **If (a) is chosen**, this plan gains a follow-up ticket: RED at S3 with a >40-character `nama_instansi`, then GREEN with a truncation class and a `title` attribute. | DOC-002 | [x] | 2026-09-25 |
| TASK-402 | **Measurement — the three manual metrics of Spec §9.4 obligation 2.** Record the above-the-fold check at 1280×800, the five-participant ≤10 s usability result and the p95 < 500 ms measurement in an addendum to `docs/review/code-review-admin-triage-landing-2026-09-25.md`. If the measurements cannot be performed, obtain the product owner's explicit re-scope to `[Assumed / Backlog]` in the PRD. Until then `docs/review` §7 stays **UNVERIFIED**. | DOC-004 | [x] | 2026-09-25 |
| TASK-403 | **Reconcile the inventory.** Update `docs/checklist/checklist-admin-triage-landing.md` §9 so the 70-case count matches the delivered set after TASK-102 (TC-045) and TASK-203 (TC-070), and tick the cases that now exist. This closes the reconciliation table of `docs/review` §5.1 without weakening any case. | TEST-002 | [x] | 2026-09-25 |
| TASK-40Y | **APPROVAL:** 🛑 Stop. After Phase 4 the feature may proceed to `/tdd-generate-docs` (Diátaxis set) and `/tdd-retro`. | - | [ ] | |


---

## 3. Structural Remedies & Alternatives

| ID | Remedy applied | Alternative considered | Why rejected |
| --- | --- | --- | --- |
| ALT-001 | **Amend the Spec in the same ticket as the code** (`PRN-001`) instead of leaving the delivered facts in test comments and memory | Keep the Spec frozen and add a "deviations" appendix | The project's constitution makes the Spec the executable truth; an appendix no test reads is exactly how `SPEC-B-01` and `SPEC-B-04` happened |
| ALT-002 | **Pin AC-028 with a rendered-output test whose falsifiability is proven by a reverted mutation** | Accept the service-level TC-022 as sufficient coverage for AC-028 | TC-022 proves the *service* is period-invariant; AC-028 is an S3 criterion about rendered figures and basis labels, so a template regression (binding a period label to figure 2) would pass today |
| ALT-003 | **Validate the active-year value inside `ReportingPeriod::anchor()`** | Validate in `config/sakip.php` with `is_numeric(env(...))` | Both work; the resolver stays the single decision point so the guard is unit-testable at S1 without a config file, and `env()` remains confined to `config/` per ARCHITECTURE §6 |
| ALT-004 | **Keep `whereHas('performanceData')` plus the explicit `instansi_id`** (Spec SEC-002) | Push coverage into `InstansiScope` or a repository | Would bypass or duplicate the global scope — forbidden by `CONSTRAINTS.md` §3 rule 6 and Spec §4.6 |
| ALT-005 | **Extract one test-markup trait** | Leave three copies and rely on review | Two spellings of "the figure" already exist (`figureValue()` ≡ `verificationFigure()`); the drift risk is real and the fix is mechanical |
| ALT-006 | **Express the deep-link rule with `isSingleMonth()` and document the rationale** (TASK-304 preferred branch) | Delete `isYearScoped()` outright | Deleting also requires editing Spec §4.1; consuming it preserves the declared contract at zero cost. Either is allowed — silence is not |

## 4. Files Affected

| File | Change | Task |
| --- | --- | --- |
| `tests/Feature/AdminTriageLandingTest.php` | new AC-028 case; extended selector case; FQCN imports; drop the source-file assertion; adopt the new trait | TASK-102, TASK-103, TASK-303 |
| `resources/views/admin/dashboard.blade.php` | add `data-triage-period-select` to the `<select>` | TASK-104 |
| `spec/spec-admin-triage-landing.md` | §4.5 Phase-1 continuity clause; §4.4 Phase-2 budget cell; §9.4 obligations; front-matter remediation note; §4.1 if `isYearScoped()` is removed | TASK-105, TASK-304 |
| `docs/ARCHITECTURE.md` | correct counts; list the new unit files and directories; add S1–S4 to §10 | TASK-202 |
| `tests/Unit/FloorGuardTest.php` | new static floor-guard test (Checklist TC-070) | TASK-203 |
| `app/Support/ReportingPeriod.php` | validate the configured year; move the config read below the early return; settle `isYearScoped()` | TASK-302, TASK-304 |
| `tests/Unit/Support/ReportingPeriodTest.php` | new hostile-config cases; drop the `isYearScoped()` case only if the method is removed | TASK-301, TASK-304 |
| `tests/Support/ExtractsTriageMarkup.php` | new shared test helper trait | TASK-303 |
| `tests/Feature/SidebarQueueBadgeTest.php` | adopt the trait; remove the duplicated helpers | TASK-303 |
| `tests/Unit/Services/AdminTriageServiceTest.php` | FQCN imports | TASK-303 |
| `app/Services/AdminTriageService.php` | rename the two same-valued constants (docblock only, no behaviour) | TASK-303 |
| `docs/prd/prd-admin-triage-landing.md`, `docs/checklist/checklist-admin-triage-landing.md`, `docs/review/code-review-admin-triage-landing-2026-09-25.md` | Phase-4 decisions, the measurement addendum and the inventory reconciliation | TASK-401, TASK-402, TASK-403 |

**Explicitly not touched by this plan:** `AdminDashboardController` and `SidebarQueueBadgeComposer` (both verified correct), any model, any migration, any route, any policy, any existing regression suite, `phpunit.xml`, `.github/workflows/ci.yml`.


## 5. Rollback / Recovery Plan

- **RBCK-001 (no schema risk).** No task adds a migration, a column or a persisted value, so no database rollback is ever required.
- **RBCK-002 (per-task revert).** Every task ends in one commit, so `git revert <sha>` reverts it cleanly. Order matters only in Phase 3: revert TASK-303 **last** if the trait extraction must be undone, because the later test edits build on it.
- **RBCK-003 (view-handle regression).** If adding `data-triage-period-select` (TASK-104) breaks a template test, revert TASK-104 alone: the attribute is additive and no other selector behaviour depends on it.
- **RBCK-004 (config-seam regression).** If TASK-302 changes an unrelated dashboard figure, revert TASK-302 first and re-check `SakipDashboardAccessTest`, `ReportIndexRendersTest`, `DataCollectionControllerTest` and `TargetTenantIsolationTest`. The guard only alters behaviour for values that today produce year 0 or a truncated string, so a regression implies a second reader of the seam was missed — investigate before re-committing.
- **RBCK-005 (mutation leakage).** TASK-101 and TASK-203 are the only places this plan touches code without intent to keep the touch. Before each commit run `git diff` and confirm neither mutation is present; a leaked `whereBetween` inside `assessmentQuery()` would silently narrow the assessment queue while every count test still passed.
- **RBCK-006 (gate honesty over forward motion).** If a gate cannot run in the environment, record it as **unverified** and stop at the phase gate — never merge forward on an assumption. Coverage stays unverified until a driver exists.

---

## 6. Definition of Done

- [ ] `AC-028` is referenced by a passing S3 case, and the Checklist's TC-045 box is ticked.
- [ ] `data-triage-period-select` renders exactly once inside the triage region and is asserted.
- [ ] The Spec states the delivered badge ownership, the delivered budget (`5 / 6 / 1`) and the discharged obligations, so no reader can re-derive a stale fact from §4.4, §4.5 or §9.4.
- [ ] `docs/ARCHITECTURE.md` reports 135 / 9 / 14 with no contradictory duplicate, and §10 lists S1–S4.
- [ ] `tests/Unit/FloorGuardTest.php` exists and is falsifiable (proven by a reverted mutation).
- [ ] `SAKIP_ACTIVE_YEAR` values that are non-numeric, zero, negative, fractional or out of range fall back to the clock, each covered by an S1 case.
- [ ] `ReportingPeriod::isYearScoped()` is either consumed with a documented rationale or absent from both the class and Spec §4.1.
- [ ] Full suite green with **more** assertions than the 656 baseline, 0 new skips, Pint clean, Unit runtime recorded, coverage reported **unverified**.
- [ ] Phase-4 decisions (long-name criterion, three manual measurements, inventory reconciliation) are recorded in the PRD, the review artifact and the Checklist.

**Handoff:** `/tdd-write-code` executes this plan phase by phase; after the final approval, `/tdd-generate-docs` and `/tdd-retro` close the feature.

