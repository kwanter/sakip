---
title: "How-To: Add a Queue Figure to the Triage Landing"
version: 1.0
date_created: 2026-09-26
last_updated: 2026-09-26
status: Active
quadrant: How-to (Diátaxis)
upstream_spec: spec/spec-admin-triage-landing.md (v1.5, §4.2–§4.4, §6.1 seams S2/S3)
upstream_reference: docs/reference/ref-admin-triage-landing.md (§4, §5, §8, §11)
living_examples:
  - app/Services/AdminTriageService.php (the three delivered count forms and their URL builders)
  - resources/views/admin/dashboard.blade.php (the delivered figure array)
  - tests/Unit/Services/AdminTriageServiceTest.php (TC-015, TC-025)
  - tests/Feature/AdminTriageLandingTest.php (TC-042, TC-043, TC-044, TC-052)
generated_by: /tdd-generate-docs
---

<!-- markdownlint-disable -->

# How-To: Add a Queue Figure to the Triage Landing

> **Target audience:** developers extending the triage landing with a further approval queue
> **Prerequisites:** familiarity with the delivered slice — read `docs/reference/ref-admin-triage-landing.md` first; a working test toolchain (`php artisan test` runs)

## 1. Overview & Expected Outcome

The landing renders exactly three figures today, one per approval queue: Antrean Verifikasi, Antrean Asesmen, Antrean Laporan. This guide adds a **fourth** figure — for example an anomaly queue —
following the same four-step shape the delivered figures use: a count at seam S2, a `route()`-built deep link, a rendered anchor with its own handle and basis label at seam S3, and a re-measured query budget.

Expected outcome: the new figure renders inside `data-triage-region`, counts only the population it declares, deep-links into a target that can honour every parameter it sends, and the query budget is
asserted again with an exact value per viewer state.

## 2. Before You Start — the Contracts You Must Extend

| Constraint | Why it matters here |
| --- | --- |
| **Amend the Spec first** | Spec is the executable truth: add the requirement, its acceptance criteria and the seam cases before writing code. A figure that exists only in code is a contract no future reader can find |
| One period vocabulary only | `ReportingPeriod::KEYS` is closed. A new figure either uses the selected period or carries `Tidak dibatasi periode`; it never introduces a second notion of "current" |
| One count implementation | The figure and any badge or secondary surface must share one query, as `summaryFor()` and `verificationCountFor()` do (TC-019) |
| Deep links are `route()`-built | never by string concatenation, and never with a parameter the target does not honour |
| Tenancy stays explicit | agency coverage is applied by the read model (`instansi_id` or the parent constraint), never by bypassing `InstansiScope` |
| The budget is asserted exactly | a new figure adds at least one statement per rendered state: TC-052 must be updated in the same change, not left to rot |

## 3. Step-by-Step Implementation

### Step 1 — RED at S2: declare the count and the link

Add the case to `tests/Unit/Services/AdminTriageServiceTest.php`. There is intentionally **no `actingAs()`** at this seam: the service receives the viewer explicitly, and the ambient `InstansiScope` is a
no-op without an authenticated user. Assert the population against the fixture matrix before asserting the count, exactly as the delivered cases do — a fixture defect must fail in the precondition, never
inside a count. The link assertion uses the same shape as TC-025:

```php
// The delivered pattern (TC-025) — a new figure asserts its own target route and parameters.
$this->assertSame(
    route('sakip.reports.index', ['status' => 'submitted']),
    $yearly->reportUrl,
);
$this->assertStringNotContainsString('period=', $yearly->reportUrl);
```

Run the seam and confirm it fails for the right reason (the field or the method does not exist yet):

```bash
php artisan test --filter=AdminTriageServiceTest
```

### Step 2 — GREEN at S2: one count, one URL

Add the count to `app/Services/AdminTriageService.php` using the only permitted forms. The delivered code shows all three shapes — an exact `YYYY-MM` range for the period-scoped queue, a parent
constraint for a queue without its own agency column, and a plain instansi-scoped count:

