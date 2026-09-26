---
title: "TDD Retrospective: Admin Triage Landing (Review Remediation + Diátaxis Documentation)"
date_created: 2026-09-26
status: Complete
evaluated_artifacts:
  - plan/plan-admin-triage-landing.md (v1.0)
  - plan/plan-refactor-admin-triage-landing-v1.0.md (v1.0, all four phases executed)
  - spec/spec-admin-triage-landing.md (v1.5)
  - docs/review/code-review-admin-triage-landing-2026-09-25.md (v1.0, 17/17 findings closed)
  - docs/reference/ref-admin-triage-landing.md and the four other Diátaxis documents (new this session)
  - tests/ (24 test files, 151 tests)
measurement_environment: "PHP 8.3.33 at /opt/homebrew/opt/php@8.3/bin/php · PHPUnit 11.5.56 · SQLite :memory: · PCOV present (line coverage only) · Xdebug absent"
generated_by: /tdd-retro
---

<!-- markdownlint-disable -->

# TDD Retrospective: Admin Triage Landing (Review Remediation + Diátaxis Documentation)

**Date:** 2026-09-26
**Author:** TDD Retrospective Optimizer
**Evaluated artifacts:** the eleven-ticket delivery and its four-phase review remediation, the five-document Diátaxis set produced this session, and the current test suite of `tests/`.

## 0. Measurement Provenance

Every number below was produced in this session, on this machine, in this order. No figure is inherited from a plan note without being labelled as such.

| # | Command | Result |
| --- | --- | --- |
| 1 | `php artisan config:clear` | cache cleared before any measurement |
| 2 | `php artisan test --testsuite=Unit` | 57 passed, 285 assertions, **1.27 s** |
| 3 | `php artisan test --profile` (run 1) | 2 skipped, 149 passed, 693 assertions, **13.42 s** |
| 4 | `php artisan test` (run 2, stability) | 2 skipped, 149 passed, 693 assertions, **13.19 s** |
| 5 | `php artisan test --testsuite=Feature` | 2 skipped, 92 passed, 408 assertions, **11.22 s** |
| 6 | `php artisan test --filter='AdminTriageLandingTest\|SidebarQueueBadgeTest\|AdminTriageServiceTest\|ReportingPeriodTest'` | 60 passed, 376 assertions, **3.73 s** |
| 7 | `php artisan test --filter=RateLimitingTest` (×2) | 2 skipped, 8 passed, 121 assertions, **7.69 s / 7.41 s** |
| 8 | `php vendor/bin/phpunit --display-deprecations` | **PHPUnit Deprecations: 63**, Skipped: 2, Assertions: 693 |
| 9 | `php vendor/bin/pint --test --no-interaction` | **PASS — 253 files**, 0.66 s |
| 10 | `php artisan test --coverage` (PCOV) | line coverage **Total: 8.8 %** over `app/`, suite duration 15.54 s |
| 11 | `php vendor/bin/phpunit --testsuite=Unit --coverage-text` | Classes 2.27 %, Methods 3.09 %, Lines 2.27 % of 15,159 instrumented lines; **no branch metrics emitted** |

> [!IMPORTANT]
> The runner is **not on `PATH`**. `command -v php` returns nothing; every gate must be invoked as `/opt/homebrew/opt/php@8.3/bin/php …`. This has now recurred across sessions (blocker B1) and is the single cheapest friction item on the list.

## 1. ⏱️ Test Suite Health & Performance Telemetry

- **Total tests executed:** **151** (57 unit, 94 feature) — **149 passed, 2 skipped, 0 failed, 693 assertions**.
- **Total execution duration:** **13.42 s** (run 1) and **13.19 s** (run 2); wall clock ≈ 15.3 s.
- **Runtime SLA:** `CONSTRAINTS.md` §1 declares an SLA for the **Unit** suite only (< 10.0 s target / < 20.0 s hard). Measured **1.27 s** ✅ — an 8× margin. The full suite has **no declared SLA**; 13.2–13.4 s is now the measured baseline to attach one to.
- **Suite split:** Unit 1.27 s · Feature 11.22 s · triage seams S1–S4 3.73 s (60 tests, 376 assertions).
- **Deprecations:** **63** PHPUnit deprecations (backlog **F2**) — unchanged from the pre-feature baseline; the suite is green but reports "OK, but there were issues".

### Slowest tests (top 10 = 8.14 s = 60.67 % of the run)

