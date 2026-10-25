# Slice 2 GREEN: Assignment Authority and Permission Ceilings

## Execution status

The user approved Slice 2 GREEN and its memory checkpoint on 2026-10-11
(Asia/Makassar). REQ-002, DEC-002 and TASK-102 of
`plan/plan-bugfix-security-v1.0.md` are implemented. The HTTP RED evidence
remains in `docs/review/slice-2-red-2026-10-11.md`.

All functional security regressions pass. The overall quality-gate set is
**not entirely green**: the Composer audit still reports an existing high
advisory, and the full-suite runtime exceeds the 15-second target while meeting
the 20-second hard floor. Branch coverage and strict type analysis remain
unverified. Slice 3 and accepted-slice commits require the next checkpoint;
nothing was staged, committed, pushed or deployed.

## Minimal implementation and shared owner

- Active User role and direct-permission endpoints now use the existing
  `UpdateRolesRequest` and `UpdatePermissionsRequest`. Their authorization
  requires the corresponding UserPolicy assignment ability and the existing
  protected-target rule before input validation. Existing validation rules and
  Indonesian messages are retained.
- `AdminService::assignRoles()` and `assignPermissions()` additionally authorize
  the appropriate assignment ability, including when called directly or from
  user creation/scalar updates.
- `UserPolicy::grantPermissions()` is the single effective-permission ceiling.
  It is entered through Gate, requires assignment authority for non-Super-Admin
  actors, and rejects permission IDs absent from their direct/inherited grants.
- Role assignment checks the union of permissions conveyed by the requested
  ordinary roles before pivot synchronization or a successful mutation audit.
- Role creation and role-permission edits use the same Gate ability before
  inserting a role or synchronizing its permissions.
- The existing Super Admin Gate bypass and reserved-role filtering are retained,
  as explicitly required by DEC-002. The legacy reserved-role filter remains
  its own behavior; ordinary mixed grants exceeding the ceiling are rejected
  wholesale, with no partial accepted subset.
- Existing User service transactions roll back both identity/account creation
  and access changes on failed delegation. No successful mutation audit remains.
- `docs/ARCHITECTURE.md` records the shared ceiling and public test seams.

No schema, framework, role-ID, tenant-scope, dependency or frontend source
change was introduced. Reserved-role naming/mutation remediation belongs to
Slice 3 and has not started.

## Additional RED evidence before production changes

Before implementation, four direct-service delegation regressions and two
scalar-update rollback regressions were added. Both invalid-ID HTTP validation
controls also passed. The pre-GREEN focused run had **23 expected security
failures and 36 passes, 965 assertions**, exit 1.

These six additional failures showed that fixing the controller alone would
leave direct service calls and combined scalar/access updates exposed. The
production patch was written only after observing these failures.

## Fixture and characterization corrections

Two existing positive fixtures now model legitimate role assigners by adding
`manage-roles`: the Slice 1 ordinary-account creation/read case and the existing
Admin reserved-role escalation case. Their original assertions were preserved.
The new Slice 2 tests independently prove that actors without assignment
authority cannot grant even permissions already in their effective set.
Ordinary account creation without roles still works without `manage-roles`.

The new ordinary-role lifecycle control initially expected an update redirect
to the detail page. Source at HEAD already redirected to the role index.
The expectation was corrected to that original contract; production redirect
behavior was not changed. This was a characterization expectation error,
not evidence of a vulnerability or a reason to weaken a security assertion.

## Verified behavior

The new class contains 39 cases; together with Slice 1 and the existing
reserved-role tests, the focused set contains 64 cases. It verifies:

- Assignment authority independently of the permission ceiling, for self and
  another target, through direct permissions and role membership.
- Whole-request rejection of ordinary mixed grants with absent permissions;
  original role/direct-permission IDs and successful audit counts stay unchanged.
- Sensitive endpoints remain forbidden after rejected grants, cache clearing
  and a fresh authenticated HTTP request.
- Allowed direct and inherited permission subsets remain assignable; subsequent
  real HTTP access demonstrates their effect.
- Role creation and permission edits cannot manufacture authority absent from
  the actor, including edits affecting the actor's own role.
- Super Admin retains delegation beyond its explicit permission records.
- Authorized empty User role/permission arrays remove only the requested access;
  allowed role-permission removal revokes the member's previously working access.
- Direct service calls enforce both controls, and scalar User updates roll back
  when an included role/permission grant fails.
- User creation with unauthorized roles leaves no account, grants or successful
  creation/assignment audit; plain creation remains available to user managers.
- Authorized role lifecycle and response formats remain supported. The existing
  reserved-role deletion prohibition is preserved as a characterization control.
- Invalid assignment IDs still return 422 without mutation. A protected target
  is denied with 403 before validation, even for an authorized role assigner.

