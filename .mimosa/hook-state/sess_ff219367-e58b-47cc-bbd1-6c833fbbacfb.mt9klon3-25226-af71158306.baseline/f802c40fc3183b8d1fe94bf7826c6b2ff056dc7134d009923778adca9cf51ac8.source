<?php

namespace App\Constants;

/**
 * Assessment Status Constants
 *
 * Defines standard status values for assessments
 */
class AssessmentStatus
{
    const DRAFT = 'draft';
    const SUBMITTED = 'submitted';
    const IN_REVIEW = 'in_review';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const REVISED = 'revised';

    /**
     * Get all available statuses
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::IN_REVIEW,
            self::APPROVED,
            self::REJECTED,
            self::REVISED,
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