| Rank | Test | Time | Diagnosis & remedy |
| --- | --- | --- | --- |
| 1 | `RateLimitingTest > different users have separate rate limits` | 1.31 s | six full-framework `POST /login` requests; exercise the limiter middleware directly or pre-seed via `RateLimiter::hit()` |
| 2 | `RateLimitingTest > rate limiter works with different ips` | 1.27 s | same shape; identical remedy |
| 3 | `RateLimitingTest > login rate limit includes retry after` | 1.10 s | same shape; identical remedy |
| 4 | `RateLimitingTest > rate limit response contains helpful message` | 1.07 s | six requests to reach the boundary plus one JSON probe; seed the limiter instead of driving it |
| 5 | `AuthenticationTest > login attempts are throttled` | 1.07 s | overlaps the same throttle family from the auth side; consider one parameterised throttle suite |
| 6 | `RateLimitingTest > login is rate limited after five attempts` | 1.06 s | same shape; identical remedy |
| 7 | `ArchitectureGuardTest > no duplicate short class names in app/` | 0.50 s | filesystem walk of `app/`; acceptable, and the only non-HTTP test in the top ten |
| 8 | `AdminTriageLandingTest > unassigned viewer renders its handle with zero figures` | 0.31 s | a full HTTP render; the triage seams are cheap by comparison — keep them that way |
| 9 | `AuthenticationTest > users cannot authenticate with non-existent email` | 0.23 s | framework login pipeline; acceptable |
| 10 | `AuthenticationTest > users cannot authenticate with invalid password` | 0.23 s | framework login pipeline; acceptable |

### Cost concentration — the finding that matters most

`tests/Feature/RateLimitingTest.php` alone costs **7.41–7.69 s**: roughly **57 %** of the 13.2 s suite, from **8 passing tests**, and it is simultaneously the file that owns **both** skipped tests. The suite's dominant cost and its outstanding debt live in the same file, so closing backlog **F1** is both a correctness action and the single largest available performance action.

### Flaky tests

| Test | Status | Root cause (read from the code, not inferred) |
| --- | --- | --- |
| `RateLimitingTest::rate_limit_resets_after_time_window` | **skipped** (line 116) | comment: *"relies on real-time rate-limiter state across requests"*; the body calls `RateLimiter::clear()` and still cannot make the window deterministic |
| `RateLimitingTest::guest_users_have_separate_rate_limit` | **skipped** (line 189) | comment: *"31 requests hitting / redirect to login (302) instead of 429"* — a behaviour mismatch rather than a timing race: `/` is not the throttled surface the test assumes |

No other flakiness was observed. Two consecutive full-suite runs produced identical counts (149 / 2 skipped / 693 assertions) with a 0.23 s spread, and the two `RateLimitingTest` runs differed by 0.28 s.

### Coverage — the "unverified" gate is now measurable, and it fails at project scale

`PCOV` **is installed** in the local PHP 8.3 build, so the line-coverage gate that every artifact since Session 8 has reported as *unverified* can be measured today:

| Scope | Line coverage | Reading |
| --- | --- | --- |
| Whole `app/` (the scope `phpunit.xml` instruments) | **8.8 %** | the `CONSTRAINTS.md` §1 floor (≥ 75 % line) is **unmet** — 99 of the instrumented files are at 0.0 % |
| The triage slice | **91.8 – 100 %** | `AdminDashboardController` 100 %, `AdminTriageSummary` 100 %, `TriageScope` 100 %, `SidebarQueueBadgeComposer` 100 %, `ReportingPeriod` 97.1 % (line 112), `AdminTriageService` 91.8 % (lines 124–128, the soft-deleted-agency fallback) |
| Legacy neighbours touched by the feature | 25.0 % and 1.9 % | `ForYearTrait` 25.0 %; `SakipDashboardService` **1.9 %** — a 600-line service the agency dashboard depends on |
| Branch coverage | **not measurable** | PCOV emits line coverage only; Xdebug is absent, and the unit-suite report contains no branch metrics at all |

Two conclusions follow, and they point in opposite directions:

1. **The slice-level discipline is real:** every new triage contract is at or near full line coverage, so "write the seam test first" is working as designed.
2. **The project-level threshold is currently unreachable:** a 75 % floor over all of `app/` would require covering ~11,000 untouched legacy lines. The honest options are to re-scope the threshold to the changed slice (diff coverage / ratchet) with product-owner approval, or to start a deliberate legacy-coverage campaign — and not to keep reporting "unverified" as if the number were unknown.

## 2. 🔁 SDLC Process & Friction Analysis

### What Went Well (Praise & Retain)

