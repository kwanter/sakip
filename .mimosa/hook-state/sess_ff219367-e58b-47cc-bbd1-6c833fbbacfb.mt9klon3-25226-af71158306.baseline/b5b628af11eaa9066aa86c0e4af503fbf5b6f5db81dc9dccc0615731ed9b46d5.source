<?php

namespace App\Constants;

/**
 * Status Constants
 *
 * Provides centralized status values to eliminate magic strings
 * and ensure consistency across the application.
 *
 * All status values use lowercase English with underscores for consistency.
 */
class Status
{
    // General statuses
    public const DRAFT = 'draft';
    public const ACTIVE = 'aktif';
    public const COMPLETED = 'selesai';
    public const INACTIVE = 'tidak_aktif';
    public const PENDING = 'pending';
    public const CANCELLED = 'dibatalkan';

    // Performance data statuses
    public const SUBMITTED = 'submitted';
    public const VALIDATED = 'validated';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    // Assessment statuses
    public const ASSESSED = 'assessed';
    public const REVIEWED = 'reviewed';

    // Report statuses
    public const GENERATED = 'generated';
    public const PUBLISHED = 'published';
    public const ARCHIVED = 'archived';

    /**
     * Get all available statuses
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::ACTIVE,
            self::COMPLETED,
            self::INACTIVE,
            self::PENDING,
            self::CANCELLED,
            self::SUBMITTED,
            self::VALIDATED,
            self::APPROVED,
            self::REJECTED,
        ];
    }

    /**
     * Get all workflow statuses (draft, active, completed)
     */
    public static function workflow(): array
    {
        return [
            self::DRAFT,
            self::ACTIVE,
            self::COMPLETED,
        ];
    }

    /**
     * Get all approval statuses
     */
    public static function approval(): array
    {
        return [
            self::PENDING,
            self::APPROVED,
            self::REJECTED,
        ];
    }
}
