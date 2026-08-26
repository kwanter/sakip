<?php

namespace App\Constants;

/**
 * Report Status Constants
 *
 * Defines standard status values for reports to avoid magic strings
 * and provide type safety throughout the application.
 */
class ReportStatus
{
    const DRAFT = 'draft';
    const SUBMITTED = 'submitted';
    const UNDER_REVIEW = 'under_review';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const PUBLISHED = 'published';
    const ARCHIVED = 'archived';

    /**
     * Get all available statuses as an array
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::UNDER_REVIEW,
            self::APPROVED,
            self::REJECTED,
            self::PUBLISHED,
            self::ARCHIVED,
        ];
    }

    /**
     * Check if a status is valid
     *
     * @param string $status
     * @return bool
     */
    public static function isValid(string $status): bool
    {
        return in_array($status, self::all());
    }
}
