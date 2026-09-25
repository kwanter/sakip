# Project Quality Constraints (`CONSTRAINTS.md`)

> **Project:** SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah)
> **Last Calibrated On:** 2026-09-24

<!-- markdownlint-disable -->

## 1. Quality Thresholds

| Metric | Target Threshold | Hard Floor (CI Failure) | Enforcement Tool |
| --- | --- | --- | --- |
| PHPUnit suite (Unit + Feature) | 100% pass | 100% pass | `php artisan test` |
| Unit test suite runtime (SLA) | < 10.0s | < 20.0s (investigate before merge) | `php artisan test --testsuite=Unit` |
| Line coverage (Unit + Feature) | >= 80% | >= 75% | `php artisan test --coverage` (requires Xdebug/PCOV; CI currently runs `coverage: none`) |
| Branch coverage | >= 75% | >= 70% | `php artisan test --coverage --min=70` (coverage driver required) |
| Feature regression coverage | All changed behavior covered | No behavior change without a focused regression test | PHPUnit |
| PHP formatting | 0 violations in changed PHP files | 0 formatter errors | `./vendor/bin/pint --test --no-interaction` |
| Architecture guards | `ArchitectureGuardTest` green | No guard regressions | PHPUnit (`tests/Unit/ArchitectureGuardTest.php`) |
| Tenant isolation | `InstansiScope` intact on instansi-scoped models | No unscoped read/write outside an authorized path covered by a test | PHPUnit (`tests/Feature/TargetTenantIsolationTest.php`, `tests/Feature/EvidenceUpdateIsolationTest.php`) |
| Dependency security | 0 known high/critical advisories | No new high/critical findings | `composer audit --locked` and `npm audit --omit=dev` |
| Frontend asset build | Production build succeeds | Build failure blocks merge | `npm run build` |
| Database safety | Migrations apply cleanly and rollback where supported | No destructive or data-losing migration without explicit approval and a tested recovery plan | Laravel migrations / tests |

## 2. Verification Commands

Run the focused test first, then the complete applicable quality gates (mirrors `.github/workflows/ci.yml`):

```bash
# 1. Focused test for the changed behavior
php artisan test --filter=AffectedBehaviorTest

# 2. Full quality gates
composer validate --strict --no-check-all
./vendor/bin/pint --test --no-interaction
php artisan config:clear && php artisan test
composer audit --locked
npm audit --omit=dev
npm run build
```

Optional coverage gate (requires a coverage driver such as Xdebug or PCOV):

```bash
php artisan test --coverage --min=75
```

For local environments without a PHP toolchain, use the Docker stack instead — the same commands run inside the app container:

```bash
make up
make test    # docker-compose exec app php artisan test
make lint    # docker-compose exec app ./vendor/bin/pint --test
```

If `vendor/` is missing, run `composer install` before claiming any PHP check passed. Do not claim a check passed unless its command completed successfully. If a gate is blocked by network, registry, or toolchain availability, report it as **unverified** rather than treating it as passed.

## 3. Floor-Guard Anti-Cheat Rules

Agents and contributors must not:

1. Add suppressions such as `@phpstan-ignore`, `@psalm-suppress`, `phpcs:ignore`, `// @noinspection`, `// @ts-ignore`, or `eslint-disable` to hide genuine errors.
2. Remove or weaken existing PHPUnit assertions to make a test pass without an approved behavior change.
3. Add skipped or bypassed tests (`markTestSkipped`, `@skip`, `$this->markTestIncomplete`, or commented-out suites) to dodge failures.
4. Write tautological tests that repeat the implementation's own calculation instead of asserting independent expected outcomes.
5. Lower these constraints, disable CI jobs, or change `phpunit.xml` / CI configuration to conceal a regression.
6. Weaken tenant isolation (removing `InstansiScope`, hardcoding `instansi_id`, or broadening a query scope) to make a test pass.
7. Disable, silence, or bypass `AuditLog` writes to simplify a test or a workflow.
8. Introduce destructive database changes without explicit approval and a verified recovery plan.

Violations of these rules block completion until corrected.
