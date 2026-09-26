---
description: "Project memory file for tracking progress, active artifacts, and cross-session decisions."
---

# Project Memory: Active Context & Knowledge Base

> This file is managed by the `memory-manager` skill and `/tdd-retro`.

## 1. Project Context
- **Project Name:** SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah)
- **Current Phase:** **Phase 6 (Documentation) and Phase 7 (Retrospective) complete — the Admin Triage Landing chain is closed end-to-end** (delivery → five-axis review → four-phase remediation → Diátaxis set → retro). The five-document Diátaxis set (`docs/tutorials/`, `docs/how-to/` ×2, `docs/reference/`, `docs/explanation/`) and `docs/retro/retro-admin-triage-landing-2026-09-26.md` are delivered; the Spec is at **v1.5**, all **17 of 17** review findings are closed, and `TASK-40Y` is satisfied by the handoff. Baseline measured 2026-09-26: 151 tests / 149 passed / 2 skipped / 693 assertions; full suite **13.19–13.42 s**, Unit **1.27 s**, Pint clean on **253 files**. **Line coverage was measured for the first time — 8.8 % project-wide, 91.8–100 % for the triage slice; branch coverage remains unverified (Xdebug absent).** Next sanctioned route: the next feature cycle, in a **new chat session** via `/tdd-explore-ideas` or `/tdd-prd`. **All ten retro actions (A1–A10) are executed and decided**: toolchain resolution via `make gates-local`, `--profile` as a gate, stale docs metrics corrected, backlogs **F1 and F2 closed**, `CONSTRAINTS.md` §1 carrying the A5/A6 decision records, `docs/ARCHITECTURE.md` re-measured (A8), and **A10 wiring `throttle:guest`** onto `/`, `GET /login`, `POST /login` and `POST /logout` with `Limit::none()` for a resolved user. Suite: **OK (152 tests, 767 assertions)**, 0 skips, 0 PHPUnit deprecations, Pint clean on 253 files; full suite ~12.2 s (SLA < 15 s target / < 20 s hard).

## 2. Active Artifacts & Documents
- `AGENTS.md`, `CONSTITUTION.md`, `CONSTRAINTS.md`, `CONTEXT.md` — governance contracts (versioned via `.gitignore` negations)
- `docs/ARCHITECTURE.md`, `docs/discovery/`, `docs/prd/`, `docs/audit/` — architecture map, discovery drafts, requirements, clarification reports
- `spec/spec-admin-triage-landing.md` (v1.3), `plan/plan-admin-triage-landing.md` (v1.0), `docs/checklist/checklist-admin-triage-landing.md` (70 test cases) — the approved blueprint, its task matrix, and the test-case inventory for the admin triage landing
- `docs/adr/` — still not created; the Triple Gate has been unmet for every decision taken so far
- Per-session artifact status lives in the **Active Artifacts** list of the latest checkpoint below; this section is only the top-level index.

## 3. Session Progress Log
- Initialized TDD-Spec SDLC Architecture.
- Session 8: implemented the Admin Triage Landing Phases 1 + 2 (11/11 tickets) under strict Red-Green-Refactor; 144 passed / 2 skipped, Pint clean, coverage `unverified`.
- Session 9: reviewed that delivery on two axes (**17 findings, none critical** — 9 Standards, 8 Spec), wrote the four-phase remediation plan, and executed **Phase 1** of it (AC-028 pinned with a mutation RED-proof, `data-triage-period-select` rendered and asserted, Spec amended to **v1.4**), then **Phase 2** (architecture-map fidelity plus the static floor-guard test TC-070), then **Phase 3** (the active-year seam hardened against a non-numeric value, `isYearScoped()` removed with its Spec line, and the triage seam tests collapsed onto one shared markup boundary), then **Phase 4** (the checklist reconciled to 72 cases / 68 measured methods, decision 1A delivering the long-agency-name truncation with TC-072, and decision 2B re-scoping the three manual metrics to `[Assumed / Backlog]`); **149 passed / 2 skipped, 693 assertions**, Pint clean on 253 files, Unit 57 in ~1.6 s, coverage `unverified`.

## 4. Permanent Knowledge Base & Architecture Decisions
- Strict Test-First & Pre-Agreed Seams Mandate enforced across all phases.
- **Memory fast path is already configured:** the `## Memory Configuration` section of `AGENTS.md` (lines 439–443) records `Active Memory Path: .agents/instructions/memory.instructions.md`, which matches this file. The `memory-manager` Workflow 1 Step 0 fast path therefore works; checkpoints in Sessions 1–2 that listed this as "pending" are **superseded** and no `AGENTS.md` edit is required.
- **`InstansiScope` is default-deny and marked immutable** (`app/Models/Scopes/InstansiScope.php:11-18`): a viewer whose `instansi_id` is null sees **nothing**. No requirement, PRD, or spec may therefore promise an unscoped viewer a cross-agency view. A PRD that promised "viewers without an agency see all agencies" had to be corrected on this ground (Session 7, finding F-03).
- **Period vocabulary is not uniform across tables:** `performance_data.period` is a `string(7)` `YYYY-MM`, `targets.year` is an integer, and `reports.period` is a free-form `string(20)` seeded in the test suite as `YYYY-Qn` (`tests/Feature/ReportIndexRendersTest.php:34`). Never map one period vocabulary onto another without verifying the stored format first (Session 7, finding F-02).
- **A sidebar badge is a property of (link × count), never of the count alone.** The queue badge lives inside the `@can('manage-sakip')` section of `resources/views/layouts/modern.blade.php:81,88-93`, so a viewer who cannot see that link sees no badge even at a count above zero. Never assert a badge without first establishing that the viewer may open its link (Session 8, finding F-1).
- **The permission `manage-sakip` is referenced by two blade guards but defined and granted nowhere** (`resources/views/layouts/modern.blade.php:81`, `resources/views/layouts/app.blade.php:97`; no seeder or config defines it). In practice that sidebar section, and therefore the Phase-2 badge, is Super-Admin-only today. Pre-existing governance gap, recorded rather than fixed (Session 8, finding F-1).
- **`PerformanceDataFactory` has no `approved()` state** (its states are `draft()`, `submitted()`, `validated()`, `rejected()`, plus `forInstansi()`/`forPeriod()`). The landing figures are `verification` (submitted data), `assessment` (`Assessment::pending()`) and `report` (submitted reports) — never a "pending/approved" pair (Session 8).
- **A statement budget can legitimately reshape production code.** Because Spec §4.4 caps the landing at six statements, `AdminTriageService::verificationCountFor()` resolves the scope kind *without* the agency-name lookup (a badge renders no label), while `resolveScope()` keeps the label carve-out for the summary path that does render it. The budget measured seven before the split (Session 8).
- **Reproduce "manual" browser checks with a throwaway HTTP-kernel harness.** The repository installs no browser harness; a temporary PHPUnit class that renders pages through the kernel and writes text to `/tmp` yields readable evidence, and it must be deleted afterwards — never commit a non-asserting probe (Session 8).
- **A pin needs a mutation to be honest.** When a new test guards behaviour that is already correct it cannot produce a RED, so its falsifiability must be *proven* rather than assumed: mutate the production line its subject depends on, observe **the intended assertion** fail, then revert and confirm the production diff is empty before committing. A mutation that fails at a *different* assertion than intended is evidence the **fixture**, not the guard, is wrong — the AC-028 pin failed at its non-zero guard until the fixture stopped relying on row order (Session 9).
- **A query without an explicit order makes a test non-deterministic when *which* row matches changes the assertion.** `Model::query()->where(...)->firstOrFail()` returns whichever row the database happens to yield; when the test's meaning depends on that row's attributes (year, period, status), select it **by attribute**, never by position (Session 9).
- **An accepted deviation recorded only in a code comment, a memory checkpoint, or a commit message is not recorded.** The Spec is this project's executable truth, so the amendment belongs in the Spec **in the same commit** as the change. The review found three cases where the delivered behaviour was right and only the document was stale, plus one where the Spec's planned value — the `5 / 6 / 2` statement budget — would have produced a **false assertion** had it been followed literally (Session 9).
- **A remediation plan's "preferred" branch is a hypothesis, not an obligation.** The plan preferred to *consume* `ReportingPeriod::isYearScoped()`, but the change it described only documented why a *different* predicate (`isSingleMonth()`) operates — following it would have left declared-but-unused API in place, which was the finding rather than the fix. Take the sanctioned alternative, and record why in the commit message, the plan's evidence line **and** the review/report, so the deviation reads as a decision instead of an oversight (Session 9).
- **Cross-check the assertion count across a behaviour-neutral refactor and explain every delta.** The suite moved 694 → 688 assertions during Phase 3; every one of the six is accounted for (a deleted source-file probe plus five per-key assertions whose subject was deliberately retired with the flag they tested). An *unexplained* drop is the signature of an assertion quietly deleted to reach green — exactly what the floor-guard forbids — so the delta belongs in the commit message, not in the reviewer's imagination (Session 9).
- **An approval gate is a record of consent, so it is ticked when consent exists and never before.** Two gates in this session were left open after their confirmations had arrived (which misreads as "not approved") and one was nearly ticked before its confirmation existed (which would have invented consent). Check the direction of the gate before touching the box: gates that *open* a phase close when the human says go; the gate that *exits* the phase stays open until the handoff is actually accepted (Session 9).
- **Re-scoping unverifiable evidence is a legitimate closure — when the product owner owns it and the exceptions are named.** The three manual metrics this repository cannot measure were closed by an explicit product-owner decision recorded in the PRD (the decision of record), with the Spec obligation discharged as a deliberate deferral and the review artifact rewritten from "unverified" to "deferred". The rule that keeps it honest is naming what the re-scope does **not** cover: coverage stayed an unmet, unverified gate, so the project never reads a deferral as evidence (Session 9).
- **An "unverified" gate must carry an expiry; re-probe its blocker at every phase gate.** The coverage gate was reported as `unverified` for four sessions on the inherited claim that no driver existed. **PCOV is installed** in the local PHP 8.3 build (`/opt/homebrew/opt/php@8.3/bin/php -m | grep pcov`), so the gate was never unverifiable — it was unmeasured: `php artisan test --coverage` reports **8.8 % total line coverage over `app/`**, while the triage slice is **91.8–100 %** (`AdminTriageService` 91.8 %, `ReportingPeriod` 97.1 %, controller/DTO/enum/composer 100 %). **Branch coverage is genuinely unmeasurable here**: PCOV emits line metrics only, Xdebug is absent, and even `--coverage-text` omits branch rows (Session 10, KB-P1).
- **Profile before optimizing, then check whether the slowest file is also the debt file.** `php artisan test --profile` prints the top ten slowest tests in one command; the top ten were **60.67 %** of the run and `tests/Feature/RateLimitingTest.php` alone was **57 %** (7.41–7.69 s of a 13.2 s suite) — *and* it owns both skipped tests, so one fix pays twice (Session 10, KB-P2).
- **A stateful middleware assertion belongs at its seam, not behind N full HTTP requests.** Six of the ten slowest tests were throttle tests at ~1.0–1.3 s each because each one drove the limiter through 6–31 framework requests. Control time with Carbon and seed/clear through `RateLimiter::hit()/clear()` instead (Session 10, KB-P3).
- **Every metric quoted in an artifact carries its source and its date.** A documentation set written by a non-executing persona cannot observe the suite; it must record where each number came from so the next session can re-derive it. `docs/reference/ref-admin-triage-landing.md` §1 quotes the plan's Unit figure (1.56 s) while today's measurement is **1.27 s** — both true, only the date tells them apart (Session 10, KB-P4).
- **Dead-end — driving the rate limiter through full HTTP requests:** it produced the suite's dominant cost *and* its only two skips (`rate_limit_resets_after_time_window`, `guest_users_have_separate_rate_limit`). Correct solution: assert at the limiter seam with controlled time, and close backlog **F1** via `/tdd-bug-report` (Session 10).
- **Dead-end — expecting a whole-`app/` coverage floor to pass on a brownfield monolith:** 99 of ~132 instrumented files sit at 0.0 %, so the `CONSTRAINTS.md` §1 floor (≥ 75 % line) is unreachable without a project-wide campaign. Correct solution: report changed-slice coverage plus a ratchet; re-scoping the threshold is a product-owner decision and must never be a silent edit (`CONSTRAINTS.md` §3 rule 5) (Session 10).
- **Measured baseline (2026-09-26):** 151 tests (57 unit / 94 feature) — 149 passed, 2 skipped, 693 assertions; full suite **13.19–13.42 s** (no SLA declared), Feature 11.22 s, Unit **1.27 s** (SLA < 10 s ✅), triage seams S1–S4 **3.73 s** for 60 tests; Pint **PASS 253 files** in 0.66 s; **63** PHPUnit deprecations (F2); line coverage 8.8 % total / 91.8–100 % slice; `php` is **not on `PATH`** (use `/opt/homebrew/opt/php@8.3/bin/php`).
- **Backlog F2 is CLOSED (2026-09-26).** The 63 PHPUnit deprecations were one kind only — doc-comment test metadata (`/** @test */`), deprecated in PHPUnit 11 and removed in PHPUnit 12. Fix: a 1:1 migration to the `#[Test]` attribute across 8 files (every method name preserved), pinned by a new `FloorGuardTest` case that forbids doc-comment test metadata repository-wide. Operational note: these are *PHPUnit* deprecations, detailed only by `--display-phpunit-deprecations` — `--display-deprecations` shows PHP deprecations and printed nothing here. Suite after: **152 passed / 0 skipped / 732 assertions**, deprecations **0**.
- **A static guard that forbids a token must not contain that token in the shape it forbids.** The doc-comment metadata scan flagged its own author's method docblock, which described the migration by quoting the old annotation — the single deprecation that survived the fix. Keep such tokens in a data structure (an array literal) or describe them in words; a doc-comment is not a safe place to quote doc-comment metadata (Session 10c).
- **Backlog F1 is CLOSED (2026-09-26).** Both skipped rate-limit cases are green and the floor-guard allow-list is now **empty** (`markTestSkipped` is forbidden repository-wide). Two root causes, neither of them flakiness: (1) `rate_limit_resets_after_time_window` cleared a key that `ThrottleRequests` namespaces **and hashes** (`md5($limiterName.$limit->key)`, `ThrottleRequests.php:134`), so the clear never matched and the window never reset — fixed by travelling past the decay (`travel(2)->minutes()`), which is key-agnostic and tests the stated property; (2) `guest_users_have_separate_rate_limit` rested on a false premise — the `guest` limiter (30/min) is **declared in `RateLimitServiceProvider` but attached to no route**, so `/` always answers 302 — now pinned as a characterisation case with the gap filed as retro action **A10**. Post-fix suite: **151 passed / 0 skipped / 731 assertions / 12.37 s**, Pint PASS 253 files, Unit 1.34 s. New baseline supersedes the row above. **A10 closed that gap the same day (product-owner go "pasang"):** the `guest` limiter is now wired to `/`, `GET /login`, `POST /login` and `POST /logout`, and it answers `Limit::none()` for a resolved user so authenticated traffic never consumes the guest budget — the characterisation pin flipped into `guest_requests_to_the_root_route_are_rate_limited_per_ip` (30 guest requests reach the redirect, the 31st is 429, and 35 authenticated requests on the same IP are never 429).

