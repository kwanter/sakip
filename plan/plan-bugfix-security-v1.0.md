---
goal: Remediate eight source-supported SAKIP security findings with TDD
version: 1.0
date_created: 2026-10-11
last_updated: 2026-10-25
status: "Partially Implemented - slices 4/5/8 SUPERSEDED by plan-bugfix-security-v2.0.md; slices 1-3 landed in 9894947; REQ-006/REQ-007 remain open"
tags: ["bug-fix", "security", "remediation", "tdd"]
---

## Introduction

![Status: Planned](https://img.shields.io/badge/status-planned-yellow)

This plan addresses eight findings from the completed static security audit.
It is a bug remediation contract, not approval to implement or deploy changes.
No regression tests or production fixes have been written or executed for this
plan. Every slice must first prove its specific vulnerability at a public seam.

The audit reviewed revision `c4c091969aef6692237561e50e8ba7a32368fc21`.
Its scan ID is `9e0ac738-0396-4d2a-8feb-8d12e1dd5fb4`.
The source snapshot digest is
`codex-security-snapshot/v1:sha256:45cf36d1b219ba026ed77e58d3e3318877f5a8fe78c3a63b8c611c539a26ad89`.

[Original audit report](/Users/macbook/.codex/state/plugins/codex-security/scans/sakip/c4c091969aef6692237561e50e8ba7a32368fc21_20261011T020538Z_mc6ehqjk/report.md)
contains the evidence, prerequisites, and counterevidence. Keep its sealed
artifacts unchanged. The audit is static and has partial coverage: 598 files
were fully reviewed, 124 received security-boundary projections, and one empty
asset was classified as not applicable. Live exploitation and dependency
advisory checks were not performed.

The plan uses the canonical domain labels **Cakupan Instansi**,
**Semua Instansi**, and **Instansi Belum Ditetapkan** from `CONTEXT.md`.
Technical model names retain their existing source spelling.

## 1. Requirements & Constraints (Fix Constraints)

| Ref ID | Finding rule ID | Requirement | Severity |
| --- | --- | --- | --- |
| REQ-001 | `admin-account-takeover` | Non-Super-Admin user managers cannot change credentials, identity, ownership, or membership of Super Admin accounts. | High |
| REQ-002 | `unchecked-permission-grant` | Access grants require assignment authority and cannot exceed the non-Super-Admin actor's effective permissions. | High |
| REQ-003 | `reserved-role-rename` | Non-Super-Admin actors cannot create, rename into, rename away from, or mutate the reserved Super Admin role. | High |
| REQ-004 | `tenant-master-reassignment` | Ordinary master-data writes preserve authorized Cakupan Instansi and bind strategic-goal references to the authorized institution. | High |
| REQ-005 | `kegiatan-cross-tenant-read` | Kegiatan lists and object reads enforce the institution of their Program. | Medium |
| REQ-006 | `approved-evidence-deletion` | Validated performance submissions cannot lose evidence through collector edits; failed transactions preserve original evidence bytes. | Medium |
| REQ-007 | `tool-journal-symlink` | Live journal cleanup cannot read, remove, patch, or prune outside the canonical project root through symlinks. | Medium |
| REQ-008 | `tenant-template-list` | Report template listings and selection expose only active global templates or templates within authorized Cakupan Instansi. | Low |

- **CON-001:** Follow `CONSTITUTION.md` and the active `CONSTRAINTS.md`,
  not generic thresholds from a template. Never weaken tests, guards, or CI.
- **CON-002:** Preserve route names and successful response formats.
  Unauthorized operations return 403; inaccessible bound objects may return
  404. Invalid input remains 422. Do not expose protected target metadata.
- **CON-003:** Retain intentional Super Admin access to **Semua Instansi**.
  An ordinary account in **Instansi Belum Ditetapkan** must not gain access
  to institution-owned records or templates by default.
- **CON-004:** Use real HTTP, database, permission, and storage behavior in
  feature tests. Do not mock authorization, `InstansiScope`, or the vulnerable
  sink. Unit tests supplement rather than replace public-seam regressions.
- **CON-005:** Use minimal edits. No schema migration, role-ID redesign,
  framework upgrade, bulk permission cleanup, or deployment is included.
- **CON-006:** Keep `AuditLog` enabled and sensitive fields masked. Denied
  operations cannot create a successful mutation audit entry.
- **CON-007:** Run tests against disposable databases/storage only. Resolve
  PHP 8.3+ before running gates. Node fixtures must use fresh temporary roots.
- **CON-008:** Refresh source and reproduce each finding before implementation.
  An unrelated 500, validation failure, absent method, or fixture error is not
  an accepted RED result.
- **CON-009:** Add architecture documentation for any new public seam, helper,
  test directory, or CI check before declaring implementation complete.

### Approved-plan behavior decisions

These decisions become approved only when the user approves this plan.

- **DEC-001:** `manage-users` continues to manage ordinary accounts but cannot
  manage Super Admin targets. Apply the same target protection to update,
  deletion, role assignment, and direct permission assignment. Super Admin
  retains its existing authorized administrative operations.
- **DEC-002:** Reuse `UserPolicy::assignRoles` and `assignPermissions`, which
  currently require `manage-roles`. Wire the existing assignment Form Requests
  into active endpoints. For non-Super-Admin actors, every requested permission
  and every permission conveyed by a requested role must be in the actor's
  effective permission set. Enforce the same ceiling on role permission edits
  and role creation to avoid a sibling grant bypass. Reject the whole request;
  do not silently apply a partial grant. Existing test-pinned Super Admin role
  filtering in `AdminService::assignRoles` must remain compatible.
- **DEC-003:** Reserve `SystemRoles::SUPER_ADMIN` using the existing constant.
  Block non-Super-Admin mutations of that role and transitions to its name.
  Do not introduce a role-ID migration. Preserve the existing ban on deleting
  the Super Admin role and prevent renaming it away even for Super Admin,
  because name-based Gate and scope consumers depend on that identity.
- **DEC-004:** Reject foreign `instansi_id` submitted by an ordinary actor
  rather than silently move or rewrite ownership. Validate Program's
  `sasaran_strategis_id` within the authorized destination institution,
  including for Super Admin. Do not change the unrelated Program-create
  required-year mismatch as part of this slice.
- **DEC-005:** Collector edits are permitted only for draft or returned
  (`rejected`) submissions. Submitted and validated records are locked.
  If another status is actually supported by the schema, it remains locked
  unless an existing explicit revision contract authorizes it. Do not invent
  an `approved` database enum merely to manufacture a test.
- **DEC-006:** Preserve restrictive `EvidenceDocumentPolicy::delete` rules.
  Ordinary collectors cannot remove existing evidence via update. An actor
  legitimately authorized to delete a draft document can schedule physical
  cleanup only after the database transaction commits. Cleanup failure must
  preserve a diagnosable file path; it must not silently claim deletion.
- **DEC-007:** Global report templates have null `instansi_id`; these remain
  available to authorized report users without an institution. Super Admin
  may access institution templates explicitly. Other unassigned users see
  only global templates.

## 2. Implementation Steps (Prove-It Sequence)

> [!IMPORTANT]
> Execute one complete vertical slice at a time, in the order below.
> For the selected slice, execute Phase 1, verify RED, and obtain approval.
> Then execute Phase 2, verify GREEN and the applicable gates, and obtain
> approval before starting the next slice. Do not write all production layers
> first, batch all fixes behind one final test run, or start implementation
> merely because this plan exists.

### Implementation Phase 1: Test Writing (Prove-It Regression Test)

- **GOAL-001:** Reproduce the selected finding through its public seam while
  retaining positive controls and asserting independent persisted outcomes.

| Task | Description | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| TASK-001 | Capture source revision, clean-change baseline, toolchain, and pre-existing suite failures in the implementation log. | CON-007, CON-008 | [ ] | |
| TASK-002 | Add the selected slice's regression and positive control from Section 6 using real permissions and disposable fixtures. | REQ-001–REQ-008, CON-004 | [ ] | |
| TASK-003 | Record the exact request/input, failing security assertion, persisted effects, and strongest counterevidence. | CON-008 | [ ] | |
| TASK-004 | **VERIFY:** Run the focused test. The security assertion must fail for the expected reason; fixture and positive controls must pass. | - | [ ] | |
| TASK-005 | **APPROVAL:** Stop after RED evidence; obtain explicit approval to implement this slice. | - | [ ] | |

For deletion tests, the expected secure assertion is that original file bytes
remain. A vulnerable response may be 500: that status is not the proof. The
test must demonstrate physical deletion independently of database rollback.
For permission tests, create all named permission records explicitly, clear
Spatie's cache, and ensure the actor passes the route's administrative guards.

### Implementation Phase 2: Minimal Root Cause Remediation

- **GOAL-002:** Make the selected regression pass with the smallest complete
  fix across the relevant request, authorization, persistence, and output path.

| Order / Task | Vertical slice and minimal change | Ref ID | Completed | Date |
| --- | --- | --- | --- | --- |
| 1 / TASK-101 | Protect Super Admin User targets in active administrative endpoints and service mutations; retain ordinary-account administration and Super Admin positive controls. | REQ-001, DEC-001 | [ ] | |
| 2 / TASK-102 | Activate assignment requests/policies and enforce an actor grant ceiling for direct grants, role grants, and role permission mutations, including create paths. | REQ-002, DEC-002 | [ ] | |
| 3 / TASK-103 | Protect the reserved Super Admin role on create/update/permission operations; prevent its name from being transferred while retaining deletion protection. | REQ-003, DEC-003 | [ ] | |
| 4 / TASK-104 | Bind Sasaran Strategis create/update and existing Program update to authorized destination ownership and same-institution strategic-goal references. | REQ-004, DEC-004 | [ ] | |
| 5 / TASK-105 | Filter Kegiatan lists through Program institution ownership and authorize detail reads; check sibling mutation policies for equivalent institution guards. | REQ-005, CON-003 | [ ] | |
| 6 / TASK-106 | Lock submitted/validated collector edits, authorize each evidence removal, and coordinate original-file cleanup with successful commit. | REQ-006, DEC-005, DEC-006 | [ ] | |
| 7 / TASK-107 | Canonically contain journal reads/deletes/patches/pruning, reject escaping symlink components, and cover both tracked helper copies. | REQ-007, CON-007 | [ ] | |
| 8 / TASK-108 | Derive template availability from authorized viewer scope and enforce it in index, selection, and submitted template_id validation. | REQ-008, DEC-007 | [ ] | |
| TASK-109 | Update architecture seams and CI wiring when introduced by the selected slice; retain audit behavior and scope-compatible UI responses. | CON-006, CON-009 | [ ] | |
| TASK-110 | **VERIFY:** Run focused regressions, sibling-path controls, full applicable suites and Section 6 quality gates. Capture failures and coverage honestly. | - | [ ] | |
| TASK-111 | **APPROVAL:** Stop after GREEN/gates; obtain explicit approval before the next slice. | - | [ ] | |
| TASK-112 | Save the accepted slice in an atomic commit containing only its reviewed files; do not stage unrelated changes or push/deploy automatically. | CON-005, RBCK-001 | [ ] | |

Authorization rules shared by HTTP and services must have one owner. Reuse
existing policies/services where possible. If a small shared grant or path
validator is necessary, agree its public test seam during RED and add it to
`docs/ARCHITECTURE.md`; do not expand into an architectural redesign.

## 3. Rollback Strategy

- **RBCK-001:** Record one commit and exact file list per accepted slice.
  Revert only that commit if rollback is required; never reset the working
  tree or discard unrelated changes. No database migration rollback is needed.
- **RBCK-002:** Reverting an authorization fix restores a known vulnerability.
  Keep the affected administrative/write endpoint unavailable, or restrict it
  to Super Admin, until a corrected patch passes. Production containment and
  deployment require separate explicit approval.
- **RBCK-003:** Do not grant broad permissions, remove `InstansiScope`, or
  silently normalize existing account ownership to recover compatibility.
  Any existing-access inventory is read-only; access changes need approval.
- **RBCK-004:** A code revert cannot restore previously deleted evidence.
  Inventory affected document IDs/paths first; restore only verified bytes
  from an authorized backup through a separate recovery procedure. Never
  fabricate replacement evidence or rewrite the audit history.
- **RBCK-005:** If post-commit cleanup fails, preserve enough document/path
  information for controlled retry. Retry only previously authorized cleanup
  within the correct storage boundary; avoid arbitrary-path orphan sweeps.
- **RBCK-006:** Roll back tracked helper changes as a pair. Preserve the
  application fixes independently. Refresh ignored installed helper copies
  only through a separately authorized sync/update, then rerun their tests.

## 4. Dependencies

- **DEP-001:** Existing Laravel, PHPUnit, Spatie Permission and storage test
  helpers. `Tests\TestCase` seeds automatically and clears permission cache.
  Do not assume the default seeder provisions delegated administration or
  the modern `sakip.*` permission family; create fixture permissions explicitly.
- **DEP-002:** Node's built-in `node:test`, `assert`, filesystem and temporary
  directory APIs for helper tests. No new npm testing framework is required.
- **DEP-003:** No dependency upgrade is planned. Run the existing Composer
  and npm advisory gates during execution; any unrelated advisory is a
  separately scoped blocker, not a reason to weaken the gates.
- **DEP-004:** Evidence update currently reads a missing `has_errors` result
  key. Prove original-file preservation even through that failure. A positive
  successful edit/cleanup test may require a small result-contract correction
  with its own failing regression; do not substitute a mock to conceal it.
- **DEP-005:** Program creation omits required `tahun`; report creation may
  load an absent assessments relation; Excel export queries absent
  `indicator_id`. Use working public paths identified by the audit, and do
  not silently bundle those correctness repairs into security slices.

## 5. Files Affected

These are expected edit boundaries, not permission to modify every file.
Existing neighboring tests must remain intact.

| Slice | Existing implementation boundaries | Proposed regression file |
| --- | --- | --- |
| 1 | `UserManagementController`, `AdminService`, `UserPolicy`, applicable administrative User requests | `tests/Feature/AdminProtectedAccountTest.php` |
| 2 | Active role/permission assignment requests, `UserPolicy`, `AdminService`, `RoleManagementController` | `tests/Feature/AdminAccessDelegationTest.php` |
| 3 | `RoleManagementController`, `SystemRoles`; preserve Gate and InstansiScope consumers | `tests/Feature/AdminReservedRoleTest.php` |
| 4 | `SasaranStrategisController`, `ProgramController`, `ProgramFormRequest`, ownership policies | `tests/Feature/MasterDataInstitutionIsolationTest.php` |
| 5 | `KegiatanController`, `KegiatanPolicy`, `Kegiatan` if a relation-based scope is required | `tests/Feature/KegiatanInstitutionIsolationTest.php` |
| 6 | `DataCollectionController`, `PerformanceDataPolicy`, evidence deletion authorization/cleanup owner | `tests/Feature/ValidatedEvidenceIntegrityTest.php` |
| 7 | `.agents/skills/impeccable/scripts/live/frameworks/journal.mjs` and identical tracked `.github` copy; applicable pruning helper | `.agents/skills/impeccable/tests/journal-boundary.test.mjs` |
| 8 | `ReportController`, `TemplateService`, `ReportTemplate`, template input validation | `tests/Feature/ReportTemplateInstitutionIsolationTest.php` |

Full controller/request paths are under `app/Http/Controllers/Admin/`,
`app/Http/Controllers/Sakip/`, and `app/Http/Requests/Admin/` or `Sakip/`.
Services, models, and policies are under their existing `app/` directories.
Also update `docs/ARCHITECTURE.md` for new seams and
`.github/workflows/ci.yml` to run the Node regression if Slice 7 introduces it.
The ignored `.codex/skills/impeccable/` copy is not an atomic source commit;
record its refresh status separately instead of claiming it was fixed.

## 6. Testing Strategy & Edge Cases

### Public-seam regression inventory

| Test ID | Seam / minimum setup | RED security assertion and positive controls | Requirement |
| --- | --- | --- | --- |
| TEST-001 | PUT `/admin/users/{superAdmin}`; verified delegated user manager | Request denied; password hash, email, institution and role membership unchanged. Super Admin can perform an authorized credential update; login with attacker password fails. | REQ-001 |
| TEST-002 | DELETE and access-assignment routes targeting Super Admin | Target survives and retains access; ordinary-account management retains intended behavior. Prevent changes before password hashing or persistence. | REQ-001 |
| TEST-003 | POST `/admin/users/{self}/permissions`; manage-users without manage-roles | Sensitive direct permission remains absent and denied response is returned. Repeat with another ordinary target and with roles endpoint. | REQ-002 |
| TEST-004 | Authorized assignment actor, deliberately limited permission set | Attempt to grant a permission or role conveying absent authority is wholly rejected. An allowed subset succeeds; clear Spatie cache and verify subsequent real requests. | REQ-002 |
| TEST-005 | Role create/updatePermissions for limited role manager | An indirect privilege increase is denied; permitted role edits succeed. Test self and another role member, plus valid empty grant removal. | REQ-002 |
| TEST-006 | Two PUT role requests: free Super Admin name, then rename own role | Reserved original name and actor role remain unchanged; actor never obtains Gate bypass or access to another Instansi. A normal role rename still succeeds. | REQ-003 |
| TEST-007 | Create/permission-edit reserved role; delete existing reserved role | Non-Super-Admin cannot claim the reserved identity or mutate its grants; deletion remains denied. Authorized ordinary-role lifecycle stays functional. | REQ-003 |
| TEST-008 | POST Sasaran Strategis with foreign instansi_id | Denied; no foreign record is inserted. Same-institution creation succeeds with a unique code and valid status. | REQ-004 |
| TEST-009 | PUT own Sasaran Strategis and existing Program with foreign ownership | Denied; original ownership and relationships unchanged. Use valid Program request fields while retaining its existing tahun. | REQ-004 |
| TEST-010 | Program update with same institution but foreign strategic-goal ID | Validation/authorization rejects the foreign parent. Same-institution parent and legitimate Super Admin destination work. | REQ-004 |
| TEST-011 | GET Kegiatan list and foreign detail; two institutions | Foreign code/name/budget/link absent from pagination and search/filter results; detail inaccessible. Same-institution and Super Admin access work. | REQ-005 |
| TEST-012 | Unassigned viewer, soft-deleted/missing Program, sibling mutations | Default deny owned data when the parent is unavailable or foreign; existing valid own-institution operations remain available. | REQ-005 |
| TEST-013 | PUT own validated PerformanceData with existing_files=[] | Original evidence bytes and document association remain; no successful update audit. Establish expected failure via file loss, not an unrelated response error. | REQ-006 |
| TEST-014 | Submitted/validated state and unauthorized draft evidence removal | Locked-state update denied; removing a draft document without evidence-delete authority also denied. Allowed collector scalar draft edit remains supported. | REQ-006 |
| TEST-015 | Authorized draft removal with a failure after scheduling cleanup | Rollback preserves original bytes and metadata. Successful real commit cleans only the authorized file. Exercise the actual outer transaction boundary. | REQ-006 |
| TEST-016 | Node temporary project, sibling sentinel file, intermediate directory symlink, forged created journal | Healing refuses outside-root access; sentinel bytes survive. Run against both tracked helper copies. Normal generated-artifact cleanup works. | REQ-007 |
| TEST-017 | Patched journal, lexical traversal, final symlink, symlink ancestor and pruneTo | Refuse external read/write/pruning; external bytes and directories remain unchanged. Internal supported symlinks have an explicit tested policy. | REQ-007 |
| TEST-018 | GET reports index with A, B, global and inactive templates | B names/descriptions/IDs absent; A/global appear; inactive templates absent. Use the working index rather than a broken populated create page. | REQ-008 |
| TEST-019 | GET template selection and POST foreign template_id | Foreign selection denied and submission rejected before content generation. Own/global selection succeeds; Super Admin behavior is preserved. | REQ-008 |
| TEST-020 | Authorized unassigned report viewer | Only global active templates visible/selectable; another institution's template is denied. | REQ-008 |

Use `Storage::fake` with known sentinel contents, and assert actual bytes rather
than merely document-row counts. Laravel `RefreshDatabase` may wrap the test
in an outer transaction: do not claim an after-commit callback ran just because
the controller returned. Use a dedicated disposable database test without an
outer test transaction for successful post-commit cleanup, or document a
public cleanup seam and still include one real-commit integration test.

Helper fixtures must never use real home/project files. Create project and
sibling sentinels under one temporary test root and clean up only that root.
Do not skip the symlink regression to obtain GREEN. A platform without
symlink support leaves the required gate unverified until run on a supported
CI runner. Run both tracked implementations from the same parameterized test.

### Quality gates and commands

Resolve `php` to PHP 8.3+; if absent, use
`/opt/homebrew/opt/php@8.3/bin/php` or the existing Makefile/Docker commands.
The following commands are planned, not evidence that tests have passed:

```bash
php artisan test --filter=AdminProtectedAccountTest
node --test .agents/skills/impeccable/tests/journal-boundary.test.mjs
composer validate --strict --no-check-all
./vendor/bin/pint --test --no-interaction
php artisan config:clear
php artisan test --profile
php artisan test --testsuite=Unit
php artisan test --coverage
composer audit --locked
npm audit --omit=dev
npm run build
```

Replace the focused filter with the selected slice's class. Run the Node gate
when Slice 7 adds its test; include it in final CI. Run the full applicable
suite after each GREEN checkpoint, and the complete gate set before handoff.

- All PHPUnit and Node regressions must pass without skipped tests.
- Changed PHP files target at least 80% line coverage, with a 75% hard floor.
  Whole-application coverage must not fall below the recorded 8.8% baseline
  or a higher subsequently accepted ratchet. Inspect changed-file rows;
  a whole-suite `--min=75` flag alone does not express this two-scope contract.
- Unit suite runtime targets under 10 seconds, full suite under 15 seconds;
  each has the existing 20-second investigation floor.
- Branch coverage targets 75%, floor 70%, and remains unverified without
  a branch-capable driver. PCOV line coverage is not branch coverage.
- Pint must report zero errors; production asset build must succeed;
  existing architecture and isolation guards must remain green.
- Dependency gates must introduce no high/critical advisories. Report a
  blocked registry/toolchain gate as unverified, never passed.
- No strict type-check command is configured in the current manifests.
  Do not invent a PHPStan/TypeScript pass or install a new analyzer implicitly.
  Record the existing tooling limitation; configuring an analyzer is separate.
- Before completion, revalidate all eight finding triggers and their positive
  controls. Preserve source/runtime evidence and the exact accepted revision.

## 7. Risks & Assumptions

- **RISK-001:** Administrative findings require explicitly delegated
  `admin.dashboard` plus user/role-management permissions. Tests must create
  this supported actor rather than rely on the default Super Admin seeder.
- **RISK-002:** The grant ceiling intentionally restricts previously accepted
  unauthorized grants. List existing delegation workflows before rollout;
  do not auto-revoke existing access or treat current grants as trustworthy
  evidence that the security ceiling should be removed.
- **RISK-003:** Restricting evidence edits can change collector UI behavior.
  Retain Indonesian feedback for denied/locked states. Do not implement a
  new revision workflow or broaden evidence deletion merely for compatibility.
- **RISK-004:** File deletion is irreversible without backup. After-commit
  handling needs explicit success, rollback and cleanup-failure assertions.
  Retaining a diagnosable orphan on failure is preferable to deleting before
  commit or silently losing the recovery path.
- **RISK-005:** The journal finding concerns local developer tooling, not
  a remote Laravel endpoint. Both tracked copies need the same patch and
  regressions; ignored installed copies need a recorded separate refresh.
- **RISK-006:** Static audit does not prove live exposure, rotation status,
  production schema, or deployment permissions. Reproduction can narrow a
  finding, but do not dismiss it solely because a default role lacks a
  permission that the application supports delegating.
- **RISK-007:** Two audit candidates remain outside these eight fixes:
  report XLSX formula inference behind the broken calculation path, and
  null-institution evidence download authorization dependent on provisioned
  `sakip.*` permissions. Validate separately; do not count them as remediated.
- **RISK-008:** Pre-existing suite failures or broken positive paths must be
  identified before RED. A minimal prerequisite repair requires its own
  regression and approval; unexplained failures block phase completion.
- **RISK-009:** This document supplies the bounded bug-remediation plan accepted
  by `/tdd-write-code`. A larger authorization redesign, new data model, or
  product behavior change requires an upstream specification first.

### Approval and handoff

Approve this document's scope and behavior decisions before execution.
After approval, open a new chat and attach this plan and the Slice 1 source:

```text
/tdd-write-code Execute the approved plan in
@plan/plan-bugfix-security-v1.0.md, starting with Slice 1 RED only.
Use @app/Http/Controllers/Admin/UserManagementController.php,
@app/Services/AdminService.php, @app/Policies/UserPolicy.php,
and @tests/Feature/AdminRoleEscalationTest.php.
Record the failing security assertion and stop at the RED approval checkpoint.
```

Offer to persist this plan, audit location, scope, decisions and pending gates
through `memory-manager` after the user authorizes a checkpoint. Do not modify
the project memory as an implicit side effect of plan creation.
