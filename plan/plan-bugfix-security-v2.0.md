---
goal: Resolve all eight run-2 security audit findings with TDD (2 confirmed, 6 needs-validation)
version: 2.0
date_created: 2026-10-24
last_updated: 2026-10-25
status: "Implemented (slices 1-8; commits 13625c9..ba21dec)"
tags: ["bug-fix", "security", "remediation", "tdd", "tenant-isolation"]
---

## Introduction

![Status: Implemented](https://img.shields.io/badge/status-implemented-green)

This plan resolves the eight findings of security audit **run-2** (standard profile,
completed 2026-10-24, source `c4c091969aef6692237561e50e8ba7a32368fc21` + the
2026-10-11 dirty-tree remediation, byte-verified). It is a remediation contract,
not approval to implement. Every slice proves its finding at a public seam first
(RED), then applies the smallest fix (GREEN).

Audit artifacts (read-only, keep unchanged):
[REPORT.md](/Users/macbook/security-audit-skill/sakip/run-2/REPORT.md) ·
[FINDINGS-DETAIL.md](/Users/macbook/security-audit-skill/sakip/run-2/FINDINGS-DETAIL.md) ·
[NEEDS-VALIDATION.md](/Users/macbook/security-audit-skill/sakip/run-2/NEEDS-VALIDATION.md) ·
`~/security-audit-skill/sakip/run-2/findings.json` (machine-readable records with
full traces, evidence, and validation plans).

**Relationship to `plan/plan-bugfix-security-v1.0.md` (2026-10-11):** v1.0 slices 1–3
are implemented (admin account protection, delegation ceiling, reserved role — the
current dirty tree). This plan **supersedes v1.0 slices 4, 5, and 8** (identical
root causes re-confirmed independently by run-2: `REQ-004`→F2, `REQ-005`→F1,
`REQ-008`→F6) and **absorbs the two candidates v1.0 deferred in RISK-007**
(Excel formula cells → F5, evidence download authorization → F4). v1.0 `REQ-006`
(evidence edit locking) and `REQ-007` (journal symlink containment) are NOT run-2
findings and remain open v1.0 scope — they are not resolved by this document.

Domain labels use **Cakupan Instansi**, **Semua Instansi**, **Instansi Belum
Ditetapkan** per `CONTEXT.md`.

## 1. Requirements & Constraints

| Ref | Audit fingerprint (verdict) | Requirement | Priority |
| --- | --- | --- | --- |
| REQ-101 | `planning.kegiatan-read-unscoped-cross-tenant` (confirmed, medium) | Kegiatan index/show enforce the institution of their Program; foreign rows are absent from lists/search and detail is denied; Super Admin keeps Semua Instansi access. | P0 |
| REQ-102 | `planning.instansi-id-write-unbound-parent` (confirmed, medium) | SasaranStrategis create derives `instansi_id` server-side; update rejects foreign `instansi_id`; Program update binds ownership and same-institution parent. | P0 |
| REQ-103 | `app/Http/Controllers/Admin/PermissionManagementController.php:reserved-permission-name-unprotected` (needs_validation) | Permission create/update/destroy enforce reserved-name protection and in-use integrity so a delegated permission manager cannot convert held authority into reserved authority or cascade-revoke it. | P1 |
| REQ-104 | `evidence-download.legacy-public-disk-fallback` (needs_validation) | Routed evidence download serves from the private disk only; missing local files return 404; the web-exposed public disk is never a serving fallback. | P1 |
| REQ-105 | `ReportGenerationService.generateExcelFile.unsanitized-formula-cells` (needs_validation) | Report Excel export neutralizes leading `= + - @` in title, indicator code, and name cells, matching the repo's own `sanitizeCells` invariant. | P1 |
| REQ-106 | `report-template.catalog-missing-instansi-filter` (needs_validation) | Report template listings and selection expose only active global templates or templates within the viewer's Cakupan Instansi (v1.0 DEC-007 semantics). | P2 |
| REQ-107 | `sakip-dashboard.institutional-branch-cross-tenant-counts` (needs_validation) | Dashboard branch selectors resolve to the registered gates/policy; institutional metrics are instansi-bound for every model, including Target and Assessment counts. | P2 |
| REQ-108 | `docker-compose-prod-render-drift` (needs_validation) | The default compose render contains no development bind mount, no all-interface plaintext HTTP, no dead 443, and no non-functional TLS profile; session hardening variables are explicit. | P2 |

Carried-over constraints from v1.0 that remain binding: **CON-001…CON-009**
(constitution first; preserve route names and response codes — 403/404/422 as
applicable; preserve Super Admin Semua Instansi access and default-deny for
Instansi Belum Ditetapkan; real HTTP/DB/permission/storage in feature tests, no
mocked authorization or scope; minimal edits, no schema migration here; audit log
enabled; disposable databases only; re-reproduce each finding before fixing;
update `docs/ARCHITECTURE.md` for new seams).

### Behavior decisions (become active on plan approval)

- **DEC-101:** Kegiatan tenant checks bind through `program.instansi_id` (Kegiatan
  has no `instansi_id` column — a direct-column `InstansiScope` is wrong). Fix
  `KegiatanPolicy::viewAny/view` (keep the existing create/update/delete methods
  untouched, including their legacy role literals) and add a tenant predicate to
  the controller index query. Tenantless ordinary users default-deny; Super Admin
  (`SystemRoles::SUPER_ADMIN` / `isAdmin()`) bypasses, mirroring `InstansiScope`
  semantics.
- **DEC-102:** SasaranStrategis `store` stops accepting `instansi_id` from the
  payload: validate the remaining fields, then set `instansi_id` from the
  authenticated user (Super Admin may explicitly pass any instansi — preserve
  that authority via an explicit branch). `update` rejects a foreign
  `instansi_id` (`in:<own>` for ordinary actors) instead of silently reparenting.
  `SasaranStrategisPolicy::create` additionally requires a non-null
  `instansi_id`. Program **update** gets the same ownership + same-institution
  `sasaran_strategis_id` binding (v1.0 DEC-004). Program **create** binding is
  deferred behind the `programs.tahun` prerequisite repair (v1.0 DEP-005
  discipline: no bundling; see RISK-806).
- **DEC-103:** Reserved permission names = the seeded administrative vocabulary
  (`manage-users`, `manage-roles`, `manage-permissions`, `admin.dashboard`,
  `admin.settings`, `view-sakip-dashboard`, … the full `RolesAndPermissionsSeeder`
  list, derived from the seeder, not a new hand-typed array) plus route gate
  names (`view-audit-logs`, `debug`, `manage-system-maintenance`). Contract:
  (a) non-Super-Admin cannot create or rename **into** a reserved name;
  (b) rename **away** from a reserved name and deletion of a reserved permission
  are denied for everyone (name-based Gate consumers depend on the identity —
  same rationale as v1.0 DEC-003 for roles); (c) deletion of any permission
  still attached to a role or user is denied (cascade-revocation integrity),
  with an Indonesian error message; (d) `store`/`update`/`destroy` of
  non-reserved permissions by a non-Super-Admin additionally require the actor
  to hold the affected permission (`grantPermissions` ceiling shape), closing
  the rename-own-row-into-unseeded-gate-name variant.
- **DEC-104:** `downloadEvidence` serves from `local` only; a path missing on
  the private disk is 404. No automatic legacy migration in code — legacy
  public-disk files (if any exist on a host) are an operator runbook item
  (RBCK-804). `EvidenceDocument::deleteFile` keeps cleaning both disks.
- **DEC-105:** Port the sibling `sanitizeCells` behavior (leading `= + - @`
  neutralized with a single-quote prefix) into the export path used by
  `generateExcelFile` — preferred: extract the existing
  `ExcelExportService::sanitizeCells` into one shared private helper or service
  method used by both exporters (single owner of the invariant), then apply it
  to the title cell (`ReportGenerationService.php:63`) and the indicator
  code/name cells (`:95-96`). Unit-test via the existing
  `tests/Unit/ExportFormulaInjectionTest.php` seam, extended to cover the
  report-generation path with a dummy `'=1+1'` indicator/report title.
- **DEC-106:** `ReportController::index`/`create` pass the authenticated
  `instansi_id` to `getAvailableTemplates('sakip', $instansiId)`; template
  selection and report `store` validate `template_id` against availability
  (`isAvailableFor`) before use (v1.0 TEST-019 shape). Global (null-instansi)
  templates remain visible to all authorized viewers.
- **DEC-107:** `getDashboardMetrics` selectors use the registered gates
  (`sakip.dashboard.executive`, `sakip.dashboard.data_collector`) **without** a
  model argument so the `SakipDashboardPolicy` abilities resolve, or the policy
  gains those abilities — one owner, agreed during RED. `getInstitutionalMetrics`
  binds `total_targets` and `total_assessments` to `$instansiId` through each
  model's existing tenant-bearing relation (mirroring the `whereHas` used for
  `total_performance_data`). `AddSakipPermissions --fix-all` stops attaching
  `sakip.dashboard.view` to every user (audit hardening: it converts the
  dashboard gate into a no-op).
