<?php

namespace Tests\Unit\Support;

use App\Support\ReportingPeriod;
use Carbon\CarbonImmutable;
use Tests\TestCase;

/**
 * Seam S1 — the Periode Pelaporan resolver (Spec v1.3 §4.1; REQ-001, REQ-002, REQ-015).
 *
 * Pure unit seam: no database, no HTTP, no authentication (Spec §6.2).
 * Cases: TC-001 … TC-012 of docs/checklist/checklist-admin-triage-landing.md.
 */
class ReportingPeriodTest extends TestCase
{
    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-09-24 10:15:00');
        CarbonImmutable::setTestNow($this->now);
    }

    protected function tearDown(): void
    {
        config()->set('sakip.reporting.active_year', null);
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /** TC-001 / AC-001 — the year key resolves its calendar window, label and period range. */
    public function test_current_year_resolves_calendar_bounds_label_and_performance_range(): void
    {
        $period = ReportingPeriod::fromKey('current_year', $this->now);

        $this->assertSame('current_year', $period->key);
        $this->assertSame('2026-01-01 00:00:00', $period->start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-12-31 23:59:59', $period->end->format('Y-m-d H:i:s'));
        $this->assertSame('Tahun 2026', $period->label());
        $this->assertSame(['2026-01', '2026-12'], $period->performancePeriodRange());
    }

    /** TC-002 / AC-002 — unknown, empty, null or non-listed keys fall back without throwing. */
    public function test_unknown_empty_null_or_non_string_key_falls_back_to_default(): void
    {
        $default = ReportingPeriod::default($this->now);

        foreach ([null, '', 'not-a-key', '2026', 'CURRENT_YEAR'] as $key) {
            $period = ReportingPeriod::fromKey($key, $this->now);

            $this->assertSame(ReportingPeriod::DEFAULT_KEY, $period->key);
            $this->assertSame($default->label(), $period->label());
            $this->assertSame($default->performancePeriodRange(), $period->performancePeriodRange());
        }
    }

    /** TC-003 / AC-003 — quarter and month keys resolve exact bounds with Indonesian labels. */
    public function test_quarter_and_month_keys_resolve_exact_bounds_and_labels(): void
    {
        $expected = [
            'current_quarter' => ['2026-07-01 00:00:00', '2026-09-30 23:59:59', 'Triwulan III 2026'],
            'last_quarter' => ['2026-04-01 00:00:00', '2026-06-30 23:59:59', 'Triwulan II 2026'],
            'current_month' => ['2026-09-01 00:00:00', '2026-09-30 23:59:59', 'September 2026'],
            'last_month' => ['2026-08-01 00:00:00', '2026-08-31 23:59:59', 'Agustus 2026'],
        ];

        foreach ($expected as $key => [$start, $end, $label]) {
            $period = ReportingPeriod::fromKey($key, $this->now);

            $this->assertSame($key, $period->key);
            $this->assertSame($start, $period->start->format('Y-m-d H:i:s'), "start of {$key}");
            $this->assertSame($end, $period->end->format('Y-m-d H:i:s'), "end of {$key}");
            $this->assertSame($label, $period->label(), "label of {$key}");
        }
    }

    /**
     * TC-004 / AC-003 — the single-month shape flag is true for exactly the two month keys.
     *
     * It is the only shape flag the class exposes: `isYearScoped()` was removed because no consumer
     * existed (TASK-304) — the deep-link rule is driven by `isSingleMonth()`, which is the predicate the
     * target filter can actually express.
     */
    public function test_single_month_flag_is_true_only_for_the_month_keys(): void
    {
        foreach (ReportingPeriod::KEYS as $key) {
            $period = ReportingPeriod::fromKey($key, $this->now);

            $this->assertSame(
                in_array($key, ['current_month', 'last_month'], true),
                $period->isSingleMonth(),
                "isSingleMonth of {$key}",
            );
        }
    }

    /** TC-005 — every key resolves inside the anchor year (§6.1 S1 case list). */
    public function test_every_key_resolves_inside_the_anchor_year(): void
    {
        foreach (ReportingPeriod::KEYS as $key) {
            $period = ReportingPeriod::fromKey($key, $this->now);

            $this->assertSame(2026, $period->start->year, "start year of {$key}");
            $this->assertSame(2026, $period->end->year, "end year of {$key}");
        }
    }

    /** TC-006 / REQ-006 — the performance range is an ordered `YYYY-MM` pair for every key. */
    public function test_performance_period_range_is_a_lexicographically_ordered_pair(): void
    {
        foreach (ReportingPeriod::KEYS as $key) {
            [$from, $to] = ReportingPeriod::fromKey($key, $this->now)->performancePeriodRange();

            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $from, "from of {$key}");
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $to, "to of {$key}");
            $this->assertLessThanOrEqual($to, $from, "ordering of {$key}");
        }
    }

    /** TC-007 — the window is inclusive from start of day to end of day. */
    public function test_period_bounds_are_start_of_day_and_end_of_day(): void
    {
        foreach (ReportingPeriod::KEYS as $key) {
            $period = ReportingPeriod::fromKey($key, $this->now);

            $this->assertSame('00:00:00', $period->start->format('H:i:s'), "start time of {$key}");
            $this->assertSame('23:59:59', $period->end->format('H:i:s'), "end time of {$key}");
        }
    }

    /** TC-008 / AC-004 — the config seam is written as the string `env()` really yields. */
    public function test_active_year_accepts_the_configured_string_and_returns_an_int(): void
    {
        config()->set('sakip.reporting.active_year', '2025');

        $this->assertSame(2025, ReportingPeriod::activeYear());
        $this->assertIsInt(ReportingPeriod::activeYear());
        $this->assertSame(2025, ReportingPeriod::fromKey('current_year')->start->year);
    }

    /** TC-009 / AC-004 — a null config key means "use the clock". */
    public function test_active_year_falls_back_to_the_clock_when_config_is_null(): void
    {
        config()->set('sakip.reporting.active_year', null);

        $this->assertSame(2026, ReportingPeriod::activeYear());
        $this->assertSame(2026, ReportingPeriod::fromKey('current_year')->start->year);
    }

    /** TC-010 / AC-039 — an injected clock outranks the configured active year. */
    public function test_explicit_clock_beats_the_configured_active_year(): void
    {
        config()->set('sakip.reporting.active_year', '2025');

        $this->assertSame(2026, ReportingPeriod::activeYear($this->now));
        $this->assertSame(2026, ReportingPeriod::anchor($this->now)->year);
        $this->assertSame(
            '2026-01-01 00:00:00',
            ReportingPeriod::fromKey('current_year', $this->now)->start->format('Y-m-d H:i:s'),
        );
    }

    /** TC-011 / AC-039 — without an injected clock the configured year wins. */
    public function test_configured_active_year_wins_when_no_clock_is_injected(): void
    {
        config()->set('sakip.reporting.active_year', '2025');

        $this->assertSame(2025, ReportingPeriod::activeYear());
        $this->assertSame(2025, ReportingPeriod::anchor()->year);
        $this->assertSame(
            '2025-01-01 00:00:00',
            ReportingPeriod::fromKey('current_year')->start->format('Y-m-d H:i:s'),
        );
    }

    /** TC-012 / AC-022 — only a single-month period can address a deep link's `period` exactly. */
    public function test_deep_link_period_derivation_is_available_only_for_single_month_keys(): void
    {
        foreach (ReportingPeriod::KEYS as $key) {
            $period = ReportingPeriod::fromKey($key, $this->now);
            [$from, $to] = $period->performancePeriodRange();

            if ($period->isSingleMonth()) {
                $this->assertSame($from, $to, "a month key must collapse to one value: {$key}");
                $this->assertSame($period->start->format('Y-m'), $from, "range must equal the start month: {$key}");
            } else {
                $this->assertNotSame($from, $to, "a non-month key must span more than one month: {$key}");
            }
        }
    }

    /**
     * TASK-301 / REQ-003 + PRN-003 — the active-year seam is a trust boundary like any other input.
     *
     * A configured value that is not a whole year inside a sane window must fall back to the clock
     * instead of moving every year-scoped query somewhere absurd: `(int) 'abc'` is 0 today, so an
     * operator typo silently re-points the whole application at year 0.
     *
     * Note on `2026.5`: under this frozen 2026 clock the assertion below cannot tell "rejected" from
     * "truncated to 2026", so the fractional value is probed a second time under a 2027 clock.
     */
    public function test_non_numeric_or_out_of_range_active_year_falls_back_to_the_clock(): void
    {
        foreach (['abc', '0', '-5', '2026.5', '99999'] as $value) {
            config()->set('sakip.reporting.active_year', $value);

            $this->assertSame(2026, ReportingPeriod::activeYear(), "active year for '{$value}'");
            $this->assertSame(
                2026,
                ReportingPeriod::fromKey('current_year')->start->year,
                "window for '{$value}'",
            );
        }

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2027-03-15 09:00:00'));
        config()->set('sakip.reporting.active_year', '2026.5');

        $this->assertSame(2027, ReportingPeriod::activeYear(), 'a fractional year is malformed, not a year');
    }
}
