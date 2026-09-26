---
title: "How-To: Configure the Active Reporting Year"
version: 1.0
date_created: 2026-09-26
last_updated: 2026-09-26
status: Active
quadrant: How-to (Diátaxis)
upstream_spec: spec/spec-admin-triage-landing.md (v1.5, REQ-014, REQ-015)
upstream_reference: docs/reference/ref-admin-triage-landing.md (§3.3, §12.3)
living_examples:
  - tests/Unit/Support/ReportingPeriodTest.php (TC-008 … TC-011, TC-071)
  - tests/Unit/Scopes/ForYearTraitTest.php (TC-013)
  - tests/Unit/Services/DashboardDateRangeAdapterTest.php (TC-068)
generated_by: /tdd-generate-docs
---

<!-- markdownlint-disable -->

# How-To: Configure the Active Reporting Year

> **Target audience:** operators and developers deploying or maintaining SAKIP
> **Prerequisites:** a working checkout with PHP 8.3+, `composer install` completed, and write access to the environment file of the target deployment

## 1. Overview & Expected Outcome

SAKIP treats one calendar year as the **active reporting year**. By default it is derived from the server clock. This guide pins it explicitly, so that the whole application — the HQ landing's period labels,
its verification count, and every model using the `forCurrentYear` scope — stays on one deliberate year instead of drifting with the server, for example during a fiscal-year migration or when staging a
historical review.

Expected outcome: `ReportingPeriod::activeYear()` returns the year you configured, the landing renders `Tahun <that year>` for its default period, and no year-scoped query follows the server clock any more.

## 2. Step-by-Step Implementation

### Step 1 — Set `SAKIP_ACTIVE_YEAR`

Add the variable to the deployment's environment (`.env` for local work, the deployment's secret/config store for hosted environments). The value is a whole calendar year:

```dotenv
# .env — the calendar year treated as the active reporting year
SAKIP_ACTIVE_YEAR=2025
```

The key is read once at its single call site; no code change and no call-site update is needed:

```php
// config/sakip.php — inside the existing `reporting` block (no new block is created)
'active_year' => env('SAKIP_ACTIVE_YEAR', null),
```

### Step 2 — Clear the configuration cache

Laravel caches configuration, so the change only takes effect after the cache is rebuilt:

```bash
php artisan config:clear
```

### Step 3 — Verify the pin

Run the seam that owns the decision (S1) — it is a pure unit seam, so it needs no database and finishes in milliseconds:

```bash
php artisan test --filter=ReportingPeriodTest
```

The seam contains the case that proves both the happy path and the type of the result — a configured value is consumed as an integer, never as a string:

```php
/** TC-008 / AC-004 — the config seam is written as the string `env()` really yields. */
public function test_active_year_accepts_the_configured_string_and_returns_an_int(): void
{
    config()->set('sakip.reporting.active_year', '2025');

    $this->assertSame(2025, ReportingPeriod::activeYear());
    $this->assertIsInt(ReportingPeriod::activeYear());
    $this->assertSame(2025, ReportingPeriod::fromKey('current_year')->start->year);
}
```

Then confirm the visible effect: open `GET /admin/dashboard` without a `period` parameter and read the verification figure's basis label — it must render `Tahun 2025`, and the quarter and month labels must
belong to 2025 as well.

## 3. What the Pin Changes

The configuration key has a single reader — the resolver — and its effect reaches every consumer of the active year:

| Consumer | Effect of the pin |
| --- | --- |
| `ReportingPeriod::anchor()` / `activeYear()` | every period key resolves inside the configured year |
| `ForYearTrait::scopeForCurrentYear()` | every model using the trait filters on the configured year instead of `date('Y')` (asserted by TC-013) |
| `SakipDashboardService::getDateRange()` | the agency dashboard's date windows follow the configured year, while keeping its `array{0: Carbon, 1: Carbon}` contract (asserted by TC-068) |
| The HQ landing | the default period label renders `Tahun <year>`, and the period-scoped verification count follows it |
| The sidebar badge | the badge resolves the same period from the request, so it agrees with the landing figure |

## 4. Precedence and Invalid Values

Three rules apply, in this order:

1. **An injected clock always wins.** Any caller that passes a clock to `ReportingPeriod::anchor($now)`, `fromKey()`, `default()` or `activeYear()` gets that clock's year, and the configuration seam is not
   consulted at all (TC-010). Tests rely on this to freeze time without touching configuration.
2. **A valid configured year wins over the server clock** when no clock is injected (TC-011).
3. **Anything else falls back to the server clock.** A malformed value never moves the application to an absurd year (TC-071):

```php
// The rejected shapes, as asserted by TC-071 under a frozen 2026 clock.
foreach (['abc', '0', '-5', '2026.5', '99999'] as $value) {
    config()->set('sakip.reporting.active_year', $value);

    $this->assertSame(2026, ReportingPeriod::activeYear(), "active year for '{$value}'");
}
```

The accepted window is `1970…9999`. A fractional value such as `2026.5` is treated as malformed rather than truncated, which the seam proves by probing it a second time under a 2027 clock — under the
2026 clock alone, "rejected" and "truncated to 2026" would look identical.

## 5. Rollback

Remove the variable (or set it to an empty value) and clear the configuration cache:

```bash
php artisan config:clear
```

The default `null` means "derive the year from the server clock", which reproduces the original behaviour exactly. The key is inert if left in place, so no code rollback is required; no migration, schema
change or persisted state is involved.

## 6. Troubleshooting

| Symptom | Likely cause | Resolution |
| --- | --- | --- |
| The landing still shows the previous year | the configuration cache was not rebuilt | run `php artisan config:clear` |
| The landing shows `Tahun 2026` although `SAKIP_ACTIVE_YEAR=2025` | a caller injected a clock (rule 1 above), or the variable sits in the wrong environment file | check which environment the process reads; injected clocks intentionally win |
| The year silently returned to the server clock | the value is not a whole number inside `1970…9999` (e.g. `2025.0`, `2026.5`, `0`, `-5`, `99999`) | use a four-digit whole number |
| Unit tests behave differently from the running application | the suite never sets the real environment variable; tests pin the year with `config()->set(...)` | assert through `ReportingPeriod` (S1) rather than through the process environment |

> [!NOTE]
> The seam is a trust boundary by design (review finding `STD-A-06`). If you need a different validation policy — for example accepting a fiscal-year label rather than a calendar year — that is a
> specification change: amend `spec/spec-admin-triage-landing.md` §3.1 (REQ-015) and TC-071 first, then change the resolver. The resolver stays the single decision point (see
> `docs/explanation/explanation-triage-landing-decisions.md` §7).