- **DEC-108:** Compose changes, applied as a set: move the `./:/var/www/html`
  bind mounts (app `:14`, queue `:122`, scheduler `:161`) behind a `dev`
  profile or dedicated override file so the default render is image-only; bind
  the HTTP publish (`:52`) to `127.0.0.1` unless TLS fronting exists; remove the
  dead `${APP_PORT_SSL:-443}:443` publish (`:53`) or implement a real listener —
  removal preferred, the `with-nginx` profile is non-functional (`listen 80` vs
  published 443; `fastcgi_pass app:9000` against a non-FPM image) and is removed
  pending a real TLS design; add `SESSION_SECURE_COOKIE=true` and
  `SESSION_ENCRYPT=true` to the compose environment (and `SESSION_ENCRYPT=true`
  to `.env.docker.example`, aligning it with `.env.example`). No container is
  started as part of verification — `docker compose config` renders only.

## 2. Implementation Steps (one vertical slice at a time)

> [!IMPORTANT]
> For the selected slice: Phase 1 (RED) → verify the security assertion fails for
> the expected reason → approval checkpoint → Phase 2 (GREEN, minimal fix) →
> focused + full gates → approval checkpoint → atomic commit. No batch fixing.

### Phase 1 per slice (Prove-It RED)

| Task | Description | Ref |
| --- | --- | --- |
| TASK-201 | Capture revision, baseline failures, toolchain (PHP 8.3+; see Section 6 commands). | CON-008 |
| TASK-202 | Add the slice's regression + positive controls from Section 6 (real permissions, disposable fixtures, dummy instansi A/B). | All |
| TASK-203 | Record exact input, failing assertion, persisted effect. A 500/422 from an unrelated bug is NOT a valid RED (CON-008). | All |
| TASK-204 | **VERIFY:** focused test red for the right reason; controls green. | — |
| TASK-205 | **APPROVAL:** stop; obtain explicit approval to implement. | — |