```php
// Verification: the only period-scoped queue (index-backed by idx_perf_data_instansi_period).
return PerformanceData::query()
    ->whereBetween('period', $period->performancePeriodRange())
    ->submitted()
    ->when($instansiId !== null, fn (Builder $query) => $query->where('instansi_id', $instansiId));

// Assessment: no instansi_id of its own, so coverage travels through the parent row.
$query = Assessment::query()->pending();

return $query->whereHas(
    'performanceData',
    fn (Builder $parent) => $parent->where('performance_data.instansi_id', $instansiId),
);

// Report: a plain instansi-scoped count.
return Report::query()
    ->submitted()
    ->when($instansiId !== null, fn (Builder $query) => $query->where('instansi_id', $instansiId));
```

Add the URL builder next, with an explicit statement of which parameters the target honours:

```php
// The only target whose filter can express the selection exactly, so only it ever gets `period`.
private function verificationUrl(ReportingPeriod $period): string
{
    $parameters = ['validation_status' => self::VERIFICATION_FILTER_VALUE];

    if ($period->isSingleMonth()) {
        $parameters['period'] = $period->performancePeriodRange()[0];
    }

    return route('sakip.data-collection.index', $parameters);
}
```

Then extend the read-model contract: add the count and the URL fields to `App\Support\AdminTriageSummary` and populate them in `summaryFor()`. Re-run the S2 filter until green.

### Step 3 — RED at S3: declare how the figure renders

Add the case to `tests/Feature/AdminTriageLandingTest.php`. The delivered cases give you the assertions to copy: the handle appears exactly once (TC-042), the name renders once inside its own anchor
(TC-043), the basis label sits inside that anchor and a period label never leaks into a period-independent one (TC-044):

```php
// A period-independent anchor never claims the selected period (TC-044 pattern).
foreach (['assessment', 'report'] as $handle) {
    $anchor = $this->figureAnchor($content, $handle);

    $this->assertNotSame('', $anchor, "anchor {$handle} is extractable");
    $this->assertStringContainsString('Tidak dibatasi periode', $anchor, "basis of {$handle}");
    $this->assertStringNotContainsString('Triwulan II 2026', $anchor, "period label leaked into {$handle}");
}
```

Run the S3 filter and record which assertions fail — the handle and the name must fail until Step 4 renders them:

```bash
php artisan test --filter=AdminTriageLandingTest
```

### Step 4 — GREEN at S3: one row in the figure array

The blade renders figures from a single array, so a new figure is one row. The delivered rows are the template; add yours with a unique handle, the canonical Indonesian name, its count and its basis:

```blade
{{-- resources/views/admin/dashboard.blade.php — one row per queue, in render order --}}
@foreach ([
    ['handle' => 'verification', 'name' => 'Antrean Verifikasi', 'count' => $summary->verificationCount, 'basis' => $summary->periodLabel, 'url' => $summary->verificationUrl],
    ['handle' => 'assessment', 'name' => 'Antrean Asesmen', 'count' => $summary->assessmentCount, 'basis' => \App\Support\AdminTriageSummary::PERIOD_INDEPENDENT_BASIS_LABEL, 'url' => $summary->assessmentUrl],
    ['handle' => 'report', 'name' => 'Antrean Laporan', 'count' => $summary->reportCount, 'basis' => \App\Support\AdminTriageSummary::PERIOD_INDEPENDENT_BASIS_LABEL, 'url' => $summary->reportUrl],
] as $figure)
```

Use `PERIOD_INDEPENDENT_BASIS_LABEL` rather than a literal whenever the new queue has no honest period anchor. The anchor markup itself needs no change — the loop already applies the handle, the
`.stat-value`, the name and the basis to every row. Re-run the S3 filter until green.

Two adjacent surfaces also key off the three counts and must include the new queue:

- **The attention strip** renders one signal per non-empty queue (each with exactly one named action). Add your signal row beside the three delivered ones, otherwise a non-empty new queue is silently
  absent from the strip — and a zero-count queue must still render no signal at all.
