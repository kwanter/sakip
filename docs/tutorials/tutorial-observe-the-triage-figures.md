---
title: "Tutorial: Observe the Triage Figures with the Canonical Fixture"
version: 1.0
date_created: 2026-09-26
last_updated: 2026-09-26
status: Active
quadrant: Tutorial (Diátaxis)
upstream_spec: spec/spec-admin-triage-landing.md (v1.5, §5.0 canonical fixture matrix)
upstream_reference: docs/reference/ref-admin-triage-landing.md (§5, §6)
living_examples:
  - tests/Unit/Services/AdminTriageServiceTest.php (the canonical fixture builder, TC-014 … TC-017)
  - tests/Unit/Support/ReportingPeriodTest.php (the frozen-clock idiom)
generated_by: /tdd-generate-docs
---

<!-- markdownlint-disable -->

# Tutorial: Observe the Triage Figures with the Canonical Fixture

In this tutorial you will build the canonical fixture the project uses to reason about the triage landing, and watch the same code produce four different answers for four different viewers. You will
finish knowing — because you saw it — how `Periode Pelaporan`, `Cakupan Instansi` and the three `Antrean` counts fit together.

You need no prior knowledge of this feature. You need a terminal, a working PHP toolchain, and about ten minutes.

## 1. Prerequisites

| Requirement | Check |
| --- | --- |
| PHP 8.3 or newer | `php -v` |
| Dependencies installed | `composer install` has been run in the project root |
| The suite can run | `php artisan test --filter=ReportingPeriodTest` reports green — this seam is a pure unit test, so it proves the toolchain without touching a database |

> [!IMPORTANT]
> You will create one **scratch** test file. It is an exercise, not a deliverable: Step 5 deletes it. Never commit it — the project's floor-guard forbids leaving exploratory files in a change.

## 2. Step 1 — Create the Scratch Test

Create the file `tests/Feature/ScratchTriageExploreTest.php` and paste this. It freezes the clock so the period labels are stable, and reuses the project's canonical fixture builder — the same one
`AdminTriageServiceTest::buildCanonicalFixture()` uses:

```php
<?php

namespace Tests\Feature;

use App\Constants\SystemRoles;
use App\Models\Assessment;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminTriageService;
use App\Support\ReportingPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScratchTriageExploreTest extends TestCase
{
    private AdminTriageService $service;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AdminTriageService;
        $this->now = CarbonImmutable::parse('2026-09-24 10:15:00');
        CarbonImmutable::setTestNow($this->now);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /** The three viewers, the same dataset, three different answers. */
    public function test_three_viewer_states_over_one_dataset(): void
    {
        [$a, $b] = $this->buildCanonicalFixture();
        $period = ReportingPeriod::fromKey('current_year', $this->now);

        $cross = $this->service->summaryFor($this->superAdmin(), $period);
        $agencyA = $this->service->summaryFor($this->agencyViewer($a), $period);
        $agencyB = $this->service->summaryFor($this->agencyViewer($b), $period);
        $unassigned = $this->service->summaryFor($this->unassignedViewer(), $period);

        dump([
            'cross' => [$cross->scopeLabel, $cross->verificationCount, $cross->assessmentCount, $cross->reportCount],
            'A' => [$agencyA->scopeLabel, $agencyA->verificationCount, $agencyA->assessmentCount, $agencyA->reportCount],
            'B' => [$agencyB->scopeLabel, $agencyB->verificationCount, $agencyB->assessmentCount, $agencyB->reportCount],
            'unassigned' => [$unassigned->scopeLabel, $unassigned->verificationCount, $unassigned->assessmentCount, $unassigned->reportCount],
        ]);

        $this->assertSame([5, 4, 3], [$cross->verificationCount, $cross->assessmentCount, $cross->reportCount]);
        $this->assertSame([2, 3, 1], [$agencyA->verificationCount, $agencyA->assessmentCount, $agencyA->reportCount]);
        $this->assertSame([3, 1, 2], [$agencyB->verificationCount, $agencyB->assessmentCount, $agencyB->reportCount]);
        $this->assertSame([0, 0, 0], [$unassigned->verificationCount, $unassigned->assessmentCount, $unassigned->reportCount]);
    }
```

Do not run it yet: the fixture builder and the viewer factories come next.

## 3. Step 2 — Add the Canonical Fixture

Append the rest of the class. The fixture is the exact builder from `AdminTriageServiceTest`, trimmed in nothing: two agencies, five live submitted performance-data rows (one of them outside the
current year), one soft-deleted parent, four pending assessments and four reports in three different statuses.