---

## 📝 Session Checkpoint: 2026-09-24

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Phase 0 (Discovery) — governance bootstrap complete; no PRD / Spec / Plan exists yet
- **Active Artifacts:**
  - `AGENTS.md` — Status: ✅ Finalized (bootstrap; project title and description calibrated)
  - `CONSTITUTION.md` — Status: ✅ Finalized (5 principles, calibrated to SAKIP)
  - `CONSTRAINTS.md` — Status: ✅ Finalized (thresholds, CI-mirrored verification commands, floor-guards)
  - `docs/ARCHITECTURE.md` — Status: ✅ Finalized (legacy codebase auto-mapped, 11 sections)
  - `docs/prd/`, `/spec/`, `/plan/` — Status: ⏳ Pending
- **Achieved Milestones:**
  - Installed the official TDD-Spec SDLC scaffolding from `GulajavaMinistudio/awesome-copilot-id/tdd-spec-skills#main` via `npx degit` (source reachability verified through the GitHub API before download).
  - Installed 21 skills (`tdd-*` ×20 plus `memory-manager`) together with `instructions/`, `rules/SDLCOrchestrator.md`, and `standards/` into both `.agents/` and `.claude/`.
  - Calibrated `AGENTS.md`, `CONSTITUTION.md`, and `CONSTRAINTS.md` to the SAKIP domain: instansi scoping, the performance-data lifecycle, UUID/soft-delete/audit invariants, and Bahasa Indonesia user-facing strings.
  - Generated `docs/ARCHITECTURE.md` from the real repository topography (131 PHP files, 31 controllers, 34 services, 54 migrations).
  - Added `.gitignore` negations so the governance artifacts become versionable.
- **Updated Files:**
  - `AGENTS.md` — new; title and project description calibrated (upstream SDLC map preserved verbatim)
  - `CONSTITUTION.md` — new; five non-negotiable engineering principles
  - `CONSTRAINTS.md` — new; quality thresholds, verification commands, floor-guard anti-cheat rules
  - `docs/ARCHITECTURE.md` — new; canonical architecture map of the repository
  - `.agents/**`, `.claude/**` — instructions, rules, standards, and 21 skills installed
  - `.agents/instructions/memory.instructions.md` and its `.claude/instructions/` mirror — project name set
  - `.gitignore` — negations added for the governance artifacts
- **Decisions Made:**
  - Governance documents are written in English; conversational output stays in Bahasa Indonesia (per the `AGENTS.md` language policy).
  - The coverage floor (80% line / 75% branch) is recorded as a local/optional gate because CI currently runs PHP with `coverage: none`.
  - `.agents/` is the canonical skill root and `.claude/` is a mirror; `/.claude` stays gitignored.
- **Next Action / Pending:**
  - Proceed to `/tdd-explore-ideas` (Phase 0 Discovery), then `/tdd-prd`.
  - Run `composer install` (or bring up the Docker stack) before trusting the quality gates; `php artisan test` and `./vendor/bin/pint --test` remain **unverified** until then.
  - Recording the `## Memory Configuration` fast path in `AGENTS.md` is not yet done and still requires explicit user consent.

<!-- checkpoint-tail: TDD-Spec SDLC bootstrap complete for SAKIP — 21 skills plus constitution, constraints, and architecture map installed and calibrated; quality gates unverified pending `composer install`. -->

---

## 📝 Session Checkpoint: 2026-09-24 — Session 2: SDLC Phase Reconnaissance (`/tdd-ask-help`)

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Pre-Phase 0 complete (governance + architecture map) → Phase 0 (Discovery) not yet started
- **Active Artifacts:**
  - `AGENTS.md`, `CONSTITUTION.md`, `CONSTRAINTS.md` — Status: ✅ Finalized (bootstrap, Session 1)
  - `docs/ARCHITECTURE.md` — Status: ✅ Finalized (codebase auto-mapped)
  - `docs/discovery/`, `docs/prd/`, `/spec/`, `/plan/`, `docs/adr/` — Status: ❌ Absent (not started)
  - `docs/plans/*.md`, `docs/architecture-remediation-plan.md`, `docs/history/plan.md` — Status: ⚠️ Ad-hoc artifacts, NOT TDD-Spec `/spec/` or `/plan/`
- **Achieved Milestones:**
  - Ran `/tdd-ask-help` Mode 1 filesystem reconnaissance (read-only) and locked the roadmap decision set.
  - Confirmed the repository is mature brownfield: 131 PHP files under `app/`, 42 test methods across 17 test files, and P1–P6 architecture remediation already landed on `main` (HEAD `95ef4cc`).
- **Blockers (Do NOT Repeat / Resolve First):**
  - **B1 — Test runner unavailable:** `vendor/` absent, `php` present only at `/opt/homebrew/bin/php` (8.5.10, off-PATH), Docker not installed, so the `CONSTRAINTS.md` §1–§2 gates are `unverified`.
    - **Correct Solution:** `export PATH="/opt/homebrew/bin:$PATH" && composer install`, then `./vendor/bin/pint --test --no-interaction && php artisan test`.
  - **F1 — Floor-Guard §3 violation (pre-existing):** 2 skipped tests in `tests/Feature/RateLimitingTest.php` (lines 116, 189) caused by real-time rate-limiter state flakiness. Matches the "75 / 2 skipped / 1 risky" baseline recorded in `docs/architecture-remediation-plan.md`.
    - **Correct Solution:** `/tdd-bug-report` with the Prove-It pattern, pinning a proper seam (`RateLimiter::clear()` or per-request state isolation) instead of skipping.
