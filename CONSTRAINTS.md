# Project Quality Constraints (`CONSTRAINTS.md`)

> **Project:** SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah)
> **Last Calibrated On:** 2026-09-24

<!-- markdownlint-disable -->

## 1. Quality Thresholds

| Metric | Target Threshold | Hard Floor (CI Failure) | Enforcement Tool |
| --- | --- | --- | --- |
| PHPUnit suite (Unit + Feature) | 100% pass | 100% pass | `php artisan test` |
| Unit test suite runtime (SLA) | < 10.0s | < 20.0s (investigate before merge) | `php artisan test --testsuite=Unit` |
| Full suite runtime (SLA) | < 15.0s | < 20.0s (investigate before merge) | `php artisan test --profile` (its `Duration:` line) |
| Line coverage — **changed files** (diff scope) | >= 80% | >= 75% | `php artisan test --coverage` — read the per-file rows for the files a change touches |
| Line coverage — whole `app/` (**ratchet**) | never below the recorded baseline | never below the recorded baseline | `php artisan test --coverage` (its `Total:` row) |
| Branch coverage | >= 75% | >= 70% | `php artisan test --coverage` — **unverified**: PCOV reports line metrics only and Xdebug is not installed |
| Feature regression coverage | All changed behavior covered | No behavior change without a focused regression test | PHPUnit |
| PHP formatting | 0 violations in changed PHP files | 0 formatter errors | `./vendor/bin/pint --test --no-interaction` |
| Architecture guards | `ArchitectureGuardTest` green | No guard regressions | PHPUnit (`tests/Unit/ArchitectureGuardTest.php`) |
| Tenant isolation | `InstansiScope` intact on instansi-scoped models | No unscoped read/write outside an authorized path covered by a test | PHPUnit (`tests/Feature/TargetTenantIsolationTest.php`, `tests/Feature/EvidenceUpdateIsolationTest.php`) |
| Dependency security | 0 known high/critical advisories | No new high/critical findings | `composer audit --locked` and `npm audit --omit=dev` |
| Frontend asset build | Production build succeeds | Build failure blocks merge | `npm run build` |
| Database safety | Migrations apply cleanly and rollback where supported | No destructive or data-losing migration without explicit approval and a tested recovery plan | Laravel migrations / tests |

> [!IMPORTANT]
> **Measured baselines (2026-09-26).** Unit suite **1.34 s** (SLA met with a 7× margin), full suite **12.21–12.37 s** (151 tests, 0 skipped, 731 assertions), line coverage **8.8 %** over `app/` and **91.8–100 %** for the admin-triage slice — commands and per-file rows in `docs/retro/retro-admin-triage-landing-2026-09-26.md` §0–§1. The ratchet row may only be **raised** when the measured total rises; it may never be lowered to accommodate a regression.

> [!CAUTION]
> **A5 decision record (product owner, 2026-09-26).** Line coverage is now measured in **two scopes**. The bar for **changed** code is unchanged (>= 80 % target / >= 75 % floor); the whole-`app/` figure is governed by a ratchet instead of an absolute floor. This supersedes the former single ">= 75 % over the whole suite" row, which had been reported *unverified* for four sessions and is unmeetable without a project-wide legacy campaign (99 of ~132 instrumented files sit at 0.0 %; PCOV *is* installed, so the number was never unknown — it is 8.8 %). This is a **scope change, not a lowering of the bar for changed code**; `CONSTRAINTS.md` §3 rule 5 still forbids lowering any threshold to conceal a regression. **A6 decision record (same date):** the full-suite runtime SLA above mirrors the Unit-suite shape and is set against the measured 12.21–12.37 s baseline. Branch coverage stays **unverified** until Xdebug is installed.

## 2. Verification Commands

Run the focused test first, then the complete applicable quality gates (mirrors `.github/workflows/ci.yml`):

```bash
# 1. Focused test for the changed behavior
php artisan test --filter=AffectedBehaviorTest

# 2. Full quality gates
composer validate --strict --no-check-all
./vendor/bin/pint --test --no-interaction
php artisan config:clear && php artisan test --profile
composer audit --locked
npm audit --omit=dev
npm run build
```

`--profile` is part of the gate set on purpose: it prints the ten slowest tests beside the totals, so a runtime regression is visible in the same run that proves correctness (retro 2026-09-26, action A3).

**PHP binary resolution.** `php` must resolve to PHP 8.3+ before any gate above can run. Non-interactive shells (agent tooling, CI hooks) do not source `~/.zshrc`, so a working interactive shell is not proof that `php` is on `PATH` for the gate commands. Resolve it in one of three ways, in order of preference:

1. `make gates-local` (or `make test-local` / `make lint-local`) — the Makefile resolves the binary itself (`PHP ?= $(shell command -v php 2>/dev/null || echo /opt/homebrew/opt/php@8.3/bin/php)`), so it works in any shell.
2. Use the absolute path `/opt/homebrew/opt/php@8.3/bin/php` in place of `php`.
3. `export PATH="/opt/homebrew/opt/php@8.3/bin:$PATH"` in the shell that runs the gates.

`make php-version` prints the resolved binary. If `command -v php` is empty and the absolute path does not exist, the PHP gates are **unverified**, never passed (`CONSTRAINTS.md` §2 honesty rule below).

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
