<?php

namespace Tests\Unit\Services;

use App\Services\Sakip\SakipService;
use App\Services\SakipDashboardService;
use App\Support\ReportingPeriod;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Tests\TestCase;

/**
 * Seam: `SakipDashboardService::getDateRange()` as an adapter over `ReportingPeriod`
 * (Spec v1.3 §3.3, REQ-014). Case: TC-068 — the mutable `Carbon` pair the five call sites rely on.
 *
 * The protected method is exercised through an anonymous subclass, so its visibility is untouched.
 */
class DashboardDateRangeAdapterTest extends TestCase
{
    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-09-24 10:15:00');
        CarbonImmutable::setTestNow($this->now);
        Carbon::setTestNow($this->now);
    }

    protected function tearDown(): void
    {
        config()->set('sakip.reporting.active_year', null);
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /** TC-068 / REQ-014 — the mutable pair and its inclusive day bounds survive for all five keys. */
    public function test_adapter_preserves_the_mutable_carbon_pair_contract(): void
    {
        $adapter = $this->adapter();

        foreach (ReportingPeriod::KEYS as $key) {
            $range = $adapter->dateRangeFor($key);
            $expected = ReportingPeriod::fromKey($key, $this->now);

            $this->assertInstanceOf(Carbon::class, $range[0], "start of {$key}");
            $this->assertInstanceOf(Carbon::class, $range[1], "end of {$key}");
            $this->assertNotInstanceOf(CarbonImmutable::class, $range[0], "start of {$key} must stay mutable");
            $this->assertSame($expected->start->toDateTimeString(), $range[0]->toDateTimeString(), "start of {$key}");
            $this->assertSame($expected->end->toDateTimeString(), $range[1]->toDateTimeString(), "end of {$key}");
            $this->assertSame('00:00:00', $range[0]->format('H:i:s'), "start time of {$key}");
            $this->assertSame('23:59:59', $range[1]->format('H:i:s'), "end time of {$key}");
        }
    }

    /** TC-068 / REQ-014 — the returned instances are independent, so call sites may mutate them. */
    public function test_returned_instances_are_not_shared_between_calls(): void
    {
        $adapter = $this->adapter();

        $first = $adapter->dateRangeFor('current_year');
        $first[0]->addDay();

        $second = $adapter->dateRangeFor('current_year');

        $this->assertSame('2026-01-01 00:00:00', $second[0]->toDateTimeString());
    }

    /** TC-068 / REQ-015 — the adapter resolves through the one active-year source. */
    public function test_adapter_honours_the_configured_active_year(): void
    {
        config()->set('sakip.reporting.active_year', '2025');

        $range = $this->adapter()->dateRangeFor('current_year');

        $this->assertSame('2025-01-01 00:00:00', $range[0]->toDateTimeString());
        $this->assertSame('2025-12-31 23:59:59', $range[1]->toDateTimeString());
    }

    /** TC-068 — the old adapter's unknown-key fallback must survive the rewrite. */
    public function test_unknown_key_still_falls_back_to_the_current_year(): void
    {
        $range = $this->adapter()->dateRangeFor('not-a-key');

        $this->assertSame('2026-01-01 00:00:00', $range[0]->toDateTimeString());
        $this->assertSame('2026-12-31 23:59:59', $range[1]->toDateTimeString());
    }

    private function adapter(): SakipDashboardService
    {
        return new class(new SakipService) extends SakipDashboardService
        {
            /** @return array{0: Carbon, 1: Carbon} */
            public function dateRangeFor(string $period): array
            {
                return $this->getDateRange($period);
            }
        };
    }
}