- **Decisions Made:**
  - Primary route: `/tdd-explore-ideas` (Phase 0 Discovery), because no discovery draft exists and new capability must start from the WHAT/WHY.
  - Conditional on-ramps: continue remediation via `/tdd-prd` → `/tdd-spec`; fix flaky/skipped rate-limit tests via `/tdd-bug-report` → `/tdd-write-code`; harden fragile legacy controllers via `/tdd-refactor-legacy` → `/tdd-write-code`.
  - Ad-hoc plan documents under `docs/plans/` are explicitly NOT accepted as TDD-Spec `/plan/` artifacts: they lack Readiness Scores, Pre-Agreed Test Seams, and Tracer-Bullet tickets.
  - `/tdd-write-code` stays blocked until the suite runs and reports a true GREEN baseline.
- **Updated Files:**
  - `.agents/instructions/memory.instructions.md` — Project Context "Current Phase" corrected, Session 2 checkpoint appended
  - `.claude/instructions/memory.instructions.md` — mirror synced after the update
  - No functional source code, technical specification, or implementation plan was authored: `/tdd-ask-help` operates in read-only advisory mode.
- **Next Action / Pending:**
  - Open a NEW chat session (phase isolation per `AGENTS.md`) and run `/tdd-explore-ideas`, attaching `@docs/ARCHITECTURE.md`, `@AGENTS.md`, `@CONSTITUTION.md`, `@CONSTRAINTS.md`, and `@PRODUCT.md`.
  - Resolve blocker **B1** before entering Phase 4 (Code); until then every `CONSTRAINTS.md` gate must be reported as `unverified`, never as passed.
  - Decide whether to record the `## Memory Configuration` fast path in `AGENTS.md` — still requires explicit user consent.

<!-- checkpoint-tail: SAKIP sits between Pre-Phase 0 (done) and Phase 0 (not started); primary next route was `/tdd-explore-ideas`, with blocker B1 (no test runner) and finding F1 (2 skipped rate-limit tests) still open. B1 was resolved in Session 3. -->

---

## 📝 Session Checkpoint: 2026-09-24 — Session 3: Quality-Gate Verification & Verified Baseline

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Pre-Phase 0 complete → Phase 0 (Discovery) not started, but a **trustworthy GREEN baseline now exists**
- **Active Artifacts:**
  - `CONSTRAINTS.md` — Status: ✅ Gates now **VERIFIED** (formerly `unverified`); no content change was needed
  - `AGENTS.md`, `CONSTITUTION.md`, `docs/ARCHITECTURE.md` — Status: ✅ Finalized (Sessions 1–2)
- **Achieved Milestones:**
  - Resolved blocker **B1**: installed PHP dependencies (`vendor/`, 101 MB) and executed the real quality gates for the first time.
  - Verified GREEN baseline: `php artisan test` → **81 passed, 2 skipped, 263 assertions, 8.58s** (CI-parity environment).
  - Verified formatting gate: `./vendor/bin/pint --test --no-interaction` → **PASS, 241 files**.
  - Verified Unit runtime SLA: **22 tests in 0.514s** (CONSTRAINTS floor: < 10.0s).
  - Diagnosed a **false RED** first: 40 failures + 5 errors were environmental, not code defects.
- **Dead-Ends (Do NOT Repeat):**
  - **Attempted:** running `php artisan test` / `./vendor/bin/phpunit` straight from the agent shell.
  - **Reason:** the shell exports the project's `.env` (`DB_CONNECTION=mysql`, `DB_HOST=mysql`, `APP_ENV=production`, `REDIS_CLIENT=phpredis`, Redis cache/session). PHPUnit's non-`force` `<env>` entries cannot override
    pre-existing env vars, so tests hit MySQL host `mysql` (`getaddrinfo` failure) and the missing phpredis extension (`Class "Redis" not found`, 73 occurrences) → 40 failures + 5 errors.
  - **Correct Solution:** force the CI-equivalent environment (see the reference runner below): `APP_ENV=testing CACHE_STORE=array SESSION_DRIVER=array QUEUE_CONNECTION=sync DB_CONNECTION=sqlite DB_DATABASE=':memory:' BCRYPT_ROUNDS=4 TELESCOPE_ENABLED=false`, and unset the leaked `DB_*` / `REDIS_*` variables. Reference runner: `/tmp/sakip-run-tests-ci-parity.sh`.
  - **Attempted:** running long commands (`composer install`, full suite) in the foreground.
  - **Reason:** the agent tool window is roughly 30 seconds and reaps child processes when the call ends.
  - **Correct Solution:** launch detached with `nohup bash <script> > /tmp/log 2>&1 < /dev/null &`, then poll; background jobs survive the tool boundary and complete on their own.

<!-- checkpoint-tail: BASELINE VERIFIED GREEN — 83 tests (81 pass, 2 skip), 263 assertions, 8.58s via `php artisan test`; Pint PASS (241 files); Unit SLA 0.514s; remaining true issues are 2 skipped rate-limit tests and 63 PHPUnit 12 deprecations. -->

---

## 📝 Session Checkpoint: 2026-09-24 — Session 4: Session Closure & Phase 0 Handoff

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Pre-Phase 0 complete → **Phase 0 (Discovery) intentionally NOT started** (session-isolation enforced)
- **Active Artifacts:**
  - `AGENTS.md`, `CONSTITUTION.md`, `CONSTRAINTS.md`, `docs/ARCHITECTURE.md` — Status: ✅ Finalized (Sessions 1–3)
  - `CONSTRAINTS.md` gates — Status: ✅ VERIFIED (see Session 3 checkpoint metrics; do not renegotiate without new evidence)
  - `docs/discovery/`, `docs/prd/`, `/spec/`, `/plan/`, `docs/adr/` — Status: ❌ Absent (expected; Phase 0 is the next step)
  - `CONTEXT.md` — Status: ❌ Not created yet (lazy creation: only after the first domain term is resolved in Phase 0)
- **Achieved Milestones:**
  - `/tdd-explore-ideas` was invoked but **refused**: the `TDD Bootstrapper Architect` persona from Session 1 still held the session lock, and the skill's own directive forbids mixing personas in one chat. No persona activation key was emitted.
  - Phase 0 pre-flight verified as ready: `docs/ARCHITECTURE.md` exists and is current (so `/tdd-map-architecture` can be skipped), governance is complete, and the test baseline is GREEN.
  - Handoff contract defined for the next session: one idea per session, discovery draft at `docs/discovery/idea-<slug>.md`, no feature code.
- **Dead-Ends (Do NOT Repeat):**
  - See **Session 3** dead-ends (leaked `.env` export breaking the test env; ~30s agent tool window reaping child processes). Do NOT re-derive them; run gates only through the CI-parity environment.
  - **Attempted:** executing `/tdd-explore-ideas` inside the bootstrap chat session.
  - **Reason:** a different persona activation key was already active in this session; the skill instructs refusal, and `AGENTS.md` §8 mandates strict session isolation plus one session per phase.
  - **Correct Solution:** open a NEW chat session and invoke `/tdd-explore-ideas` there, loading memory first via `memory-manager` (Workflow 2: Read Mode).
- **Updated Files:**
  - `.agents/instructions/memory.instructions.md` and `.claude/instructions/memory.instructions.md` — Session 4 checkpoint appended (mirror synced)
  - No source code, specification, discovery draft, or ADR was authored in this session.
- **Decisions Made:**
  - Phase 0 will run in a dedicated new chat session; this session is hereby closed for phase work.
  - No ADR is warranted yet: the Triple-Gate test (hard to reverse + surprising without context + real trade-off) is not met by anything decided this session.
  - The `## Memory Configuration` fast path in `AGENTS.md` needs **no change**: it already exists at lines 439–443 and points to this file. The earlier "pending consent" item in Sessions 1–2 was based on a wrong assumption and is superseded. `AGENTS.md` stays untouched, as the user chose.
  - `/tdd-write-code` remains gated on a GREEN baseline, which is now satisfied; the only open prerequisites are the two backlog items below.
- **Next Action / Pending:**
  - New session: run `/tdd-explore-ideas` with `@AGENTS.md`, `@CONSTITUTION.md`, `@CONSTRAINTS.md`, `@docs/ARCHITECTURE.md`, `@PRODUCT.md`, and memory in Read Mode.
  - **F1 (Floor-Guard §3):** 2 skipped tests in `tests/Feature/RateLimitingTest.php` (lines 116, 189) — route to `/tdd-bug-report` with the Prove-It pattern when prioritized.
  - **F2 (PHPUnit 12 readiness):** 63 doc-comment metadata deprecations (`/** @test */` → `#[Test]`) — needs a Spec/Plan item before any PHPUnit 12 upgrade.
  - **Unverified gate:** `composer audit --locked` and `npm audit --omit=dev` have never been executed in this environment (network audit); keep reporting them as unverified.
- **Phase 0 Kickoff Decision (confirmed at session close):**
  - **Chosen scope for the next session — I1: admin triage landing.** Deliverable intent: the admin landing answers "what needs attention first" (pending submissions, verification queue, audit anomalies) with a period/instansi selector.
  - **Evidence basis (already collected, no new research needed):** `PRODUCT.md` *Users* section and *Product Principles #4*; two critique baselines for `resources/views/admin/dashboard.blade.php` (14/40 and 16/40); `SakipDashboardService` plus the 5-minute role-based dashboard cache documented in `docs/ARCHITECTURE.md`.
  - **Deferred candidates:** **I2** anomaly detection before `submitted → validated` (strong public seams in `app/Services/Validation/`, thresholds in `config/sakip.php`) — next-cycle candidate; **F1** → `/tdd-bug-report`; **F2** → `/tdd-prd` or `/tdd-spec` item.
  - **Explicitly KILLED as Discovery ideas:** F1 and F2 are *HOW* concerns (implementation hygiene), not *WHAT/WHY* problems; routing them through Phase 0 would waste the discovery budget.

