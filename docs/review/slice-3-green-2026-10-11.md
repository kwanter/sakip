# Slice 3 GREEN: Reserved Super Admin Role Identity

## Execution status

The user approved Slice 3 GREEN and its memory checkpoint on 2026-10-11
(Asia/Makassar). REQ-003, DEC-003 and TASK-103 in
`plan/plan-bugfix-security-v1.0.md` are implemented. The original HTTP exploit
evidence remains in `docs/review/slice-3-red-2026-10-11.md`.

All selected security regressions and functional suites pass. The entire gate
set is **not fully green**: Composer still reports an existing high advisory,
and full-suite runtime exceeds the 15-second target while meeting the 20-second
hard floor. Branch coverage and strict type analysis remain unverified.
Slice 4, accepted-slice commits, pushes and deployment are pending.

## Minimal production change

Only `app/Http/Controllers/Admin/RoleManagementController.php` was modified
for this slice. The controller uses the existing `SystemRoles::SUPER_ADMIN`
constant to enforce these boundaries:

- Non-Super-Admin creation with the reserved name is denied before validation.
- A non-Super-Admin cannot update an existing reserved role or rename another
  role into the reserved name, including changing its guard namespace.
- After valid input, the existing reserved role cannot be renamed away even by
  Super Admin. This direct invariant remains effective despite the global
  Super Admin Gate bypass.
- Non-Super-Admin reserved permission edits are denied before validation.
- The existing reserved-role deletion prohibition and redirect-with-error
  response are preserved.

The Slice 2 permission ceiling remains intact, and authorized Super Admin
permission edits still work. Existing UUIDs, membership associations, ordinary
role lifecycle responses and validation rules are preserved. No new Role policy,
role-ID migration, schema, dependency or Gate/InstansiScope consumer change was
introduced. The direct name invariant is documented in a code comment and in
`docs/ARCHITECTURE.md`.

## RED to GREEN evidence

Immediately before production edits, the original class was rerun and produced
**8 expected security failures, 7 passing controls, 273 assertions**, exit 1.
The failures included the two-request name release/reclaim exploit, which
previously gave an ordinary manager Gate bypass and foreign indicator metadata
without changing its role IDs or explicit permission IDs.

After the patch, all original security assertions pass. Both rename requests
return 403, both role names remain unchanged, the actor stays non-Super-Admin,
the sensitive administrative endpoint stays 403, and foreign indicator access
stays 404. Reserved-role creation, guard mutation and permission set/clear
attempts are also denied without persistence.

No existing assertion was removed or weakened, no fixture authority was changed,
and no source/sink mock or skipped test was introduced in this slice.

## Supplemental controls

Seven additional cases bring `AdminReservedRoleTest` to 22 cases:

- Four requests prove protected mutations are denied before validation: reserved
  creation/claim with an existing duplicate name, a reserved update with a missing
  name, and a reserved permission request containing a missing ID.
- A legitimate Super Admin can update the reserved identity without renaming it,
  preserves its UUID/name/guard, and retains intended foreign-indicator access.
- Authorized Super Admin invalid/duplicate inputs still return 422 rather than
  being rejected by the non-Super-Admin authorization guard.
- Invalid ordinary role creation/update inputs continue returning 422 with the
  original name unchanged.

The original controls also remain green: ordinary rename/lifecycle operations,
reserved deletion prevention for both actor kinds, authorized Super Admin
permission set/clear, and legitimate global indicator access. The other 64
security cases from Slices 1/2 and the existing role-assignment tests pass.

## Verification results

