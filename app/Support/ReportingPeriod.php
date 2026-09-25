<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Periode Pelaporan — the annual, quarterly, or monthly window that scopes a triage figure.
 *
 * Single source of truth for "what does this period mean" (CONTEXT.md: Periode Pelaporan).
 * Spec: spec/spec-admin-triage-landing.md v1.3 §4.1 (seam S1).
 */
final readonly class ReportingPeriod
{
    public const DEFAULT_KEY = 'current_year';

    /** @var list<string> */
    public const KEYS = ['current_year', 'current_quarter', 'last_quarter', 'current_month', 'last_month'];

    private function __construct(
        public string $key,
        public CarbonImmutable $start, // inclusive, at 00:00:00
        public CarbonImmutable $end,   // inclusive, at 23:59:59
    ) {}

    /**
     * Unknown, empty, null, or non-listed keys resolve to DEFAULT_KEY and never throw (REQ-002).
     */
    public static function fromKey(?string $key, ?CarbonImmutable $now = null): self
    {
        $key = in_array($key, self::KEYS, true) ? $key : self::DEFAULT_KEY;
        $anchor = self::anchor($now);

        [$start, $end] = match ($key) {
            'current_quarter' => [$anchor->startOfQuarter(), $anchor->endOfQuarter()],
            'last_quarter' => [$anchor->subQuarter()->startOfQuarter(), $anchor->subQuarter()->endOfQuarter()],
            'current_month' => [$anchor->startOfMonth(), $anchor->endOfMonth()],
            'last_month' => [$anchor->subMonth()->startOfMonth(), $anchor->subMonth()->endOfMonth()],
            default => [$anchor->startOfYear(), $anchor->endOfYear()],
        };

        return new self($key, $start, $end);
    }

    public static function default(?CarbonImmutable $now = null): self
    {
        return self::fromKey(self::DEFAULT_KEY, $now);
    }

    /**
     * Anchor instant (REQ-015, C-7 precedence): an explicitly injected clock is authoritative and
     * ignores the config seam; only when no clock is injected does config('sakip.reporting.active_year')
     * replace the clock's year (it is stored as a string by env(), hence the cast).
     */
    public static function anchor(?CarbonImmutable $now = null): CarbonImmutable
    {
        $configuredYear = config('sakip.reporting.active_year');

        if ($now !== null) {
            return $now;
        }

        $anchor = CarbonImmutable::now();

        return $configuredYear === null ? $anchor : $anchor->setYear((int) $configuredYear);
    }

    /** Calendar year of the anchor (REQ-015). */
    public static function activeYear(?CarbonImmutable $now = null): int
    {
        return self::anchor($now)->year;
    }

    /** Indonesian label: 'Tahun 2026' | 'Triwulan III 2026' | 'September 2026'. */
    public function label(): string
    {
        return match ($this->key) {
            'current_quarter', 'last_quarter' => 'Triwulan '.$this->quarterNumeral().' '.$this->start->year,
            'current_month', 'last_month' => $this->start->locale('id')->translatedFormat('F').' '.$this->start->year,
            default => 'Tahun '.$this->start->year,
        };
    }

    /** True only for current_year: the selected range covers a whole calendar year. */
    public function isYearScoped(): bool
    {
        return $this->key === 'current_year';
    }

    /** True for current_month and last_month: the range collapses into exactly one YYYY-MM value. */
    public function isSingleMonth(): bool
    {
        return in_array($this->key, ['current_month', 'last_month'], true);
    }

    /**
     * Bounds for performance_data.period, a string(7) YYYY-MM column (REQ-006).
     *
     * @return array{0: string, 1: string}
     */
    public function performancePeriodRange(): array
    {
        return [$this->start->format('Y-m'), $this->end->format('Y-m')];
    }

    /** Carbon's `id` locale exposes no quarter token, so the numeral is rendered here. */
    private function quarterNumeral(): string
    {
        return match ($this->start->quarter) {
            1 => 'I',
            2 => 'II',
            3 => 'III',
            default => 'IV',
        };
    }
}