- **The empty-state condition** must include the new count, otherwise a page whose only pending work is in the new queue renders the sentence `Belum ada pekerjaan tertunda pada periode ini.` while a
  figure above it shows a non-zero count.

### Step 5 — Re-baseline the query budget

A new count adds one statement to every rendered state, so TC-052 must be updated in the same change. Its shape is deliberate: the exact value per state *and* the ceiling, so one added or removed statement
fails the case instead of rotting silently:

```php
// The delivered assertions (TC-052 / AC-034 / CON-003), before a fourth figure.
$this->assertSame(5, $counts['cross-agency'], '3 counts + 1 badge query + 1 recent-activity list');
$this->assertSame(6, $counts['agency-bound'], '3 counts + 1 agency-name lookup + 1 badge query + 1 recent-activity list');
$this->assertSame(1, $counts['unassigned'], '0 counts (short-circuit, badge included) + 1 recent-activity list');

foreach ($counts as $state => $count) {
    $this->assertLessThanOrEqual(6, $count, "the ceiling was exceeded for {$state}");
}
```

Measure rather than predict: raise each exact value by the number of statements your change actually issues, and re-check whether the `≤ 6` ceiling still holds. If it no longer holds, the ceiling —
and therefore the Spec's stated budget — is what must be renegotiated, not the assertion.

### Step 6 — Verify and record

```bash
php artisan test --filter=AdminTriageServiceTest
php artisan test --filter=AdminTriageLandingTest
php artisan test --filter=ReportingPeriodTest   # run if the period handling changed at all
./vendor/bin/pint --test --no-interaction
php artisan test                                 # full suite: 0 failures, 0 new skips
```

Close the loop in the documents that own the contract:

| Artifact | Update |
| --- | --- |
| `spec/spec-admin-triage-landing.md` | the new figure's requirement, acceptance criteria, handle row and budget row |
| `docs/checklist/checklist-admin-triage-landing.md` | the new cases and the requirement → criterion → case map |
| `docs/reference/ref-admin-triage-landing.md` | the figure table, the handle table, the deep-link table and the budget table |
| `docs/ARCHITECTURE.md` | only if a new directory, module or public seam appeared |

## 4. Anti-Patterns the Seams Will Catch

| Anti-pattern | What fails |
| --- | --- |
| Building the URL by concatenation (`'/sakip/reports?status=submitted'`) | TC-025 asserts equality with `route(...)`, so a hand-built string fails immediately |
| Sending `period` to a target whose filter cannot express it | TC-039/TC-040 assert the anchor equals the status-filtered route, and that `period=` never appears |
| Counting with a new status literal inline in the controller | the count lives in the service; S2 has no controller to test |
| `whereYear('period', …)` on the verification count | the Spec forbids it: the column is `YYYY-MM`, so `whereBetween` is the index-friendly form |
| Bypassing `InstansiScope` or spreading coverage across two columns | TC-027 and the tenancy guard greps; the pre-flight grep for `withoutGlobalScope`/`withoutInstansiScope`/`whereRaw` |
| Adding the figure without its attention signal or its empty-state clause | the strip and empty-state cases assert mutual exclusion, not just presence |
| Leaving TC-052 untouched | the budget case fails as soon as the new statement is issued — which is the point |

## 5. Done Checklist

- [ ] The Spec and the checklist carry the new requirement, criteria and cases **before** the code change.
- [ ] S2 asserts the population against the fixture matrix and the exact `route()` for the deep link, with no `actingAs()`.
- [ ] S3 asserts the handle exactly once, the name once inside its anchor, the basis label, and the deep-link parameters.
- [ ] The attention strip and the empty-state condition both account for the new queue.
- [ ] TC-052's exact values are re-measured and the ceiling is still satisfied.
- [ ] The full suite is green with no new skips, and Pint reports no violations.
- [ ] The reference document's figure, handle, deep-link and budget tables are updated.
