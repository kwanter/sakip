<?php

namespace App\Support;

/**
 * Immutable read-model result for one landing render.
 *
 * Spec: spec/spec-admin-triage-landing.md v1.3 §4.1 (seam S2 return contract).
 */
final readonly class AdminTriageSummary
{
    /** The shared basis label for figures that are deliberately not period-scoped (A1, D9). */
    public const PERIOD_INDEPENDENT_BASIS_LABEL = 'Tidak dibatasi periode';

    public function __construct(
        public ReportingPeriod $period,
        public string $periodLabel,
        public TriageScope $scope,
        public string $scopeLabel,
        public int $verificationCount,
        public int $assessmentCount,
        public int $reportCount,
        public string $verificationUrl,
        public string $assessmentUrl,
        public string $reportUrl,
    ) {}
}
