# TDD Retrospective: Security Audit Remediation (audit run-2 + reviews)

**Date:** 2026-10-25
**Author:** TDD Retrospective Optimizer
**Evaluated Artifacts:**
- Plans: `plan/plan-bugfix-security-v2.0.md` (executed), `plan/plan-refactor-security-remediation-v1.0.md` (executed), `plan/plan-refactor-security-remediation-v1.1.md` (planned)
- Audit evidence: `~/security-audit-skill/sakip/run-2/` (REPORT.md, findings.json)
- Reviews: round-1 and round-2 two-axis sub-reviews (in-chat, aggregated 2026-10-24/25)
- Test Suite: `tests/` (313 tests at close)

---

## 1. ⏱️ Test Suite Health & Performance Telemetry

- **Total Test Execution Duration:** 21.7–23.4 s across six runs (Target SLA: < 15.0 s ⚠️ **BREACHED**; Hard floor: < 20.0 s ⚠️ **BREACHED**)
- **Total Tests Executed:** 313 tests (60 unit / 253 feature, 0 failed, 0 skipped, 2 430 assertions); Unit suite **1.39 s** (SLA < 10 s ✅)
- **Slowest Test Suites Identified:**
  1. `tests/Feature/RateLimitingTest` — 6 of the top-10 slots, ≈ 7 s combined ➔ *Remedy: the KB already owns the fix (session 10, KB-P3): drive the limiter at the `RateLimiter` seam with controlled time instead of full HTTP requests.*
  2. `tests/Feature/AuthenticationTest::login_attempts_are_throttled` (1.07 s) ➔ *same remedy.*
- **Growth context (honesty):** baseline 2026-09-26 was 151 tests / 12.21–12.37 s (≈ 81 ms/test); today 313 tests / ≈ 73 ms/test — **per-test efficiency held while the suite doubled**. The breach is a volume effect, not a rot effect; the SLA number, not the code, needs a product-owner decision (see §4).
- **Flaky Tests Detected:** 0 observed (all runs green; single-execution observation, no repeated-run soak was performed).

## 2. 🔁 SDLC Process & Friction Analysis

### What Went Well (Praise & Retain)
- **Audit → plan → TDD pipeline held end-to-end:** every confirmed/needs-validation finding became a REQ with a RED-proven public-seam regression; 13 atomic commits; zero suppressions/skips throughout.
- **Two independent two-axis reviews caught real defects the coder missed** (STD-01 mint path, STD-04 total_reports, STD-05 AJAX leaks) — the reviewer rounds paid for themselves.
- **The sandbox observation discipline from audit run-1/2 transferred:** byte-level proof (TYPE_FORMULA → TYPE_STRING) now lives in the repo suite where the audit sandbox could not reach it.
- **Mutation-verified RED** for the dashboard counts pin (fleet 5 vs instansi 2) prevented a false-green commit.

### Friction Points & Blockers Encountered
- **Fixture drift (3×):** `ReportFactory` carries schema-absent columns; `targets`/`performance_data`/`programs` unique constraints shaped test data; `kegiatan/show` requires the program→sasaran link. Each was misread once as a security RED before being classified (CON-008 discipline saved the run, but cost cycles).
- **Shared in-memory connection across test files** made cross-file `assertDatabaseMissing` unsound (mint regression) and surfaced a guard-ordering bug (unique 422 masking a 403). Root cause is now documented in the test docblock.
- **Skill/tooling friction:** `general-purpose` subagent type failed on this account (provider reasoning-level error) — `explore` was used for all delegated review agents; the Mimosa pre-commit hook blocked legitimate commits twice (false-positive hardcoded credentials; Bash heredoc writes) and required Write/Edit-tool compliance.
- **`scopeForInstansi` already existed** with an `int` typehint (latent 500 for UUID callers) — the "add a scope" task became "find, fix, and reuse".

## 3. 🧠 Permanent Knowledge Base Updates (`memory.instructions.md`)

### Architecture & Patterns Promoted
- **[P-01] Guards precede validation.** An authorization guard placed after `$request->validate()` is masked when the row pre-exists (unique → 422/302 instead of 403). Order: identity guard → validate.
- **[P-02] Dual-denial is a contract, not ambiguity.** `InstansiScope`-filtered route-model binding answers 404; explicit guards answer 403. Tests must accept both for foreign selectors (CON-002) — but at least one explicit-403 branch should be pinned so the guard itself cannot be deleted silently (open: plan v1.1 TASK-405).
- **[P-03] Authority literals need a mechanical drift guard.** Hand-maintained reserved-name lists drift (round-1 missed 10 Gate::define names). ALT-101's scan test is the remedy (plan v1.1 TASK-401).
- **[P-04] Scope lifts are decisions, not accidents.** Lifting `InstansiScope` in `getSystemWideMetrics` is correct only because the executive gate *grants* Semua Instansi authority; helper aggregates inside that branch must be lifted consistently or the dashboard mixes fleet and tenant numbers (R2-STD-02).
- **[P-05] Cross-file DB state persists on the shared `:memory:` connection.** Fixture rows from earlier suites are visible later; row-presence assertions must either use per-test-unique names or assert the denial, not absence.

### Dead-Ends (Do NOT Repeat)
| # | Attempted Approach | Why It Failed | Correct Solution |
|---|---|---|---|
| 1 | `Rule::in([$user?->instansi_id])` for tenant binding | null-instansi case rides on string-cast semantics; unreadable contract | `TenantRule::ownInstansi()` with an explicit closure failure (STD-09) |
| 2 | `assertDatabaseMissing` for names other suites may have created | shared `:memory:` connection persists rows across files | assert the denial (403); reserve absence assertions for names no other suite creates |
| 3 | Writing the sandbox runner via Bash heredoc / editing Python via Bash | Mimosa pre-commit hook rejects non-Write-tool code writes | use Write/Edit for every code file, Bash only for copies and commands |
| 4 | `general-purpose` subagent for delegated review | provider rejects the model/reasoning selection on this account | `explore` agent type (read-only, sufficient for review) |

## 4. 🎯 Action Items for Next SDLC Cycle
- [ ] **A-101 (product owner):** recalibrate the full-suite runtime SLA against the 313-test baseline (proposal: target < 25 s / hard < 30 s) **or** sanction an optimization slice for `RateLimitingTest` (KB-P3 remedy pays ≈ 7 s). Do not silently lower the number.
- [ ] **A-102:** execute `plan/plan-refactor-security-remediation-v1.1.md` via `/tdd-write-code` (closes R2-STD-01/02 + review-record hygiene).
- [ ] **A-103:** before deploying past `6ded457`, run the RBCK-103 read-only inventory of tenantless Executive accounts (R2-SPEC-07).
- [ ] **A-104 (carried):** v1.0 `REQ-006` (evidence edit locking) and `REQ-007` (journal symlink containment) remain open backlog.
- [ ] **A-105:** add the `docs/review/render-diff-2026-10-25.md` render evidence (HYG-204).