<!-- checkpoint-tail: Session closed 2026-09-24 — Phase 0 not started here; next session runs `/tdd-explore-ideas` on the chosen idea I1 (admin triage landing), with governance, architecture map, and a GREEN baseline already in place. -->

---

## 📝 Session Checkpoint: 2026-09-24 — Session 5: Phase 0 Discovery (Idea I1) — Draft Produced

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** **Phase 0 (Discovery) — DRAFT produced**, awaiting `/tdd-prd` in a new session
- **Active Artifacts:**
  - `docs/discovery/idea-admin-triage-landing.md` — Status: ✅ DRAFT (Phase 0), **verdict GO**, 191 lines, 7 template sections, 3 `[ASSUMPTION]` tags
  - `AGENTS.md`, `CONSTITUTION.md`, `CONSTRAINTS.md`, `docs/ARCHITECTURE.md` — Status: ✅ Finalized (Sessions 1–3)
  - `CONTEXT.md` — Status: ❌ Still NOT created: the user chose to skip it; the five canonical terms remain a **proposal** in draft section 6 (lazy creation preserved)
  - `docs/prd/`, `/spec/`, `/plan/`, `docs/adr/` — Status: ❌ Absent (expected)
- **Achieved Milestones:**
  - Ran `/tdd-explore-ideas` under an **explicit session override** (the session still held the Session-1 persona lock; the override line `[Session Override Active - Warning: Context Mixing Active]` was printed as required).
  - Completed Phases 1–4: reconnaissance, Staff-Engineer critique, doubt-driven grilling, and the Discovery draft.
  - Reconnaissance was grounded in `file:line` evidence, not inference; the codebase seams (`getDateRange()`, the three queue scopes, `?validation_status=`, period indexes) were verified before being cited.
- **Key Findings (C1–C7) — the draft's substance:**
  - **C1 (defect):** `AdminDashboardController.php:14` gates on `can:access-admin-dashboard`, a permission seeded nowhere (`RolesAndPermissionsSeeder.php:31` seeds `admin.dashboard`),
    while `routes/web.php:201` gates on `admin.dashboard`. A non-Super-Admin holding `admin.dashboard` therefore passes the route guard and is rejected 403 by the controller.
    Also: `Gate::before` (`AppServiceProvider.php:88-90`) makes the `isAdmin()` branch of that gate dead code. **Legitimate, already-existing RED for Phase 4.**
  - **C2:** period vocabulary is hand-rolled (`now()->format('Y-m')` at `:25`) while `getDateRange()` already exists; worse, year filtering is duplicated in two files (`trait ForYear` inside `ForYearScope.php` and `trait ForYearTrait`), one using `date('Y')`, the other `Carbon::now()`.
  - **C3:** only 1 of 3 existing approval queues is surfaced (`Assessment::pending()`, `Report::submitted()` unused); `audit anomalies` from `PRODUCT.md` has no implementation; a 7-day login count occupies a triage slot.
  - **C4:** three conflicting caching stories — `config/sakip.php` says 5 minutes, `SakipDashboardService.php:46` hard-codes 15, `AdminDashboardController` caches nothing; this also deviates from `docs/ARCHITECTURE.md` §6 and `CONSTITUTION.md` Prinsip V.
  - **C5:** `layouts/modern.blade.php:91-92` reads `isset($pendingDataCount)`, produced only by the admin controller → the sidebar badge silently vanishes elsewhere.
  - **C6:** **zero** tests reference `admin/dashboard` or `admin.dashboard`; neither the figures nor the authorization of this page are pinned.
  - **C7:** dead affordances (inert search, permanent red bell dot) erode the triage signal — parked as layout-shell scope.
- **Dead-Ends (Do NOT Repeat):**
  - See **Session 4** dead-end: switching persona inside the bootstrap session requires a new chat, or an explicit override with the warning line. The override was used here deliberately; prefer a fresh session.
  - **Attempted:** citing the newest impeccable critique's claim that `$validatedCount` is all-time.
  - **Reason:** that critique (05:13Z) predates the controller edit (05:15Z); the controller now filters `validated` by `period` (`:26-28`). The claim is stale.
  - **Correct Solution:** re-verify critique-era claims against the current file before repeating them; the durable finding is the *mixture* of cumulative and period-scoped figures, not the single claim.
- **Updated Files:**
  - `docs/discovery/idea-admin-triage-landing.md` — new Phase 0 discovery draft (verdict GO, 7 template sections)
  - `docs/discovery/` — new directory (versionable via the `!/docs/**/*.md` negation added in Session 1)
  - `.agents/instructions/memory.instructions.md` and `.claude/instructions/memory.instructions.md` — this checkpoint (mirror synced)
  - No application source code, PRD, spec, plan, or ADR was authored; `CONTEXT.md` was intentionally **not** created.
- **Decisions Made:**
  - **Option A accepted:** the landing treats the *Periode Pelaporan* as a first-class dimension (default = current reporting year, month/quarter toggle) with an always-visible instansi scope chip, and every figure deep-links to an already-filtered queue.
  - Option B (label-only honesty) and Option C (queue inbox with bulk actions) were rejected with recorded technical reasons; audit-anomaly rules deferred to idea **I2**; layout-shell P2 items parked.
  - The sidebar badge moves to a view composer so the layout owns its own data (fixes C5).
  - No ADR created — Triple-Gate unmet (documented in draft section 7).
  - `CONTEXT.md` left uncreated; its five canonical terms stay a proposal in draft section 6 for the user to approve later.
- **Next Action / Pending:**
  - New session → `/tdd-prd` with `@docs/discovery/idea-admin-triage-landing.md`, closing the four decisions in draft section 7.
  - The three `[ASSUMPTION]` tags are the Spec agent's interrogation targets — especially the period anchor for `Assessment` and `Report`, which is **not** verifiable from the current models.
  - **F1** → `/tdd-bug-report`; **F2** → a `/tdd-spec` item. Still-unverified gates: `composer audit --locked`, `npm audit --omit=dev`.

<!-- checkpoint-tail: Phase 0 Discovery draft for idea I1 (admin triage landing) is ready at docs/discovery/idea-admin-triage-landing.md with verdict GO; next session runs `/tdd-prd`, and finding C1 (latent 403 on /admin/dashboard) is an already-existing legitimate RED for Phase 4. -->

---

## 📝 Session Checkpoint: 2026-09-24 — Session 6: PRD Produced (Admin Triage Landing)

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** **PRD phase — DRAFT produced**, awaiting `/tdd-clarify` in a new session
- **Active Artifacts:**
  - `docs/prd/prd-admin-triage-landing.md` — Status: ✅ DRAFT · 432 lines · 9 template sections · 7 user stories · 21 BDD scenarios · 8 FEAT · §7.7 decision log
  - `docs/discovery/idea-admin-triage-landing.md` — Status: ✅ Final (upstream; its §7 open questions are now **closed**)
  - `AGENTS.md`, `CONSTITUTION.md`, `CONSTRAINTS.md`, `docs/ARCHITECTURE.md` — Status: ✅ Finalized (Sessions 1–3)
  - `CONTEXT.md` — Status: ❌ Still not created; its six canonical terms are ratified inside PRD §2 but the file awaits explicit user approval
  - `/spec/`, `/plan/`, `docs/adr/`, `docs/review/` — Status: ❌ Absent (expected)
- **Achieved Milestones:**
  - Ran `/tdd-prd` under an **explicit session override** (declared: `[Session Override Active - Warning: Context Mixing Active]`), since the session still held earlier persona locks.
  - Closed every open decision from discovery draft §7 with the product owner's explicit acceptance (PRD §7.7, decisions D1–D7).
  - Verified new evidence that **changed product scope**: `AssessmentController.php:91-92` filters `?period=` via `whereYear('created_at')` (not a real period column), and no report-index period filtering was found.
  - Produced a deep-link capability matrix: `?validation_status=` (data collection), `?status=`/`?period=` (assessment), none evidenced for reports — reflected in FEAT-004 and US-006.
- **Dead-Ends (Do NOT Repeat):**
  - **Attempted:** writing the whole PRD body in a single editor call (~6.2 KB payload).
  - **Reason:** the editor rejects inputs above roughly 6000 characters.
  - **Correct Solution:** split long markdown into focused sequential edits — the same discipline already used for the governance documents in Sessions 1–4.
  - See Sessions 3–5 dead-ends (leaked `.env` breaking the test environment; ~30 s agent tool window; stale critique-era claims). Do not re-derive them.
- **Updated Files:**
  - `docs/prd/prd-admin-triage-landing.md` — new PRD (DRAFT) including the §7.7 ratified decision log
  - `.agents/instructions/memory.instructions.md` and `.claude/instructions/memory.instructions.md` — this checkpoint (mirror synced)
  - No application source code, spec, plan, or ADR was authored — the PRD skill is strictly a documentation phase.
- **Decisions Made:**
  - **D1–D7 ratified by the product owner (2026-09-24):** clock-derived reporting year behind a config seam; assessment period scoping via the `created_at` precedent (flagged for Spec);
    report figure not period-scoped in Phase 1; empty period explained with labelled zeros and a hidden strip; attention strip carries three signals;
    HQ scope labelled `Semua Instansi`; no caching in Phase 1; login telemetry removed from the figure row.
  - Report period handling is deliberately deferred rather than fabricated — US-006 makes "no figure claims a period basis it does not have" testable.
  - `CONTEXT.md` left uncreated (lazy creation); PRD §2 records the six terms as its own authoritative use.
  - No ADR created — Triple-Gate still unmet.
  - PRD `Status` remains **Draft** until `/tdd-clarify` returns a Readiness Score ≥ 80.
- **Next Action / Pending:**
  - New session → `/tdd-clarify` on `@docs/prd/prd-admin-triage-landing.md`, interrogating four self-flagged targets: the report period anchor; the untestable-sounding "HQ figures equal the sum across agencies" claim (US-002 scenario 3); the measurability of the §6 technical budgets against the seeded dataset; and whether the Spec may add a new filter parameter for the report deep link.
  - `/tdd-spec` only after clarification scores ≥ 80.
  - Backlog unchanged: **F1** → `/tdd-bug-report`; **F2** → `/tdd-spec` item. Still-unverified gates: `composer audit --locked`, `npm audit --omit=dev`.