All tests use real HTTP, SQL/Spatie grants, authorization and audit behavior.
Every Artisan test uses a disposable SQLite `:memory:` database. No
authorization/sink mock, skipped test, suppression or weaker assertion was added.

## Verification results

| Check | Result |
| --- | --- |
| Pre-Slice-2 full baseline | 196 passed; 1,149 assertions; 14.06 s |
| Final focused tests | 64 passed; 1,082 assertions; 2.53 s |
| Final full suite | 235 passed; 1,898 assertions; 17.40 s |
| Unit suite | 58 passed; 286 assertions; 1.49 s |
| Coverage run | 235 passed; 1,898 assertions; 23.77 s |
| Changed production file line coverage | 100% for all six files |
| Whole-application line coverage | 13.6%; above the 8.8% ratchet baseline |
| PHP formatting | Pint PASS; 264 files |
| Composer manifest validation | PASS, exit 0 |
| Composer locked audit | FAIL, exit 1; four existing advisories |
| npm production audit | PASS, exit 0; zero vulnerabilities |
| Vite production build | PASS, exit 0; 61 modules; 0.556 s |
| Markdown lint for new reports and checkpoint | PASS; no new suppressions |
| Diff whitespace check | PASS |
| Branch coverage | Unverified; PCOV present, Xdebug absent |
| Strict type analysis | Unverified; no configured analyzer |

The six changed production files with 100% line coverage are:

- `app/Http/Controllers/Admin/UserManagementController.php`
- `app/Http/Controllers/Admin/RoleManagementController.php`
- `app/Http/Requests/Admin/UpdateRolesRequest.php`
- `app/Http/Requests/Admin/UpdatePermissionsRequest.php`
- `app/Policies/UserPolicy.php`
- `app/Services/AdminService.php`

Architecture, FloorGuard and tenant-isolation checks are included in the passing
full suite. No failures, skipped or incomplete tests were reported in these
successful runs. Coverage runtime is instrumented and recorded separately from
the normal full-suite runtime used by the SLA gate.

## Runtime investigation and dependency gate

The uninstrumented full-suite run is **17.40 s**: above the **15 s target**, below
the **20 s hard floor**. Its ten slowest tests consume **8.91 s (51.17%)**, still
dominated by the existing rate-limit/authentication cases. The 39 newly added
delegation cases total approximately **3.22 s** when summing their displayed,
rounded case durations. That explains most of the **3.34 s** increase against
the 196-case pre-Slice-2 baseline. The Unit runtime remains below its 10 s target.
No quality threshold was lowered, and no broad test optimization was attempted.

The fresh Composer audit reports the same unchanged dependencies:

| Package | Severity | Advisory |
| --- | --- | --- |
| `league/commonmark` | High | `GHSA-3q6v-r5mr-hxv8` |
| `league/commonmark` | Medium | `GHSA-97jj-33gv-5xf9` |
| `laravel/framework` | Low | `GHSA-jh5r-qr3c-85q8` |
| `league/flysystem` | Low | `GHSA-cxf4-7mrp-vvpr` |

The lockfiles have empty diffs. This slice introduces no new advisory, but the
zero-known-high dependency target remains unmet. Runtime exploitability was not
assessed. Dependency updates and framework upgrades remain outside this bounded
slice and require separately scoped remediation.

## Commands and evidence scope

Use `/opt/homebrew/opt/php@8.3/bin/php` (8.3.35). Every Artisan command runs with
`APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:`. Composer
receives the PHP directory on PATH. Commands executed include:

```bash
php artisan test --filter='AdminAccessDelegationTest|AdminProtectedAccountTest|AdminRoleEscalationTest'
php -d pcov.enabled=1 artisan test --coverage --profile
php artisan config:clear
php artisan test --profile
php artisan test --testsuite=Unit
php vendor/bin/pint --test --no-interaction
composer validate --strict --no-check-all
composer audit --locked --format=json
npm audit --omit=dev
npm run build
```

Source starts at HEAD `c4c091969aef6692237561e50e8ba7a32368fc21` plus the preserved
uncommitted Slice 1 patch. No production or dependency deployment verification
was performed. Tests prove the local disposable behavior, not live exposure.

## Checkpoint and pending work

- TASK-102: delegation authority and effective ceilings implemented, tests GREEN.
- TASK-109: shared authorization/test seams documented; no new runner required.
- TASK-110: checks executed; runtime target missed and Composer audit failed as
  recorded above. Do not declare the complete quality-gate set passed.
- TASK-111: await explicit approval before Slice 3 RED.
- TASK-112: accepted-slice atomic commits remain pending; no staging or commit.
- Approved checkpoint saved to `.agents/instructions/memory.instructions.md`
  through `memory-manager`. Canonical work-state
  tools are unavailable, so reports and project checkpoints track current state.
- Preserve `.zcodeignore`, the original remediation plan, and all existing
  worktree edits. Do not start reserved-role remediation or dependency upgrades
  as an implicit side effect of this checkpoint.
