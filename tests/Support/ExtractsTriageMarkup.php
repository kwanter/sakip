<?php

namespace Tests\Support;

use App\Constants\SystemRoles;
use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

/**
 * The one markup boundary and the viewer factories shared by the triage seam tests (PRN-002).
 *
 * `tests/Feature/AdminTriageLandingTest.php` (seam S3) and `tests/Feature/SidebarQueueBadgeTest.php`
 * (seam S4) both read the same rendered handles, so a second spelling of "the figure" would let the two
 * seams disagree about what they assert while both stayed green.
 */
trait ExtractsTriageMarkup
{
    /** The inner HTML of one `data-triage-figure` anchor; '' when the anchor is absent. */
    private function figureAnchor(string $content, string $handle): string
    {
        preg_match('/<a[^>]*data-triage-figure="'.$handle.'"[^>]*>(.*?)<\/a>/s', $content, $anchor);

        return $anchor[1] ?? '';
    }

    /** The formatted count rendered inside one `data-triage-figure` anchor. */
    private function figureValue(string $content, string $handle): string
    {
        preg_match('/stat-value">([^<]*)</', $this->figureAnchor($content, $handle), $value);

        return trim($value[1] ?? '');
    }

    /** The href of one `data-triage-figure` anchor, with HTML entities decoded. */
    private function figureHref(string $content, string $handle): string
    {
        preg_match('/<a[^>]*data-triage-figure="'.$handle.'"[^>]*>/s', $content, $tag);
        preg_match('/href="([^"]*)"/', $tag[0] ?? '', $href);

        return html_entity_decode($href[1] ?? '');
    }

    /** The sidebar badge value; '' when no badge is rendered. */
    private function badgeValue(string $content): string
    {
        preg_match('/sidebar-link-badge">\s*([^<]*?)\s*</s', $content, $matches);

        return trim($matches[1] ?? '');
    }

    /** Counts data rows in the first table body, ignoring the colspan'd empty-state row. */
    private function renderedRowCount(string $content): int
    {
        preg_match('/<tbody>(.*?)<\/tbody>/s', $content, $body);
        $rows = substr_count($body[1] ?? '', '<tr');

        return str_contains($body[1] ?? '', 'colspan') ? max(0, $rows - 1) : $rows;
    }

    /** A verified holder of `admin.dashboard` that is deliberately NOT a Super Admin (finding C1). */
    private function permittedViewer(): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => null]);

        $permission = Permission::firstOrCreate(
            ['name' => 'admin.dashboard'],
            ['display_name' => 'admin.dashboard'],
        );

        $user->givePermissionTo($permission);

        return $user->refresh();
    }

    private function permittedViewerFor(Instansi $agency): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => $agency->id]);
        $user->givePermissionTo(Permission::firstOrCreate(
            ['name' => 'admin.dashboard'],
            ['display_name' => 'admin.dashboard'],
        ));

        return $user->refresh();
    }

    /** A landing viewer that ALSO holds the two target permissions its deep links point at. */
    private function targetViewerFor(Instansi $agency): User
    {
        $user = $this->permittedViewerFor($agency);

        foreach (['view-performance-data', 'view-assessment-reports'] as $permission) {
            $user->givePermissionTo(Permission::firstOrCreate(
                ['name' => $permission],
                ['display_name' => $permission],
            ));
        }

        return $user->refresh();
    }

    private function superAdminViewer(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::firstOrCreate(
            ['name' => 'admin.dashboard'],
            ['display_name' => 'admin.dashboard'],
        ));
        $user->assignRole(Role::firstOrCreate(
            ['name' => SystemRoles::SUPER_ADMIN],
            ['display_name' => SystemRoles::SUPER_ADMIN],
        ));

        return $user->refresh();
    }
}
