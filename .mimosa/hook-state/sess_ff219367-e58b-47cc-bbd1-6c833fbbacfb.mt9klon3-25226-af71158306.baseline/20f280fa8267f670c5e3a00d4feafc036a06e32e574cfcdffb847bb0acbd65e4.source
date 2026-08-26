<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAKIP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings specific to the SAKIP
    | (Sistem Akuntabilitas Kinerja Instansi Pemerintah) module.
    |
    */

    "performance" => [
        /*
         |--------------------------------------------------------------------------
         | Performance Thresholds
         |--------------------------------------------------------------------------
         |
         | Threshold values for calculating performance ratings and classifications.
         | These are used to determine if performance is excellent, good, satisfactory,
         | or needs improvement.
         |
         */
        "thresholds" => [
            "excellent" => 100,
            "good" => 80,
            "satisfactory" => 60,
            "minimum" => 0,
        ],

        /*
         |--------------------------------------------------------------------------
         | Performance Calculation Method
         |--------------------------------------------------------------------------
         |
         | Default method for calculating performance percentages.
         | Options: 'simple', 'weighted', 'formula_based'
         |
         */
        "calculation_method" => env("SAKIP_CALCULATION_METHOD", "simple"),

        /*
         |--------------------------------------------------------------------------
         | Maximum Performance Percentage
         |--------------------------------------------------------------------------
         |
         | Maximum allowed performance percentage to prevent unrealistic values.
         | This caps the performance at 200% for exceptional overachievement.
         |
         */
        "max_percentage" => 200,
    ],

    "validation" => [
        /*
         |--------------------------------------------------------------------------
         | File Upload Settings
         |--------------------------------------------------------------------------
         |
         | Configure file upload validation rules for evidence documents
         | and supporting materials.
         |
         */
        "max_file_size" => env("SAKIP_MAX_FILE_SIZE", 10240), // 10MB in KB

        "allowed_mime_types" => [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "image/jpeg",
            "image/png",
            "image/jpg",
        ],

        "allowed_extensions" => [
            "pdf",
            "doc",
            "docx",
            "xls",
            "xlsx",
            "jpg",
            "jpeg",
            "png",
        ],

        /*
         |--------------------------------------------------------------------------
         | Data Quality Validation
         |--------------------------------------------------------------------------
         |
         | Configure automatic data quality validation rules.
         |
         */
        "require_target" => true,
        "require_actual_value" => true,
        "allow_negative_targets" => true,
        "allow_negative_actual" => false,
    ],

    "reporting" => [
        /*
         |--------------------------------------------------------------------------
         | Report Generation Settings
         |--------------------------------------------------------------------------
         |
         | Configure report generation parameters.
         |
         */
        "default_template" => "standard",
        "include_charts" => true,
        "include_evidence_summary" => true,
        "max_report_size_mb" => 50,
    ],

    "assessment" => [
        /*
         |--------------------------------------------------------------------------
         | Assessment Configuration
         |--------------------------------------------------------------------------
         |
         | Configure assessment and evaluation parameters.
         |
         */
        "auto_calculate_score" => true,
        "require_evidence" => true,
        "min_assessors" => 1,
        "max_assessors" => 5,
    ],

    "export" => [
        /*
         |--------------------------------------------------------------------------
         | Export Settings
         |--------------------------------------------------------------------------
         |
         | Configure data export parameters.
         |
         */
        "default_format" => "xlsx",
        "include_metadata" => false,
        "max_rows_per_export" => 10000,
        "enable_csv_sanitize" => true,
    ],

    "audit" => [
        /*
         |--------------------------------------------------------------------------
         | Audit Trail Configuration
         |--------------------------------------------------------------------------
         |
         | Configure audit trail and logging parameters.
         |
         */
        "enabled" => env("SAKIP_AUDIT_ENABLED", true),
        "log_all_changes" => true,
        "log_read_access" => false,
        "retention_days" => 365,
    ],

    "dashboard" => [
        /*
         |--------------------------------------------------------------------------
         | Dashboard Settings
         |--------------------------------------------------------------------------
         |
         | Configure dashboard behavior and data display.
         |
         */
        "cache_enabled" => env("SAKIP_DASHBOARD_CACHE", true),
        "cache_ttl_minutes" => 5,
        "show_trends" => true,
        "trend_period_days" => 30,
    ],
];