```php
    /** Builds the §5.0 matrix. @return array{0: Instansi, 1: Instansi} */
    private function buildCanonicalFixture(): array
    {
        $a = Instansi::factory()->create(['nama_instansi' => 'Dinas A']);
        $b = Instansi::factory()->create(['nama_instansi' => 'Dinas B']);

        // performance_data carries a unique (indicator, instansi, period) index, so rows sharing a
        // period must still differ by indicator — the matrix never constrains the indicator.
        for ($row = 0; $row < 2; $row++) {
            PerformanceData::factory()->submitted()->forInstansi($a->id)->forPeriod('2026-03')->create([
                'performance_indicator_id' => $this->newIndicatorFor($a),
            ]);
        }

        for ($row = 0; $row < 3; $row++) {
            PerformanceData::factory()->submitted()->forInstansi($b->id)->forPeriod('2026-05')->create([
                'performance_indicator_id' => $this->newIndicatorFor($b),
            ]);
        }

        PerformanceData::factory()->submitted()->forInstansi($a->id)->forPeriod('2025-03')->create([
            'performance_indicator_id' => $this->newIndicatorFor($a),
        ]);

        $trashedParent = PerformanceData::factory()->submitted()->forInstansi($a->id)->forPeriod('2026-04')->create([
            'performance_indicator_id' => $this->newIndicatorFor($a),
        ]);
        Assessment::factory()->pending()->forPerformanceData($trashedParent->id)->create();
        $trashedParent->delete();

        foreach ($this->livePerformanceDataOf($a) as $row) {
            Assessment::factory()->pending()->forPerformanceData($row->id)->create();
        }

        Assessment::factory()->pending()
            ->forPerformanceData($this->livePerformanceDataOf($b)->first()->id)
            ->create();

        $author = User::factory()->create(['instansi_id' => $a->id]);

        $this->newReportFor($a, 'submitted', $author->id);
        $this->newReportFor($a, 'pending', $author->id);
        $this->newReportFor($b, 'submitted', $author->id);
        $this->newReportFor($b, 'submitted', $author->id);

        return [$a, $b];
    }
```

Append the small helpers the builder needs, then the three viewer factories:

```php
    private function newIndicatorFor(Instansi $agency): string
    {
        return PerformanceIndicator::factory()->create(['instansi_id' => $agency->id])->id;
    }

    private function newReportFor(Instansi $agency, string $status, string $authorId): Report
    {
        return Report::forceCreate([
            'id' => (string) Str::uuid(),
            'instansi_id' => $agency->id,
            'generated_by' => $authorId,
            'report_type' => 'quarterly_report',
            'period' => '2026-Q3',
            'status' => $status,
            'generated_at' => now(),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, PerformanceData> */
    private function livePerformanceDataOf(Instansi $agency): \Illuminate\Support\Collection
    {
        return PerformanceData::query()->where('instansi_id', $agency->id)->get();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(
            ['name' => SystemRoles::SUPER_ADMIN],
            ['display_name' => SystemRoles::SUPER_ADMIN],
        ));

        return $user;
    }

    private function agencyViewer(Instansi $agency): User
    {
        return User::factory()->create(['instansi_id' => $agency->id]);
    }

    private function unassignedViewer(): User
    {
        return User::factory()->create(['instansi_id' => null]);
    }
}
```

## 4. Step 3 — Run It and Read the Four Answers

```bash
php artisan test --filter=ScratchTriageExploreTest
```

The test passes, and the `dump()` prints four rows. Read them in this order:

| Viewer | Label | Verifikasi | Asesmen | Laporan | What it tells you |
| --- | --- | --- | --- | --- | --- |
| Cross-agency (`Super Admin`) | `Semua Instansi` | 5 | 4 | 3 | every agency, so the counts are the whole dataset |
| Agency-bound — Dinas A | `Dinas A` | 2 | 3 | 1 | only A's rows, and only those inside the selected period |
| Agency-bound — Dinas B | `Dinas B` | 3 | 1 | 2 | the same code, the other agency |
| Unassigned | `Instansi Belum Ditetapkan` | 0 | 0 | 0 | no agency assignment means no agency-scoped data |

Four facts to notice:

1. **The verification figure respects the period; the other two do not.** Dinas A has three submitted rows in total but only two inside `Tahun 2026` — the 2025 row is outside the window. The assessment
   and report figures never filter by period, which is why the fixture's 2026-Q3 report counts even under a year window.