- **Pre-agreed seams keep paying compound interest.** Spec §6.1 fixed S1–S4 before any code existed; this session they cost **3.73 s for 60 tests / 376 assertions**, gave the documentation phase verbatim snippets to quote, and let the retro measure each seam independently instead of guessing. The seam matrix is the single highest-leverage artifact the SDLC produces.
- **The remediation closed its own loop with commit-level evidence.** 24 commits since the review fixpoint (`c494ad4..HEAD`) turned 17 findings into: one new S3 pin, one render handle, three Spec amendments (v1.3 → v1.5), one hardened config seam, one deleted dead API, one shared test boundary, one static floor-guard test, and two product-owner decisions recorded in the PRD. Nothing was closed by assertion alone.
- **Pins over already-correct behaviour were RED-proofed by mutation, then reverted.** `TASK-101` (period-scope the assessment count → the AC-028 pin fails at the intended equality) and `TASK-203` (inject a suppression into a scratch file → `FloorGuardTest` reports that exact path). The "revert before commit, verify an empty diff" rule is what makes a pin honest.
- **"Amend the Spec in the same commit" (PRN-001) worked.** Because the Spec reached v1.5 *before* the Diátaxis set was written, the documentation could be generated from a document that matched reality — and the retro then measured the same reality again. The chain Spec → code → tests → docs → retro never had a stale link.
- **The documentation phase verified itself before handing off.** Snippet tracing (every reported line found verbatim in the source), link resolution (10 referenced paths), vocabulary audit (`_Avoid_` synonyms only inside the declared lists) and whitespace hygiene caught four inaccuracies — a wrong `action` attribute phrasing, an imprecise removed-string name, a false "one reader, three consumers" count, and a parsing claim about `FILTER_VALIDATE_INT` that could not be verified. None reached the user.
- **The suite is honest and stable.** No mocks in the four seam files, no suppressions anywhere (statically enforced by `FloorGuardTest`), two consecutive runs byte-identical in counts, and Pint clean on 253 files in 0.66 s.

### Friction Points & Blockers Encountered

| # | Friction | Impact | Proposed remedy |
| --- | --- | --- | --- |
| FR-1 | **`php` is not on `PATH`** (binary at `/opt/homebrew/opt/php@8.3/bin/php`) — recurring since blocker B1 | every gate command needs a prefix; mistakes read as "toolchain unavailable" | add `export PATH="/opt/homebrew/opt/php@8.3/bin:$PATH"` to the shell profile, or a `Makefile` target, and record the resolved invocation in `CONSTRAINTS.md` §2 |
| FR-2 | **The `ask_question` approval checkpoint timed out (300 s)** during the documentation phase | the docs phase stalled for five minutes and then proceeded on the recommended option anyway | timebox approval checkpoints: present the outline and proceed with the stated default unless objected, or ask in prose so the user can answer asynchronously |
| FR-3 | **A four-session-old "coverage unverified" claim was wrong** — PCOV was installed the whole time | the honesty rule was preserved, but a measurable gate stayed unmeasured; the real number (8.8 % project-wide) went undiscovered until this retro | every "unverified" claim gets an **expiry**: re-probe the driver (or the blocking cause) at each phase gate instead of inheriting it from memory |
| FR-4 | **`RateLimitingTest` is 57 % of the suite and owns both skips** | suite runtime and test debt are concentrated in one file; F1 remains open across sessions | close F1 with `/tdd-bug-report` (Prove-It) using the limiter seam; expected outcome: the two skips disappear *and* the suite loses several seconds |
| FR-5 | **63 PHPUnit deprecations (F2) are unchanged** | upgrade debt that will block the next PHPUnit major | schedule a dedicated chore under `/tdd-bug-report` or `/tdd-write-code` |
| FR-6 | **The documentation persona cannot execute commands** | every metric in the Diátaxis set is cited from another artifact rather than observed | accepted division of labour: the docs record the *source* of each number (done in §1 of the reference), and the retro re-measures it — the two artifacts must always be read together |
| FR-7 | **A cited metric can age silently** (the reference cites Unit 1.56 s; today it is 1.27 s) | a reader may treat a historical measurement as current | re-point the reference's evidence line at this retro, or date each figure in place |
| FR-8 | **This session switched personas mid-session** (Technical Writer → Retro Optimizer) under the user-override protocol | context mixing risk that the project's convention avoids by using a new session per phase | keep the convention; when a switch is unavoidable, write the memory checkpoint *before* switching so continuity never depends on the chat history |

## 3. 🧠 Permanent Knowledge Base Updates (`memory.instructions.md`)

### Architecture & Patterns Promoted