<!-- checkpoint-tail: PRD draft for the admin triage landing is ready at docs/prd/prd-admin-triage-landing.md with decisions D1–D7 ratified; next session runs `/tdd-clarify`, and CONTEXT.md still awaits explicit approval. -->

---

## 📝 Session Checkpoint: 2026-09-24 — Session 7: PRD Clarification (Admin Triage Landing)

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Phase 1 (PRD) — **clarified at Readiness Score 82/100**; Phase 2 (Specification) is the next route
- **Active Artifacts:**
  - `docs/prd/prd-admin-triage-landing.md` — Status: 🔄 Clarified (Score 82/100) with **4 mandatory text corrections pending** (clarification report §4)
  - `docs/audit/clarification-report-admin-triage-landing-2026-09-24.md` — Status: ✅ Finalized (Iteration 1, 120 lines, 6 findings F-01…F-07)
  - `CONTEXT.md` — Status: ✅ **Created** (first domain glossary: 7 canonical terms, `_Avoid_` strict 7/7, 3 subheadings)
  - `docs/discovery/idea-admin-triage-landing.md` — Status: ✅ Final (upstream; §7 questions closed by the PRD)
  - `/spec/`, `/plan/`, `docs/adr/`, `docs/review/` — Status: ❌ Absent (expected)
- **Achieved Milestones:**
  - Interrogated the PRD in doubt-driven mode and produced a vetted clarification report; 6 findings raised (F-01…F-07), **all resolved by explicit product decisions** inside the session.
  - **Falsified two PRD claims against the codebase** and replaced them with verified facts: `reports.period` exists (`string(20)`, indexed) and `ReportController@index` already filters `?status/?type/?period/?category`. Evidence that decided the question: the suite seeds `'period' => '2024-Q1'` (`tests/Feature/ReportIndexRendersTest.php:34`), i.e. report periods are quarter-coded, so no verified range mapping exists.
  - Created `CONTEXT.md` — the project's first domain glossary — and patched `.gitignore` with `!/CONTEXT.md` so the glossary stays versionable.
  - Scored the PRD 82/100 (Completeness 35/40, Clarity 26/30, Alignment 21/30, Critical Flaw Veto **not** triggered).
  - Re-ran the CI-parity suite as the pre-commit gate (Session 3 runner `/tmp/sakip-run-tests-ci-parity.sh`) → **identical GREEN baseline**: `Tests: 83, Assertions: 263, PHPUnit Deprecations: 63, Skipped: 2`, zero failures. No PHP/Blade/CSS file was touched, so the baseline cannot have moved by construction.
  - Saved the session's artifacts as four atomic commits: `067d697` (architecture map), `78c1ca9` (glossary + ignore rules), `20277d0` (memory), `c8b26b4` (discovery + PRD + clarification report). Verified via `git log --name-only` that none of them contains application source.
- **Dead-Ends (Do NOT Repeat):**
  - **Attempted:** trusting the PRD's statement that reports have no period column and no index filtering.
  - **Reason:** both claims are false — verified against `create_reports_table.php:28,46` and `ReportController@index`. Echoing unverified column/filter claims downstream is how a Spec inherits a defect.
  - **Correct Solution:** verify every column and filter claim against the migration, the controller, and the seeders before carrying it forward (promoted to the Knowledge Base).
  - **Attempted:** assuming the suite asserts rendered content with `assertSee`.
  - **Reason:** the suite contains **zero** `assertSee`; HTTP tests use `assertOk()`/`assertStatus()` plus string assertions across 10 files.
  - **Correct Solution:** pre-agree the content-assertion seam per the existing house pattern before writing the Spec.
  - **Attempted:** batching a write (file edit or `cp`) together with a read-verification of the same file inside ONE `run_commands` call.
  - **Reason:** commands passed in a single batch run **concurrently**, so the verification raced the write and produced two false alarms in this session — `diff -q` reported the `.claude` memory mirror as differing while both files were 32226 bytes (and later hashed identically), and `git check-ignore -v` combined with shell `&&` logic reported `CONTEXT.md` as still ignored.
  - **Correct Solution:** sequence write ➔ read-verify in **separate** turns; never verify a file in the same parallel batch that wrote it. For ignore questions use `git ls-files --others --exclude-standard` or `git status --short` as the authority, because `git check-ignore -v` prints a matching **negation** pattern and exits 0 for a path that is NOT ignored.
- **Updated Files:**
  - `CONTEXT.md` — created (7 canonical terms, `_Avoid_` strict)
  - `docs/audit/clarification-report-admin-triage-landing-2026-09-24.md` — new (clarification iteration 1)
  - `.gitignore` — `!/CONTEXT.md` negation added to the governance-contract block
  - `.agents/instructions/memory.instructions.md` (+ `.claude/instructions/` mirror) — Section 1 phase corrected, two Knowledge Base bullets added, this checkpoint appended
  - No application source code, spec, plan, or ADR was authored — the clarification skill is documentation-only.
- **Decisions Made:**
  - **F-03:** `Semua Instansi` is reserved for a Super Admin; a delegated administrator without an agency assignment sees an explicit `Instansi Belum Ditetapkan` state with zero counts. `InstansiScope` default-deny semantics are immutable and may never be contradicted by a requirement.
  - **F-01:** assessment agency coverage is derived through `performance_data.instansi_id`, because `assessments` carries no `instansi_id`.
  - **F-02:** ratified decision D2 stands on corrected grounds — the report figure is **not** period-scoped in Phase 1 (report periods are free-form and quarter-coded).
  - **F-04:** FEAT-005 and US-007 move into Phase 1, because the attention strip described in §5.2 is the intended Phase-1 end state; §9 is the section to rewrite.
  - **F-05:** §6 metrics declare an enforcement mode (CI-enforced versus manual with criteria). No browser harness is introduced — the repository installs none.
  - **ADR:** still none; the Triple Gate is unmet for every decision taken in this session.
- **Next Action / Pending:**
  - New chat session → `/tdd-prd` in remediation mode, applying the four mandatory corrections in `docs/audit/clarification-report-admin-triage-landing-2026-09-24.md` §4, then `/tdd-spec` with `@CONTEXT.md` plus the four target seams from discovery §5.
  - `CONTEXT.md` is now versionable; the glossary must not be extended without a resolved canonical term (lazy creation).
  - Backlog unchanged: **F1** (2 skipped rate-limit tests) → `/tdd-bug-report`; **F2** (63 PHPUnit 12 deprecations) → `/tdd-spec` item; still-unverified gates `composer audit --locked` and `npm audit --omit=dev`.
  - **Pre-existing working-tree dirt (NOT part of this feature):** `.mimosa/hook-state/` runtime churn plus uncommitted edits to `app/Http/Controllers/Admin/AdminDashboardController.php`, `app/Http/Controllers/AdminController.php`, `app/Services/AdminService.php`, `resources/views/admin/dashboard.blade.php`, `public/css/modern-sakip.css`, and a deleted `resources/views/_impeccable_smoke_test.blade.php`. Never stage these as part of the triage landing.

<!-- checkpoint-tail: PRD for the admin triage landing is clarified at 82/100, CONTEXT.md now exists with 7 canonical terms, and .gitignore no longer swallows the glossary; the next session runs `/tdd-prd` remediation on four mandatory text corrections before `/tdd-spec`. -->

---

## 📝 Session Checkpoint: 2026-09-25 — Session 8: Admin Triage Landing Implementation (Phases 1 + 2)

- **Summary:** The eleven-ticket implementation plan for the admin triage landing is fully executed under Red-Green-Refactor. Phase 1 (T1–T9) and Phase 2 (T10–T11) are complete, the working tree is clean, and every gate is green except the PHP coverage gate, which is `unverified` because no Xdebug/PCOV driver is installed.
- **Work Done:**
  - **T1–T2** (`0c6fc64`, `4bd7291`): seam S1 read model (`ReportingPeriod`, `TriageScope`, `AdminTriageSummary`), the `reporting.active_year` config key, the active-year source, the `getDateRange()` adapter, and the deletion of `ForYearScope.php`.
  - **T3** (`0fb08c1`): seam S2 `AdminTriageService` with fourteen unit cases.
  - **T4–T7** (`df57eb8`, `2f6236f`, `10bc90c`, `365e4b7`): the landing region, the scope indicator with basis labels, deep links (mutation-verified), and the attention strip plus empty state.
  - **T8–T9** (`01675b6`, `6937515`): measured statement contracts and the architecture note. `6937515` came from a **concurrent external writer** (`ragrin`, framework hook automation) and did exactly T9's deliverable — always re-check `git log` before assuming state.
  - **T10** (`7e8642e`): the Phase-2 badge. `app/View/Composers/SidebarQueueBadgeComposer.php` resolves the period from the request and delegates to `AdminTriageService::verificationCountFor()`; it is registered on `layouts.modern` beside the global nonce composer. TC-057 and TC-059 drove the RED; TC-058 turned into a real guard for the empty queue.
  - **T11** (`cf08e72`): recorded `app/View/Composers/` in `docs/ARCHITECTURE.md` (tree plus boundary table). All ten `app/` subdirectories are now named in the map.
- **Measured Contract (Spec §4.4):** cross-agency **5**, agency-bound **6**, unassigned **1** statements against a ceiling of six. The spec's Phase-2 column predicted two for the unassigned state; its short-circuit issues no badge query at all, and the test comment records the measured one.
- **Key Findings:**
  - **The six-statement ceiling reshaped production code.** With the composer in place the agency-bound landing measured **seven** statements, because the shared resolver spent a second statement on an agency name the badge never renders. `verificationCountFor()` now resolves the scope through `resolveScopeKind()` (no lookup), while `resolveScope()` keeps the A7 label carve-out for the summary. The S2 label cases still pass.
  - **A sidebar badge is a property of (link × count).** The badge sits inside `@can('manage-sakip')` (`layouts/modern.blade.php:81,88-93`), so an agency-bound viewer without that permission sees no badge even at a count of two. Spec AC-023/024/026 name a `Super Admin` viewer, which is exactly why. A throwaway HTTP-kernel smoke proved the behaviour instead of assuming it.
  - **The three deep links need their own permissions** (`view-performance-data`, `view-assessment-reports`): a viewer without them receives an explicit **403**, never a 500, while a Super Admin receives 200.
  - **Spec §5.0 F-6 is still documentation-misleading:** `assessments.performance_data_id` is UNIQUE, so its "three assessments on one row" scenario is not executable; the deviation lives only in a test header and a commit message.
  - **`ReportFactory` writes a `metadata` column absent from the `reports` table** (pre-existing, worked around with `Report::forceCreate`, unfixed).
  - **AC-004 residual:** the `(int)` cast in `AdminTriageSummary` is unfalsifiable in a non-strict-types repository — recorded for review instead of being silently closed.