### Phase 2 per slice (GREEN)

| Order | Vertical slice | Minimal change | Ref / DEC | Regression file |
| --- | --- | --- | --- | --- |
| 1 | Kegiatan read isolation | `KegiatanPolicy::viewAny/view` tenant checks via program relation; index `whereHas('program', …)` predicate; Super Admin + tenantless semantics per DEC-101 | REQ-101 | `tests/Feature/KegiatanInstitutionIsolationTest.php` |
| 2 | Planning write binding | `SasaranStrategisController::store/update` server-side instansi binding; `SasaranStrategisPolicy::create`; Program update ownership + same-institution parent (DEC-102) | REQ-102 | `tests/Feature/MasterDataInstitutionIsolationTest.php` |
| 3 | Permission lifecycle guards | Reserved-name + in-use + ceiling guards on `PermissionManagementController::store/update/destroy` mirroring `RoleManagementController` (DEC-103) | REQ-103 | `tests/Feature/AdminPermissionLifecycleTest.php` |
| 4 | Evidence download local-only | Remove public fallback in `DataCollectionController::downloadEvidence:58-61` (DEC-104) | REQ-104 | `tests/Feature/EvidenceDownloadLocalOnlyTest.php` |
| 5 | Excel formula neutralization | Shared sanitizer applied in `ReportGenerationService::generateExcelFile` (DEC-105) | REQ-105 | extend `tests/Unit/ExportFormulaInjectionTest.php` |
| 6 | Template catalog tenancy | Pass instansi id at `ReportController:108/:178`; availability validation on selection + store (DEC-106) | REQ-106 | `tests/Feature/ReportTemplateInstitutionIsolationTest.php` |
| 7 | Dashboard gates + counts | Registered-gate selectors; instansi-bound Target/Assessment counts; `--fix-all` gate no-op removal (DEC-107) | REQ-107 | `tests/Feature/SakipDashboardMetricsIsolationTest.php` |
| 8 | Compose render hardening | `docker-compose.yml`, `.env.docker.example` per DEC-108; render-only verification | REQ-108 | `docker compose config` render diff (no PHPUnit) |

