<?php

namespace App\Services;

use App\Constants\SystemRoles;
use App\Models\Assessment;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\Report;
use App\Models\User;
use App\Support\AdminTriageSummary;
use App\Support\ReportingPeriod;
use App\Support\TriageScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * The instansi-scoped triage read model (Spec v1.3 §4.2, seam S2).
 *
 * Read-only: it resolves one viewer's three queue counts and the deep links that describe them.
 */
final class AdminTriageService
{
    /** Filter value honoured by AssessmentController@index and matching Assessment::pending(). */
    private const ASSESSMENT_PENDING_STATUS = 'pending';

    /** Filter value honoured by DataCollectionController@index. */
    private const VERIFICATION_SUBMITTED_STATUS = 'submitted';

    /** Filter value honoured by ReportController@index and matching Report::submitted(). */
    private const REPORT_SUBMITTED_STATUS = 'submitted';

    public function summaryFor(User $user, ReportingPeriod $period): AdminTriageSummary
    {
        $state = $this->resolveScope($user);

        // An unassigned viewer is entitled to no agency-scoped data: short-circuit without any count query.
        $counts = $state['scope'] === TriageScope::Unassigned
            ? ['verification' => 0, 'assessment' => 0, 'report' => 0]
            : [
                'verification' => $this->verificationQuery($period, $state['instansi_id'])->count(),
                'assessment' => $this->assessmentQuery($state['instansi_id'])->count(),
                'report' => $this->reportQuery($state['instansi_id'])->count(),
            ];

        return new AdminTriageSummary(
            period: $period,
            periodLabel: $period->label(),
            scope: $state['scope'],
            scopeLabel: $state['label'],
            verificationCount: $counts['verification'],
            assessmentCount: $counts['assessment'],
            reportCount: $counts['report'],
            verificationUrl: $this->verificationUrl($period),
            assessmentUrl: $this->assessmentUrl(),
            reportUrl: $this->reportUrl(),
        );
    }

    /**
     * Antrean Verifikasi count for one viewer; a null period means the default period.
     * Shares one implementation with {@see summaryFor()} so the figure and the Phase-2 badge agree.
     * It resolves the scope without the agency-name lookup, because a badge never renders that label.
     */
    public function verificationCountFor(User $user, ?ReportingPeriod $period = null): int
    {
        $period ??= ReportingPeriod::default();
        $state = $this->resolveScopeKind($user);

        if ($state['scope'] === TriageScope::Unassigned) {
            return 0;
        }

        return $this->verificationQuery($period, $state['instansi_id'])->count();
    }

    /**
     * Cakupan Instansi for one viewer in the fixed order of §4.2, without the agency-name lookup.
     *
     * The count path uses this form so that a badge render does not spend a second statement on a
     * label it never shows — the §4.4 ceiling depends on it.
     *
     * @return array{scope: TriageScope, instansi_id: ?string}
     */
    private function resolveScopeKind(User $user): array
    {
        if ($user->hasRole(SystemRoles::SUPER_ADMIN)) {
            return ['scope' => TriageScope::CrossAgency, 'instansi_id' => null];
        }

        if ($user->instansi_id !== null) {
            return ['scope' => TriageScope::Agency, 'instansi_id' => $user->instansi_id];
        }

        return ['scope' => TriageScope::Unassigned, 'instansi_id' => null];
    }

    /**
     * Cakupan Instansi plus the label rendered on screen. A7: a soft-deleted agency must still name
     * itself, and when no name resolves at all the viewer is treated as unassigned so that a scope
     * label is never empty.
     *
     * @return array{scope: TriageScope, label: string, instansi_id: ?string}
     */
    private function resolveScope(User $user): array
    {
        $state = $this->resolveScopeKind($user);

        if ($state['scope'] !== TriageScope::Agency) {
            return $state + ['label' => (string) $state['scope']->defaultLabel()];
        }

        // Label-only carve-out: the withTrashed() lookup never participates in a count.
        $label = Instansi::withTrashed()->whereKey($state['instansi_id'])->value('nama_instansi');

        if (is_string($label) && $label !== '') {
            return ['scope' => TriageScope::Agency, 'label' => $label, 'instansi_id' => $state['instansi_id']];
        }

        return [
            'scope' => TriageScope::Unassigned,
            'label' => (string) TriageScope::Unassigned->defaultLabel(),
            'instansi_id' => null,
        ];
    }

    /** @return Builder<PerformanceData> */
    private function verificationQuery(ReportingPeriod $period, ?string $instansiId): Builder
    {
        return PerformanceData::query()
            ->whereBetween('period', $period->performancePeriodRange())
            ->submitted()
            ->when($instansiId !== null, fn (Builder $query) => $query->where('instansi_id', $instansiId));
    }

    /**
     * Antrean Asesmen is not period-scoped (A1) and always carries the canonical population:
     * an assessment whose parent PerformanceData row is soft-deleted is not pending work (A8/D-S9).
     *
     * @return Builder<Assessment>
     */
    private function assessmentQuery(?string $instansiId): Builder
    {
        $query = Assessment::query()->pending();

        if ($instansiId === null) {
            return $query->whereHas('performanceData');
        }

        return $query->whereHas(
            'performanceData',
            fn (Builder $parent) => $parent->where('performance_data.instansi_id', $instansiId),
        );
    }

    /** @return Builder<Report> */
    private function reportQuery(?string $instansiId): Builder
    {
        return Report::query()
            ->submitted()
            ->when($instansiId !== null, fn (Builder $query) => $query->where('instansi_id', $instansiId));
    }

    /** The only target whose filter can express the selection exactly, so only it ever gets `period`. */
    private function verificationUrl(ReportingPeriod $period): string
    {
        $parameters = ['validation_status' => self::VERIFICATION_SUBMITTED_STATUS];

        if ($period->isSingleMonth()) {
            $parameters['period'] = $period->performancePeriodRange()[0];
        }

        return route('sakip.data-collection.index', $parameters);
    }

    private function assessmentUrl(): string
    {
        return route('sakip.assessments.index', ['status' => self::ASSESSMENT_PENDING_STATUS]);
    }

    private function reportUrl(): string
    {
        return route('sakip.reports.index', ['status' => self::REPORT_SUBMITTED_STATUS]);
    }
}
