---
description: "Project memory file for tracking progress, active artifacts, and cross-session decisions."
---

# Project Memory: Active Context & Knowledge Base

> This file is managed by the `memory-manager` skill and `/tdd-retro`.

## 1. Project Context
- **Project Name:** SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah)
- **Current Phase:** Phase 4 (Code) — the Admin Triage Landing Phases 1 + 2 are complete (11/11 plan tickets, Red-Green-Refactor throughout). Every gate is green except the PHP **coverage** gate, which is `unverified` because no Xdebug/PCOV driver is installed. → **Phase 5 (`/tdd-code-review`) is the next route**, and it must run in a **new chat session** because the implementation session is locked to the code persona.

## 2. Active Artifacts & Documents
- `AGENTS.md`, `CONSTITUTION.md`, `CONSTRAINTS.md`, `CONTEXT.md` — governance contracts (versioned via `.gitignore` negations)
- `docs/ARCHITECTURE.md`, `docs/discovery/`, `docs/prd/`, `docs/audit/` — architecture map, discovery drafts, requirements, clarification reports
- `spec/spec-admin-triage-landing.md` (v1.3), `plan/plan-admin-triage-landing.md` (v1.0), `docs/checklist/checklist-admin-triage-landing.md` (70 test cases) — the approved blueprint, its task matrix, and the test-case inventory for the admin triage landing
- `docs/adr/` — still not created; the Triple Gate has been unmet for every decision taken so far
- Per-session artifact status lives in the **Active Artifacts** list of the latest checkpoint below; this section is only the top-level index.

## 3. Session Progress Log
- Initialized TDD-Spec SDLC Architecture.
- Session 8: implemented the Admin Triage Landing Phases 1 + 2 (11/11 tickets) under strict Red-Green-Refactor; 144 passed / 2 skipped, Pint clean, coverage `unverified`.

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

<!-- checkpoint-tail: the admin triage landing is implemented across Phases 1 + 2 (11/11 tickets, commits 0c6fc64 → cf08e72) with 144 passing tests and a clean Pint run; the next session runs `/tdd-code-review` on the Spec and Plan, carrying four open findings (Spec §5.0 F-6, ReportFactory, manage-sakip F-1, AC-004 cast) and an unverified coverage gate. -->

---
