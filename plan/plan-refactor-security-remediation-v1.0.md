---
goal: Close the 5-axis review findings on the audit-remediation commits (reserved-list gaps, dashboard branch reachability, remaining unscoped seams, plan metadata honesty)
version: 1.0
date_created: 2026-10-24
last_updated: 2026-10-24
status: "Planned - Awaiting Approval"
tags: ["refactor", "security", "review", "tdd", "remediation"]
---

# Refactoring & Remediation Plan: Security Review Findings (post plan v2.0)

## Introduction

![Status: Planned](https://img.shields.io/badge/status-planned-yellow)

This plan resolves the findings of the 5-axis code review of commits `c4c0919..HEAD`
(the plan-bugfix-security-v2.0.md execution). Review sources: two independent
sub-reviews (Standards & Security; Spec Compliance) against
`plan/plan-bugfix-security-v2.0.md`, `docs/ARCHITECTURE.md`, and the audit run-2
records. One sub-review finding (SPEC-01, `.env.docker.example` missing session
flags) was verified FALSE on the working tree and is excluded.

Out of scope (recorded, unchanged): v1.0 production remediation files remain
uncommitted in the worktree (`UserPolicy`, `AdminService`,
`UserManagementController`, `RoleManagementController`, `UpdatePermissions/UpdateRoles`
requests, `AdminRoleEscalationTest`) — they must be committed separately before or
with Phase 1, because committed test `AdminAccessDelegationTest` depends on them.

## 1. Traceability: Requirements & Constraints

| Ref | Finding | Requirement | Priority |
| --- | --- | --- | --- |
| SEC-101 | STD-01 [CRITICAL] | Reserved permission set must cover every authority-bearing literal: merge `RolesAndPermissionsSeeder::permissionNames()`, the 3 route gate names, and the policy-consumed unseeded names (`view-executive-dashboard`, `view-data-entry-dashboard`, `view-assessor-dashboard`, `view-audit-dashboard`, the `sakip.*` policy family, the `view|create|update|delete performance indicators` space-name family) into one mechanical source; a delegated manager must not be able to mint any of them. | P0 |
| SEC-102 | STD-02 [REQUIRED] | Permission `store` honors the DEC-103(d) delegation ceiling: non-Super-Admin actors cannot create permission names they do not hold by policy contract (restrict creation of never-held names to Super Admin). | P0 |
| SEC-103 | STD-05 [REQUIRED] | The unscoped AJAX cascade endpoints (`KegiatanController::byProgram`, `SasaranStrategisController::byInstansi`, `ProgramController::bySasaranStrategis`) enforce the same tenant predicates as the fixed read paths. | P0 |
| SEC-104 | STD-04 [REQUIRED] | `getInstitutionalMetrics::total_reports` binds to the caller's instansi like its siblings. | P1 |
| SEC-105 | STD-07 [REQUIRED] | `SasaranStrategisPolicy::sameTenant` drops the tenantless-Executive bypass (default-deny per InstansiScope semantics); the index query binds to the viewer's instansi. | P1 |
| SEC-106 | STD-03 [REQUIRED] | The four dashboard branch permissions (`view-executive-dashboard`, `view-data-entry-dashboard`, `view-assessor-dashboard`, `view-audit-dashboard`) join the seeder vocabulary with role grants matching the intended branches (Data Collector/Collector → data-entry; Executive → executive), so the institutional branch is reachable by its intended seeded roles. | P1 |
| SEC-107 | STD-06 [REQUIRED] | Report store rejects foreign performance indicators (`Rule::exists -> where instansi_id`; Super Admin exempt). | P1 |
| STD-108 | STD-08 [REQUIRED] | Dashboard tests assert `total_reports` (instansi-B rows present) and pin which branch each principal shape reaches (collector / executive / personal). | P1 |
| STD-109 | STD-10 [NIT] | `guard_name` is forced server-side to `web` on permission create/update. | P2 |
| STD-110 | STD-11 [OPTIONAL] | CellSanitizer also neutralizes leading `\t` / `\r` (OWASP CSV-injection guidance); test fixtures added. | P2 |
| STD-111 | STD-09, STD-12 [NIT] | Extract the tenant rule helper (`required|exists + reject null instansi`) used by all three planning write paths; add a `scopeForInstansi` on the tenant-bearing model and reuse it in dashboard aggregates. | P2 |
| STD-112 | STD-13 [NIT] | Drop the obsolete `version:` key from `docker-compose.dev.yml`. | P2 |
| SPEC-113 | SPEC-02, SPEC-05 [REQUIRED] | Plan metadata honesty: v2.0 frontmatter → Implemented; v1.0 frontmatter → superseded-for-slices-4/5/8 note. | P2 |
| SPEC-114 | SPEC-03, SPEC-04 [NIT] | Record the sanctioned deviations in the plan addendum: ProgramFormRequest rules also tighten create (tightening-only), and the dashboard policy fail-closed rewrite rationale. | P2 |
| SPEC-115 | SPEC-07 [FYI/OPTIONAL] | Optional machine guard for the compose render invariant; may be deferred to backlog. | P3 |

Binding constraints (carry over from plan v2.0): CON-001…CON-009 — real HTTP/DB
tests at public seams, RED before GREEN, Super Admin authority preserved,
default-deny for Instansi Belum Ditetapkan, no skipped/suppressed tests, one
atomic commit per phase, ARCHITECTURE.md updated when a seam changes.

## 2. Implementation Steps

> **⚠️ EXECUTION DIRECTIVE FOR AI AGENTS (`/tdd-write-code`):**
> Execute phase by phase through strict TDD. Write failing regression tests (RED),
> implement fixes (GREEN), run the full suite, and **STOP AND WAIT** for user
> approval between phases.

### Implementation Phase 1: Reserved-vocabulary integrity (P0)

- **GOAL-101:** No delegated manager can mint or attach an authority-bearing permission name; the ceiling covers store.

| Task ID | Description (Exact File Paths & TDD Steps) | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-101 | **Commit the v1.0 production files first** (separate commit: `UserPolicy`, `AdminService`, `UserManagementController`, `RoleManagementController`, `UpdatePermissions/UpdateRoles` requests, `AdminRoleEscalationTest`) so the review baseline is complete. | — | [ ] | |
| TASK-102 | **RED:** extend `tests/Feature/AdminPermissionLifecycleTest.php` — delegated manager POSTs each authority-bearing unseeded name (`view-executive-dashboard`, `view-data-entry-dashboard`, `sakip.admin`, `sakip.evidence.download`, `view performance indicators`, `create performance indicators`) expecting 403, and the permission row is absent. | SEC-101, SEC-102 | [ ] | |
| TASK-103 | **GREEN:** `PermissionManagementController` — extend `reservedPermissionNames()` with a `RESERVED_POLICY_PERMISSION_NAMES` constant (dashboard four + `sakip.*` policy family + space-name indicator family, each cited from its consuming policy file), and enforce the store ceiling for non-admins per SEC-102. | SEC-101, SEC-102 | [ ] | |
| TASK-104 | **VERIFY:** focused test, full suite, Pint. | — | [ ] | |
| TASK-105 | **APPROVAL:** 🛑 Wait for explicit user confirmation before Phase 2. | — | [ ] | |

### Implementation Phase 2: Remaining tenant seams + dashboard reachability (P1)

- **GOAL-102:** No authenticated JSON leak bypasses the fixed read paths; the dashboard branches work for their intended seeded roles.

| Task ID | Description (Exact File Paths & TDD Steps) | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-201 | **RED:** new `tests/Feature/AjaxCascadeIsolationTest.php` — authenticated viewer of instansi A hits `sakip.api.kegiatan.by-program` (foreign program), `sakip.api.sasaran-strategis.by-instansi` (instansi B), `sakip.api.program.by-sasaran-strategis` (foreign parent) and must be denied or see only tenant-visible rows; positive control for own-instansi. | SEC-103 | [ ] | |
| TASK-202 | **GREEN:** tenant predicates/authorization in `KegiatanController::byProgram`, `SasaranStrategisController::byInstansi`, `ProgramController::bySasaranStrategis` (mirror DEC-101 semantics: Super Admin bypass, tenantless deny). | SEC-103 | [ ] | |
| TASK-203 | **RED:** extend `tests/Feature/SakipDashboardMetricsIsolationTest.php` — instansi-B reports exist and `data.total_reports` must equal the A-count only; assert branch keys for collector (institutional), executive-permission holder (system-wide), and a seeded role. | STD-108, SEC-104 | [ ] | |
| TASK-204 | **GREEN:** bind `total_reports` in `getInstitutionalMetrics`; add the four dashboard permissions to `RolesAndPermissionsSeeder::permissionNames()` + role grants (Data Collector/Collector → data-entry, Executive → executive); keep them in the reserved list. | SEC-104, SEC-106 | [ ] | |
| TASK-205 | **RED:** extend `tests/Feature/MasterDataInstitutionIsolationTest.php` (or a policy test) — tenantless Executive must be denied view/update of a foreign SasaranStrategis (403/404) and the index must not render foreign rows. | SEC-105 | [ ] | |
| TASK-206 | **GREEN:** remove the tenantless-Executive branch from `SasaranStrategisPolicy::sameTenant`; bind the controller index to the viewer instansi (Super Admin bypass preserved). | SEC-105 | [ ] | |
| TASK-207 | **RED + GREEN:** report store rejects foreign indicator ids — extend `tests/Feature/ReportTemplateInstitutionIsolationTest.php` with an instansi-B indicator payload expecting `assertJsonValidationErrors('indicators.0')`; implement `Rule::exists->where instansi_id` in `ReportController::store` (Super Admin exempt). | SEC-107 | [ ] | |
| TASK-208 | **VERIFY:** full suite + Pint; update `docs/ARCHITECTURE.md` seams (AJAX cascade + seeder vocabulary). | — | [ ] | |
| TASK-209 | **APPROVAL:** 🛑 Wait for explicit user confirmation before Phase 3. | — | [ ] | |

### Implementation Phase 3: Hygiene and metadata (P2)

- **GOAL-103:** NIT-level cleanups and honest plan metadata; no security behavior change.

| Task ID | Description (Exact File Paths & TDD Steps) | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-301 | **RED + GREEN:** `guard_name` forced to `web` server-side in `PermissionManagementController::store/update` (test asserts a non-`web` guard_name payload persists as `web`). | STD-109 | [ ] | |
| TASK-302 | **RED + GREEN:** `CellSanitizer` neutralizes leading `\t`/`\r`; extend `tests/Unit/ExportFormulaInjectionTest.php` with tab/CR fixtures. | STD-110 | [ ] | |
| TASK-303 | Extract the tenant rule helper used by `SasaranStrategisController` + `ProgramFormRequest` (explicit null-instansi rejection instead of `Rule::in([null])`); add `scopeForInstansi` on `PerformanceIndicator` and reuse in dashboard aggregates (behavior-neutral; existing tests must stay green). | STD-111 | [ ] | |
| TASK-304 | Drop `version:` from `docker-compose.dev.yml`; verify both renders with `docker compose config`. | STD-112 | [ ] | |
| TASK-305 | Plan metadata: v2.0 frontmatter → Implemented; v1.0 frontmatter → superseded-for-slices-4/5/8 note; record SPEC-03/SPEC-04 sanctioned deviations in a plan addendum. | SPEC-113, SPEC-114 | [ ] | |
| TASK-306 | **VERIFY:** full suite + Pint; final render diff captured in the commit message. | — | [ ] | |
| TASK-307 | **APPROVAL:** 🛑 Wait for explicit user confirmation; then hand off to `/tdd-retro` if requested. | — | [ ] | |

## 3. Structural Remedies & Alternatives

- **ALT-101:** Reserved names could be auto-scanned from the codebase (grep literals) at runtime instead of a curated constant. Rejected: runtime scanning is fragile, caches stale reads, and hides authority in a heuristic. A curated, citable constant (each name backed by its consuming policy file) is auditable; a unit test can pin the constant against a regex scan as a drift guard in a later slice.
- **ALT-102:** The AJAX cascade endpoints could be deleted instead of scoped. Rejected: dropdown flows depend on them (create/edit forms); scoping preserves the product contract while closing the leak.

## 4. Files Affected

- **FILE-001:** `app/Http/Controllers/Admin/PermissionManagementController.php` (reserved set + store ceiling + guard_name)
- **FILE-002:** `app/Console/Commands/AddSakipPermissions.php` (no change expected; verify fixtures)
- **FILE-003:** `database/seeders/RolesAndPermissionsSeeder.php` (dashboard permissions + grants)
- **FILE-004:** `app/Http/Controllers/Sakip/SakipDashboardController.php` (total_reports, scope reuse)
- **FILE-005:** `app/Http/Controllers/Sakip/KegiatanController.php`, `app/Http/Controllers/Sakip/SasaranStrategisController.php`, `app/Http/Controllers/Sakip/ProgramController.php` (AJAX scoping)
- **FILE-006:** `app/Policies/SasaranStrategisPolicy.php` (tenantless branch removal)
- **FILE-007:** `app/Http/Controllers/Sakip/ReportController.php` (indicator exists-rule)
- **FILE-008:** `app/Services/Export/CellSanitizer.php` (tab/CR)
- **FILE-009:** tests: `AdminPermissionLifecycleTest`, `SakipDashboardMetricsIsolationTest`, `MasterDataInstitutionIsolationTest`, `ReportTemplateInstitutionIsolationTest`, `ExportFormulaInjectionTest`, new `AjaxCascadeIsolationTest`
- **FILE-010:** `docker-compose.dev.yml`, `plan/plan-bugfix-security-v2.0.md`, `plan/plan-bugfix-security-v1.0.md`, `docs/ARCHITECTURE.md`

## 5. Rollback / Recovery Plan

- **RBCK-101:** One atomic commit per phase; revert the phase commit only. The P0 commit must never be reverted without immediately re-restricting the affected endpoints (reverting restores a CRITICAL mint path).
- **RBCK-102:** Seeder additions are additive rows; rollback removes only the new permission names from the vocabulary (no data migration). Confirm no production role assignment references them before rollback.
- **RBCK-103:** The tenantless-Executive policy change can break existing tenantless Executive workflows by design (default-deny); inventory such accounts read-only before rollout (same discipline as plan v2.0 DEP-805).