Shared tasks: TASK-206 update `docs/ARCHITECTURE.md` when a slice introduces a
seam; TASK-207 run full gates; TASK-208 approval; TASK-209 atomic commit with
only the slice's files.

## 3. Rollback Strategy

- **RBCK-801:** One revertable commit per slice; never reset the tree.
- **RBCK-802:** Reverting an authorization fix restores a confirmed/likely
  vulnerability — keep the endpoint restricted until a corrected patch passes.
- **RBCK-803:** No permission/role data cleanup, no account reassignment, and no
  bulk grant revocation is included. Pre-rollout inventories are read-only.
- **RBCK-804:** Slice 4 must not delete or migrate legacy public-disk files.
  Operators inventory `storage/app/public` first; migration is a separate
  approved procedure. The code fix only stops *serving* from the public disk.
- **RBCK-805:** Slice 8 rollback restores the previous compose file verbatim;
  deployment of compose changes is a separate explicit step, not part of GREEN.

## 4. Dependencies

- **DEP-801:** Existing PHPUnit/Spatie/storage helpers; `Tests\TestCase` seeds and
  clears the permission cache. Create fixture permissions explicitly; do not
  assume the default seeder provisions delegated admin or `sakip.*` families
  (v1.0 DEP-001 carries over).
- **DEP-802:** Run-2's promoted sandbox regression
  (`agents/dynamic-inline/artifacts/regression-1792799853046775000.log` in the
  audit output dir) is the reference RED shape for slices 1–2 — port its
  two-instansi fixture structure into the repo tests.
- **DEP-803:** For slice 5, sandbox PHP lacks the zip extension; the repo's own
  test suite (with zip available) runs the byte-level assertion the audit could
  not (reload exported file, assert cell type/`<f>` absence after fix).
- **DEP-804:** Slice 7 depends on the relation names actually defined on
  `Target`/`Assessment` toward instansi-bearing parents — confirm during RED; if
  no direct relation exists, bind through the documented chain used by their
  controllers.
- **DEP-805:** Read-only owner checks (optional, pre-slice, never blocking):
  inventory non-Super-Admin holders of `manage-permissions`+`admin.dashboard`
  (slice 3 rollout risk); check `public/storage` symlink + legacy prefixes
  (slice 4 context); capture `docker compose config` on the maintained host
  (slice 8 context).

## 5. Files Affected

| Slice | Implementation boundaries | Regression file |
| --- | --- | --- |
| 1 | `app/Policies/KegiatanPolicy.php`, `app/Http/Controllers/Sakip/KegiatanController.php` | `tests/Feature/KegiatanInstitutionIsolationTest.php` |
| 2 | `app/Http/Controllers/Sakip/SasaranStrategisController.php`, `app/Policies/SasaranStrategisPolicy.php`, `app/Http/Controllers/Sakip/ProgramController.php`, `app/Http/Requests/Sakip/ProgramFormRequest.php` (update path only) | `tests/Feature/MasterDataInstitutionIsolationTest.php` |
| 3 | `app/Http/Controllers/Admin/PermissionManagementController.php`, reserved-name source (`RolesAndPermissionsSeeder` list), `app/Policies/UserPolicy.php` (only if the ceiling needs a new ability) | `tests/Feature/AdminPermissionLifecycleTest.php` |
| 4 | `app/Http/Controllers/Sakip/DataCollectionController.php` (`downloadEvidence` only) | `tests/Feature/EvidenceDownloadLocalOnlyTest.php` |
| 5 | `app/Services/ReportGenerationService.php`, `app/Services/Export/ExcelExportService.php` (extract shared helper) | `tests/Unit/ExportFormulaInjectionTest.php` |
| 6 | `app/Http/Controllers/Sakip/ReportController.php`, `app/Services/TemplateService.php` (if contract changes) | `tests/Feature/ReportTemplateInstitutionIsolationTest.php` |
| 7 | `app/Http/Controllers/Sakip/SakipDashboardController.php`, `app/Policies/SakipDashboardPolicy.php`, `app/Providers/AppServiceProvider.php` (gate mapping, only if needed), `app/Console/Commands/AddSakipPermissions.php` | `tests/Feature/SakipDashboardMetricsIsolationTest.php` |
| 8 | `docker-compose.yml`, `.env.docker.example` | render diff in the slice commit message |

