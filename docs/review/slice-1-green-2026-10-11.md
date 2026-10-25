# Slice 1 GREEN: Protected Super Admin Accounts

## Execution status

The user approved Slice 1 GREEN after the credential RED checkpoint and asked
execution to continue on 2026-10-11 (Asia/Makassar). REQ-001, DEC-001,
TEST-001, TEST-002, and TASK-101 of `plan/plan-bugfix-security-v1.0.md`
are implemented. The original credential evidence remains in
`docs/review/slice-1-red-2026-10-11.md`.

Security regressions and functional checks are GREEN. The Composer dependency
audit is **not green** because unchanged dependencies have existing advisories,
including one high-severity advisory. Branch coverage and strict type analysis
remain unverified. This is not completion of the entire remediation plan or
authorization to start Slice 2, commit, push, or deploy.

## Root cause and surgical fix

The active User controller previously checked route-level administrative
permissions without protecting an existing Super Admin target. The service
also allowed credential changes, deletion, and access synchronization directly.
The existing reserved-role grant filter addressed a different boundary.

`UserPolicy::manageProtectedAccount()` now owns the shared target restriction:
an existing Super Admin account may be mutated only by a Super Admin actor.
It is a target restriction, not replacement for route-level permissions or
the assignment authority and permission ceiling planned for Slice 2.

- `UserPolicy::update()` and `delete()` incorporate the target restriction.
- Active controller update, deletion, role and permission mutation endpoints
  authorize their target before processing input or calling the service.
- The four `AdminService` mutators authorize the same target before hashing,
  persistence, deletion, pivot synchronization, or successful audit writes.
- Successful response formats and route names are preserved. The controller's
  existing self-deletion error remains intact.
- `docs/ARCHITECTURE.md` records the shared policy and regression seams.

Production changes are limited to `UserPolicy`, `AdminService`, and
`Admin/UserManagementController`. No migration, dependency, role-ID, framework,
tenant-scope, frontend source, or assignment-authority redesign was introduced.

## Expanded RED evidence before implementation

Additional tests were written and executed before the production patch.
The expanded run had **12 expected failures and 5 passing controls**:

- The original four credential/identity/login security assertions failed.
- DELETE and role/permission replacement returned 302 instead of 403.
- Four direct service calls accepted the unauthorized Super Admin target.
- An invalid protected update reached input validation (422) instead of being
  rejected at the authorization boundary (403). This boundary-order test is
  supplemental; it is not the credential exploit evidence.

The authorized deletion, ordinary-account management, and Super Admin access
controls passed before implementation. After the patch, all regressions passed.

## Regression and positive-control coverage

`AdminProtectedAccountTest` contains 23 cases, including four service datasets.
It checks:

- Denied HTTP updates, preserved password hash and identity/institution,
  unchanged reserved role, no successful mutation audit, and failed replacement
  login after explicitly ending the manager session.
- Denied deletion, role removal, direct permission replacement, and direct
  service bypass attempts; persisted target and access remain intact.
- Authorization before input validation for a protected target.
- Authorized ordinary-account creation, reads, updates, deletion and access
  assignment, plus preserved self-deletion protection.
- Authorized Super Admin credential updates, another Super Admin's deletion,
  and another Super Admin's role/direct permission changes.
- Existing ordinary ownership and separate administrative policy contracts,
  and ordinary service updates without a replacement password.

Tests use real HTTP middleware, login, authorization, SQL persistence, Spatie
roles/permissions, and audit writes. No vulnerable sink or authorization mock
is used. All data is disposable SQLite `:memory:` data.

## Fixture failure investigation

The first post-patch focused run passed, but the first full-suite attempt failed
all new cases with `PermissionAlreadyExists` for `admin.dashboard`. Laravel's
`RefreshDatabase` restores the initial in-memory PDO baseline between cases;
the earlier seeded classes had already created these permissions. Setting
`$seed = false` prevents seeding from this class but does not erase an existing
suite baseline.