- **Updated Files:**
  - `app/Support/ReportingPeriod.php`, `app/Support/TriageScope.php`, `app/Support/AdminTriageSummary.php` — new read-model contracts
  - `app/Services/AdminTriageService.php` — new service with split scope resolution
  - `app/View/Composers/SidebarQueueBadgeComposer.php` — new layout composer
  - `app/Providers/AppServiceProvider.php`, `config/sakip.php`, `app/Models/Scopes/ForYearTrait.php`, `app/Services/SakipDashboardService.php`, `app/Http/Controllers/Admin/AdminDashboardController.php`, `resources/views/admin/dashboard.blade.php`
  - `tests/Unit/Support/ReportingPeriodTest.php`, `tests/Unit/Scopes/ForYearTraitTest.php`, `tests/Unit/Services/DashboardDateRangeAdapterTest.php`, `tests/Unit/Services/AdminTriageServiceTest.php`, `tests/Feature/AdminTriageLandingTest.php`, `tests/Feature/SidebarQueueBadgeTest.php`
  - `docs/ARCHITECTURE.md` — the `app/Support/` and `app/View/Composers/` boundaries
  - `app/Models/Scopes/ForYearScope.php` — **deleted** (superseded by the trait)
- **Decisions Made:**
  - The composer owns the badge value; no controller passes it. The old controller variable was dropped in T4, so T10 needed no controller change.
  - Accepted spec divergences D-S7/D-S8/D-S9 are **asserted openly** (AC-037/AC-038) rather than hidden.
  - No ADR was written; the Triple Gate remains unmet.
- **Next Action / Pending:**
  - **New chat session → `/tdd-code-review`** with `@spec/spec-admin-triage-landing.md` and `@plan/plan-admin-triage-landing.md` (its mandatory upstream documents), plus `docs/checklist/checklist-admin-triage-landing.md` as the case inventory.
  - Carry these into the review: the Spec §5.0 F-6 wording, the `ReportFactory` defect, the new F-1 `manage-sakip` finding, and the AC-004 cast residual.
  - Coverage stays `unverified` until a driver is installed; never report it green.
  - Backlog unchanged: **F1** (2 skipped rate-limit tests), **F2** (63 PHPUnit 12 deprecations), `composer audit --locked`, `npm audit --omit=dev`.

## 📝 Session Checkpoint: 2026-09-25 — Session 9: Review of the Admin Triage Landing, Its Remediation Plan, and Phase 1 Execution

- **Summary:** The eleven-ticket delivery was reviewed on two axes (Standards vs Spec) and then remediated. The review reproduced every gate and produced **17 findings, none `[CRITICAL]`** — 9 Standards (all hygiene; the strongest is an unvalidated `SAKIP_ACTIVE_YEAR`) and 8 Spec issues (all traceability/documentation fidelity, no behaviour defect). Those findings became `plan/plan-refactor-admin-triage-landing-v1.0.md`, a four-phase TDD remediation plan. **Phase 1 is delivered and committed; Phase 2 is in progress.**
- **Work Done:**
  - **Review + plan** (`f89722f`): `docs/review/code-review-admin-triage-landing-2026-09-25.md` (five-axis report, inventory reconciliation, security tables) plus the refactoring plan. The review document doubles as the **Phase-1 review artifact** that Spec §9.4 obligations 2 and 4 require and that `docs/review/` did not previously exist to hold.
  - **TASK-101/102** (`86235e0`): the AC-028 pin — the **only** acceptance criterion in the entire delivery with no test at any seam, because the Plan never assigned it or its checklist case TC-045 to a ticket.
  - **TASK-103/104/105** (`775a201`): the `data-triage-period-select` handle (declared in Spec §4.4, rendered nowhere, asserted nowhere) added to the blade and asserted by TC-051, plus the Spec amendment to **v1.4**.
  - **Plan record** (`6fa2bda`): Phase-1 rows ticked with dates; TASK-10Y left open because the plan requires explicit human approval before Phase 2.
  - **Phase 2** (`5cce6fa`, `17947ff`, `534f3ac`): every figure in `docs/ARCHITECTURE.md` re-measured rather than inherited (**135** / **9** / **14**, zero stale numbers left, seams S1–S4 now named in §10 with their boundaries and the F-1 caveat), and `tests/Unit/FloorGuardTest.php` added implementing checklist **TC-070** — a static scan of four roots for suppression tokens and out-of-allow-list skips, with reachability assertions so a broken iterator cannot pass vacuously.
  - **Phase 3** (`ee8ba2a`, `b2876cb`, `79b67c1`, `5c94ed7`, `c7df239`, `910cdc3`, `775cc8c`): the active-year configuration is now untrusted input (`anchor()` validates with `FILTER_VALIDATE_INT` inside a 1970–9999 window and falls back to the clock), `isYearScoped()` removed from the class **and** Spec §4.1, the triage seam tests collapsed onto one shared markup boundary (`tests/Support/ExtractsTriageMarkup.php`), the two same-valued constants renamed for the parameters they feed, and the plan's approval gates 10Y/20Y ticked once the confirmations arrived.
  - **Phase 4** (`b49a117`, `195fd66`, `672f42f`, `ec1c15c`, `d1046d1`): the checklist inventory reconciled (**70 of 70 cases ticked plus TC-071 and TC-072**, counts re-measured to **68 seam methods**, four stale facts corrected), **decision 1A** delivered the PRD's long-agency-name criterion (`triage-scope-chip` truncation + `title`, TC-072 asserting the markup contract at S3), **decision 2B** re-scoped the three manual metrics to `[Assumed / Backlog]` with the PRD §6 as decision of record, the Spec moved to **v1.5**, the review artifact gained an **§8.1 closure log for all seventeen findings**, and the plan's Definition of Done was ticked 9 of 9.
- **Verified Evidence:** full suite 144 → **149 passed / 2 skipped, 693 assertions**; `AdminTriageLandingTest` 30 cases (all 29 checklist S3 cases plus TC-072); Pint **PASS 253 files**; Unit **57 passed in 1.56–2.02 s** against the 10 s floor; `FloorGuardTest` failed on an injected `@phpstan-ignore` and passed once the scratch file was deleted; `ReportingPeriodTest` failed first with `0 is identical to 2026` for `SAKIP_ACTIVE_YEAR=abc`; TC-072 failed first on the missing truncation class; `git diff app/ database/ routes/` empty after every mutation probe; working tree clean; every checklist box ticked. Coverage stays **unverified** and was explicitly excluded from the manual-metrics re-scope.

- **Key Findings:**
  - **The worst Spec finding was an untested criterion, not a bug:** AC-028 — the US-006 honesty invariant that a period switch must move only the verification figure — had no test at any seam, and was the single acceptance criterion unreferenced by any test file.
  - **The most surprising finding was historical:** Spec §4.5's own "no intermediate state loses the badge" clause was violated by T4 (`df57eb8`) and repaired only at T10 (`7e8642e`), so for five commits no `layouts.modern` page rendered a badge at all — invisible to the suite because the S4 seam arrived in Phase 2. Now recorded in Spec §4.5 and as `SPEC-B-01` in the review.
  - **The Spec predicted a value that would have been a false assertion:** §4.4's Phase-2 column said the unassigned viewer would cost 2 statements; it costs **1**, because the badge short-circuits before querying. The measured `5 / 6 / 1` is now in the Spec and §9.4 obligation 3 is marked discharged.
  - **`docs/ARCHITECTURE.md` was stale exactly where this feature moved:** `131` PHP files vs **135** actual, `5` unit tests vs **9**, `12`/`13` feature tests vs **14** (three different figures for one quantity), and §10 never listed the four new seams. Phase 2 (TASK-202) fixes it.
  - **The review's own non-findings matter too:** zero `[CRITICAL]` issues, no tautological tests, no over-mocking (no `Mockery` in any seam file), tenancy intact, and the accepted divergences asserted as positive expectations rather than contained loosely.
  - **Two pruned-but-real defects surfaced only under mutation and probing:** a period-scoped assessment count was correctly caught by the new pin, and a regex-or-similar fixture weakness (row-order dependence) was exposed by the same mutation. Neither was visible from reading the code.
- **Updated Files:**
  - `docs/review/code-review-admin-triage-landing-2026-09-25.md` — new five-axis review artifact
  - `plan/plan-refactor-admin-triage-landing-v1.0.md` — new remediation plan (Phase 1 ticked, TASK-10Y open)
  - `tests/Feature/AdminTriageLandingTest.php` — the AC-028 pin plus the extended TC-051 (now 29 methods)
  - `resources/views/admin/dashboard.blade.php` — one additive attribute (`data-triage-period-select`)
  - `spec/spec-admin-triage-landing.md` — **v1.4**: §4.4 budget row plus delivered measurement, §4.5 badge ownership, §9.3 closure row, §9.4 obligations 3 and 4, front-matter note
  - `.agents/instructions/memory.instructions.md` — this checkpoint
- **Decisions Made:**
  - The badge owner is the **composer only**, from the first release; the Spec's Phase-1 controller variable is formally abandoned rather than retro-fitted, and the five-commit consequence is stated in the Spec instead of smoothed over.
  - The Spec is amended in the **same commit** as the change it describes; a test comment or a memory note is not a substitute.
  - `isYearScoped()` and the `SAKIP_ACTIVE_YEAR` hardening wait for Phase 3 — nothing is changed silently now, and nothing is deleted from the class without also leaving Spec §4.1.
- **Next Action / Pending:**
  - **Phase 4 is closed; `TASK-40Y` is the only row still open** — it is the gate for *leaving* Phase 4, so it waits for the handoff to be accepted rather than being ticked by the agent.
  - **Next routes, in a NEW chat session** (this one is locked to the code persona): **`/tdd-generate-docs`** — attach `spec/spec-admin-triage-landing.md` (v1.5), `plan/plan-refactor-admin-triage-landing-v1.0.md` and the passing test files, which the skill requires as its mandatory upstream set; then **`/tdd-retro`** for suite-speed and memory work.
  - **Still open, deliberately:** coverage is `unverified` (no Xdebug/PCOV) and was **not** part of decision 2B; **F1** (2 skipped rate-limit tests) and **F2** (63 PHPUnit 12 deprecations) remain backlog.