| ID | Pattern | Why it generalizes |
| --- | --- | --- |
| **KB-P1** | **An "unverified" gate must carry an expiry.** Re-probe the blocker (driver, binary, service) at every phase gate instead of inheriting the claim from memory. | A four-session-old note said no coverage driver existed; PCOV was installed the whole time. The honesty rule was kept, but a *measurable* gate stayed unmeasured and the real figure (8.8 % project-wide) hid until this retro. |
| **KB-P2** | **Profile before optimizing; then check whether the slowest file is also the debt file.** `php artisan test --profile` prints the top ten slowest in one command; compute their share of the run. | The top ten were 60.67 % of the suite and one file (`RateLimitingTest`) was 57 % — and the same file owned both skipped tests, so one fix pays twice. |
| **KB-P3** | **Pre-seed stateful middleware at its seam; do not drive it through N full HTTP requests.** For the rate limiter: control time (Carbon) and seed/clear via `RateLimiter::hit()/clear()` rather than posting six to thirty-one times to reach the boundary. | Six of the ten slowest tests are throttle tests at ~1.0–1.3 s each for what is a limiter-state assertion, not an HTTP-behaviour assertion. *Proposed by this retro; to be validated when F1 is closed.* |
| **KB-P4** | **Every metric quoted in an artifact carries its source and its date.** A documentation set written by a non-executing persona must state where each number came from, so the numbers can be re-derived rather than trusted. | The Diátaxis reference cites the plan's Unit figure (1.56 s); today's measurement is 1.27 s. Both are true, and only the date/source tells them apart. |

### Dead-Ends (Do NOT Repeat)

| # | Attempted Approach | Why It Failed | Correct Solution |
| --- | --- | --- | --- |
| 1 | Reporting a gate as "unverified" because a previous session recorded no driver | PCOV was installed all along; the claim was inherited rather than measured, so a measurable gate stayed unmeasured for four sessions | re-probe the blocker before repeating the claim (KB-P1); today's measured baseline is 8.8 % total / 91.8–100 % for the triage slice, with branch coverage explicitly Xdebug-only |
| 2 | Driving the rate limiter through 6–31 full framework requests per test | ~1.0–1.3 s per test and 57 % of total suite time; two such tests had to be skipped as flaky, so the cost *and* the debt concentrated in one file | assert at the limiter seam with controlled time (KB-P3); close backlog F1 via `/tdd-bug-report` with the Prove-It pattern |
| 3 | Expecting a whole-`app/` coverage floor (≥ 75 %) to pass on a brownfield monolith | 99 of ~132 instrumented files sit at 0.0 %; the floor is unreachable without a project-wide legacy campaign, so the gate could never be evaluated honestly | report **slice coverage** for changed files plus a ratchet, and keep branch coverage labelled Xdebug-only; re-scoping the threshold is a product-owner decision, never a silent edit (`CONSTRAINTS.md` §3 rule 5) |
| 4 | Treating a cited metric as if it were current | the documentation set quotes Unit 1.56 s and no full-suite duration; today the same suite measures Unit 1.27 s and 13.2–13.4 s | carry source + date per figure (KB-P4) and re-measure at each retro |

## 4. 🎯 Action Items for Next SDLC Cycle

Ordered by leverage per unit of effort. Items A1–A3 are cheap; A5 is the largest unblock.

