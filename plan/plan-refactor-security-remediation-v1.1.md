---
goal: Close the round-2 review findings (reserved-set drift guard, system-wide helper consistency, review-record hygiene)
version: 1.1
date_created: 2026-10-25
last_updated: 2026-10-25
status: "Planned - Awaiting Approval"
tags: ["refactor", "security", "review", "tdd"]
---

# Refactoring & Remediation Plan: Round-2 Review Closeout

## Introduction

![Status: Planned](https://img.shields.io/badge/status-planned-yellow)

Resolves the 4 REQUIRED findings of the second 5-axis review over commits
`9894947..97d1f41`. Round-1 verification: 11 of 12 findings closed; STD-01
partial (R2-STD-01 below). R2-STD-01 is explicitly **not exploitable today**
(the store ceiling already blocks non-admin minting); R2-STD-02 is a semantic
inconsistency, not a leak.

## 1. Traceability: Requirements & Constraints

| Ref | Finding | Requirement | Priority |
| --- | --- | --- | --- |
| SEC-201 | R2-STD-01 [REQUIRED] | Reserved set covers every authority-bearing literal: add `manage-settings` (UserPolicy:119, UpdateSystemSettingsRequest:19) and the AppServiceProvider `Gate::define` abilities (`sakip.view.dashboard`, `sakip.view.performance-indicators`, `sakip.view.performance-data`, `sakip.view.assessments`, `sakip.view.reports`, `sakip.export.data`, `sakip.dashboard.assessor`, `sakip.dashboard.audit`, `isSuperAdmin`) to `RESERVED_POLICY_PERMISSION_NAMES`, each cited. Add the ALT-101 drift-guard unit test: regex-scan `app/` + `routes/` for ability literals and assert the union covers them. | P1 |
| SEC-202 | R2-STD-02 [REQUIRED] | `calculateAveragePerformance` and `getPendingActions` lift `InstansiScope` consistently with the rest of the system-wide branch (non-SA executive holder sees one coherent fleet view); dashboard test extends to `average_performance`/`pending_actions` fleet-wide for a non-SA executive holder. | P1 |
| HYG-203 | R2-SPEC-01 [REQUIRED] | Commit the review records: `plan/plan-refactor-security-remediation-v1.0.md`, `docs/review/slice-*`, and decide `tests/Feature/AdminReservedRoleTest.php` (run it; commit if green, else file a bug) + `memory.instructions.md`; ignore/delete `.v2c/` and `.zcodeignore` deliberately. | P1 |
| HYG-204 | R2-SPEC-02 [REQUIRED] | Capture the actual `docker compose config` render diff for both renders in `docs/review/` (the TASK-306 evidence), referenced from the commit message. | P2 |
| HYG-205 | R2-SPEC-03/04 [NIT] | Record the system-wide `InstansiScope` lift and the base-compose `version` drop as sanctioned deviations in the v2.0 addendum. | P2 |
| HYG-206 | R2-STD-03 [NIT] | Correct the `scopeForInstansi` docstring (IS NULL semantics, not "empty set") or guard with `whereRaw('1=0')` for null. | P3 |
| HYG-207 | R2-STD-05/06 [NIT/OPTIONAL] | Symmetric cleanup of the SA-created permission rows in `AdminPermissionLifecycleTest` tearDown; unify the held-permission ceiling idiom (one helper for store/update). | P3 |

Binding: CON-001…CON-009 carry over; RED-first for SEC-201/202; no severity
labels on NIT items in reports; Super Admin authority preserved.

## 2. Implementation Steps

> **⚠️ EXECUTION DIRECTIVE FOR AI AGENTS (`/tdd-write-code`):**
> Single phase; RED first where a behavior change is claimed (SEC-201 drift
> guard is a characterization test of the extended constant; SEC-202 has a
> RED assertion), then VERIFY and one atomic commit.

| Task ID | Description | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-401 | **RED:** drift-guard unit test — scan app/+routes/ for authority literals, assert every literal ∈ reserved union; fails today on `manage-settings` + AppServiceProvider gate names. | SEC-201 | [ ] | |
| TASK-402 | **GREEN:** extend `RESERVED_POLICY_PERMISSION_NAMES` with the 10 missing names + citations. | SEC-201 | [ ] | |
| TASK-403 | **RED:** extend `SakipDashboardMetricsIsolationTest` — non-SA executive holder's `average_performance`/`pending_actions` must be fleet-consistent; fails today (helpers stay scoped). | SEC-202 | [ ] | |
| TASK-404 | **GREEN:** lift `InstansiScope` inside `calculateAveragePerformance` + `getPendingActions` (system-wide callers only — they are only called from the executive branch; verify before editing). | SEC-202 | [ ] | |
| TASK-405 | **RED (characterization):** pin one AJAX explicit-403 branch that would fail if the controller guard were deleted (R2-STD-04), e.g. direct unit test of the guard predicate or a non-scoped-model foreign case. | SEC-103 | [ ] | |
| TASK-406 | Commit review records (HYG-203): refactor plan v1.0, docs/review/*, AdminReservedRoleTest decision, memory.instructions.md; add `docs/review/render-diff-2026-10-25.md` with both `docker compose config` outputs (HYG-204). | HYG-203/204 | [ ] | |
| TASK-407 | Addendum additions (HYG-205): system-wide scope lift + base-compose version drop recorded as sanctioned deviations; `scopeForInstansi` docstring correction (HYG-206); tearDown cleanup + ceiling-idiom unification (HYG-207) if time-boxed. | HYG-205..207 | [ ] | |
| TASK-408 | **VERIFY:** full suite, Pint, both renders; **APPROVAL:** 🛑 stop before merge. | — | [ ] | |

## 3. Structural Remedies & Alternatives

- **ALT-201:** Runtime permission-scan caching instead of the curated constant
  was rejected in round 1 (ALT-101) and stays rejected; the drift-guard test
  pins the constant mechanically at CI time instead.

## 4. Files Affected

- **FILE-001:** `app/Http/Controllers/Admin/PermissionManagementController.php`
- **FILE-002:** `app/Http/Controllers/Sakip/SakipDashboardController.php`
- **FILE-003:** `tests/Unit/ReservedPermissionVocabularyTest.php` (new drift guard)
- **FILE-004:** `tests/Feature/SakipDashboardMetricsIsolationTest.php`, `tests/Feature/AjaxCascadeIsolationTest.php`, `tests/Feature/AdminPermissionLifecycleTest.php`
- **FILE-005:** `app/Models/PerformanceIndicator.php` (docstring only)
- **FILE-006:** `docs/review/*`, `plan/plan-refactor-security-remediation-v1.0.md`, `plan/plan-bugfix-security-v2.0.md`, `.gitignore` (deliberate ignores)

## 5. Rollback / Recovery Plan

- **RBCK-201:** One atomic commit; revert restores the round-1 state (the
  ceiling already blocks live exploitation, so rollback is safe).
- **RBCK-202:** The scope-lift consistency fix touches only system-wide
  helpers; if reverted, document the inconsistency instead of shipping a
  mixed-view executive dashboard silently.