<!-- checkpoint-tail: the Admin Triage Landing review remediation is complete — all four phases delivered, 17 of 17 findings closed or explicitly accepted, the Spec at v1.5, the architecture map re-measured with seams S1-S4 named, a static floor-guard test, an untrusted configuration seam, one shared markup boundary, the long-agency-name truncation delivered under decision 1A, the manual metrics re-scoped under decision 2B, and the checklist fully ticked — with 149 passing tests, Pint clean on 253 files, and only TASK-40Y (the exit gate) plus the unverified coverage gate still open. The next session runs /tdd-generate-docs, then /tdd-retro. -->

---

## 📝 Session Checkpoint: 2026-09-26 — Session 10: Diátaxis Documentation Set + Retrospective & Test-Suite Telemetry

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** **Documentation (Phase 6) complete; Retrospective (Phase 7) complete.** The Admin Triage Landing chain is closed end-to-end: delivery → five-axis review → four-phase remediation → Diátaxis set → retro. `TASK-40Y` (the exit gate of the remediation plan) is satisfied by this handoff.
- **Active Artifacts:**
  - `spec/spec-admin-triage-landing.md` (v1.5) — Status: ✅ Finalized; matches the delivered code
  - `plan/plan-refactor-admin-triage-landing-v1.0.md` (v1.0) — Status: ✅ All four phases executed
  - `docs/review/code-review-admin-triage-landing-2026-09-25.md` (v1.0) — Status: ✅ 17/17 findings closed
  - `docs/checklist/checklist-admin-triage-landing.md` — Status: ✅ 70/70 ticked + TC-071/TC-072
  - **`docs/tutorials/`, `docs/how-to/` (×2), `docs/reference/`, `docs/explanation/`** — Status: ✅ **New this session**, five documents / 1,198 lines covering all four Diátaxis quadrants
  - **`docs/retro/retro-admin-triage-landing-2026-09-26.md`** — Status: ✅ **New this session**, retro artifact with fresh telemetry
- **Achieved Milestones:**
  - **Diátaxis set delivered** from Spec v1.5 + the four seam test files: a tutorial (build the canonical fixture and observe the three viewer states), two how-tos (configure the active reporting year; add a queue figure), an exhaustive reference (handles, viewer matrix, deep-link rules, budget `5 / 6 / 1`, frozen copy, class APIs, seams S1–S4, accepted divergences) and an explanation (why the figures, links, badge and config seam are shaped as they are).
  - **The docs verified themselves before handoff:** snippet tracing (every reported line found verbatim in the source), link resolution (10 paths, all present), `_Avoid_` vocabulary audit, and whitespace/structure hygiene. Four inaccuracies were caught and fixed (an `action`-attribute phrasing, the `Tervalidasi` string, a false "one reader, three consumers" claim, an unverifiable `FILTER_VALIDATE_INT` whitespace claim).
  - **Fresh telemetry (this retro):** 151 tests (57 unit / 94 feature), **149 passed / 2 skipped / 693 assertions**; full suite **13.42 s and 13.19 s** across two runs (stable); Unit **1.27 s**; triage seams S1–S4 **3.73 s**; Pint **PASS 253 files**; **63** PHPUnit deprecations; **coverage measured for the first time: 8.8 % total over `app/`, 91.8–100 % for the triage slice** — PCOV was installed all along, so the longstanding "unverified" label was an inherited claim, not a fact.
  - **Hot spot identified:** `RateLimitingTest` = **57 %** of suite runtime (7.41–7.69 s) and the owner of both skipped tests; the top-10 slowest are 60.67 % of the run, six of them throttle tests at ~1.0–1.3 s each.
- **Dead-Ends (Do NOT Repeat):**
  - See the two Knowledge Base dead-ends (rate limiter driven through full HTTP requests; whole-`app/` coverage floor expectation) and KB-P1…KB-P4 for the patterns that replace them.
  - **Persona note:** this session was overridden mid-chat (Technical Writer → Retro Optimizer) under the user-override protocol. The house convention is one persona per session; when a switch is unavoidable, write the memory checkpoint **before** switching so continuity never depends on chat history.
- **Updated Files:**
  - `docs/tutorials/tutorial-observe-the-triage-figures.md`, `docs/how-to/how-to-configure-the-active-reporting-year.md`, `docs/how-to/how-to-add-a-queue-figure-to-the-triage-landing.md`, `docs/reference/ref-admin-triage-landing.md`, `docs/explanation/explanation-triage-landing-decisions.md` — new Diátaxis set
  - `docs/retro/retro-admin-triage-landing-2026-09-26.md` — new retro artifact (measurement provenance, slowest-test table, coverage finding, eight friction items, four patterns, four dead-ends, nine action items)
  - `.agents/instructions/memory.instructions.md` — this checkpoint plus the Knowledge Base promotions (KB-P1…KB-P4, two dead-ends, the measured baseline)
- **Decisions Made:**
  - The documentation set cites the **source** of every metric; the retro re-measures them. Both artifacts are read together (KB-P4).
  - **No threshold was changed.** Re-scoping the coverage gate (changed-slice/diff coverage) and declaring a full-suite runtime budget are proposed as product-owner decisions in retro §4 (A5, A6) — never silent edits (`CONSTRAINTS.md` §3 rule 5).
  - Backlog **F1** (2 skips) and **F2** (63 deprecations) stay open with named routes.
- **Next Action / Pending:**
  - **A1 (cheapest):** put `/opt/homebrew/opt/php@8.3/bin` on `PATH` (or add a `Makefile` target) — `php` is not on `PATH` and every gate needs the absolute prefix.
  - **A4:** close **F1** via `/tdd-bug-report` (Prove-It at the limiter seam) — pays both correctness and ~57 % of the suite runtime.
  - **A5/A6:** product-owner decisions on the coverage scope and a full-suite runtime budget.
  - **A9:** next feature cycle in a **new chat session** — `/tdd-explore-ideas` or `/tdd-prd` per the roadmap; keep the F1/F2 chores on their own bug route.
  - Coverage must be reported as **8.8 % total / 91.8–100 % slice**, with branch coverage **unverified (Xdebug absent)** — the number is now known, so "unverified" is no longer an honest label for it.

<!-- checkpoint-tail: the Admin Triage Landing chain is closed end-to-end (delivery → review → remediation → Diátaxis docs → retro); the docs set is new and verified, the suite is 149 passed / 2 skipped / 693 assertions at 13.2 s, and the retro replaced the stale "coverage unverified" claim with measurements (8.8 % total, 91.8–100 % slice) while naming the suite hot spot (RateLimitingTest, 57 % of runtime, both skips) — next actions are A1 (PATH), A4 (close F1), A5/A6 (coverage-scope and runtime-budget decisions), then a new session for /tdd-explore-ideas or /tdd-prd. -->

---

## 📝 Session Checkpoint: 2026-09-26 — Session 10b: Retro Action Items A1–A4 Executed (F1 Closed)

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Documentation + Retrospective complete; the retro's own action items **A1–A4 are executed**. Open: A5, A6 (product-owner decisions), A7 (F2 chore), A8 (architecture-map hygiene), A10 (new).
- **Active Artifacts:** unchanged from Session 10, plus `docs/retro/retro-admin-triage-landing-2026-09-26.md` §4 (A1–A4 ticked with an A4 execution note) and the new A10 row.
- **Achieved Milestones:**
  - **A1 (toolchain):** root cause was *not* a missing export — `~/.zshrc:83` already exports `php@8.3`, but non-interactive shells (the agent tool shell is `/bin/bash` with `PATH=/usr/bin:/bin:/usr/sbin:/sbin`) never source it. Fixed with a Makefile `PHP` resolution (`make php-version`, `make test-local`, `make lint-local`, `make gates-local`), a `~/.local/bin/php` symlink, and a recorded invocation in `CONSTRAINTS.md` §2.
  - **A2 (docs):** the reference's evidence line now carries measured figures with source and date, and the three stale "coverage unverified / no driver" claims were corrected to the measured values (8.8 % total / 91.8–100 % slice; branch Xdebug-only).
  - **A3 (gates):** `--profile` adopted in `CONSTRAINTS.md` §2 and in `make test-local`.
  - **A4 (backlog F1):** **closed.** RED proved three failures first (two intended assertion failures plus the floor-guard detecting the disappeared skips), then GREEN. `RateLimitingTest` is **10 passed** (was 8 passed / 2 skipped), `FloorGuardTest`'s allow-list tightened from 2 to **0**, and the suite is **151 passed / 0 skipped / 731 assertions / 12.37 s** with Pint PASS 253 files and Unit 1.34 s.
- **Dead-Ends (Do NOT Repeat):** see the Knowledge Base entries above — the two rate-limit cases were **wrong**, not flaky (hashed/namespaced limiter key; a `guest` limiter attached to no route). Never "fix" a flaky test by skipping it: read the key/semantics first.
- **Updated Files:** `Makefile` (local toolchain targets), `CONSTRAINTS.md` (§2: `--profile` + PHP resolution), `tests/Feature/RateLimitingTest.php` (two cases rewritten), `tests/Unit/FloorGuardTest.php` (allow-list → 0), `docs/retro/retro-admin-triage-landing-2026-09-26.md` (§4 ticks + execution note + A10), `docs/reference/ref-admin-triage-landing.md`, `docs/explanation/explanation-triage-landing-decisions.md`, `docs/checklist/checklist-admin-triage-landing.md` (stale metric/guard wording), this file.
- **Decisions Made:** no production code, route, policy, or threshold was changed; the only guard change *tightens* it. The unwired `guest` limiter is **not** silently wired — it is filed as A10 for a product/security decision.
- **Next Action / Pending:**
  - **A5 and A6 are decided (2026-09-26)** and recorded in `CONSTRAINTS.md` §1: coverage is measured in two scopes (changed files at >= 80 % target / >= 75 % floor; whole `app/` as a ratchet against the measured 8.8 % baseline) and the full suite now has an SLA of < 15.0 s target / < 20.0 s hard, enforced through `php artisan test --profile`.
  - **A10** (`throttle:guest` wiring vs deleting the dead limiter) awaits one word from the product owner; the recorded recommendation is to wire it onto the guest-facing routes with its own RED test (trade-off: 30 req/min per IP also caps guests behind a shared NAT). **A7** (F2: 63 PHPUnit deprecations) is the remaining chore; **A8** (architecture-map hygiene) is optional.
  - The work is committed in two atomic commits: the Diátaxis documentation set, then A1–A6 plus the retro and memory.
  - Next feature cycle: a **new chat session** with `/tdd-explore-ideas` or `/tdd-prd`.