| Check | Result |
| --- | --- |
| Pre-Slice-3 full baseline | 235 passed; 1,898 assertions; 17.14 s |
| Final focused security set | 86 passed; 1,485 assertions; 3.00 s |
| Final full suite | 257 passed; 2,301 assertions; 18.78 s |
| Unit suite | 58 passed; 286 assertions; 1.31 s |
| Coverage run | 257 passed; 2,301 assertions; 25.89 s |
| Slice 3 controller line coverage | 100% |
| All six cumulative changed production files | 100% line coverage each |
| Whole-application line coverage | 13.8%; above the 8.8% ratchet baseline |
| PHP formatting | Pint PASS; 265 files |
| Composer manifest validation | PASS, exit 0 |
| Composer locked audit | FAIL, exit 1; four existing advisories |
| npm production audit | PASS, exit 0; zero vulnerabilities |
| Vite production build | PASS, exit 0; 61 modules; 0.529 s |
| New report/checkpoint Markdown lint | PASS; no new suppressions |
| Diff whitespace check | PASS |
| Branch coverage | Unverified; PCOV present, Xdebug absent |
| Strict type analysis | Unverified; no configured analyzer |

The cumulative covered files are the User and Role management controllers,
both assignment Form Requests, UserPolicy and AdminService. This slice only
adds executable production code to the Role controller. The coverage run
preceded its explanatory comment; executable behavior was identical. The normal
full suite ran afterward. Coverage runtime is instrumented and recorded
separately from the uninstrumented SLA measurement.

No failures, skipped or incomplete tests were reported in successful runs.
Architecture, FloorGuard and tenant-isolation checks are part of the full suite.
Tests use actual HTTP, SQL/Spatie role state, Gate and tenant scope behavior in
disposable SQLite `:memory:` databases.

## Open runtime and dependency gates

The normal full suite is **18.78 s**: above the **15 s target**, below the
**20 s hard floor**. The ten slowest cases total **9.02 s (48.05%)**, primarily
the existing authentication/rate-limit tests. Adding 22 reserved-role cases
increases the suite from 235 to 257 cases; their displayed, rounded durations
total approximately 1.38 s. The pre-slice baseline was already
above the target at 17.14 s. No threshold was lowered, test was omitted, or broad
test optimization was performed. The Unit suite remains below its 10 s target.

The fresh Composer audit reports the same unchanged advisory set:

| Package | Severity | Advisory |
| --- | --- | --- |
| `league/commonmark` | High | `GHSA-3q6v-r5mr-hxv8` |
| `league/commonmark` | Medium | `GHSA-97jj-33gv-5xf9` |
| `laravel/framework` | Low | `GHSA-jh5r-qr3c-85q8` |
| `league/flysystem` | Low | `GHSA-cxf4-7mrp-vvpr` |

Both lockfiles have empty diffs. No new advisory was introduced, but the
zero-known-high target remains unmet. Runtime exploitability was not assessed;
dependency remediation remains separately scoped work. Do not declare every
quality target passed or this application release-ready from these results.

## Commands and source scope

Use `/opt/homebrew/opt/php@8.3/bin/php` (PHP 8.3.35). Every Artisan test/config
command receives `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and
`DB_DATABASE=:memory:`. Composer receives the PHP directory on PATH.

```bash
php artisan test --filter=AdminReservedRoleTest
php artisan test --filter='AdminReservedRoleTest|AdminAccessDelegationTest|AdminProtectedAccountTest|AdminRoleEscalationTest'
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

Starting source is HEAD `c4c091969aef6692237561e50e8ba7a32368fc21` plus the
preserved Slice 1/2 patches. Source, test, architecture and approved checkpoint
edits remain uncommitted. The original plan and `.zcodeignore` are untouched.
No live production verification, release, push or deployment occurred.

## Checkpoint and pending work

- TASK-103: reserved-name and reserved-role mutation protection implemented;
  selected security tests GREEN.
- TASK-109: reserved identity and its public test probes recorded in architecture.
- TASK-110: quality checks executed; runtime target and Composer audit remain
  open as recorded above. Branch/type-analysis limitations remain unverified.
- TASK-111: await explicit approval before Slice 4 RED.
- TASK-112: accepted-slice atomic commits remain pending; no staging or commit.
- Approved checkpoint saved to `.agents/instructions/memory.instructions.md`
  through `memory-manager`. Canonical work-state
  tools are unavailable, so reports and project memory track execution state.
- Keep dependency remediation and test-runtime optimization separate from the
  next institution-ownership slice unless explicitly authorized.
