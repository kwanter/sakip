# TDD Retrospective: Security Closeout (round-2 review, plan v1.1, A-101/A-102)

**Date:** 2026-10-25
**Author:** TDD Retrospective Optimizer
**Evaluated Artifacts:**
- Plan: `plan/plan-refactor-security-remediation-v1.1.md` (executed, commit `549f8ca`)
- Reviews: round-2 two-axis sub-reviews (R2-STD-01..07, R2-SPEC-01..07)
- Decision record: A-101 SLA recalibration (`CONSTRAINTS.md` §1, commit `281cf60`)
- Predecessor: `docs/retro/retro-security-remediation-2026-10-25.md` (covers the v2.0 slices + round-1)

---

## 1. ⏱️ Test Suite Health & Performance Telemetry

- **Total Test Execution Duration:** 21.28–21.65 s (new SLA: < 25.0 s target / < 30.0 s floor — **met** under the A-101 decision) ✅
- **Total Tests Executed:** 316 tests (61 unit / 255 feature, 0 failed, 0 skipped, 2 437 assertions); Unit suite within SLA.
- **Slowest Test Suites Identified:** unchanged profile — `RateLimitingTest` owns 6 of the top-10 slots (≈ 6.9 s, 41.6 % of the run with `AuthenticationTest` throttle cases). **A-101b** (seam-level rewrite, KB-P3) remains the single highest-leverage optimization.
- **Flaky Tests Detected:** 0 (single-run observation; the drift-guard scanner test is deterministic by construction — it reads the tree, not the database).

## 2. 🔁 SDLC Process & Friction Analysis

### What Went Well (Praise & Retain)
- **The drift guard out-hunted the reviewer:** round-2's reviewer reported 10 missing literals; the committed scanner test found **19** — including `isSuperAdmin` and the generic policy-ability names (`create`/`update`/`viewAny`/`assignRoles`/`assignPermissions`/`manageProtectedAccount`) whose mint is a **universal policy bypass** via the Spatie name-match `Gate::before`. Mechanizing the check (ALT-101) found more than expert eyeballing.
- **Mutation-verified pinning is now routine:** the AJAX explicit-403 pin was proven by deleting the guard (403 → 200) and restoring — same discipline as the round-1 dashboard counts pin. Two cycles in a row, no unproven RED shipped.
- **Honest findings stayed honest:** the first RED attempt for the dashboard helper test produced `0.0 vs 60.0` — a sqlite `strftime` artifact on `'YYYY-MM'` periods, not the scoped-vs-fleet distinction. The fixture was fixed (full-date periods) until the RED showed the real numbers (80.0 vs 60.0), matching the CON-008 rule that an unexplained RED is a fixture error.
- **Review-record hygiene closed:** plans v1.0/v1.1, the 2026-10-11 slice review records, and the render evidence are now in the repository — the audit trail is self-contained.

### Friction Points & Blockers Encountered
- **`scopeForInstansi` already existed** with an `int` typehint — the "add a scope" task became "discover, fix the latent UUID 500, and reuse". Lesson applied immediately: check the model for an existing scope before writing one.
- **Scanner false positives needed domain triage:** the drift guard flagged `assignRoles`/`manageProtectedAccount`/`viewAny` etc. — investigation showed they ARE authority-bearing (used via `->can(..., Model)` with the Spatie name-match), so they were reserved rather than filtered. Filtering them would have been a security mistake.
- **Dual review rounds duplicated some context cost** (~2.5–6.7 M tokens per sub-agent). Acceptable for security scope; for lower-stakes cycles a single round with the drift-guard tests in place should suffice.

## 3. 🧠 Permanent Knowledge Base Updates (`memory.instructions.md`)

All five patterns from the morning retro (P-01..P-05) held during this cycle — notably P-03 (drift guard) and P-05 (shared `:memory:` state, which drove the scoped `tearDown`). New entries recorded this cycle:

- **[P-06] A reserved-name list is only as complete as its last mechanical scan.** Expert review found 10 missing names; the scanner found 19, including a universal-bypass class (generic ability names + model arguments). Keep `ReservedPermissionVocabularyTest` in the gate set; extend `RESERVED_POLICY_PERMISSION_NAMES` only through its failure output.
- **[P-07] `whereYear` on a text period column is driver-ambiguous.** SQLite `strftime` needs a full date; production MySQL accepts `'YYYY-MM'`. Any test asserting on `whereYear` aggregates over `performance_data.period` must fixture full dates (`'YYYY-MM-DD'`) or the assertion measures the driver, not the code.
- **[P-08] Scope consistency is a property of the whole call graph.** Lifting a global scope in one aggregate (counts) while helpers keep it (average, pending) produces a self-inconsistent dashboard. When lifting a scope, grep every helper reachable from the same branch.

## 4. 🎯 Action Items for Next SDLC Cycle
- [ ] **A-101b:** `RateLimitingTest` seam optimization (KB-P3; ≈ 7 s, 41.6 % of the run) — then re-measure; the SLA row may be tightened only on a re-measure.
- [ ] **A-102b (carry):** wire `ReservedPermissionVocabularyTest` into the CI gate list in `.github/workflows/ci.yml` if the workflow enumerates filters explicitly (verify current invocation covers the Unit suite wholesale).
- [ ] **A-103 (carried):** read-only inventory of tenantless Executive accounts before deploying past `6ded457`.
- [ ] **A-104 (carried):** v1.0 backlog `REQ-006` (evidence edit locking) and `REQ-007` (journal symlink containment).
- [ ] Start the next feature cycle with `/tdd-explore-ideas` or `/tdd-prd` in a fresh session.