2. **The cross-agency total equals the sum of the two agencies** for every queue (5 = 2 + 3, 4 = 3 + 1, 3 = 1 + 2) — that invariant is what makes the numbers trustworthy.
3. **Four assessments are counted, not five.** The fifth one sits on a performance-data row that was soft-deleted, and a trashed parent is not pending work. Its child left the queue with it.
4. **The label is never empty.** `Semua Instansi` is reserved for the cross-agency viewer; a viewer with no agency gets `Instansi Belum Ditetapkan` instead, and no agency-scoped data.

## 5. Step 4 — Switch the Period and Watch What Moves

Add one more test method to the same class. It asks the same service for the same viewer twice, with two different periods, and prints the deep links as well as the counts:

```php
    /** A period switch moves the verification figure only, and only it is deep-linked with a period. */
    public function test_a_period_switch_moves_only_the_verification_figure(): void
    {
        [$a] = $this->buildCanonicalFixture();
        $viewer = $this->agencyViewer($a);

        $yearly = $this->service->summaryFor($viewer, ReportingPeriod::fromKey('current_year', $this->now));
        $monthly = $this->service->summaryFor($viewer, ReportingPeriod::fromKey('current_month', $this->now));

        dump([
            'year' => [$yearly->periodLabel, $yearly->verificationCount, $yearly->verificationUrl],
            'month' => [$monthly->periodLabel, $monthly->verificationCount, $monthly->verificationUrl],
            'assessment_url' => $monthly->assessmentUrl,
            'report_url' => $monthly->reportUrl,
        ]);

        $this->assertSame(2, $yearly->verificationCount);
        $this->assertSame(0, $monthly->verificationCount, 'September holds none of Dinas A\'s rows');

        $this->assertSame($yearly->assessmentCount, $monthly->assessmentCount);
        $this->assertSame($yearly->reportCount, $monthly->reportCount);

        $this->assertStringNotContainsString('period=', $yearly->verificationUrl, 'a year cannot be addressed exactly');
        $this->assertStringContainsString('period=2026-09', $monthly->verificationUrl, 'a month can');
        $this->assertStringNotContainsString('period=', $monthly->assessmentUrl);
        $this->assertStringNotContainsString('period=', $monthly->reportUrl);
    }
```

Run the scratch test again:

```bash
php artisan test --filter=ScratchTriageExploreTest
```

Read the dumped links. They show the rule the landing follows: **the period travels only where the target's filter can express it**. The verification link carries `validation_status=submitted` and, for a
single month, `period=2026-09` — because that queue's filter accepts an exact month. The assessment and report links carry only their status, because their targets cannot express the selection honestly.

## 6. Step 5 — Clean Up

Delete the scratch file and confirm the working tree is clean again:

```bash
rm tests/Feature/ScratchTriageExploreTest.php
git status --short
```

`git status --short` should print nothing. The exercise is over; the canonical fixture lives on in `tests/Unit/Services/AdminTriageServiceTest.php`, and the landing's rendered behaviour is pinned by
`tests/Feature/AdminTriageLandingTest.php`.

## 7. What You Have Learned

| Term (as ratified in `CONTEXT.md`) | What you observed |
| --- | --- |
| **Periode Pelaporan** | one vocabulary of five keys resolves to exact windows and Indonesian labels; an unknown value falls back to `current_year` instead of erroring |
| **Cakupan Instansi** | the same code answers three ways: every agency, exactly one agency, or no agency-scoped data at all |
| **Semua Instansi** | reserved for the cross-agency viewer; the sum invariant makes the totals verifiable |
| **Instansi Belum Ditetapkan** | a viewer with no agency gets zeros and its own label — never `Semua Instansi` |
| **Antrean Verifikasi** | the only period-scoped figure, and the only one whose deep link can carry the exact month |
| **Antrean Asesmen** | period-independent by decision: its population follows the parent performance-data row, and a soft-deleted parent removes the assessment from the queue |
| **Antrean Laporan** | period-independent, status-filtered, no period parameter ever sent |

## 8. Where to Go Next

| If you want to… | Read |
| --- | --- |
| see every handle, label, count and budget the page exposes | `docs/reference/ref-admin-triage-landing.md` |
| understand *why* the assessment figure is not period-scoped, or why the badge is a composer | `docs/explanation/explanation-triage-landing-decisions.md` |
| pin the reporting year in a deployment | `docs/how-to/how-to-configure-the-active-reporting-year.md` |
| add a fourth queue figure the right way | `docs/how-to/how-to-add-a-queue-figure-to-the-triage-landing.md` |
| read the contract the code must satisfy | `spec/spec-admin-triage-landing.md` |
