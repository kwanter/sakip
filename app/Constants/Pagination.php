<?php

namespace App\Constants;

/**
 * Pagination Constants
 *
 * Provides centralized pagination limit values to eliminate magic numbers
 * and ensure consistency across the application.
 */
class Pagination
{
    /**
     * Default pagination limit for most list views
     */
    public const DEFAULT = 15;

    /**
     * Pagination limit for user management
     */
    public const USERS = 20;

    /**
     * Pagination limit for audit logs
     */
    public const AUDIT_LOGS = 50;

    /**
     * Pagination limit for admin section
     */
    public const ADMIN = 15;

    /**
     * Pagination limit for performance data
     */
    public const PERFORMANCE_DATA = 15;

    /**
     * Pagination limit for reports
     */
    public const REPORTS = 20;

    /**
     * Pagination limit for small datasets
     */
    public const SMALL = 10;

    /**
     * Pagination limit for large datasets
     */
    public const LARGE = 50;
}
