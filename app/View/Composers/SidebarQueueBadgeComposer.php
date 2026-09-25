<?php

namespace App\View\Composers;

use App\Services\AdminTriageService;
use App\Support\ReportingPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Provides the sidebar queue badge for every page that renders `layouts.modern`
 * (Spec v1.3 §4.5, seam S4).
 *
 * The period is resolved from the request, so the badge agrees with the landing figure even on a
 * non-default period (US-005 scenario 1), and it shares one count implementation with the landing.
 */
final class SidebarQueueBadgeComposer
{
    public function __construct(
        private readonly AdminTriageService $triageService,
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $user = $this->request->user();

        if ($user === null) {
            return; // guest pages (login, verification notice) render no badge
        }

        $rawPeriod = $this->request->query('period');
        $period = ReportingPeriod::fromKey(is_string($rawPeriod) ? $rawPeriod : null);

        $view->with('pendingDataCount', $this->triageService->verificationCountFor($user, $period));
    }
}