- [x] **A1 — Put the runner on `PATH`** (FR-1): **done 2026-09-26.** The root cause was not a missing export — `~/.zshrc:83` already exports `php@8.3`, but non-interactive shells (the agent tool shell runs `/bin/bash` with `PATH=/usr/bin:/bin:/usr/sbin:/sbin`) never source it. Fixed by a Makefile `PHP` resolution (`make php-version`, `make test-local`, `make lint-local`, `make gates-local`) plus a `~/.local/bin/php` symlink, and the resolved invocation is now recorded in `CONSTRAINTS.md` §2.
- [x] **A2 — Refresh the reference document's evidence line** (FR-7 / KB-P4): **done 2026-09-26.** `docs/reference/ref-admin-triage-landing.md` §1 now carries measured figures with their source and date, and the three stale "coverage unverified / no driver" claims across the reference and the explanation were corrected to the measured values.
- [x] **A3 — Adopt `--profile` in the standard verification set**: **done 2026-09-26.** `CONSTRAINTS.md` §2 now runs `php artisan test --profile`, and the Makefile's `test-local` mirrors it.
- [x] **A4 — Close backlog F1** (the 2 skipped rate-limit tests): **done 2026-09-26.** See the execution note below.
- [x] **A5 — Decide the coverage policy**: **decided 2026-09-26** (option (a), recorded in `CONSTRAINTS.md` §1 as an A5 decision record). Line coverage now has two scopes: **changed files** keep the unchanged bar (>= 80 % target / >= 75 % floor), and the whole-`app/` figure is governed by a **ratchet** (never below the recorded baseline) instead of an absolute floor. Branch coverage stays Xdebug-only and unverified. The legacy campaign (option (b), starting with `SakipDashboardService` at 1.9 %) remains the way to raise the ratchet.
- [x] **A6 — Declare a full-suite runtime budget**: **decided 2026-09-26** — target **< 15.0 s**, hard floor **< 20.0 s** (investigate before merge), mirroring the Unit suite's shape, enforced through `php artisan test --profile` and recorded in `CONSTRAINTS.md` §1 alongside the measured 12.21–12.37 s baseline.
- [ ] **A7 — Schedule the PHPUnit deprecation chore** (F2, 63 deprecations): upgrade debt that will block the next PHPUnit major.
- [ ] **A8 — Optional hygiene:** `/tdd-map-architecture` to refresh `docs/ARCHITECTURE.md` §5's `docs/` line (it predates `prd/`, `audit/`, `checklist/`, `discovery/`, `review/`, and now the Diátaxis set and `docs/retro/`).
- [ ] **A9 — Next feature cycle:** open a **new chat session** and run `/tdd-explore-ideas` or `/tdd-prd` for the next milestone (per the roadmap); run the F2 chore on its own `/tdd-bug-report` route rather than folding it into a feature.
- [ ] **A10 — The declared `guest` limiter is attached to no route** (new, surfaced by the A4 diagnosis). `RateLimitServiceProvider` defines `RateLimiter::for('guest', 30/min per IP)`, but no route carries `throttle:guest`, so guest traffic is throttled only where a limiter is wired (`throttle:login`, `throttle:email_verification`, `throttle:60,1`). **Recommendation (added 2026-09-26, pending a one-word go from the product owner):** attach `throttle:guest` to the guest-facing routes with its own RED test — a declared policy with no enforcement is a silent DoS surface, and the code already states the intent ("Global rate limit for unauthenticated users: 30 per minute"). The trade-off to accept: 30 requests/minute per IP also caps legitimate guests behind a shared NAT. The alternative — deleting the dead limiter — is behaviour-preserving but discards the stated policy. No production change is made until the owner picks.

### Execution note — A4 (F1 closure), 2026-09-26

| Case | Root cause found | Fix |
| --- | --- | --- |
| `rate_limit_resets_after_time_window` | `RateLimiter::clear('login:email\|ip')` could never match the live key: `ThrottleRequests` namespaces and hashes named-limiter keys (`md5($limiterName.$limit->key)`, `ThrottleRequests.php:134`), so the "clear" was a no-op and the window never reset. The case was skipped as *flaky*; it was **wrong** | the stated property is now tested directly: exhaust the window, assert the sixth attempt is refused, `travel(2)->minutes()`, assert the account is released. Key-agnostic and deterministic |
| `guest_users_have_separate_rate_limit` | false premise: the case expected `GET /` to return 429 after 30 requests, but no route carries `throttle:guest`, so `/` always answers 302 (redirect to login) | renamed to `guest_requests_to_the_root_route_are_redirected_and_never_throttled` and pinned as a characterisation of the real behaviour, with the unattached limiter recorded as A10 |
| `FloorGuardTest` | its allow-list expected exactly 2 skips in `RateLimitingTest.php` | tightened to **zero**: `markTestSkipped` is now forbidden in every scanned file. The guard got stricter, not weaker |

Evidence: RED proved all three failures first (two intended assertion failures, plus the guard detecting the disappeared skips), then GREEN. Gates after the change: **151 passed / 0 skipped / 731 assertions / 12.37 s**, Pint **PASS 253 files**, Unit **57 passed / 1.34 s**, zero skips repository-wide, and `git status` shows only tests, the guard, `Makefile` and `CONSTRAINTS.md` changed — no production code, no migration, no route, no policy.

## 5. Verdict

The Admin Triage Landing chain — delivery, five-axis review, four-phase remediation, and the Diátaxis documentation set — is **closed with evidence**: 149 passing tests and 693 assertions, zero failures, Pint clean, the Spec at v1.5 matching the delivered code, and every one of the 17 review findings traceable to a commit. The suite is stable and its slice coverage is 91.8–100 %.

The one thing this retro changes is the project's self-image of its own gates: **the coverage gate was never unverifiable — it was unmeasured, and it stands at 8.8 % project-wide.** The test-suite hot spot is equally measurable: one file is 57 % of the runtime and owns both skips. Both facts now have owners (A4, A5) and numbers to move.
