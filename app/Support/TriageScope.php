<?php

namespace App\Support;

/**
 * Cakupan Instansi — the agency coverage of a figure or screen (CONTEXT.md).
 *
 * Spec: spec/spec-admin-triage-landing.md v1.3 §4.1 (seam S1).
 */
enum TriageScope: string
{
    case CrossAgency = 'cross_agency'; // Semua Instansi
    case Agency = 'agency';            // the viewer's own agency, named on screen
    case Unassigned = 'unassigned';    // Instansi Belum Ditetapkan

    /** The two canonical CONTEXT.md labels; null for Agency, whose label is the agency name. */
    public function defaultLabel(): ?string
    {
        return match ($this) {
            self::CrossAgency => 'Semua Instansi',
            self::Unassigned => 'Instansi Belum Ditetapkan',
            self::Agency => null,
        };
    }
}