The fixture now uses `Permission::findOrCreate()` and `Role::findOrCreate()`
for the two existing administrative permissions and the reserved role. Actors
and targets are still created explicitly, verified, and checked for their
exact privileges. Both focused and seeded full-suite runs then passed.
No seeder, framework, or production workaround was needed.

## Verification results

| Check | Result |
| --- | --- |
| Pre-change full baseline | 173 passed; 818 assertions; 12.69 s |
| Final focused regression and sibling role tests | 25 passed; 333 assertions |
| Final full suite without coverage overhead | 196 passed; 1,149 assertions |
| Full-suite runtime | 13.74 s; below the 15 s target |
| Unit suite | 58 passed; 286 assertions; 1.71 s |
| Coverage run | 195 passed; 1,133 assertions; 17.73 s |
| Changed production file line coverage | 100% for all three files |
| Whole-application line coverage | 13.0%; above the 8.8% ratchet baseline |
| Laravel Pint | PASS; 263 PHP files |
| Composer manifest validation | PASS |
| Composer locked dependency audit | FAIL; four existing advisories |
| npm production dependency audit | PASS; zero vulnerabilities |
| Production Vite build | PASS; 61 modules transformed |
| Markdown lint for new logs | PASS; no rule suppressions |
| Diff whitespace check | PASS |
| Branch coverage | Unverified; PCOV present, Xdebug absent |
| Strict type analysis | Unverified; no configured analyzer |

The coverage run preceded the final additional ordinary-access HTTP control;
production code was identical. The final suite includes that passing control.
No failed, skipped, or incomplete tests were reported in the successful runs.
Architecture and tenant-isolation guards are included in the passing full suite.

Commands use `/opt/homebrew/opt/php@8.3/bin/php` (PHP 8.3.35). Every Artisan
test/config command runs with `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and
`DB_DATABASE=:memory:`. The key commands are:

```bash
php artisan test --filter='AdminProtectedAccountTest|AdminRoleEscalationTest'
php artisan config:clear
php artisan test --profile
php artisan test --testsuite=Unit
php -d pcov.enabled=1 artisan test --coverage --profile
php vendor/bin/pint --test --no-interaction
composer validate --strict --no-check-all
composer audit --locked --format=json
npm audit --omit=dev
npm run build
```

PHP's absolute directory is added to PATH for Composer. The final standalone
Composer audit returned exit 1; the earlier combined shell invocation's exit 0
came from the following successful npm audit and is not a Composer pass.

## Existing dependency advisories

The unchanged lockfile contains four advisories across three packages:

| Package | Severity | Advisory |
| --- | --- | --- |
| `league/commonmark` | High | `GHSA-3q6v-r5mr-hxv8` |
| `league/commonmark` | Medium | `GHSA-97jj-33gv-5xf9` |
| `laravel/framework` | Low | `GHSA-jh5r-qr3c-85q8` |
| `league/flysystem` | Low | `GHSA-cxf4-7mrp-vvpr` |

The high advisory concerns quadratic table parsing in CommonMark's GFM table
extension. No advisory was introduced by this slice: both dependency lockfiles
have empty diffs against the starting revision. Runtime exploitability was not
assessed, and no dependency upgrade was performed because the approved plan
excludes framework/dependency upgrades. The zero-known-high dependency target
therefore remains unmet; do not describe the entire quality gate set as passed.

## Checkpoint and pending work

- TASK-001 through TASK-005: Slice 1 RED evidence recorded and GREEN approved.
- TASK-101: Slice 1 production protection implemented and security tests GREEN.
- TASK-109: Slice 1 architecture seam documented; no new CI runner required.
- TASK-110: Checks executed; dependency audit failed as recorded above.
- TASK-111: Await explicit approval before starting Slice 2.
- TASK-112: No commit yet; accepted-slice commit remains pending.
- Dependency remediation, branch tooling and strict analyzer setup require
  separately scoped work. Production verification and release were not run.

The user's pre-existing `.zcodeignore` and original remediation plan are
preserved. No files were staged, no commit was created, and nothing was pushed
or deployed. The requested canonical work-state tools remain unavailable;
this report records the current execution state.