`docs/ARCHITECTURE.md` updates accompany any slice that adds a seam.

## 6. Testing Strategy & Edge Cases

### Public-seam regression inventory

| Test ID | Seam / setup | RED assertion + positive controls | Ref |
| --- | --- | --- | --- |
| TEST-301 | `GET /sakip/kegiatan` + `GET /sakip/kegiatan/{foreign}` as verified Data Collector of A (B owns foreign rows with unique markers) | Foreign markers absent from index/search/filter; foreign detail 403/404; own-instansi 200; Super Admin sees both | REQ-101 |
| TEST-302 | Kegiatan sad paths: tenantless user, soft-deleted/missing program parent | Default-deny (empty set / 404), never foreign data; own-instansi unaffected | REQ-101 |
| TEST-303 | `POST /sakip/sasaran-strategis` with foreign `instansi_id` as Executive of A | Denied; no row persisted (assert raw DB); own-instansi create 302; Super Admin explicit-instansi create preserved | REQ-102 |
| TEST-304 | `PUT` own SasaranStrategis with foreign `instansi_id`; Program update with foreign ownership / foreign same-request `sasaran_strategis_id` | Denied; ownership + parent unchanged; same-institution updates succeed (existing `tahun` retained) | REQ-102 |
| TEST-305 | Delegated manager (`admin.dashboard`+`manage-permissions`, no Super Admin): PUT rename held permission → `manage-users` / `view-audit-logs`; DELETE reserved/in-use permission; POST create reserved name | All denied 403 with Indonesian message; authority unchanged (`can()` re-checked after cache flush); ordinary non-reserved rename by an authorized holder succeeds; Super Admin operations preserved | REQ-103 |
| TEST-306 | `GET sakip.evidence.download` with file only on `Storage::fake('public')` | 404; no public-disk bytes streamed; local-disk file downloads 200 with audit row; policy denial for instansi-bound user unchanged | REQ-104 |
| TEST-307 | `generateExcelFile` with report title `=1+1` and indicator name `=HYPERLINK("x")` | Reloaded workbook cells are literal strings (type `s`/quoted), no `<f>` element; sibling export behavior unchanged (existing assertions green) | REQ-105 |
| TEST-308 | `GET sakip.reports.index` with A/B/global/inactive templates; selection + `POST` store with foreign `template_id` as A-bound viewer | B names/descriptions/ids absent; A + global present; inactive absent; foreign selection/submission rejected; global works; Super Admin explicit access preserved | REQ-106 |
| TEST-309 | `GET /sakip/api/dashboard-data` as A-bound non-admin granted the data-entry gate through its **registered** path; two instansi with distinct Target/Assessment rows | `total_targets`/`total_assessments` equal instansi-A counts (not fleet); sibling metrics unchanged; personal branch for ordinary roles; executive branch reachable only via its registered gate; `--fix-all` no longer mass-attaches the dashboard permission | REQ-107 |
| TEST-310 | `docker compose config` default render + `--profile dev` | Default render: no `./:/var/www/html` mounts, no `0.0.0.0` HTTP publish, no 443 mapping, `with-nginx` absent, session flags present; dev profile restores the mount for local work | REQ-108 |

### Quality gates and commands

Same gate set as v1.0 (planned, not evidence): focused `php artisan test
--filter=<SliceTest>`; then Unit suite; then full `php artisan test --profile`;
`./vendor/bin/pint --test`; `composer validate --strict`; `composer audit
--locked`; `npm audit --omit=dev`; `npm run build`; coverage floors per
`CONSTRAINTS.md` (changed files ≥80% line, 75% floor; suite baseline not
regressed). Slice 8 adds `docker compose config` (and `--profile dev`) render
checks; no container start. No new analyzer is introduced.