<!-- checkpoint-tail: retro actions A1-A4 are executed — the runner resolves via Makefile targets (the export in ~/.zshrc never reached non-interactive shells), --profile is now a gate, the docs' stale metrics are corrected, and backlog F1 is closed with zero skips repo-wide (151 passed / 731 assertions / 12.37 s) after proving both "flaky" cases were wrong (hashed limiter key; a guest limiter attached to no route, filed as A10) — open: A5/A6 decisions, A7 (F2), A8, A10, and the uncommitted changes. -->

---

## 📝 Session Checkpoint: 2026-09-26 — Session 10c: Retro Actions A5–A7 Executed (F2 Closed)

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Documentation + Retrospective complete; retro actions **A1–A7 executed/decided**. Open: **A8** (architecture-map hygiene, optional), **A9** (next feature cycle, new session), **A10** (one-word product-owner go).
- **Active Artifacts:** `CONSTRAINTS.md` §1 decision records (A5, A6), `docs/retro/retro-admin-triage-landing-2026-09-26.md` §4 (A1–A7 ticked), `tests/Unit/FloorGuardTest.php` (three static scans).
- **Achieved Milestones:**
  - **A5 decided:** line coverage measured in two scopes — changed files keep the >= 80 % target / >= 75 % floor, whole `app/` is a ratchet over the measured 8.8 % (only raisable). Branch coverage stays Xdebug-only and unverified.
  - **A6 decided:** full-suite SLA declared — < 15.0 s target / < 20.0 s hard against the measured 12.2–12.6 s baseline, enforced through `php artisan test --profile`.
  - **A7 done, backlog F2 closed:** all **63** PHPUnit deprecations were one kind — doc-comment test metadata (`/** @test */`), deprecated in PHPUnit 11 and removed in PHPUnit 12. Migrated 1:1 to the `#[Test]` attribute across 8 files (26+12+10+5+5+2+2+1 = 63, method names preserved) and pinned by a new `FloorGuardTest` case that forbids doc-comment test metadata repository-wide. Gates: **OK (152 tests, 732 assertions)**, deprecations **0**, Pint PASS 253 files.
- **Dead-Ends (Do NOT Repeat):** see the Knowledge Base — a static guard that forbids a token must not contain that token in the shape it forbids (the guard's own method docblock was the one deprecation that survived the migration); and PHPUnit deprecations are detailed only by `--display-phpunit-deprecations`, never by `--display-deprecations`.
- **Updated Files:** 8 test files (`#[Test]` migration + one `use` import each), `tests/Unit/FloorGuardTest.php` (new metadata scan), `CONSTRAINTS.md` §1, `docs/retro/retro-admin-triage-landing-2026-09-26.md` (§0 flag note, §1, §4), this file.
- **Decisions Made:** A5/A6 are recorded as decision records with the measured rationale rather than silent threshold edits; doc-comment test metadata is now banned repository-wide; A10 is deliberately **not** executed without the owner's word.
- **Next Action / Pending:** one word on **A10** ("pasang" to wire `throttle:guest` with its own RED test, or "hapus" to delete the dead limiter); **A8** optional; then a **new chat session** for `/tdd-explore-ideas` or `/tdd-prd`.

<!-- checkpoint-tail: retro actions A5-A7 are executed - coverage is decided in two scopes (changed files 80/75, whole app/ ratcheted over 8.8%), the full suite has a 15s/20s SLA, and backlog F2 is closed by migrating 63 doc-comment test metadata to #[Test] with a new FloorGuardTest scan - suite is OK (152 tests, 732 assertions) with zero deprecations; open: A10 one-word go, A8 optional, then a new session for the next feature cycle. -->

---

## 📝 Session Checkpoint: 2026-09-26 — Session 10d: Architecture Map Refreshed (A8)

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Documentation + Retrospective complete; retro actions **A1–A8 executed**. Open: **A9** (next feature cycle, new session) and **A10** (one-word product-owner go on the unwired `guest` limiter).
- **Active Artifacts:** `docs/ARCHITECTURE.md` (239 lines, re-measured 2026-09-26) — the Living Architecture Map is current again.
- **Achieved Milestones (A8, commit `380d404`):**
  - Every figure re-measured from the repository instead of carried over: `app/` **135** PHP files, **17** Eloquent models (the old "18" predated the `ForYearScope` deletion), 35 services, 31 controllers, 54 migrations, 11 factories, `tests/Unit` **10** files (FloorGuardTest joined the four root guards), `tests/Feature` 14.
  - The `docs/` line now names the real artifact tree — `discovery/`, `prd/`, `audit/`, `checklist/`, `review/`, `retro/` plus the Diátaxis set (`tutorials/`, `how-to/`, `reference/`, `explanation/`) — so an onboarding agent can find the executable sources of truth.
  - `FloorGuardTest` is recorded in the seam catalogue as the floor-guard scan (suppression tokens, skipped tests, doc-comment test metadata — all with empty allow-lists now that F1 and F2 are closed).
  - Testing strategy records the toolchain reality: `--profile` in the gate set, `make gates-local` and friends for host runs (Makefile resolves PHP), the measured coverage picture (8.8 % over `app/`, 91.8–100 % triage slice, two-scope policy, branch Xdebug-only), and the suite size (152 tests / 732 assertions / 0 skips / 0 deprecations).
- **Dead-Ends (Do NOT Repeat):** see the Knowledge Base — the map must be re-measured, never copied forward: two sessions of carried-over counts had produced three different numbers for one quantity.
- **Updated Files:** `docs/ARCHITECTURE.md` (+13/−9, surgical edits only), this file.
- **Decisions Made:** the existing 12-section project map is preserved (surgical edits per the Surgical Edit Mandate) rather than rewritten to the skill's generic template; no application code was touched — the mapper's boundary held.
- **Next Action / Pending:** one word on **A10** ("pasang" to wire `throttle:guest` with its own RED test, or "hapus" to delete the dead limiter); then a **new chat session** with `/tdd-explore-ideas` or `/tdd-prd` for the next milestone.

<!-- checkpoint-tail: A8 done - docs/ARCHITECTURE.md re-measured and refreshed (135 app files, 17 models, 10 unit tests, the full docs/ artifact tree including the Diátaxis set, FloorGuardTest in the seam catalogue, and the toolchain/coverage/suite reality); retro actions A1-A8 are all executed; open: A10 one-word go and a new session for /tdd-explore-ideas or /tdd-prd. -->

---

## 📝 Session Checkpoint: 2026-09-26 — Session 10e: A10 Executed — `throttle:guest` Wired

- **Active Memory Path:** `.agents/instructions/memory.instructions.md`
- **Current SDLC Phase:** Documentation + Retrospective complete; **all ten retro actions (A1–A10) are executed and decided**. Only **A9** remains: the next feature cycle, in a new chat session.
- **Active Artifacts:** `docs/retro/retro-admin-triage-landing-2026-09-26.md` §4 — A1 through A10 all ticked with execution notes.
- **Achieved Milestones (A10, product-owner go "pasang"):**
  - `throttle:guest` now guards the guest-facing routes **`/`, `GET /login`, `POST /login`, `POST /logout`**, and the `guest` limiter answers **`Limit::none()`** for a resolved user so authenticated traffic never consumes the guest budget (the `/` route is shared by everyone, so without that branch a logged-in user would have eaten the guest budget).
  - Red-Green held: the characterisation pin was rewritten into `guest_requests_to_the_root_route_are_rate_limited_per_ip` and failed at the 429 assertion before the wiring landed; it now asserts 30 guest requests reach the login redirect, the 31st is 429, and 35 authenticated requests on the same route and IP are never 429.
  - Scope stayed at the approved recommendation — the four guest-facing routes, not the global web middleware stack — with the accepted trade-off recorded: 30 requests/minute per IP also caps legitimate guests behind a shared NAT.
  - Gates: `RateLimitingTest` **10 passed (194 assertions)**, suite **`OK (152 tests, 767 assertions)`** at ~12.2 s (inside the A6 SLA), **0** skips, **0** PHPUnit deprecations, Pint **PASS 253 files**.
- **Dead-Ends (Do NOT Repeat):** a rate limiter whose closure keys only by IP must decide what "guest" means — attaching it to a shared route without a `Limit::none()` branch for resolved users silently throttles logged-in traffic. See the Knowledge Base entry for A10.
- **Updated Files:** `app/Providers/RateLimitServiceProvider.php` (the `guest` limiter gains the resolved-user branch), `routes/web.php` (four routes gain `throttle:guest`), `tests/Feature/RateLimitingTest.php` (the pin flipped), `docs/retro/retro-admin-triage-landing-2026-09-26.md` (A10 ticked + A4 note superseded), `docs/ARCHITECTURE.md` (§8 rate-limiting entry), this file.
- **Decisions Made:** the `guest` limiter keeps its declared name and budget (30/min per IP) and is enforced exactly where the policy says it applies; no new permission, no global middleware change, and the login throttle (5/min per email+IP) is untouched — `POST /login` now carries both by design.
- **Next Action / Pending:** **A9** — open a **new chat session** and run `/tdd-explore-ideas` or `/tdd-prd` for the next milestone; the working tree is clean and every artifact is committed.

<!-- checkpoint-tail: A10 done - throttle:guest now guards /, GET /login, POST /login and /logout with Limit::none() for resolved users, pinned by guest_requests_to_the_root_route_are_rate_limited_per_ip; all ten retro actions A1-A10 are executed; suite is OK (152 tests, 767 assertions) with zero skips and zero deprecations; only A9 remains: a new session for /tdd-explore-ideas or /tdd-prd. -->

---
