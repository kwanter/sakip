<?php

namespace App\Traits;

/**
 * Whitelist-based sorting to prevent 500s (and odd behavior) from
 * client-supplied sort_by values naming nonexistent columns.
 */
trait SortsSafely
{
    /**
     * @param  string|null  $requested  Client-supplied sort column
     * @param  array<string>  $allowed  Allowed column names (no table prefix)
     * @param  string  $fallback  Default when the request is absent/unknown
     */
    protected function safeSortColumn(?string $requested, array $allowed, string $fallback): string
    {
        if ($requested === null) {
            return $fallback;
        }
        // Allow an optional "table." prefix when matching.
        $bare = strtolower(str_contains($requested, '.') ? substr($requested, (int) strrpos($requested, '.') + 1) : $requested);

        foreach ($allowed as $candidate) {
            if (strtolower($candidate) === $bare || str_ends_with(strtolower($candidate), '.' . $bare)) {
                return $candidate;
            }
        }

        return $fallback;
    }
}