## 7. Risks & Assumptions

- **RISK-801 (slice 1–2):** Fixing read/write isolation changes list contents
  for currently-overprivileged users. That is the intended contract; do not
  re-broaden access to keep old tests green — update those tests with the
  isolation assertions.
- **RISK-802 (slice 3):** The reserved-name list derived from the seeder must
  not drift; derive it from `RolesAndPermissionsSeeder` (single source) rather
  than a parallel array. Restricting deletes may surprise admins who used
  permission deletion for cleanup; the in-use guard's error message must name
  the attachment reason.
- **RISK-803 (slice 3):** If a deployment already provisioned a delegated
  permission manager, the new guards change what that actor can do (by design).
  Run the DEP-805 read-only inventory before rollout; do not auto-revoke.
- **RISK-804 (slice 4):** If legacy public-disk evidence exists on a host,
  removing the fallback makes those rows 404 through the route until migrated.
  That is safer than serving them; operators migrate per RBCK-804.
- **RISK-805 (slice 7):** Gate-name changes can flip which users see which
  dashboard branch. The registered gates map to `SakipDashboardPolicy`
  abilities; assert each seeded role's branch in RED before changing selectors.
- **RISK-806 (slice 2):** Program **create** tenant binding stays out of scope
  because `ProgramController::store` currently fails on `NOT NULL programs.tahun`
  before any tenant question (run-1 sandbox observation). Fixing that request
  path is a separate prerequisite bugfix with its own RED/GREEN; only then bind
  Program create. Do not bundle (v1.0 DEP-005).
- **RISK-807:** Audit `needs_validation` deployment facts (permission holders,
  legacy files, symlink, maintained compose render) remain owner-observable.
  Code fixes close the source-side gap regardless; the DEP-805 inventories
  calibrate rollout urgency, not whether to fix.
- **RISK-808:** The known-broken `performance_data` write path (nonexistent
  columns) and ~20 dead routes are audit hardening notes, NOT findings; they are
  out of scope here and tracked as backlog. Slice tests must not depend on them.
- **RISK-809:** This plan is the bounded remediation contract for
  `/tdd-write-code`. Larger redesigns (authorization model, TLS architecture,
  Program schema repair) need upstream specification first.

### Approval and handoff

**EXECUTED 2026-10-24** (commits `13625c9` Slice 1, `2370934` Slice 2, `5f7dc37`
Slice 3, `e0af39a` Slice 4, `478c8a2` Slice 5, `6386464` Slice 6, `9e9e1c3`
Slice 7, `d2c0efc` Slice 8, `ba21dec` docs; suite 310 passing). Review follow-ups
continue in `plan/plan-refactor-security-remediation-v1.0.md`.

### Implementation addendum (sanctioned deviations, review SPEC-03/04/06)

- **DEC-102 (SPEC-06):** store implements the server-side binding as a payload
  `Rule::in([own-instansi])` constraint instead of silently overwriting
  `instansi_id`; the tenant guarantee is equivalent (foreign values are
  rejected, own values equal the derived one).
- **DEC-102/RISK-806 (SPEC-03):** `ProgramFormRequest::rules()` is shared by
  store and update, so the instansi binding and same-institution parent rule
  also gate Program **create**. Tightening-only; create still fails first on
  the unrelated `programs.tahun` prerequisite.
- **REQ-107 (SPEC-04):** `SakipDashboardPolicy::viewExecutiveDashboard` and
  `viewDataCollectorDashboard` now resolve via `getAllPermissions()` (fail
  closed, no exception) instead of `hasPermissionTo`, because the registered
  branch gates fire on every dashboard request and the permission names were
  unregistered at the time; they are seeded as of the review follow-up.
- **REQ-107 follow-up (round-2 review, R2-SPEC-03):** `getSystemWideMetrics`
  lifts `InstansiScope` on its aggregates so a non-Super-Admin executive
  holder sees a coherent fleet view; the lift is gated by the executive gate
  (Semua Instansi authority granted explicitly) and institutional/personal
  branches keep the scope.
- **REQ-108 follow-up (round-2 review, R2-SPEC-04):** the obsolete
  `version:` key was also dropped from the base `docker-compose.yml` (same
  root cause as the overlay; compose v2 ignores it with a warning).
